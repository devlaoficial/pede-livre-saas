<?php

declare(strict_types=1);

namespace App\Domain\Pedidos\Controllers;

use App\Domain\Autenticacao\Models\Usuario;
use App\Domain\Entregas\Models\Entregador;
use App\Domain\Pedidos\Models\Pedido;
use App\Domain\Pedidos\Requests\AtualizarStatusPedidoRequest;
use App\Domain\Pedidos\Resources\PedidoPainelResource;
use App\Domain\Pedidos\Services\FluxoStatusPedidoService;
use App\Support\Tenancy\LojaContext;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

final class PedidoPainelController
{
    public function __construct(
        private readonly FluxoStatusPedidoService $fluxoService,
        private readonly LojaContext $context
    ) {}

    /**
     * Active orders query for Kanban board.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $statusFiltro = $request->query('status');

        $query = Pedido::with(['cliente', 'itens', 'mesa', 'entregador', 'eventos.usuario'])
            ->orderBy('created_at', 'desc');

        if (is_string($statusFiltro) && $statusFiltro !== '') {
            $query->where('status', $statusFiltro);
        } else {
            // Exclude delivered/cancelled older than today unless requested
            $query->where(function ($q): void {
                $q->whereNotIn('status', ['entregue', 'retirado', 'servido', 'cancelado'])
                    ->orWhere('created_at', '>=', now()->startOfDay());
            });
        }

        return PedidoPainelResource::collection($query->get());
    }

    public function show(int $id): JsonResponse
    {
        $pedido = Pedido::with(['cliente', 'itens', 'mesa', 'entregador', 'eventos.usuario'])->findOrFail($id);

        return response()->json(new PedidoPainelResource($pedido));
    }

    public function atualizarStatus(AtualizarStatusPedidoRequest $request, int $id): JsonResponse
    {
        $pedido = Pedido::with('itens')->findOrFail($id);

        /** @var Usuario $usuario */
        $usuario = $request->user();

        try {
            $pedidoAtualizado = $this->fluxoService->transicionar(
                $pedido,
                $request->string('status')->toString(),
                $usuario,
                $request->input('cancelado_motivo'),
                $request->filled('entregador_id') ? $request->integer('entregador_id') : null,
                $request->input('observacao')
            );

            return response()->json([
                'message' => "Status do pedido atualizado para [{$pedidoAtualizado->status}].",
                'pedido' => new PedidoPainelResource($pedidoAtualizado->load(['cliente', 'itens', 'mesa', 'entregador', 'eventos.usuario'])),
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'error' => 'TRANSICAO_INVALIDA',
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function atribuirEntregador(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'entregador_id' => ['required', 'integer'],
        ]);

        $entregador = Entregador::findOrFail($request->integer('entregador_id'));
        $pedido = Pedido::findOrFail($id);
        $pedido->update(['entregador_id' => $entregador->id]);

        return response()->json([
            'message' => 'Entregador atribuído ao pedido com sucesso.',
            'pedido' => new PedidoPainelResource($pedido->load(['cliente', 'itens', 'entregador'])),
        ]);
    }
}
