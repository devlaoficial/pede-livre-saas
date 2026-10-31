<?php

declare(strict_types=1);

namespace App\Domain\Mesas\Services;

use App\Domain\Assinaturas\Models\Auditoria;
use App\Domain\Autenticacao\Models\Usuario;
use App\Domain\Mesas\Models\Comanda;
use App\Domain\Mesas\Models\Mesa;
use App\Domain\Pedidos\Models\Pedido;
use App\Domain\Pedidos\Models\PedidoItem;
use App\Support\Security\SensitiveDataRedactor;
use DomainException;
use Illuminate\Support\Facades\DB;

final class ComandaService
{
    public function __construct(
        private readonly SensitiveDataRedactor $redactor
    ) {}

    /**
     * Opens a new table tab (Comanda) and marks table as occupied.
     */
    public function abrirComanda(Mesa $mesa, ?int $clienteId = null, int $taxaServicoPct = 10): Comanda
    {
        if ($mesa->comandaAtiva()->exists()) {
            throw new DomainException("A Mesa {$mesa->numero} já possui uma comanda aberta.");
        }

        return DB::transaction(function () use ($mesa, $clienteId, $taxaServicoPct): Comanda {
            $mesa->update(['status' => 'ocupada']);

            return Comanda::create([
                'loja_id' => $mesa->loja_id,
                'mesa_id' => $mesa->id,
                'cliente_id' => $clienteId,
                'status' => 'aberta',
                'aberta_em' => now(),
                'taxa_servico_pct' => $taxaServicoPct,
                'total_centavos' => 0,
            ]);
        });
    }

    /**
     * Recalculates the active tab total in integer centavos including service fee.
     */
    public function recalcularTotal(Comanda $comanda): int
    {
        $pedidosAtivos = Pedido::where('comanda_id', $comanda->id)
            ->where('status', '!=', 'cancelado')
            ->get();

        $subtotalItens = 0;
        foreach ($pedidosAtivos as $pedido) {
            $subtotalItens += (int) $pedido->itens()->sum('total');
        }

        $taxaServico = (int) round(($subtotalItens * $comanda->taxa_servico_pct) / 100);
        $totalFinal = $subtotalItens + $taxaServico;

        $comanda->update(['total_centavos' => $totalFinal]);

        return $totalFinal;
    }

    /**
     * Transfers an order item from one table/comanda to another with immutable audit trail.
     */
    public function transferirItem(
        PedidoItem $item,
        Comanda $comandaDestino,
        Usuario $operador,
        string $ipAddress = '127.0.0.1'
    ): void {
        $pedidoOrigem = $item->pedido;
        if ($pedidoOrigem === null || $pedidoOrigem->comanda_id === null) {
            throw new DomainException('Este item não pertence a uma comanda ativa.');
        }

        /** @var Comanda $comandaOrigem */
        $comandaOrigem = Comanda::findOrFail($pedidoOrigem->comanda_id);

        if ($comandaOrigem->loja_id !== $comandaDestino->loja_id) {
            throw new DomainException('Transferência entre lojas diferentes é proibida.');
        }

        if ($comandaDestino->status !== 'aberta') {
            throw new DomainException('A comanda de destino precisa estar aberta.');
        }

        DB::transaction(function () use ($item, $pedidoOrigem, $comandaOrigem, $comandaDestino, $operador, $ipAddress): void {
            // Find or create a receptor order on target comanda
            $pedidoDestino = Pedido::where('comanda_id', $comandaDestino->id)
                ->where('status', '!=', 'cancelado')
                ->latest('id')
                ->first();

            if ($pedidoDestino === null) {
                $pedidoDestino = Pedido::create([
                    'loja_id' => $comandaDestino->loja_id,
                    'codigo_publico' => 'TR' . strtoupper(substr(md5((string) microtime()), 0, 2)),
                    'tracking_token_hash' => hash_hmac('sha256', (string) microtime(), (string) config('app.key')),
                    'canal' => 'mesa',
                    'status' => 'em_preparo',
                    'mesa_id' => $comandaDestino->mesa_id,
                    'comanda_id' => $comandaDestino->id,
                    'subtotal' => 0,
                    'total' => 0,
                    'forma_pagamento' => 'dinheiro',
                    'origem' => 'painel',
                ]);
            }

            $item->update(['pedido_id' => $pedidoDestino->id]);

            // Update order subtotals and comanda totals
            $novoSubtotalOrigem = (int) $pedidoOrigem->itens()->sum('total');
            $pedidoOrigem->update(['subtotal' => $novoSubtotalOrigem, 'total' => $novoSubtotalOrigem]);

            $novoSubtotalDestino = (int) $pedidoDestino->itens()->sum('total');
            $pedidoDestino->update(['subtotal' => $novoSubtotalDestino, 'total' => $novoSubtotalDestino]);

            $this->recalcularTotal($comandaOrigem);
            $this->recalcularTotal($comandaDestino);

            // Write immutable audit record
            Auditoria::create([
                'loja_id' => $comandaOrigem->loja_id,
                'usuario_id' => $operador->id,
                'acao' => 'TRANSFERIR_ITEM_COMANDA',
                'entidade' => 'pedido_itens',
                'entidade_id' => $item->id,
                'antes_json' => $this->redactor->redact([
                    'comanda_id' => $comandaOrigem->id,
                    'mesa_id' => $comandaOrigem->mesa_id,
                    'pedido_id' => $pedidoOrigem->id,
                ]),
                'depois_json' => $this->redactor->redact([
                    'comanda_id' => $comandaDestino->id,
                    'mesa_id' => $comandaDestino->mesa_id,
                    'pedido_id' => $pedidoDestino->id,
                    'item_nome' => $item->nome_snapshot,
                    'valor_centavos' => $item->total,
                ]),
                'ip_hash' => hash_hmac('sha256', $ipAddress, (string) config('app.key')),
            ]);
        });
    }

    /**
     * Exact integer centavos bill splitting across N people.
     * Distributes centavos remainder cleanly so sum(quotas) === total_centavos.
     *
     * @return array{
     *     total_centavos: int,
     *     numero_pessoas: int,
     *     valor_base_pessoa_centavos: int,
     *     resto_centavos: int,
     *     cotas_por_pessoa_centavos: array<int, int>
     * }
     */
    public function dividirConta(Comanda $comanda, int $numeroPessoas): array
    {
        if ($numeroPessoas < 1) {
            throw new DomainException('O número de pessoas para divisão deve ser no mínimo 1.');
        }

        $totalCentavos = $this->recalcularTotal($comanda);
        $valorBase = intdiv($totalCentavos, $numeroPessoas);
        $resto = $totalCentavos % $numeroPessoas;

        $cotas = [];
        for ($i = 1; $i <= $numeroPessoas; $i++) {
            // First $resto people absorb 1 extra centavo so sum is 100% exact
            $cotas[$i] = $valorBase + ($i <= $resto ? 1 : 0);
        }

        return [
            'total_centavos' => $totalCentavos,
            'numero_pessoas' => $numeroPessoas,
            'valor_base_pessoa_centavos' => $valorBase,
            'resto_centavos' => $resto,
            'cotas_por_pessoa_centavos' => $cotas,
        ];
    }

    /**
     * Closes open tab and frees the table.
     */
    public function fecharComanda(Comanda $comanda): Comanda
    {
        return DB::transaction(function () use ($comanda): Comanda {
            $this->recalcularTotal($comanda);

            $comanda->update([
                'status' => 'fechada',
                'fechada_em' => now(),
            ]);

            if ($comanda->mesa !== null) {
                $comanda->mesa->update(['status' => 'livre']);
            }

            return $comanda;
        });
    }
}
