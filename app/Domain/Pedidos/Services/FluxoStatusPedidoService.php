<?php

declare(strict_types=1);

namespace App\Domain\Pedidos\Services;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Autenticacao\Models\Usuario;
use App\Domain\Bot\Services\EvolutionApiService;
use App\Domain\Catalogo\Models\Produto;
use App\Domain\Catalogo\Services\CardapioCacheService;
use App\Domain\Fidelidade\Services\CashbackService;
use App\Domain\Pedidos\Events\PedidoAtualizado;
use App\Domain\Pedidos\Models\EstoqueMovimento;
use App\Domain\Pedidos\Models\Pedido;
use App\Domain\Pedidos\Models\PedidoEvento;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

final class FluxoStatusPedidoService
{
    public function __construct(
        private readonly CardapioCacheService $cacheService,
        private readonly ImpressaoEscPosService $impressaoService,
        private readonly CashbackService $cashbackService,
        private readonly EvolutionApiService $evolutionApi
    ) {}

    /**
     * Allowed transitions per channel (including direct completion when confirmed by customer or operator).
     */
    private const TRANSIÇÕES_CANAL = [
        'delivery' => [
            'novo' => ['aceito', 'em_preparo', 'pronto', 'saiu_entrega', 'entregue', 'cancelado'],
            'aceito' => ['em_preparo', 'pronto', 'saiu_entrega', 'entregue', 'cancelado'],
            'em_preparo' => ['pronto', 'saiu_entrega', 'entregue', 'cancelado'],
            'pronto' => ['saiu_entrega', 'entregue', 'cancelado'],
            'saiu_entrega' => ['entregue', 'cancelado'],
            'entregue' => [],
            'cancelado' => [],
        ],
        'retirada' => [
            'novo' => ['aceito', 'em_preparo', 'pronto', 'retirado', 'entregue', 'cancelado'],
            'aceito' => ['em_preparo', 'pronto', 'retirado', 'entregue', 'cancelado'],
            'em_preparo' => ['pronto', 'retirado', 'entregue', 'cancelado'],
            'pronto' => ['retirado', 'entregue', 'saiu_entrega', 'cancelado'],
            'saiu_entrega' => ['entregue', 'retirado', 'cancelado'],
            'retirado' => [],
            'entregue' => [],
            'cancelado' => [],
        ],
        'mesa' => [
            'novo' => ['aceito', 'em_preparo', 'pronto', 'servido', 'entregue', 'cancelado'],
            'aceito' => ['em_preparo', 'pronto', 'servido', 'entregue', 'cancelado'],
            'em_preparo' => ['pronto', 'servido', 'entregue', 'cancelado'],
            'pronto' => ['servido', 'entregue', 'cancelado'],
            'servido' => [],
            'entregue' => [],
            'cancelado' => [],
        ],
        'balcao' => [
            'novo' => ['aceito', 'em_preparo', 'pronto', 'entregue', 'retirado', 'cancelado'],
            'aceito' => ['em_preparo', 'pronto', 'entregue', 'retirado', 'cancelado'],
            'em_preparo' => ['pronto', 'entregue', 'retirado', 'cancelado'],
            'pronto' => ['entregue', 'retirado', 'saiu_entrega', 'cancelado'],
            'saiu_entrega' => ['entregue', 'retirado', 'cancelado'],
            'entregue' => [],
            'retirado' => [],
            'cancelado' => [],
        ],
    ];

    /**
     * Executes an order status transition with atomic inventory locks and non-blocking WhatsApp/WebSocket notifications.
     */
    public function transicionar(
        Pedido $pedido,
        string $novoStatus,
        ?Usuario $usuario = null,
        ?string $motivoCancelamento = null,
        ?int $entregadorId = null,
        ?string $observacao = null,
        bool $notificarWhatsApp = true
    ): Pedido {
        $canal = $pedido->canal;
        $statusAtual = $pedido->status;

        if ($statusAtual === $novoStatus) {
            return $pedido;
        }

        // 1. Validate transition
        $transicoesPermitidas = self::TRANSIÇÕES_CANAL[$canal][$statusAtual] ?? [];
        if (!in_array($novoStatus, $transicoesPermitidas, true)) {
            throw new DomainException(
                "Transição inválida para o canal [{$canal}]: de '{$statusAtual}' para '{$novoStatus}'."
            );
        }

        // 2. Validate cancellation requirements
        if ($novoStatus === 'cancelado') {
            if (empty(trim((string) $motivoCancelamento))) {
                throw new DomainException('É obrigatório informar o motivo do cancelamento.');
            }
        }

        // 3. Fast Atomic DB Transaction (no external HTTP calls inside the transaction!)
        $pedidoAtualizado = DB::transaction(function () use ($pedido, $novoStatus, $statusAtual, $usuario, $motivoCancelamento, $entregadorId, $observacao): Pedido {
            $lojaId = $pedido->loja_id;

            // Atomic Inventory Deduction when leaving 'novo' to any active/completed status
            if ($novoStatus !== 'cancelado' && $statusAtual === 'novo') {
                $this->deduzirEstoqueAtômico($pedido, $lojaId);
                $pedido->aceito_em = now();

                // Auto-Print on Accept if configured in store settings
                $loja = $pedido->loja ?? Loja::withoutGlobalScopes()->find($lojaId);
                $autoPrint = $loja?->config_json['impressao_automatica_ao_aceitar'] ?? false;
                if ($autoPrint && in_array($novoStatus, ['aceito', 'em_preparo'], true)) {
                    try {
                        $this->impressaoService->enfileirarImpressao($pedido, 'ambas');
                    } catch (Throwable) {}
                }
            }

            // Atomic Inventory Restoration if order is cancelled after stock was deducted
            if ($novoStatus === 'cancelado' && in_array($statusAtual, ['aceito', 'em_preparo', 'pronto', 'saiu_entrega'], true)) {
                $this->estornarEstoqueAtômico($pedido, $lojaId);
            }

            // Timestamps update
            if ($novoStatus === 'pronto') {
                $pedido->pronto_em = now();
            } elseif (in_array($novoStatus, ['entregue', 'retirado', 'servido'], true)) {
                if ($pedido->pronto_em === null) {
                    $pedido->pronto_em = now();
                }
                $pedido->entregue_em = now();
                $pedido->status = $novoStatus;
                $pedido->save();
                $this->cashbackService->creditarPorPedidoConcluido($pedido);
            }

            if ($motivoCancelamento !== null) {
                $pedido->cancelado_motivo = $motivoCancelamento;
            }

            if ($entregadorId !== null) {
                $pedido->entregador_id = $entregadorId;
            }

            $pedido->status = $novoStatus;
            $pedido->save();

            // Record state machine audit log
            PedidoEvento::create([
                'pedido_id' => $pedido->id,
                'status_de' => $statusAtual,
                'status_para' => $novoStatus,
                'usuario_id' => $usuario?->id,
                'observacao' => $observacao ?: ($motivoCancelamento ? "Cancelado: {$motivoCancelamento}" : null),
            ]);

            return $pedido;
        });

        // 4. Dispatch WhatsApp notification Outside DB Transaction (Guaranteed to run even if Broadcast is disabled/offline)
        if ($notificarWhatsApp) {
            try {
                $this->evolutionApi->notificarMudancaStatus($pedidoAtualizado, $novoStatus);
            } catch (Throwable $e) {
                Log::warning("[FluxoStatusPedido] Falha ao notificar WhatsApp no pedido #{$pedidoAtualizado->id}: " . $e->getMessage());
            }
        }

        // 5. Safe WebSocket Broadcast (Isolated in try/catch so missing Reverb server never delays or breaks the flow)
        try {
            if (config('broadcasting.default') && !in_array(config('broadcasting.default'), ['null', 'log'], true)) {
                event(new PedidoAtualizado($pedidoAtualizado, $statusAtual));
            }
        } catch (Throwable $e) {
            Log::debug("[FluxoStatusPedido] Broadcast ignorado: " . $e->getMessage());
        }

        return $pedidoAtualizado;
    }

    /**
     * Deducts stock using SELECT ... FOR UPDATE (pessimistic lock) to prevent race conditions.
     */
    private function deduzirEstoqueAtômico(Pedido $pedido, int $lojaId): void
    {
        $cacheInvalidar = false;

        foreach ($pedido->itens as $item) {
            /** @var Produto|null $produto */
            $produto = Produto::where('loja_id', $lojaId)
                ->where('id', $item->produto_id)
                ->lockForUpdate()
                ->first();

            if ($produto !== null && $produto->controla_estoque) {
                $novoEstoque = $produto->estoque_qtd - $item->qtd;
                $produto->estoque_qtd = $novoEstoque;

                EstoqueMovimento::create([
                    'loja_id' => $lojaId,
                    'produto_id' => $produto->id,
                    'tipo' => 'saida',
                    'qtd' => -$item->qtd,
                    'pedido_id' => $pedido->id,
                ]);

                if ($novoEstoque <= 0) {
                    $produto->esgotado = true;
                    $cacheInvalidar = true;
                }

                $produto->save();
            }
        }

        if ($cacheInvalidar) {
            $this->cacheService->invalidar($lojaId);
        }
    }

    /**
     * Restores deducted stock on order cancellation.
     */
    private function estornarEstoqueAtômico(Pedido $pedido, int $lojaId): void
    {
        $cacheInvalidar = false;

        foreach ($pedido->itens as $item) {
            /** @var Produto|null $produto */
            $produto = Produto::where('loja_id', $lojaId)
                ->where('id', $item->produto_id)
                ->lockForUpdate()
                ->first();

            if ($produto !== null && $produto->controla_estoque) {
                $novoEstoque = $produto->estoque_qtd + $item->qtd;
                $produto->estoque_qtd = $novoEstoque;

                EstoqueMovimento::create([
                    'loja_id' => $lojaId,
                    'produto_id' => $produto->id,
                    'tipo' => 'ajuste',
                    'qtd' => $item->qtd,
                    'pedido_id' => $pedido->id,
                ]);

                if ($novoEstoque > 0 && $produto->esgotado) {
                    $produto->esgotado = false;
                    $cacheInvalidar = true;
                }

                $produto->save();
            }
        }

        if ($cacheInvalidar) {
            $this->cacheService->invalidar($lojaId);
        }
    }
}
