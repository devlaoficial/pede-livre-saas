<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Assinaturas\Models\Plano;
use App\Domain\Autenticacao\Models\Usuario;
use App\Domain\Clientes\Models\Cliente;
use App\Domain\Fidelidade\Services\CashbackService;
use App\Domain\Pedidos\Models\Pedido;
use App\Support\Tenancy\LojaContext;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class CrmCashbackLgpdBotTest extends TestCase
{
    use RefreshDatabase;

    private Loja $loja;
    private Usuario $dono;
    private Cliente $cliente;
    private CashbackService $cashbackService;
    private string $metaAppSecret;

    protected function setUp(): void
    {
        parent::setUp();

        $this->metaAppSecret = 'meta_cloud_secret_key_32_bytes_long!';
        config(['services.whatsapp_meta.app_secret' => $this->metaAppSecret]);

        $plano = Plano::create([
            'nome' => 'premium',
            'preco_centavos' => 24990,
            'recursos_json' => [
                'cardapio_online' => true,
                'crm_cashback' => true,
                'bot_whatsapp' => true,
            ],
            'limite_usuarios' => 9999,
        ]);

        $this->loja = Loja::create([
            'slug' => 'loja-bot-fase11',
            'nome' => 'Pizzaria Tech Bot',
            'cnpj_cpf' => '30.303.030/0001-30',
            'telefone_whatsapp' => '31988883030',
            'plano_id' => $plano->id,
            'status' => 'ativa',
            'config_json' => [
                'cashback_pct' => 10.0, // 10% cashback
                'cashback_max_resgate_pct' => 50.0, // Max 50% of order total
            ],
        ]);

        $this->dono = Usuario::create([
            'loja_id' => $this->loja->id,
            'nome' => 'Dono Bot',
            'email' => 'dono@bot11.com',
            'senha_hash' => Hash::make('secret'),
            'papel' => 'dono_loja',
            'ativo' => true,
        ]);

        app(LojaContext::class)->setLoja($this->loja);

        $this->cliente = Cliente::create([
            'loja_id' => $this->loja->id,
            'nome' => 'Mariana Costa',
            'telefone' => '31999991111',
            'email' => 'mariana@cliente.com',
            'aceita_marketing' => true,
            'consentimento_em' => now(),
            'saldo_cashback_centavos' => 0,
        ]);

        $this->cashbackService = app(CashbackService::class);
    }

    public function test_cashback_is_credited_only_on_completed_order_and_respects_redemption_cap(): void
    {
        $pedido = Pedido::create([
            'codigo_publico' => 'CB01',
            'tracking_token_hash' => 'hash_cb01',
            'canal' => 'delivery',
            'status' => 'entregue',
            'cliente_id' => $this->cliente->id,
            'subtotal' => 10000,
            'total' => 10000, // R$ 100,00 -> 10% cashback = 1000 centavos (R$ 10,00)
            'forma_pagamento' => 'pix',
        ]);

        $mov = $this->cashbackService->creditarPorPedidoConcluido($pedido);
        $this->assertNotNull($mov);
        $this->assertSame(1000, $mov->valor);
        $this->assertSame(1000, $this->cliente->fresh()->saldo_cashback_centavos);

        // Attempting to redeem R$ 10,00 on a R$ 15,00 order (max 50% cap = R$ 7,50 = 750 centavos) must fail
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('O resgate de cashback não pode ultrapassar 50%');

        $this->cashbackService->debitarResgate($this->loja, $this->cliente->fresh(), 1000, 1500);
    }

    public function test_whatsapp_meta_webhook_signature_human_handoff_and_lgpd_opt_out(): void
    {
        // 1. Forged signature is rejected with 403
        $payload1 = json_encode([
            'loja_slug' => $this->loja->slug,
            'from' => '31999991111',
            'text' => 'Quero falar com ATENDENTE',
        ]) ?: '';

        $resForged = $this->withHeaders([
            'X-Hub-Signature-256' => 'sha256=invalid_forged_signature',
            'Content-Type' => 'application/json',
        ])->call('POST', '/api/webhooks/whatsapp', [], [], [], ['HTTP_X-Hub-Signature-256' => 'sha256=invalid'], $payload1);

        $resForged->assertStatus(403);

        // 2. Valid signature requesting HUMAN handoff transitions conversation state to 'humano'
        $validSig1 = 'sha256=' . hash_hmac('sha256', $payload1, $this->metaAppSecret);
        $resHuman = $this->call('POST', '/api/webhooks/whatsapp', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_HUB_SIGNATURE_256' => $validSig1,
        ], $payload1);

        $resHuman->assertStatus(200);
        $resHuman->assertJson([
            'estado' => 'humano',
            'transbordo_humano' => true,
        ]);

        // 3. Valid signature sending 'PARAR' immediately disables marketing consent (LGPD Opt-Out)
        $payloadOptOut = json_encode([
            'loja_slug' => $this->loja->slug,
            'from' => '31999991111',
            'text' => 'PARAR',
        ]) ?: '';
        $validSig2 = 'sha256=' . hash_hmac('sha256', $payloadOptOut, $this->metaAppSecret);

        $resOptOut = $this->call('POST', '/api/webhooks/whatsapp', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_HUB_SIGNATURE_256' => $validSig2,
        ], $payloadOptOut);

        $resOptOut->assertStatus(200);
        $resOptOut->assertJson(['opt_out_aplicado' => true]);
        $this->assertFalse($this->cliente->fresh()->aceita_marketing);
    }

    public function test_lgpd_anonymization_scrubs_pii_while_preserving_fiscal_order_totals(): void
    {
        $pedido = Pedido::create([
            'codigo_publico' => 'LG01',
            'tracking_token_hash' => 'hash_lg01',
            'canal' => 'delivery',
            'status' => 'entregue',
            'cliente_id' => $this->cliente->id,
            'subtotal' => 8500,
            'taxa_entrega' => 700,
            'total' => 9200,
            'forma_pagamento' => 'pix',
            'endereco_json' => [
                'rua' => 'Rua Secreta do Cliente',
                'numero' => '999',
                'bairro' => 'Savassi',
            ],
        ]);

        // Export customer data first
        $resExport = $this->actingAs($this->dono, 'sanctum')
            ->getJson('/api/lgpd/exportar?telefone=31999991111');
        $resExport->assertStatus(200);
        $resExport->assertJsonPath('titular.nome', 'Mariana Costa');

        // Anonymize customer data
        $resAnon = $this->actingAs($this->dono, 'sanctum')
            ->deleteJson('/api/lgpd/excluir?telefone=31999991111');
        $resAnon->assertStatus(200);

        $clienteAnon = $this->cliente->fresh();
        $pedidoAnon = $pedido->fresh();

        $this->assertSame('Titular Anonimizado (LGPD)', $clienteAnon->nome);
        $this->assertSame('[ANONIMIZADO LGPD]', $pedidoAnon->endereco_json['rua']);

        // Fiscal financial totals remain 100% preserved
        $this->assertSame(9200, $pedidoAnon->total);
        $this->assertSame(8500, $pedidoAnon->subtotal);
    }
}
