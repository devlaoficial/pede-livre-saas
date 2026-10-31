<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Assinaturas\Models\Plano;
use App\Domain\Autenticacao\Models\Usuario;
use App\Domain\Pagamentos\Models\Pagamento;
use App\Domain\Pagamentos\Models\WebhookLog;
use App\Domain\Pedidos\Events\PagamentoConfirmado;
use App\Domain\Pedidos\Models\Pedido;
use App\Support\Security\WebhookSignatureValidator;
use App\Support\Tenancy\LojaContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

final class MercadoPagoWebhookTest extends TestCase
{
    use RefreshDatabase;

    private Loja $loja;
    private Usuario $dono;
    private Pedido $pedido;
    private Pagamento $pagamento;
    private WebhookSignatureValidator $validator;
    private string $webhookSecret;

    protected function setUp(): void
    {
        parent::setUp();

        $this->webhookSecret = 'test_webhook_secret_key_32_bytes_len!!';
        config(['services.mercadopago.webhook_secret' => $this->webhookSecret]);

        $plano = Plano::create([
            'nome' => 'premium',
            'preco_centavos' => 24990,
            'recursos_json' => ['cardapio_online' => true, 'pix_automatico' => true],
            'limite_usuarios' => 10,
        ]);

        $this->loja = Loja::create([
            'slug' => 'loja-mp-fase8',
            'nome' => 'Loja Mercado Pago Integrada',
            'cnpj_cpf' => '99.888.777/0001-66',
            'cnpj_cpf_bidx' => 'bidx_doc_mp_fase8',
            'telefone_whatsapp' => '31988889999',
            'plano_id' => $plano->id,
            'status' => 'ativa',
            'mp_access_token' => 'TEST-ACCESS-TOKEN-STORE-999',
        ]);

        $this->dono = Usuario::create([
            'loja_id' => $this->loja->id,
            'nome' => 'Dono MP',
            'email' => 'dono@mercadopago.com',
            'email_bidx' => 'bidx_dono_mp',
            'senha_hash' => Hash::make('secret'),
            'papel' => 'dono_loja',
            'ativo' => true,
        ]);

        app(LojaContext::class)->setLoja($this->loja);

        $this->pedido = Pedido::create([
            'codigo_publico' => 'MP01',
            'canal' => 'delivery',
            'status' => 'novo',
            'subtotal' => 5000,
            'total' => 5000,
            'forma_pagamento' => 'pix',
            'pagamento_status' => 'pendente',
        ]);

        $this->pagamento = Pagamento::create([
            'loja_id' => $this->loja->id,
            'pedido_id' => $this->pedido->id,
            'metodo' => 'pix',
            'valor' => 5000,
            'status' => 'pendente',
            'idempotency_key' => 'idemp_' . Str::random(20),
            'mp_payment_id' => '1234567890',
        ]);

        $this->validator = new WebhookSignatureValidator();
    }

    public function test_signature_validator_verifies_valid_header(): void
    {
        $dataId = '1234567890';
        $xRequestId = 'req_uuid_12345';
        $ts = (string) time();

        $manifest = "id:{$dataId};request-id:{$xRequestId};ts:{$ts};";
        $v1 = hash_hmac('sha256', $manifest, $this->webhookSecret);
        $header = "ts={$ts},v1={$v1}";

        $isValid = $this->validator->isValid($header, $xRequestId, $dataId, $this->webhookSecret);
        $this->assertTrue($isValid);
    }

    public function test_signature_validator_rejects_replay_attacks_older_than_5_minutes(): void
    {
        $dataId = '1234567890';
        $xRequestId = 'req_uuid_expired';
        $oldTs = (string) (time() - 360); // 6 minutes old

        $manifest = "id:{$dataId};request-id:{$xRequestId};ts:{$oldTs};";
        $v1 = hash_hmac('sha256', $manifest, $this->webhookSecret);
        $header = "ts={$oldTs},v1={$v1}";

        $isValid = $this->validator->isValid($header, $xRequestId, $dataId, $this->webhookSecret);
        $this->assertFalse($isValid);
    }

    public function test_signature_validator_rejects_tampered_payload(): void
    {
        $dataId = '1234567890';
        $xRequestId = 'req_uuid_12345';
        $ts = (string) time();

        // Forged signature
        $header = "ts={$ts},v1=forged_invalid_signature_hash";

        $isValid = $this->validator->isValid($header, $xRequestId, $dataId, $this->webhookSecret);
        $this->assertFalse($isValid);
    }

    public function test_valid_webhook_reconciles_payment_and_auto_advances_order(): void
    {
        Event::fake([PagamentoConfirmado::class]);

        $dataId = $this->pagamento->mp_payment_id;
        $xRequestId = 'req_uuid_test';
        $ts = (string) time();

        $manifest = "id:{$dataId};request-id:{$xRequestId};ts:{$ts};";
        $v1 = hash_hmac('sha256', $manifest, $this->webhookSecret);
        $header = "ts={$ts},v1={$v1}";

        $payload = [
            'type' => 'payment',
            'action' => 'payment.updated',
            'data' => [
                'id' => $dataId,
            ],
        ];

        $response = $this->withHeaders([
            'x-signature' => $header,
            'x-request-id' => $xRequestId,
        ])->postJson('/api/webhooks/mercadopago', $payload);

        $response->assertStatus(200);

        // Asserts database status updates
        $this->assertSame('aprovado', $this->pagamento->fresh()->status);
        $this->assertSame('aprovado', $this->pedido->fresh()->pagamento_status);
        $this->assertSame('aceito', $this->pedido->fresh()->status); // Auto-advanced from 'novo' to 'aceito'

        // Asserts audit logging in webhook_logs with encrypted payload
        $this->assertDatabaseHas('webhook_logs', [
            'origem' => 'mercadopago',
            'processado' => true,
        ]);

        Event::assertDispatched(PagamentoConfirmado::class);
    }

    public function test_oauth_status_endpoint_never_leaks_tokens(): void
    {
        $response = $this->actingAs($this->dono, 'sanctum')->getJson('/api/pagamentos/mercadopago/status');

        $response->assertStatus(200);
        $response->assertJson(['conectado' => true]);

        // Asserts raw secret tokens are NEVER exposed in API response
        $content = $response->getContent() ?: '';
        $this->assertStringNotContainsString('TEST-ACCESS-TOKEN-STORE-999', $content);
        $this->assertStringNotContainsString('mp_access_token', $content);
        $this->assertStringNotContainsString('mp_refresh_token', $content);
    }
}
