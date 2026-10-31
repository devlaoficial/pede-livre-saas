<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. clientes
        Schema::create('clientes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('nome', 120);
            $table->text('telefone'); // Encrypted at rest
            $table->char('telefone_bidx', 64); // HMAC-SHA256 blind index
            $table->text('email')->nullable(); // Encrypted at rest
            $table->char('email_bidx', 64)->nullable(); // HMAC-SHA256
            $table->boolean('aceita_marketing')->default(true);
            $table->timestamp('consentimento_em')->nullable();
            $table->unsignedInteger('total_pedidos')->default(0);
            $table->unsignedBigInteger('total_gasto')->default(0); // in centavos
            $table->unsignedInteger('saldo_cashback_centavos')->default(0);
            $table->timestamp('ultimo_pedido_em')->nullable();
            $table->timestamps();

            $table->unique(['loja_id', 'telefone_bidx']);
            $table->index(['loja_id', 'email_bidx']);
        });

        // 2. enderecos_cliente
        Schema::create('enderecos_cliente', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('apelido', 60)->nullable(); // e.g. Casa, Trabalho
            $table->text('cep'); // Encrypted at rest
            $table->text('rua'); // Encrypted at rest
            $table->text('numero'); // Encrypted at rest
            $table->text('complemento')->nullable(); // Encrypted at rest
            $table->string('bairro', 100); // Plaintext for zone matching
            $table->string('cidade', 100);
            $table->char('uf', 2);
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            $table->timestamps();

            $table->index(['cliente_id', 'bairro']);
        });

        // 3. cashback_movimentos
        Schema::create('cashback_movimentos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnUpdate()->cascadeOnDelete();
            $table->enum('tipo', ['credito', 'debito', 'expiracao']);
            $table->unsignedInteger('valor'); // in centavos
            $table->foreignId('pedido_id')->nullable()->constrained('pedidos')->nullOnDelete();
            $table->dateTime('expira_em')->nullable();
            $table->timestamps();

            $table->index(['loja_id', 'cliente_id', 'tipo']);
        });

        // 4. avaliacoes
        Schema::create('avaliacoes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('pedido_id')->constrained('pedidos')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedTinyInteger('nota'); // 1 to 5
            $table->text('comentario')->nullable();
            $table->text('resposta_loja')->nullable();
            $table->timestamps();

            $table->index(['loja_id', 'nota']);
        });

        // 5. estoque_movimentos
        Schema::create('estoque_movimentos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('produto_id')->constrained('produtos')->cascadeOnUpdate()->cascadeOnDelete();
            $table->enum('tipo', ['entrada', 'saida', 'ajuste', 'perda']);
            $table->integer('qtd');
            $table->foreignId('pedido_id')->nullable()->constrained('pedidos')->nullOnDelete();
            $table->timestamps();

            $table->index(['loja_id', 'produto_id', 'created_at']);
        });

        // 6. print_jobs (80mm ESC/POS print queue)
        Schema::create('print_jobs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('pedido_id')->constrained('pedidos')->cascadeOnUpdate()->cascadeOnDelete();
            $table->enum('tipo', ['comanda_cozinha', 'via_cliente', 'conta']);
            $table->text('conteudo'); // Encrypted at rest
            $table->enum('status', ['pendente', 'impresso', 'erro'])->default('pendente');
            $table->unsignedTinyInteger('tentativas')->default(0);
            $table->timestamps();

            $table->index(['loja_id', 'status']);
        });

        // 7. webhook_logs
        Schema::create('webhook_logs', function (Blueprint $table): void {
            $table->id();
            $table->enum('origem', ['mercadopago', 'meta']);
            $table->text('payload'); // Encrypted array at rest
            $table->boolean('processado')->default(false);
            $table->text('erro')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['origem', 'processado', 'created_at']);
        });

        // 8. bot_conversas
        Schema::create('bot_conversas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->text('telefone'); // Encrypted
            $table->char('telefone_bidx', 64)->index();
            $table->string('estado', 50)->default('ia_ativa');
            $table->text('contexto_json')->nullable(); // Encrypted array
            $table->timestamps();

            $table->index(['loja_id', 'estado']);
        });

        // 9. bot_mensagens
        Schema::create('bot_mensagens', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('conversa_id')->constrained('bot_conversas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->enum('direcao', ['entrada', 'saida']);
            $table->text('mensagem'); // Encrypted text
            $table->timestamp('created_at')->useCurrent();

            $table->index(['conversa_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_mensagens');
        Schema::dropIfExists('bot_conversas');
        Schema::dropIfExists('webhook_logs');
        Schema::dropIfExists('print_jobs');
        Schema::dropIfExists('estoque_movimentos');
        Schema::dropIfExists('avaliacoes');
        Schema::dropIfExists('cashback_movimentos');
        Schema::dropIfExists('enderecos_cliente');
        Schema::dropIfExists('clientes');
    }
};
