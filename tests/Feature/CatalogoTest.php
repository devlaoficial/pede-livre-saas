<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Assinaturas\Models\Plano;
use App\Domain\Autenticacao\Models\Usuario;
use App\Domain\Catalogo\Models\Categoria;
use App\Domain\Catalogo\Models\GrupoAdicional;
use App\Domain\Catalogo\Models\OpcaoAdicional;
use App\Domain\Catalogo\Models\Produto;
use App\Domain\Catalogo\Models\ProdutoVariacao;
use App\Domain\Catalogo\Services\PrecificacaoPizzaService;
use App\Support\Tenancy\LojaContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class CatalogoTest extends TestCase
{
    use RefreshDatabase;

    private Loja $lojaA;
    private Loja $lojaB;
    private Usuario $donoA;
    private Usuario $garcomA;
    private PrecificacaoPizzaService $pizzaService;

    protected function setUp(): void
    {
        parent::setUp();

        $plano = Plano::create([
            'nome' => 'premium',
            'preco_centavos' => 24990,
            'recursos_json' => ['cardapio_online' => true],
            'limite_usuarios' => 10,
        ]);

        $this->lojaA = Loja::create([
            'slug' => 'pizzaria-loja-a',
            'nome' => 'Pizzaria Loja A',
            'cnpj_cpf' => '11.111.111/0001-11',
            'cnpj_cpf_bidx' => 'bidx_doc_a',
            'telefone_whatsapp' => '31988880001',
            'plano_id' => $plano->id,
            'status' => 'ativa',
            'config_json' => [
                'estrategia_pizza_meio_a_meio' => 'maior_preco',
            ],
        ]);

        $this->lojaB = Loja::create([
            'slug' => 'burger-loja-b',
            'nome' => 'Burger Loja B',
            'cnpj_cpf' => '22.222.222/0001-22',
            'cnpj_cpf_bidx' => 'bidx_doc_b',
            'telefone_whatsapp' => '31988880002',
            'plano_id' => $plano->id,
            'status' => 'ativa',
            'config_json' => [
                'estrategia_pizza_meio_a_meio' => 'media',
            ],
        ]);

        $this->donoA = Usuario::create([
            'loja_id' => $this->lojaA->id,
            'nome' => 'Dono A',
            'email' => 'dono@a.com',
            'email_bidx' => 'bidx_dono_a',
            'senha_hash' => Hash::make('password'),
            'papel' => 'dono_loja',
            'ativo' => true,
        ]);

        $this->garcomA = Usuario::create([
            'loja_id' => $this->lojaA->id,
            'nome' => 'Garçom A',
            'email' => 'garcom@a.com',
            'email_bidx' => 'bidx_garcom_a',
            'senha_hash' => Hash::make('password'),
            'papel' => 'garcom',
            'ativo' => true,
        ]);

        $this->pizzaService = new PrecificacaoPizzaService();
    }

    public function test_catalog_tenant_isolation_prevents_cross_store_data_leak(): void
    {
        app(LojaContext::class)->setLoja($this->lojaA);
        $catA = Categoria::create([
            'nome' => 'Pizzas Artesanais Store A',
            'ordem' => 1,
            'ativa' => true,
            'canais' => ['delivery'],
        ]);

        app(LojaContext::class)->setLoja($this->lojaB);
        $catB = Categoria::create([
            'nome' => 'Burgers Store B',
            'ordem' => 1,
            'ativa' => true,
            'canais' => ['delivery'],
        ]);

        app(LojaContext::class)->setLoja($this->lojaA);
        $this->assertCount(1, Categoria::all());
        $this->assertSame($catA->id, Categoria::first()?->id);
        $this->assertNull(Categoria::find($catB->id));

        app(LojaContext::class)->setLoja($this->lojaB);
        $this->assertCount(1, Categoria::all());
        $this->assertSame($catB->id, Categoria::first()?->id);
        $this->assertNull(Categoria::find($catA->id));
    }

    public function test_channel_filtering_scopes(): void
    {
        app(LojaContext::class)->setLoja($this->lojaA);

        $catDeliveryOnly = Categoria::create([
            'nome' => 'Exclusivo Delivery',
            'ordem' => 1,
            'ativa' => true,
            'canais' => ['delivery'],
        ]);

        $catMesaOnly = Categoria::create([
            'nome' => 'Exclusivo Salão / Mesa',
            'ordem' => 2,
            'ativa' => true,
            'canais' => ['mesa'],
        ]);

        $deliveryList = Categoria::porCanal('delivery')->get();
        $mesaList = Categoria::porCanal('mesa')->get();

        $this->assertTrue($deliveryList->contains('id', $catDeliveryOnly->id));
        $this->assertFalse($deliveryList->contains('id', $catMesaOnly->id));

        $this->assertTrue($mesaList->contains('id', $catMesaOnly->id));
        $this->assertFalse($mesaList->contains('id', $catDeliveryOnly->id));
    }

    public function test_half_and_half_pizza_pricing_with_highest_price_strategy(): void
    {
        app(LojaContext::class)->setLoja($this->lojaA);

        $sabor1 = new OpcaoAdicional(['nome' => '1/2 Muçarela Simples', 'preco_centavos' => 0]);
        $sabor2 = new OpcaoAdicional(['nome' => '1/2 Camarão Especial', 'preco_centavos' => 1500]); // +R$ 15,00

        // Base 5000 + max(0, 1500) = 6500 centavos
        $totalCalculado = $this->pizzaService->calcularPrecoPizza($this->lojaA, 5000, [$sabor1, $sabor2]);
        $this->assertSame(6500, $totalCalculado);
    }

    public function test_half_and_half_pizza_pricing_with_average_price_strategy(): void
    {
        app(LojaContext::class)->setLoja($this->lojaB);

        $sabor1 = new OpcaoAdicional(['nome' => '1/2 Calabresa', 'preco_centavos' => 200]);
        $sabor2 = new OpcaoAdicional(['nome' => '1/2 4 Queijos', 'preco_centavos' => 1000]);

        // Base 6000 + round((200 + 1000)/2) = 6600 centavos
        $totalCalculado = $this->pizzaService->calcularPrecoPizza($this->lojaB, 6000, [$sabor1, $sabor2]);
        $this->assertSame(6600, $totalCalculado);
    }

    public function test_rbac_dono_can_create_category_while_garcom_is_forbidden(): void
    {
        app(LojaContext::class)->setLoja($this->lojaA);

        // Dono creates category -> 201 Created
        $responseDono = $this->actingAs($this->donoA, 'sanctum')->postJson('/api/categorias', [
            'nome' => 'Bebidas e Drinks',
            'canais' => ['delivery', 'mesa'],
        ]);
        $responseDono->assertStatus(201);
        $this->assertDatabaseHas('categorias', ['nome' => 'Bebidas e Drinks', 'loja_id' => $this->lojaA->id]);

        // Garçom attempts to create category -> 403 Forbidden
        $responseGarcom = $this->actingAs($this->garcomA, 'sanctum')->postJson('/api/categorias', [
            'nome' => 'Sobremesas Proibidas',
            'canais' => ['delivery'],
        ]);
        $responseGarcom->assertStatus(403);
    }

    public function test_public_menu_endpoint_returns_only_active_channel_items_without_secrets(): void
    {
        app(LojaContext::class)->setLoja($this->lojaA);

        $cat = Categoria::create([
            'nome' => 'Entradas',
            'ordem' => 1,
            'ativa' => true,
            'canais' => ['delivery'],
        ]);

        Produto::create([
            'categoria_id' => $cat->id,
            'nome' => 'Bruschetta Tradicional',
            'preco_centavos' => 1800,
            'ativo' => true,
            'esgotado' => false,
            'canais' => ['delivery'],
        ]);

        $response = $this->getJson("/api/p/{$this->lojaA->slug}/cardapio?canal=delivery");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'canal',
            'categorias' => [
                '*' => ['id', 'nome', 'produtos'],
            ],
        ]);

        // Asserts zero leakage of store secrets or tokens in public output
        $content = $response->getContent() ?: '';
        $this->assertStringNotContainsString('mp_access_token', $content);
        $this->assertStringNotContainsString('cnpj_cpf', $content);
    }
}
