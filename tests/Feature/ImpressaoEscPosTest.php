<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Assinaturas\Models\Plano;
use App\Domain\Autenticacao\Models\Usuario;
use App\Domain\Catalogo\Models\Categoria;
use App\Domain\Catalogo\Models\Produto;
use App\Domain\Pedidos\Models\Pedido;
use App\Domain\Pedidos\Models\PedidoItem;
use App\Domain\Pedidos\Models\PrintJob;
use App\Domain\Pedidos\Services\FluxoStatusPedidoService;
use App\Domain\Pedidos\Services\ImpressaoEscPosService;
use App\Support\Tenancy\LojaContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class ImpressaoEscPosTest extends TestCase
{
    use RefreshDatabase;

    private Loja $loja;
    private Usuario $dono;
    private Pedido $pedido;
    private ImpressaoEscPosService $impressaoService;
    private FluxoStatusPedidoService $fluxoService;

    protected function setUp(): void
    {
        parent::setUp();

        $plano = Plano::create([
            'nome' => 'premium',
            'preco_centavos' => 24990,
            'recursos_json' => ['cardapio_online' => true, 'kds' => 'full'],
            'limite_usuarios' => 10,
        ]);

        $this->loja = Loja::create([
            'slug' => 'churrascaria-fase7',
            'nome' => 'Churrascaria & Brasa Nobre',
            'cnpj_cpf' => '88.888.888/0001-88',
            'cnpj_cpf_bidx' => 'bidx_loja_fase7',
            'telefone_whatsapp' => '31988888888',
            'endereco' => 'Av. Brasil, 500',
            'cidade' => 'Ipatinga',
            'uf' => 'MG',
            'plano_id' => $plano->id,
            'status' => 'ativa',
            'config_json' => [
                'impressao_automatica_ao_aceitar' => true,
            ],
        ]);

        $this->dono = Usuario::create([
            'loja_id' => $this->loja->id,
            'nome' => 'Dono Churrascaria',
            'email' => 'dono@churrascaria.com',
            'email_bidx' => 'bidx_dono_fase7',
            'senha_hash' => Hash::make('secret'),
            'papel' => 'dono_loja',
            'ativo' => true,
        ]);

        app(LojaContext::class)->setLoja($this->loja);

        $cat = Categoria::create(['nome' => 'Carnes', 'ordem' => 1, 'ativa' => true, 'canais' => ['delivery']]);
        $prod = Produto::create([
            'categoria_id' => $cat->id,
            'nome' => 'Picanha na Brasa 600g',
            'preco_centavos' => 8990,
            'ativo' => true,
            'esgotado' => false,
            'canais' => ['delivery'],
        ]);

        $this->pedido = Pedido::create([
            'codigo_publico' => 'B4K1',
            'canal' => 'delivery',
            'status' => 'novo',
            'subtotal' => 8990,
            'taxa_entrega' => 800,
            'total' => 9790,
            'forma_pagamento' => 'pix',
            'pagamento_status' => 'aprovado',
            'observacao' => 'Enviar farofa extra se possível.',
            'endereco_json' => [
                'rua' => 'Rua Diamantina',
                'numero' => '42',
                'bairro' => 'Centro',
            ],
        ]);

        PedidoItem::create([
            'pedido_id' => $this->pedido->id,
            'produto_id' => $prod->id,
            'nome_snapshot' => $prod->nome,
            'qtd' => 1,
            'preco_unit' => 8990,
            'total' => 8990,
            'adicionais_json' => [
                ['nome' => 'Molho Chimichurri Artesanal', 'preco_centavos' => 0],
                ['nome' => 'Farofa de Bacon', 'preco_centavos' => 600],
            ],
            'observacao' => 'Carne ao ponto para mal.',
        ]);

        $this->impressaoService = app(ImpressaoEscPosService::class);
        $this->fluxoService = app(FluxoStatusPedidoService::class);
    }

    public function test_comanda_cozinha_layout_generation(): void
    {
        $layout = $this->impressaoService->gerarComandaCozinha($this->pedido);

        // Verifies presence of big order code and channel
        $this->assertStringContainsString('PEDIDO #B4K1', $layout);
        $this->assertStringContainsString('DELIVERY', $layout);

        // Verifies items, addons and kitchen observation
        $this->assertStringContainsString('PICANHA NA BRASA 600G', $layout);
        $this->assertStringContainsString('Molho Chimichurri Artesanal', $layout);
        $this->assertStringContainsString('Carne ao ponto para mal', $layout);
        $this->assertStringContainsString('Enviar farofa extra se possível', $layout);

        // Strict verification: Zero financial clutter in kitchen slip
        $this->assertStringNotContainsString('R$ 89,90', $layout);
        $this->assertStringNotContainsString('Subtotal', $layout);
        $this->assertStringNotContainsString('PIX', $layout);
    }

    public function test_via_cliente_layout_generation(): void
    {
        $layout = $this->impressaoService->gerarViaCliente($this->pedido);

        // Verifies store header and order code
        $this->assertStringContainsString('CHURRASCARIA & BRASA NOBRE', $layout);
        $this->assertStringContainsString('COMPROVANTE DE PEDIDO #B4K1', $layout);

        // Verifies itemized prices and totals
        $this->assertStringContainsString('R$ 89,90', $layout);
        $this->assertStringContainsString('R$ 8,00', $layout); // Taxa de entrega
        $this->assertStringContainsString('R$ 97,90', $layout); // Total final
        $this->assertStringContainsString('PIX (PAGO)', $layout);

        // Verifies masked delivery info
        $this->assertStringContainsString('Rua Diamantina', $layout);
        $this->assertStringContainsString('Centro', $layout);
    }

    public function test_tier3_auto_print_on_accept_enqueues_jobs(): void
    {
        $this->assertSame(0, PrintJob::where('pedido_id', $this->pedido->id)->count());

        // Accept order via FluxoStatusPedidoService
        $this->fluxoService->transicionar($this->pedido, 'aceito', $this->dono);

        // Store config impressao_automatica_ao_aceitar is true -> queues 2 jobs (cozinha and cliente)
        $jobs = PrintJob::where('pedido_id', $this->pedido->id)->get();
        $this->assertCount(2, $jobs);

        $this->assertTrue($jobs->contains('tipo', 'comanda_cozinha'));
        $this->assertTrue($jobs->contains('tipo', 'via_cliente'));
        $this->assertSame('pendente', $jobs->first()?->status);

        // Verifies payload is decrypted seamlessly via cast
        $this->assertStringContainsString('PEDIDO #B4K1', (string) $jobs->first()?->conteudo);
    }

    public function test_tier2_agent_print_queue_api_and_confirmation(): void
    {
        // Enqueue jobs
        $this->impressaoService->enfileirarImpressao($this->pedido, 'ambas');

        // Agent polls queue
        $response = $this->actingAs($this->dono, 'sanctum')->getJson('/api/impressao/fila');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_pendentes',
            'jobs' => [
                '*' => ['id', 'pedido_id', 'tipo', 'conteudo_escpos_base64', 'status' => []],
            ],
        ]);

        $jobId = $response->json('jobs.0.id');

        // Agent confirms successful print
        $confirmResponse = $this->actingAs($this->dono, 'sanctum')->postJson("/api/impressao/{$jobId}/confirmar");
        $confirmResponse->assertStatus(200);
        $this->assertDatabaseHas('print_jobs', ['id' => $jobId, 'status' => 'impresso']);
    }

    public function test_print_queue_tenant_isolation(): void
    {
        // Store B
        $plano = Plano::first();
        $lojaB = Loja::create([
            'slug' => 'loja-b-impressao',
            'nome' => 'Loja B',
            'cnpj_cpf' => '99.999.999/0001-99',
            'cnpj_cpf_bidx' => 'bidx_loja_b_print',
            'telefone_whatsapp' => '31988880099',
            'plano_id' => $plano->id,
            'status' => 'ativa',
        ]);
        $donoB = Usuario::create([
            'loja_id' => $lojaB->id,
            'nome' => 'Dono B',
            'email' => 'dono@lojabprint.com',
            'email_bidx' => 'bidx_user_b_print',
            'senha_hash' => Hash::make('secret'),
            'papel' => 'dono_loja',
            'ativo' => true,
        ]);

        // Enqueue job in Store A
        $jobsA = $this->impressaoService->enfileirarImpressao($this->pedido, 'comanda_cozinha');
        $jobAId = $jobsA[0]->id;

        // Store B agent requests print queue -> Store A job must NOT appear
        $responseB = $this->actingAs($donoB, 'sanctum')->getJson('/api/impressao/fila');
        $responseB->assertStatus(200);
        $this->assertSame(0, $responseB->json('total_pendentes'));

        // Store B agent attempts to confirm Store A's job -> 404 Not Found
        $attackResponse = $this->actingAs($donoB, 'sanctum')->postJson("/api/impressao/{$jobAId}/confirmar");
        $attackResponse->assertStatus(404);
    }
}
