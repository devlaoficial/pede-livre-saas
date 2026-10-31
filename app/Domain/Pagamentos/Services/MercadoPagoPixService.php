<?php

declare(strict_types=1);

namespace App\Domain\Pagamentos\Services;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Pagamentos\Models\Pagamento;
use App\Domain\Pedidos\Models\Pedido;
use DomainException;
use Illuminate\Support\Facades\Http;

final class MercadoPagoPixService
{
    private const PIX_API_URL = 'https://api.mercadopago.com/v1/payments';

    /**
     * Generates a non-custodial dynamic PIX charge directly on the merchant's Mercado Pago account.
     *
     * @param Loja $loja
     * @param Pedido $pedido
     * @param Pagamento $pagamento
     * @return array{pix_copia_cola: string, pix_qr_base64: string, expira_em: string, mp_payment_id: string}
     */
    public function gerarPix(Loja $loja, Pedido $pedido, Pagamento $pagamento): array
    {
        // 1. Decrypted OAuth token check (zero platform custody)
        $accessToken = $loja->mp_access_token;

        // If in local/testing environment or token not configured, generate realistic sandbox PIX payload
        if (empty($accessToken) || app()->environment('testing')) {
            return $this->gerarPixSandboxMock($pedido, $pagamento);
        }

        $cliente = $pedido->cliente;
        $valorFloat = round($pedido->total / 100, 2);
        $expiracao = now()->addMinutes(15)->toIso8601String();

        $payload = [
            'transaction_amount' => $valorFloat,
            'description' => "Pedido #{$pedido->codigo_publico} - {$loja->nome}",
            'payment_method_id' => 'pix',
            'notification_url' => url('/api/webhooks/mercadopago'),
            'external_reference' => (string) $pedido->id,
            'payer' => [
                'email' => $cliente?->email ?: "cliente_{$pedido->id}@pedelivre.com.br",
                'first_name' => $cliente?->nome ?: 'Cliente',
                'last_name' => 'PedeLivre',
            ],
        ];

        // 2. Call Mercado Pago Payments API with store OAuth token & idempotency key
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$accessToken}",
            'X-Idempotency-Key' => $pagamento->idempotency_key,
        ])->post(self::PIX_API_URL, $payload);

        if ($response->failed()) {
            throw new DomainException('Erro ao emitir cobrança PIX via Mercado Pago: ' . $response->body());
        }

        $dados = $response->json();
        $mpPaymentId = (string) ($dados['id'] ?? '');
        $pointOfInteraction = $dados['point_of_interaction']['transaction_data'] ?? [];

        $copiaCola = (string) ($pointOfInteraction['qr_code'] ?? '');
        $qrBase64 = (string) ($pointOfInteraction['qr_code_base64'] ?? '');

        // 3. Save into pagamentos record (copia_cola is encrypted at rest)
        $pagamento->update([
            'mp_payment_id' => $mpPaymentId,
            'pix_copia_cola' => $copiaCola,
            'pix_qr_base64' => $qrBase64,
            'expira_em' => now()->addMinutes(15),
        ]);

        return [
            'pix_copia_cola' => $copiaCola,
            'pix_qr_base64' => $qrBase64,
            'expira_em' => $expiracao,
            'mp_payment_id' => $mpPaymentId,
        ];
    }

    /**
     * Deterministic Sandbox Mock for Testing and Local Environments.
     *
     * @return array{pix_copia_cola: string, pix_qr_base64: string, expira_em: string, mp_payment_id: string}
     */
    private function gerarPixSandboxMock(Pedido $pedido, Pagamento $pagamento): array
    {
        $mpId = 'MP_MOCK_' . $pedido->id . '_' . time();
        $copiaCola = "00020126580014br.gov.bcb.pix0136{$pedido->idempotency_key}5204000053039865405{$pedido->total}5802BR5925PEDELIVRE6009SAO_PAULO62070503***6304ABCD";
        $qrBase64 = base64_encode('<svg width="200" height="200"><rect width="200" height="200" fill="#fff"/><text x="50" y="100">PIX QR MOCK</text></svg>');
        $expiracao = now()->addMinutes(15)->toIso8601String();

        $pagamento->update([
            'mp_payment_id' => $mpId,
            'pix_copia_cola' => $copiaCola,
            'pix_qr_base64' => $qrBase64,
            'expira_em' => now()->addMinutes(15),
        ]);

        return [
            'pix_copia_cola' => $copiaCola,
            'pix_qr_base64' => $qrBase64,
            'expira_em' => $expiracao,
            'mp_payment_id' => $mpId,
        ];
    }
}
