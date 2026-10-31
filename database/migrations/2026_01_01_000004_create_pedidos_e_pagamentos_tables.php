<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. cupons
        Schema::create('cupons', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('codigo', 50);
            $table->enum('tipo', ['percentual', 'valor', 'frete_gratis']);
            $table->unsignedInteger('valor'); // Percentage (e.g. 10 for 10%) or centavos (e.g. 1500 for R$ 15,00)
            $table->unsignedInteger('minimo_centavos')->default(0);
            $table->dateTime('validade')->nullable();
            $table->unsignedInteger('limite_uso_total')->nullable();
            $table->unsignedSmallInteger('limite_por_cliente')->default(1);
            $table->unsignedInteger('usos')->default(0);
            $table->json('canais')->nullable(); // ["delivery", "retirada", "mesa", "balcao"]
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->unique(['loja_id', 'codigo']);
            $table->index(['loja_id', 'ativo', 'validade']);
        });

        // 2. pedidos
        Schema::create('pedidos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('codigo_publico', 10)->index(); // Short code e.g. A7K2
            $table->char('tracking_token_hash', 64)->index(); // HMAC-SHA256 for public order tracking
            $table->enum('canal', ['delivery', 'retirada', 'mesa', 'balcao'])->default('delivery');
            $table->enum('status', [
                'novo',
                'aceito',
                'em_preparo',
                'pronto',
                'saiu_entrega',
                'entregue',
                'retirado',
                'servido',
                'cancelado'
            ])->default('novo');
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->foreignId('mesa_id')->nullable()->constrained('mesas')->nullOnDelete();
            $table->foreignId('comanda_id')->nullable()->constrained('comandas')->nullOnDelete();
            $table->foreignId('entregador_id')->nullable()->constrained('entregadores')->nullOnDelete();
            
            // Monetary fields exclusively in integer centavos
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('taxa_entrega')->default(0);
            $table->unsignedInteger('desconto')->default(0);
            $table->unsignedInteger('desconto_cashback')->default(0);
            $table->unsignedInteger('taxa_servico')->default(0);
            $table->unsignedInteger('total');

            $table->foreignId('cupom_id')->nullable()->constrained('cupons')->nullOnDelete();
            $table->enum('forma_pagamento', ['pix', 'cartao_online', 'cartao_maquina', 'dinheiro', 'voucher'])->default('pix');
            $table->enum('pagamento_status', ['pendente', 'aprovado', 'recusado', 'estornado'])->default('pendente');
            $table->unsignedInteger('troco_para')->nullable();
            $table->text('observacao')->nullable();
            $table->text('endereco_json')->nullable(); // Encrypted at rest JSON snapshot
            $table->dateTime('agendado_para')->nullable();
            $table->enum('origem', ['cardapio', 'bot', 'painel'])->default('cardapio');
            $table->timestamp('aceito_em')->nullable();
            $table->timestamp('pronto_em')->nullable();
            $table->timestamp('entregue_em')->nullable();
            $table->string('cancelado_motivo', 255)->nullable();
            $table->timestamps();

            $table->index(['loja_id', 'status', 'created_at']);
        });

        // 3. pedido_itens
        Schema::create('pedido_itens', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('produto_id')->constrained('produtos')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('nome_snapshot', 120);
            $table->string('variacao_snapshot', 60)->nullable();
            $table->unsignedSmallInteger('qtd')->default(1);
            $table->unsignedInteger('preco_unit'); // in centavos
            $table->json('adicionais_json')->nullable(); // snapshot of selected addons and options with price in centavos
            $table->string('observacao', 255)->nullable();
            $table->unsignedInteger('total'); // in centavos
            $table->enum('status_cozinha', ['fila', 'preparando', 'pronto'])->default('fila');
            $table->timestamps();

            $table->index(['pedido_id', 'status_cozinha']);
        });

        // 4. pedido_eventos
        Schema::create('pedido_eventos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('status_de', 30)->nullable();
            $table->string('status_para', 30);
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->string('observacao', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['pedido_id', 'created_at']);
        });

        // 5. pagamentos
        Schema::create('pagamentos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('pedido_id')->constrained('pedidos')->cascadeOnUpdate()->cascadeOnDelete();
            $table->enum('metodo', ['pix', 'cartao_online', 'cartao_maquina', 'dinheiro', 'voucher']);
            $table->unsignedInteger('valor'); // in centavos
            $table->enum('status', ['pendente', 'aprovado', 'recusado', 'expirado', 'estornado'])->default('pendente');
            $table->string('mp_payment_id', 100)->nullable()->index();
            $table->text('pix_copia_cola')->nullable(); // Encrypted at rest
            $table->text('pix_qr_base64')->nullable();
            $table->dateTime('expira_em')->nullable();
            $table->string('idempotency_key', 100)->unique();
            $table->timestamps();

            $table->index(['loja_id', 'pedido_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagamentos');
        Schema::dropIfExists('pedido_eventos');
        Schema::dropIfExists('pedido_itens');
        Schema::dropIfExists('pedidos');
        Schema::dropIfExists('cupons');
    }
};
