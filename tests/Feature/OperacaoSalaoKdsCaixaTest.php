<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Assinaturas\Models\Plano;
use App\Domain\Autenticacao\Models\Usuario;
use App\Domain\Catalogo\Models\Categoria;
use App\Domain\Catalogo\Models\Produto;
use App\Domain\Mesas\Models\Comanda;
use App\Domain\Mesas\Models\Mesa;
use App\Domain\Mesas\Services\ComandaService;
use App\Domain\Pedidos\Models\Pedido;
use App\Domain\Pedidos\Models\PedidoItem;
use App\Support\Tenancy\LojaContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class OperacaoSalaoKdsCaixaTest extends TestCase
{
    use RefreshDatabase;

    private Loja $loja;
    private Usuario $dono;
    private Usuario $garcom;
    private Usuario $cozinha;
    private ComandaService $comandaService;

    protected function setUp(): void
    {
        parent::setUp();

        $plano = Plano::create([
            'nome' => 'premium',
            'preco_centavos' => 24990,
            'recursos_json' => [
                'cardapio_online' => true,
                'canal_mesa_balcao' => true,
                'comandas_divisao' => true,
                'kds' => 'full',
            ],
            'limite_usuarios' => 9999,
        ]);

        $this->loja = Loja::create([
            'slug' => 'salao-fase10',
            'nome' => 'Restaurante Salão Nobre',
            'cnpj_cpf' => '20.202.020/0001-20',
            'telefone_whatsapp' => '31988882020',
            'plano_id' => $plano->id,
            'status' => 'ativa',
        ]);

        $this->dono = Usuario::create([
            'loja_id' => $this->loja->id,
            'nome' => 'Dono Salão',
            'email' => 'dono@salao10.com',
            'senha_hash' => Hash::make('secret'),
            'papel' => 'dono_loja',
            'ativo' => true,
        ]);

        $this->garcom = Usuario::create([
            'loja_id' => $this->loja->id,
            'nome' => 'Garçom Salão',
            'email' => 'garcom@salao10.com',
            'senha_hash' => Hash::make('secret'),
            'papel' => 'garcom',
            'ativo' => true,
        ]);

        $this->cozinha = Usuario::create([
            'loja_id' => $this->loja->id,
            'nome' => 'Chef Cozinha',
            'email' => 'cozinha@salao10.com',
            'senha_hash' => Hash::make('secret'),
            'papel' => 'cozinha',
            'ativo' => true,
        ]);

        app(LojaContext::class)->setLoja($this->loja);
        $this->comandaService = app(ComandaService::class);
    }

    public function test_exact_centavos_bill_splitting_handles_remainder_without_rounding_loss(): void
    {
        $mesa = Mesa::create(['numero' => 1, 'capacidade' => 4, 'status' => 'livre']);
        $comanda = $this->comandaService->abrirComanda($mesa, null, 0); // 0% service fee for exact R$ 100,00 test

        $cat = Categoria::create(['nome' => 'Pratos', 'ordem' => 1, 'ativa' => true, 'canais' => ['mesa']]);
        $prod = Produto::create(['categoria_id' => $cat->id, 'nome' => 'Picanha', 'preco_centavos' => 10000, 'canais' => ['mesa']]);

        $pedido = Pedido::create([
            'codigo_publico' => 'M001',
            'tracking_token_hash' => 'hash_m001',
            'canal' => 'mesa',
            'status' => 'servido',
            'mesa_id' => $mesa->id,
            'comanda_id' => $comanda->id,
            'subtotal' => 10000,
            'total' => 10000, // R$ 100,00 = 10000 centavos
            'forma_pagamento' => 'pix',
        ]);

        PedidoItem::create([
            'pedido_id' => $pedido->id,
            'produto_id' => $prod->id,
            'nome_snapshot' => 'Picanha',
            'qtd' => 1,
            'preco_unit' => 10000,
            'total' => 10000,
        ]);

        // Split R$ 100,00 (10000 centavos) among 3 people:
        // Base = 3333 centavos, Remainder = 1 centavo -> [1 => 3334, 2 => 3333, 3 => 3333]
        $divisao = $this->comandaService->dividirConta($comanda, 3);

        $this->assertSame(10000, $divisao['total_centavos']);
        $this->assertSame(3333, $divisao['valor_base_pessoa_centavos']);
        $this->assertSame(1, $divisao['resto_centavos']);
        $this->assertSame(3334, $divisao['cotas_por_pessoa_centavos'][1]);
        $this->assertSame(3333, $divisao['cotas_por_pessoa_centavos'][2]);
        $this->assertSame(3333, $divisao['cotas_por_pessoa_centavos'][3]);
        $this->assertSame(10000, array_sum($divisao['cotas_por_pessoa_centavos']));
    }

    public function test_item_transfer_between_tables_logs_audit_and_recalculates_both_comandas(): void
    {
        $mesa1 = Mesa::create(['numero' => 1, 'status' => 'livre']);
        $mesa2 = Mesa::create(['numero' => 2, 'status' => 'livre']);

        $comanda1 = $this->comandaService->abrirComanda($mesa1, null, 0);
        $comanda2 = $this->comandaService->abrirComanda($mesa2, null, 0);

        $cat = Categoria::create(['nome' => 'Bebidas', 'ordem' => 1, 'ativa' => true, 'canais' => ['mesa']]);
        $prod = Produto::create(['categoria_id' => $cat->id, 'nome' => 'Chopp Artesanal', 'preco_centavos' => 1500, 'canais' => ['mesa']]);

        $pedido1 = Pedido::create([
            'codigo_publico' => 'T001',
            'tracking_token_hash' => 'hash_t001',
            'canal' => 'mesa',
            'status' => 'em_preparo',
            'mesa_id' => $mesa1->id,
            'comanda_id' => $comanda1->id,
            'subtotal' => 1500,
            'total' => 1500,
            'forma_pagamento' => 'dinheiro',
        ]);

        $item = PedidoItem::create([
            'pedido_id' => $pedido1->id,
            'produto_id' => $prod->id,
            'nome_snapshot' => 'Chopp Artesanal',
            'qtd' => 1,
            'preco_unit' => 1500,
            'total' => 1500,
        ]);

        $this->comandaService->recalcularTotal($comanda1);
        $this->assertSame(1500, $comanda1->fresh()->total_centavos);
        $this->assertSame(0, $comanda2->fresh()->total_centavos);

        // Transfer item from Comanda 1 to Comanda 2
        $this->comandaService->transferirItem($item, $comanda2, $this->garcom);

        $this->assertSame(0, $comanda1->fresh()->total_centavos);
        $this->assertSame(1500, $comanda2->fresh()->total_centavos);
        $this->assertDatabaseHas('auditoria', [
            'loja_id' => $this->loja->id,
            'acao' => 'TRANSFERIR_ITEM_COMANDA',
            'entidade_id' => $item->id,
        ]);
    }

    public function test_kds_station_filtering_and_role_restrictions_on_caixa(): void
    {
        $cat = Categoria::create(['nome' => 'Geral', 'ordem' => 1, 'ativa' => true, 'canais' => ['mesa']]);
        $prodChapa = Produto::create(['categoria_id' => $cat->id, 'nome' => 'Smash Burger', 'preco_centavos' => 3000, 'estacao_kds' => 'chapa', 'canais' => ['mesa']]);
        $prodBar = Produto::create(['categoria_id' => $cat->id, 'nome' => 'Caipirinha', 'preco_centavos' => 2000, 'estacao_kds' => 'bar', 'canais' => ['mesa']]);

        $pedido = Pedido::create([
            'codigo_publico' => 'KDS1',
            'tracking_token_hash' => 'hash_kds1',
            'canal' => 'mesa',
            'status' => 'em_preparo',
            'subtotal' => 5000,
            'total' => 5000,
            'forma_pagamento' => 'pix',
        ]);

        PedidoItem::create(['pedido_id' => $pedido->id, 'produto_id' => $prodChapa->id, 'nome_snapshot' => 'Smash Burger', 'qtd' => 1, 'preco_unit' => 3000, 'total' => 3000, 'status_cozinha' => 'fila']);
        PedidoItem::create(['pedido_id' => $pedido->id, 'produto_id' => $prodBar->id, 'nome_snapshot' => 'Caipirinha', 'qtd' => 1, 'preco_unit' => 2000, 'total' => 2000, 'status_cozinha' => 'fila']);

        // Kitchen requests only 'chapa' station
        $resKds = $this->actingAs($this->cozinha, 'sanctum')->getJson('/api/kds/tickets?estacao=chapa');
        $resKds->assertStatus(200);
        $this->assertCount(1, $resKds->json('tickets'));
        $this->assertSame('Smash Burger', $resKds->json('tickets.0.produto_nome'));

        // Garçom and Cozinha are strictly blocked from opening or closing cash register (403)
        $resGarcomCaixa = $this->actingAs($this->garcom, 'sanctum')->postJson('/api/caixa/abrir', ['valor_inicial_centavos' => 10000]);
        $resGarcomCaixa->assertStatus(403);

        $resCozinhaRelatorio = $this->actingAs($this->cozinha, 'sanctum')->getJson('/api/relatorios/vendas');
        $resCozinhaRelatorio->assertStatus(403);

        // Dono opens cash register, records sale and sangria, and closes with exact difference calculation
        $resAbrir = $this->actingAs($this->dono, 'sanctum')->postJson('/api/caixa/abrir', ['valor_inicial_centavos' => 10000]); // R$ 100,00
        $resAbrir->assertStatus(201);
        $sessaoId = $resAbrir->json('sessao.id');

        $this->actingAs($this->dono, 'sanctum')->postJson('/api/caixa/movimento', [
            'sessao_id' => $sessaoId,
            'tipo' => 'venda',
            'valor_centavos' => 5000, // +R$ 50,00
        ]);

        $this->actingAs($this->dono, 'sanctum')->postJson('/api/caixa/movimento', [
            'sessao_id' => $sessaoId,
            'tipo' => 'sangria',
            'valor_centavos' => 2000, // -R$ 20,00
        ]);

        // Expected = 10000 + 5000 - 2000 = 13000 centavos. Operator counts 12950 (-50 centavos difference)
        $resFechar = $this->actingAs($this->dono, 'sanctum')->postJson('/api/caixa/fechar', [
            'sessao_id' => $sessaoId,
            'valor_final_centavos' => 12950,
        ]);
        $resFechar->assertStatus(200);
        $this->assertSame(-50, $resFechar->json('sessao.diferenca_centavos'));
    }
}
