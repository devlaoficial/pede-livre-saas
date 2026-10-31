<?php

declare(strict_types=1);

namespace App\Domain\Pedidos\Controllers;

use App\Domain\Pedidos\Models\PedidoItem;
use App\Support\Tenancy\LojaContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class KdsController
{
    public function __construct(
        private readonly LojaContext $context
    ) {}

    /**
     * GET /api/kds/tickets
     * Filters active kitchen items by station (chapa, forno, sushi, bar, geral).
     * Exposes ZERO customer phone, address, or financial totals to kitchen staff.
     */
    public function tickets(Request $request): JsonResponse
    {
        $loja = $this->context->getLoja();
        $estacao = $request->query('estacao');

        $query = PedidoItem::query()
            ->whereIn('status_cozinha', ['fila', 'preparando'])
            ->whereHas('pedido', function ($q) use ($loja): void {
                $q->where('loja_id', $loja->id)
                    ->whereIn('status', ['aceito', 'em_preparo']);
            })
            ->with(['pedido.mesa', 'produto']);

        if (is_string($estacao) && $estacao !== '') {
            $query->whereHas('produto', fn ($q) => $q->where('estacao_kds', $estacao));
        }

        $itens = $query->orderBy('created_at', 'asc')->get();

        $tickets = $itens->map(function (PedidoItem $item): array {
            $tempoPreparoEsperado = $item->produto?->tempo_preparo_min ?? 15;
            $minutosDecorridos = $item->created_at ? (int) $item->created_at->diffInMinutes(now()) : 0;

            return [
                'item_id' => $item->id,
                'pedido_id' => $item->pedido_id,
                'codigo_publico' => $item->pedido?->codigo_publico,
                'canal' => $item->pedido?->canal,
                'mesa_numero' => $item->pedido?->mesa?->numero,
                'estacao_kds' => $item->produto?->estacao_kds ?? 'geral',
                'produto_nome' => $item->nome_snapshot,
                'variacao' => $item->variacao_snapshot,
                'qtd' => $item->qtd,
                'adicionais' => array_map(
                    static fn (array $opt): string => (string) ($opt['nome'] ?? ''),
                    is_array($item->adicionais_json) ? $item->adicionais_json : []
                ),
                'observacao' => $item->observacao,
                'status_cozinha' => $item->status_cozinha,
                'minutos_decorridos' => $minutosDecorridos,
                'atrasado' => $minutosDecorridos > $tempoPreparoEsperado,
            ];
        });

        return response()->json([
            'estacao_filtrada' => $estacao ?: 'todas',
            'tickets' => $tickets,
        ]);
    }

    /**
     * PATCH /api/kds/itens/{itemId}/status
     * Transitions kitchen item status: fila -> preparando -> pronto
     */
    public function atualizarStatusItem(Request $request, int $itemId): JsonResponse
    {
        $request->validate([
            'status_cozinha' => ['required', 'string', 'in:fila,preparando,pronto'],
        ]);

        $loja = $this->context->getLoja();

        $item = PedidoItem::whereHas('pedido', fn ($q) => $q->where('loja_id', $loja->id))
            ->findOrFail($itemId);

        $item->update([
            'status_cozinha' => $request->string('status_cozinha')->toString(),
        ]);

        return response()->json([
            'message' => 'Status do item no KDS atualizado.',
            'item_id' => $item->id,
            'status_cozinha' => $item->status_cozinha,
        ]);
    }
}
