<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Assinaturas\Models\Plano;
use App\Domain\Catalogo\Models\Categoria;
use App\Domain\Catalogo\Models\GrupoAdicional;
use App\Domain\Catalogo\Models\OpcaoAdicional;
use App\Domain\Catalogo\Models\Produto;
use App\Domain\Catalogo\Models\ProdutoVariacao;
use App\Domain\Entregas\Models\AreaEntrega;
use App\Domain\Pedidos\Models\Cupom;
use App\Domain\Pedidos\Models\Pedido;
use App\Domain\Pedidos\Services\CalculoPedidoService;
use App\Support\Security\BlindIndexService;
use App\Support\Tenancy\LojaContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class CalculoPedidoServerTest extends TestCase
{
    use RefreshDatabase;

    private Loja $loja;
    private Produto $burger;
    private Produto $pizza;
    private AreaEntrega $areaEntrega;
    private Cupom $cupom;
    private CalculoPedidoService $calculoService;
    private BlindIndexService $blindIndex;

    protected function setUp(): void
    {
        parent::setUp();

        $plano = Plano::create([
            'nome' => 'premium',
            'preco_centavos' => 24990,
            'recursos_json' => ['cardapio_online' => true, 'canal_delivery_retirada' => true],
            'limite_usuarios' => 10,
        ]);

        $this->loja = Loja::create([
            'slug' => 'artesanal-burger',
            'nome' => 'Artesanal Burger & Pizza',
            'cnpj_cpf' => '55.555.555/0001-55',
            'cnpj_cpf_bidx' => 'bidx_doc_fase5',
            'telefone_whatsapp' => '31988885555',
            'lat' => -19.5186,
            'lng' => -42.6306,
            'plano_id' => $plano->id,
            'status' => 'ativa',
            'config_json' => [
                'estrategia_pizza_meio_a_meio' => 'maior_preco',
                'taxa_servico_pct' => 10,
            ],
        ]);

        app(LojaContext::class)->setLoja($this->loja);

        $cat = Categoria::create([
            'nome' => 'Lanches',
            'ordem' => 1,
            'ativa' => true,
            'canais' => ['delivery', 'mesa'],
        ]);

        // Product 1: Burger (R$ 35,00)
        $this->burger = Produto::create([
            'categoria_id' => $cat->id,
            'nome' => 'Super Bacon Burger',
            'preco_centavos' => 3500,
            'ativo' => true,
            'esgotado' => false,
            'canais' => ['delivery', 'mesa'],
        ]);

        // Addon group: Bacon extra (+R$ 5,00)
        $grp = GrupoAdicional::create([
            'nome' => 'Adicionais',
            'minimo' => 0,
            'maximo' => 2,
            'obrigatorio' => false,
            'tipo' => 'adicional',
        ]);
        $optBacon = OpcaoAdicional::create(['grupo_id' => $grp->id, 'nome' => 'Bacon Extra', 'preco_centavos' => 500]);
        $this->burger->gruposAdicionais()->attach($grp->id);

        // Product 2: Pizza with Variations
        $this->pizza = Produto::create([
            'categoria_id' => $cat->id,
            'nome' => 'Pizza Família 8 Fatias',
            'preco_centavos' => 6000,
            'ativo' => true,
            'esgotado' => false,
            'canais' => ['delivery'],
        ]);
        ProdutoVariacao::create(['produto_id' => $this->pizza->id, 'nome' => 'Grande (8 Fatias)', 'preco_centavos' => 6000]);

        // Delivery Zone: Centro (Taxa R$ 7,00, pedido mínimo R$ 25,00)
        $this->areaEntrega = AreaEntrega::create([
            'tipo' => 'bairro',
            'nome_bairro' => 'Centro',
            'taxa_centavos' => 700,
            'pedido_minimo_centavos' => 2500,
            'tempo_estimado_min' => 35,
            'ativa' => true,
        ]);

        // Coupon: 10% off for minimum R$ 30,00
        $this->cupom = Cupom::create([
            'codigo' => 'BEMVINDO10',
            'tipo' => 'percentual',
            'valor' => 10, // 10%
            'minimo_centavos' => 3000,
            'limite_uso_total' => 100,
            'limite_por_cliente' => 1,
            'usos' => 0,
            'canais' => ['delivery'],
            'ativo' => true,
        ]);

        $this->calculoService = app(CalculoPedidoService::class);
        $this->blindIndex = app(BlindIndexService::class);
    }

    public function test_tampered_client_prices_are_completely_ignored(): void
    {
        // Hacker tries to send Burger price as R$ 0,01 (1 centavo) and subtotal R$ 0,01
        $payloadHacker = [
            'canal' => 'delivery',
            'itens' => [
                [
                    'produto_id' => $this->burger->id,
                    'qtd' => 2,
                    'preco_unit' => 1, // Tampered: R$ 0,01
                    'total' => 2,
                ],
            ],
            'endereco' => ['bairro' => 'Centro'],
        ];

        $resultado = $this->calculoService->calcular($this->loja, $payloadHacker);

        // Server recalculates: 2 * R$ 35,00 = R$ 70,00 (7000 centavos) + R$ 7,00 frete = R$ 77,00 (7700 centavos)
        $this->assertSame(7000, $resultado['subtotal_centavos']);
        $this->assertSame(700, $resultado['taxa_entrega_centavos']);
        $this->assertSame(7700, $resultado['total_centavos']);
    }

    public function test_delivery_outside_supported_zones_throws_domain_exception(): void
    {
        $payloadForaDoRaio = [
            'canal' => 'delivery',
            'itens' => [
                ['produto_id' => $this->burger->id, 'qtd' => 1],
            ],
            'endereco' => ['bairro' => 'Bairro Desconhecido Sem Cobertura'],
        ];

        $response = $this->postJson("/api/p/{$this->loja->slug}/frete", [
            'bairro' => 'Bairro Desconhecido Sem Cobertura',
            'subtotal_centavos' => 3500,
        ]);

        $response->assertStatus(422);
        $response->assertJson(['error' => 'FORA_DO_RAIO']);
    }

    public function test_coupon_usage_limits_are_enforced_strictly(): void
    {
        $rawPhone = '31987654321';
        $phoneBidx = $this->blindIndex->generateForPhone($rawPhone);

        // 1. First validation succeeds (Order subtotal R$ 35,00 >= R$ 30,00 min)
        $cupomValido = $this->calculoService->validarCupom(
            $this->loja,
            'BEMVINDO10',
            3500,
            'delivery',
            $phoneBidx
        );
        $this->assertSame($this->cupom->id, $cupomValido->id);

        // 2. Fails if subtotal < minimum
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('O valor mínimo do pedido para utilizar este cupom');

        $this->calculoService->validarCupom(
            $this->loja,
            'BEMVINDO10',
            2000, // R$ 20,00 < R$ 30,00
            'delivery',
            $phoneBidx
        );
    }

    public function test_order_creation_persists_exact_centavos_and_generates_signed_tracking_url(): void
    {
        $idempotencyKey = 'idemp_' . Str::random(20);

        $payload = [
            'idempotency_key' => $idempotencyKey,
            'canal' => 'delivery',
            'cliente' => [
                'nome' => 'Lucas Ferreira',
                'telefone' => '31988887777',
            ],
            'endereco' => [
                'cep' => '35170-000',
                'rua' => 'Rua das Flores',
                'numero' => '120',
                'bairro' => 'Centro',
                'cidade' => 'Coronel Fabriciano',
                'uf' => 'MG',
            ],
            'itens' => [
                ['produto_id' => $this->burger->id, 'qtd' => 1],
            ],
            'cupom_codigo' => 'BEMVINDO10',
            'forma_pagamento' => 'pix',
        ];

        $response = $this->postJson("/api/p/{$this->loja->slug}/pedidos", $payload);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'pedido_id',
            'codigo_publico',
            'total_centavos',
            'tracking_url',
        ]);

        $codigoPublico = $response->json('codigo_publico');

        // Subtotal: 3500. Desconto 10%: 350. Frete: 700. Total: 3500 - 350 + 700 = 3850 centavos (R$ 38,50)
        $this->assertSame(3850, $response->json('total_centavos'));

        // Query tracking endpoint using generated URL query parameter signature
        $parsedUrl = parse_url($response->json('tracking_url'));
        parse_str($parsedUrl['query'] ?? '', $queryParams);
        $token = $queryParams['t'] ?? '';

        $trackingResponse = $this->getJson("/api/p/{$this->loja->slug}/pedidos/{$codigoPublico}?t={$token}");

        $trackingResponse->assertStatus(200);
        $trackingData = $trackingResponse->json('pedido');

        // Asserts customer phone and street number are masked
        $this->assertStringContainsString('****', $trackingData['cliente']['telefone']);
        $this->assertStringContainsString('***', $trackingData['endereco']);
        $this->assertSame(3850, $trackingData['total_centavos']);
    }

    public function test_honeypot_bot_protection_rejects_spammers(): void
    {
        $payloadSpam = [
            'website' => 'http://spam-link.com', // Honeypot filled
            'idempotency_key' => 'idemp_bot',
            'canal' => 'delivery',
            'cliente' => ['nome' => 'Spam Bot', 'telefone' => '0000000000'],
            'itens' => [['produto_id' => $this->burger->id, 'qtd' => 1]],
            'forma_pagamento' => 'pix',
        ];

        $response = $this->postJson("/api/p/{$this->loja->slug}/pedidos", $payloadSpam);
        $response->assertStatus(403);
    }
}
