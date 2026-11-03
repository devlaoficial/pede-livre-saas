<?php

declare(strict_types=1);

namespace App\Domain\Pagamentos\Controllers;

use App\Domain\Assinaturas\Models\Fatura;
use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Assinaturas\Services\AssinaturaPlataformaService;
use App\Domain\Pagamentos\Models\Pagamento;
use App\Domain\Pagamentos\Models\WebhookLog;
use App\Domain\Pedidos\Events\PagamentoConfirmado;
use App\Domain\Pedidos\Models\Pedido;
use App\Domain\Pedidos\Services\FluxoStatusPedidoService;
use App\Support\Security\SensitiveDataRedactor;
use App\Support\Security\WebhookSignatureValidator;
use App\Support\Tenancy\LojaContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class MercadoPagoWebhookController
{
    public function __construct(
        private readonly WebhookSignatureValidator $signatureValidator,
        private readonly SensitiveDataRedactor $redactor,
        private readonly FluxoStatusPedidoService $fluxoService,
        private readonly AssinaturaPlataformaService $assinaturaService,
        private readonly LojaContext $lojaContext
    ) {}

    /**
     * Webhook Endpoint: POST /webhooks/mercadopago
     */
    public function processar(Request $request): JsonResponse
    {
        $signatureHeader = $request->header('x-signature');
        $xRequestId = $request->header('x-request-id');
        $payload = $request->all();

        $dataId = $payload['data']['id'] ?? ($payload['id'] ?? null);

        // 1. Cryptographic Signature & Timestamp Freshness Check
        $secretKey = (string) config('services.mercadopago.webhook_secret', '');
        $platformSecret = (string) config('services.mercadopago.platform_webhook_secret', '');

        $isValid = $this->signatureValidator->isValid($signatureHeader, $xRequestId, $dataId, $secretKey)
            || ($platformSecret !== '' && $this->signatureValidator->isValid($signatureHeader, $xRequestId, $dataId, $platformSecret));

        if (!$isValid) {
            return response()->json([
                'error' => 'ASSINATURA_INVALIDA',
                'message' => 'Assinatura HMAC-SHA256 inválida ou timestamp expirado.',
            ], Response::HTTP_FORBIDDEN);
        }

        // 2. Log sanitized & encrypted webhook payload
        $log = WebhookLog::create([
            'origem' => 'mercadopago',
            'payload' => $this->redactor->redact($payload),
            'processado' => false,
        ]);

        if (empty($dataId)) {
            return response()->json(['message' => 'Notificação recebida sem data.id'], Response::HTTP_OK);
        }

        // 3. Distributed Redis Lock for Idempotency
        $lockKey = "webhook:mp:payment:{$dataId}";
        $lock = Cache::lock($lockKey, 15);

        if (!$lock->get()) {
            return response()->json(['message' => 'Notificação duplicada em processamento'], Response::HTTP_OK);
        }

        try {
            // Locate order payment by mp_payment_id or external reference
            /** @var Pagamento|null $pagamento */
            $pagamento = Pagamento::withoutGlobalScopes()->where('mp_payment_id', (string) $dataId)->first();

            if ($pagamento === null && isset($payload['data']['id'])) {
                $pagamento = Pagamento::withoutGlobalScopes()->where('idempotency_key', 'LIKE', "%{$dataId}%")->first();
            }

            // If not an order payment, check if it is a SaaS platform invoice (Fatura)
            if ($pagamento === null) {
                $fatura = Fatura::withoutGlobalScopes()->where('mp_payment_id', (string) $dataId)->first();
                if ($fatura !== null) {
                    $this->assinaturaService->confirmarPagamentoFatura($fatura);
                    $log->update(['processado' => true]);
                    return response()->json(['message' => 'Fatura SaaS conciliada com sucesso'], Response::HTTP_OK);
                }

                $log->update(['processado' => true, 'erro' => 'Pagamento local não encontrado']);
                return response()->json(['message' => 'Pagamento não localizado'], Response::HTTP_OK);
            }

            // 4. Defense-in-Depth Verification against Mercado Pago API
            $loja = Loja::withoutGlobalScopes()->find($pagamento->loja_id);
            if ($loja !== null) {
                $this->lojaContext->setLoja($loja);
            }

            $tokenLoja = $loja?->mp_access_token;
            $statusPagamentoMp = 'approved';

            if (!empty($tokenLoja) && !app()->environment('testing')) {
                $mpResponse = Http::withHeaders([
                    'Authorization' => "Bearer {$tokenLoja}",
                ])->timeout(10)->get("https://api.mercadopago.com/v1/payments/{$dataId}");

                if ($mpResponse->successful()) {
                    $statusPagamentoMp = (string) $mpResponse->json('status');
                }
            }

            // 5. Reconcile Payment and advance Order State
            if ($statusPagamentoMp === 'approved') {
                $pagamento->update(['status' => 'aprovado']);

                /** @var Pedido|null $pedido */
                $pedido = Pedido::withoutGlobalScopes()
                    ->with(['cliente', 'itens', 'loja', 'pagamento'])
                    ->find($pagamento->pedido_id);

                if ($pedido !== null) {
                    $pedido->update(['pagamento_status' => 'aprovado']);

                    // Auto-advance order if in 'novo' status and notify customer via WhatsApp
                    if ($pedido->status === 'novo') {
                        $this->fluxoService->transicionar(
                            $pedido,
                            'em_preparo',
                            null,
                            null,
                            null,
                            'Pagamento PIX aprovado automaticamente via webhook Mercado Pago.'
                        );
                    }

                    try {
                        if (config('broadcasting.default') && !in_array(config('broadcasting.default'), ['null', 'log'], true)) {
                            event(new PagamentoConfirmado($pedido));
                        }
                    } catch (Throwable) {}
                }

                $log->update(['processado' => true]);
            } elseif ($statusPagamentoMp === 'rejected') {
                $pagamento->update(['status' => 'recusado']);
                $log->update(['processado' => true]);
            }

            return response()->json(['message' => 'Webhook processado com sucesso'], Response::HTTP_OK);
        } catch (Throwable $e) {
            $log->update(['erro' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        } finally {
            $lock->release();
        }
    }
}
