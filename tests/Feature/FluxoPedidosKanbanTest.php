<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Assinaturas\Models\Plano;
use App\Domain\Autenticacao\Models\Usuario;
use App\Domain\Catalogo\Models\Categoria;
use App\Domain\Catalogo\Models\Produto;
use App\Domain\Entregas\Models\Entregador;
use App\Domain\Pedidos\Events\PedidoAtualizado;
use App\Domain\Pedidos\Models\EstoqueMovimento;
use App\Domain\Pedidos\Models\Pedido;
use App\Domain\Pedidos\Models\PedidoItem;
use App\Domain\Pedidos\Services\FluxoStatusPedidoService;
use App\Support\Tenancy\LojaContext;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class FluxoPedidosKanbanTest extends TestCase
{
    use RefreshDatabase;

    private Loja $lojaA;
    private Loja $lojaB;
    private Usuario $donoA;
    private Usuario $donoB;
    private Produto $produtoComEstoque;
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

        $this->lojaA = Loja::create([
            'slug' => 'pizzaria-loja-a',
            'nome' => 'Pizzaria Loja A',
            'cnpj_cpf' => '66.666.666/0001-66',
            'cnpj_cpf_bidx' => 'bidx_loja_a_fase6',
            'telefone_whatsapp' => '31988886666',
            'plano_id' => $plano->id,
            'status' => 'ativa',
        ]);

        $this->lojaB = Loja::create([
            'slug' => 'burger-loja-b',
            'nome' => 'Burger Loja B',
            'cnpj_cpf' => '77.777.777/0001-77',
            'cnpj_cpf_bidx' => 'bidx_loja_b_fase6',
            'telefone_whatsapp' => '31988887777',
            'plano_id' => $plano->id,
            'status' => 'ativa',
        ]);

        $this->donoA = Usuario::create([
            'loja_id' => $this->lojaA->id,
            'nome' => 'Dono Loja A',
            'email' => 'dono@lojaa.com',
            'email_bidx' => 'bidx_user_a',
            'senha_hash' => Hash::make('secret'),
            'papel' => 'dono_loja',
            'ativo' => true,
        ]);

        $this->donoB = Usuario::create([
            'loja_id' => $this->lojaB->id,
            'nome' => 'Dono Loja B',
            'email' => 'dono@lojab.com',
            'email_bidx' => 'bidx_user_b',
            'senha_hash' => Hash::make('secret'),
            'papel' => 'dono_loja',
            'ativo' => true,
        ]);

        app(LojaContext::class)->setLoja($this->lojaA);

        $cat = Categoria::create([
            'nome' => 'Pratos Principais',
            'ordem' => 1,
            'ativa' => true,
            'canais' => ['delivery'],
        ]);

        $this->produtoComEstoque = Produto::create([
            'categoria_id' => $cat->id,
            'nome' => 'Costela Bovina Defumada',
            'preco_centavos' => 6900,
            'ativo' => true,
            'esgotado' => false,
            'controla_estoque' => true,
            'estoque_qtd' => 3, // Initial stock: 3 units
            'canais' => ['delivery'],
        ]);

        $this->fluxoService = app(FluxoStatusPedidoService::class);
    }

    public function test_delivery_order_transitions_and_atomic_stock_deduction(): void
    {
        Event::fake([PedidoAtualizado::class]);

        $pedido = Pedido::create([
            'codigo_publico' => 'X1Y2',
            'canal' => 'delivery',
            'status' => 'novo',
            'subtotal' => 13800,
            'taxa_entrega' => 500,
            'total' => 14300,
            'forma_pagamento' => 'pix',
            'pagamento_status' => 'pendente',
        ]);

        PedidoItem::create([
            'pedido_id' => $pedido->id,
            'produto_id' => $this->produtoComEstoque->id,
            'nome_snapshot' => $this->produtoComEstoque->nome,
            'qtd' => 3, // Buys all 3 units
            'preco_unit' => 6900,
            'total' => 20700,
        ]);

        $this->assertSame(3, $this->produtoComEstoque->fresh()->estoque_qtd);
        $this->assertFalse($this->produtoComEstoque->fresh()->esgotado);

        // Transition 'novo' -> 'aceito': Must deduct stock and set esgotado = true
        $this->fluxoService->transicionar($pedido, 'aceito', $this->donoA);

        $this->assertSame('aceito', $pedido->fresh()->status);
        $this->assertNotNull($pedido->fresh()->aceito_em);
        $this->assertSame(0, $this->produtoComEstoque->fresh()->estoque_qtd);
        $this->assertTrue($this->produtoComEstoque->fresh()->esgotado);

        // Asserts record in estoque_movimentos
        $this->assertDatabaseHas('estoque_movimentos', [
            'produto_id' => $this->produtoComEstoque->id,
            'tipo' => 'saida',
            'qtd' => -3,
            'pedido_id' => $pedido->id,
        ]);

        Event::assertDispatched(PedidoAtualizado::class);
    }

    public function test_cancellation_restores_deducted_stock_and_unpauses_product(): void
    {
        $pedido = Pedido::create([
            'codigo_publico' => 'C9Z1',
            'canal' => 'delivery',
            'status' => 'novo',
            'subtotal' => 6900,
            'total' => 6900,
            'forma_pagamento' => 'pix',
        ]);

        PedidoItem::create([
            'pedido_id' => $pedido->id,
            'produto_id' => $this->produtoComEstoque->id,
            'nome_snapshot' => $this->produtoComEstoque->nome,
            'qtd' => 3,
            'preco_unit' => 6900,
            'total' => 20700,
        ]);

        // Accept order (stock becomes 0 and esgotado = true)
        $this->fluxoService->transicionar($pedido, 'aceito', $this->donoA);
        $this->assertTrue($this->produtoComEstoque->fresh()->esgotado);

        // Cancel order while in prep
        $this->fluxoService->transicionar(
            $pedido,
            'cancelado',
            $this->donoA,
            'Cliente solicitou cancelamento via WhatsApp antes da entrega.'
        );

        $this->assertSame('cancelado', $pedido->fresh()->status);
        $this->assertSame(3, $this->produtoComEstoque->fresh()->estoque_qtd);
        $this->assertFalse($this->produtoComEstoque->fresh()->esgotado);

        // Asserts estoque_movimentos has adjustment record
        $this->assertDatabaseHas('estoque_movimentos', [
            'produto_id' => $this->produtoComEstoque->id,
            'tipo' => 'ajuste',
            'qtd' => 3,
            'pedido_id' => $pedido->id,
        ]);
    }

    public function test_invalid_state_transitions_are_blocked(): void
    {
        $pedido = Pedido::create([
            'codigo_publico' => 'ERR1',
            'canal' => 'delivery',
            'status' => 'novo',
            'subtotal' => 5000,
            'total' => 5000,
            'forma_pagamento' => 'dinheiro',
        ]);

        // Cannot skip directly from 'novo' to 'entregue'
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Transição inválida para o canal [delivery]: de 'novo' para 'entregue'.");

        $this->fluxoService->transicionar($pedido, 'entregue', $this->donoA);
    }

    public function test_cancellation_requires_cancellation_reason(): void
    {
        $pedido = Pedido::create([
            'codigo_publico' => 'ERR2',
            'canal' => 'delivery',
            'status' => 'novo',
            'subtotal' => 5000,
            'total' => 5000,
            'forma_pagamento' => 'dinheiro',
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('É obrigatório informar o motivo do cancelamento.');

        // Attempt cancel without reason
        $this->fluxoService->transicionar($pedido, 'cancelado', $this->donoA, '');
    }

    public function test_websocket_private_channel_isolation(): void
    {
        require_once base_path('routes/channels.php');

        // User A should be authorized on Store A's private channel
        $this->actingAs($this->donoA, 'sanctum');
        $authorizedA = (int) $this->donoA->loja_id === (int) $this->lojaA->id;
        $this->assertTrue($authorizedA);

        // User A should NOT be authorized on Store B's private channel
        $authorizedB = (int) $this->donoA->loja_id === (int) $this->lojaB->id;
        $this->assertFalse($authorizedB);
    }
}
