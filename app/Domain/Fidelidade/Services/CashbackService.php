<?php

declare(strict_types=1);

namespace App\Domain\Fidelidade\Services;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Clientes\Models\Cliente;
use App\Domain\Fidelidade\Models\CashbackMovimento;
use App\Domain\Pedidos\Models\Pedido;
use DomainException;
use Illuminate\Support\Facades\DB;

final class CashbackService
{
    /**
     * Credits configurable cashback percentage into cashback_movimentos ONLY after order reaches
     * entregue, retirado, or servido.
     */
    public function creditarPorPedidoConcluido(Pedido $pedido): ?CashbackMovimento
    {
        if (!in_array($pedido->status, ['entregue', 'retirado', 'servido'], true)) {
            throw new DomainException('Cashback só pode ser creditado após a conclusão/entrega do pedido.');
        }

        if ($pedido->cliente_id === null) {
            return null;
        }

        $loja = $pedido->loja;
        if ($loja === null || !$loja->temRecurso('crm_cashback')) {
            return null;
        }

        // Prevent duplicate credit for the same order
        $jaCreditado = CashbackMovimento::where('loja_id', $loja->id)
            ->where('pedido_id', $pedido->id)
            ->where('tipo', 'credito')
            ->exists();

        if ($jaCreditado) {
            return null;
        }

        $config = $loja->config_json ?? [];
        $cashbackPct = (float) ($config['cashback_pct'] ?? 5.0); // Default 5%
        $diasValidade = (int) ($config['cashback_validade_dias'] ?? 60);

        $valorCreditoCentavos = (int) round(($pedido->total * $cashbackPct) / 100);
        if ($valorCreditoCentavos <= 0) {
            return null;
        }

        return DB::transaction(function () use ($loja, $pedido, $valorCreditoCentavos, $diasValidade): CashbackMovimento {
            /** @var Cliente $cliente */
            $cliente = Cliente::where('loja_id', $loja->id)
                ->lockForUpdate()
                ->findOrFail($pedido->cliente_id);

            $movimento = CashbackMovimento::create([
                'loja_id' => $loja->id,
                'cliente_id' => $cliente->id,
                'tipo' => 'credito',
                'valor' => $valorCreditoCentavos,
                'pedido_id' => $pedido->id,
                'expira_em' => now()->addDays($diasValidade),
            ]);

            $cliente->increment('saldo_cashback_centavos', $valorCreditoCentavos);
            $cliente->increment('total_gasto', $pedido->total);

            return $movimento;
        });
    }

    /**
     * Debits redeemed cashback on order creation, capped at store's configured max % of order subtotal.
     */
    public function debitarResgate(Loja $loja, Cliente $cliente, int $valorResgateCentavos, int $subtotalPedidoCentavos, ?int $pedidoId = null): CashbackMovimento
    {
        $maxPct = (float) ($loja->config_json['cashback_max_resgate_pct'] ?? 50.0);
        $limiteMaximoCentavos = (int) round(($subtotalPedidoCentavos * $maxPct) / 100);

        if ($valorResgateCentavos > $limiteMaximoCentavos) {
            $reaisMax = number_format($limiteMaximoCentavos / 100, 2, ',', '.');
            throw new DomainException("O resgate de cashback não pode ultrapassar {$maxPct}% do pedido (R$ {$reaisMax}).");
        }

        return DB::transaction(function () use ($loja, $cliente, $valorResgateCentavos, $pedidoId): CashbackMovimento {
            $clienteLocked = Cliente::where('loja_id', $loja->id)
                ->lockForUpdate()
                ->findOrFail($cliente->id);

            if ($valorResgateCentavos > $clienteLocked->saldo_cashback_centavos) {
                throw new DomainException('Saldo de cashback insuficiente para este resgate.');
            }

            $movimento = CashbackMovimento::create([
                'loja_id' => $loja->id,
                'cliente_id' => $clienteLocked->id,
                'tipo' => 'debito',
                'valor' => $valorResgateCentavos,
                'pedido_id' => $pedidoId,
            ]);

            $clienteLocked->decrement('saldo_cashback_centavos', $valorResgateCentavos);

            return $movimento;
        });
    }

    /**
     * Scheduled job helper: expires overdue cashback credits.
     */
    public function processarExpiracoes(int $lojaId): int
    {
        $vencidos = CashbackMovimento::where('loja_id', $lojaId)
            ->where('tipo', 'credito')
            ->whereNotNull('expira_em')
            ->where('expira_em', '<', now())
            ->get();

        $totalExpirados = 0;
        foreach ($vencidos as $credito) {
            DB::transaction(function () use ($credito, &$totalExpirados): void {
                $cliente = Cliente::lockForUpdate()->find($credito->cliente_id);
                if ($cliente !== null && $cliente->saldo_cashback_centavos > 0) {
                    $valorAbater = min($cliente->saldo_cashback_centavos, $credito->valor);
                    if ($valorAbater > 0) {
                        CashbackMovimento::create([
                            'loja_id' => $credito->loja_id,
                            'cliente_id' => $cliente->id,
                            'tipo' => 'expiracao',
                            'valor' => $valorAbater,
                            'pedido_id' => $credito->pedido_id,
                        ]);
                        $cliente->decrement('saldo_cashback_centavos', $valorAbater);
                        $totalExpirados++;
                    }
                }
                // Clear expira_em so it's not processed twice
                $credito->update(['expira_em' => null]);
            });
        }

        return $totalExpirados;
    }
}
