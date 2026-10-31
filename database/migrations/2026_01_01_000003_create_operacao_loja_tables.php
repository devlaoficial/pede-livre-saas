<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. horarios (Multi-shift operating hours per channel and weekday)
        Schema::create('horarios', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->enum('canal', ['delivery', 'mesa', 'balcao', 'retirada'])->default('delivery');
            $table->unsignedTinyInteger('dia_semana'); // 0 = Sunday, 1 = Monday, ..., 6 = Saturday
            $table->time('abre'); // e.g. 11:00:00
            $table->time('fecha'); // e.g. 14:30:00
            $table->timestamps();

            $table->index(['loja_id', 'canal', 'dia_semana']);
        });

        // 2. excecoes_horario (Holidays, custom off-days or override shifts)
        Schema::create('excecoes_horario', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->date('data');
            $table->boolean('fechado')->default(true);
            $table->time('abre')->nullable();
            $table->time('fecha')->nullable();
            $table->string('descricao', 150)->nullable(); // e.g. Feriado Carnaval, Manutenção
            $table->timestamps();

            $table->index(['loja_id', 'data']);
        });

        // 3. areas_entrega (Neighborhood & Haversine radius delivery zones)
        Schema::create('areas_entrega', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->enum('tipo', ['bairro', 'raio'])->default('raio');
            $table->string('nome_bairro', 100)->nullable();
            $table->decimal('raio_km_de', 5, 2)->nullable();
            $table->decimal('raio_km_ate', 5, 2)->nullable();
            $table->unsignedInteger('taxa_centavos'); // Delivery fee in centavos
            $table->unsignedInteger('pedido_minimo_centavos')->default(0);
            $table->unsignedSmallInteger('tempo_estimado_min')->default(40);
            $table->unsignedInteger('frete_gratis_acima_centavos')->nullable();
            $table->boolean('ativa')->default(true);
            $table->timestamps();

            $table->index(['loja_id', 'ativa', 'tipo']);
        });

        // 4. mesas (Table QR Mode with cryptographically random 32-char token)
        Schema::create('mesas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedSmallInteger('numero');
            $table->string('nome', 60)->nullable(); // e.g. Varanda 04, Salão Principal 02
            $table->char('qr_token', 32)->unique(); // Str::random(32) cryptographic token
            $table->unsignedSmallInteger('capacidade')->default(4);
            $table->enum('status', ['livre', 'ocupada', 'aguardando_pagamento'])->default('livre');
            $table->timestamps();

            $table->index(['loja_id', 'status']);
            $table->unique(['loja_id', 'numero']);
        });

        // 5. comandas (Open tabs linked to a table or counter customer)
        Schema::create('comandas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('mesa_id')->nullable()->constrained('mesas')->cascadeOnUpdate()->nullOnDelete();
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->enum('status', ['aberta', 'fechada'])->default('aberta');
            $table->timestamp('aberta_em')->useCurrent();
            $table->timestamp('fechada_em')->nullable();
            $table->unsignedSmallInteger('taxa_servico_pct')->default(10);
            $table->unsignedInteger('total_centavos')->default(0);
            $table->timestamps();

            $table->index(['loja_id', 'status']);
        });

        // 6. caixa_sessoes (Shift cash register sessions)
        Schema::create('caixa_sessoes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamp('aberto_em')->useCurrent();
            $table->timestamp('fechado_em')->nullable();
            $table->unsignedInteger('valor_inicial_centavos');
            $table->unsignedInteger('valor_final_centavos')->nullable();
            $table->integer('diferenca_centavos')->nullable();
            $table->timestamps();

            $table->index(['loja_id', 'usuario_id']);
        });

        // 7. caixa_movimentos (Cash register entries: sales, withdrawals, additions)
        Schema::create('caixa_movimentos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('sessao_id')->constrained('caixa_sessoes')->cascadeOnUpdate()->cascadeOnDelete();
            $table->enum('tipo', ['venda', 'sangria', 'suprimento']);
            $table->unsignedInteger('valor_centavos');
            $table->string('observacao', 255)->nullable();
            $table->timestamps();

            $table->index(['loja_id', 'sessao_id', 'tipo']);
        });

        // 8. entregadores (Couriers with encrypted phone and blind index)
        Schema::create('entregadores', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('nome', 120);
            $table->text('telefone'); // Encrypted at rest
            $table->char('telefone_bidx', 64)->index(); // HMAC-SHA256
            $table->enum('tipo', ['proprio', 'freelancer'])->default('freelancer');
            $table->unsignedInteger('valor_por_entrega_centavos')->default(700); // R$ 7,00 padrão
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index(['loja_id', 'ativo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entregadores');
        Schema::dropIfExists('caixa_movimentos');
        Schema::dropIfExists('caixa_sessoes');
        Schema::dropIfExists('comandas');
        Schema::dropIfExists('mesas');
        Schema::dropIfExists('areas_entrega');
        Schema::dropIfExists('excecoes_horario');
        Schema::dropIfExists('horarios');
    }
};
