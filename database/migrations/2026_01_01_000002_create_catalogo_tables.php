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
        // 1. categorias
        Schema::create('categorias', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('nome', 100);
            $table->string('descricao', 255)->nullable();
            $table->unsignedSmallInteger('ordem')->default(0);
            $table->boolean('ativa')->default(true);
            $table->json('canais'); // ["delivery", "mesa", "balcao"]
            $table->unsignedBigInteger('horario_id')->nullable(); // Optional time-window category
            $table->timestamps();
            $table->softDeletes();

            $table->index(['loja_id', 'ativa', 'ordem']);
        });

        // 2. produtos
        Schema::create('produtos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('categoria_id')->constrained('categorias')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('nome', 120);
            $table->text('descricao')->nullable();
            $table->unsignedInteger('preco_centavos'); // Base price in centavos
            $table->unsignedInteger('preco_promocional_centavos')->nullable();
            $table->string('imagem', 255)->nullable();
            $table->boolean('ativo')->default(true);
            $table->boolean('esgotado')->default(false);
            $table->boolean('controla_estoque')->default(false);
            $table->integer('estoque_qtd')->default(0);
            $table->json('canais'); // ["delivery", "mesa", "balcao"]
            $table->unsignedSmallInteger('ordem')->default(0);
            $table->unsignedSmallInteger('tempo_preparo_min')->default(15);
            $table->string('estacao_kds', 50)->default('geral'); // chapa, forno, sushi, bar, geral
            $table->json('alergenos')->nullable(); // ["gluten", "lactose", "amendoim"]
            $table->string('codigo_pdv', 50)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['loja_id', 'categoria_id', 'ativo', 'esgotado']);
        });

        // 3. produto_variacoes (P, M, G, etc.)
        Schema::create('produto_variacoes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('produto_id')->constrained('produtos')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('nome', 60); // P, M, G, 500ml, etc.
            $table->unsignedInteger('preco_centavos');
            $table->unsignedSmallInteger('ordem')->default(0);
            $table->timestamps();

            $table->index(['loja_id', 'produto_id', 'ordem']);
        });

        // 4. grupos_adicionais
        Schema::create('grupos_adicionais', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('nome', 100);
            $table->unsignedSmallInteger('minimo')->default(0);
            $table->unsignedSmallInteger('maximo')->default(1);
            $table->boolean('obrigatorio')->default(false);
            $table->enum('tipo', ['adicional', 'escolha_unica', 'sabor_meio_a_meio'])->default('adicional');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['loja_id', 'obrigatorio', 'tipo']);
        });

        // 5. opcoes_adicionais
        Schema::create('opcoes_adicionais', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('grupo_id')->constrained('grupos_adicionais')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('nome', 100);
            $table->unsignedInteger('preco_centavos')->default(0);
            $table->boolean('ativo')->default(true);
            $table->boolean('esgotado')->default(false);
            $table->timestamps();

            $table->index(['loja_id', 'grupo_id', 'ativo', 'esgotado']);
        });

        // 6. produto_grupos (pivot linking products to addon groups)
        Schema::create('produto_grupos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('produto_id')->constrained('produtos')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('grupo_id')->constrained('grupos_adicionais')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedSmallInteger('ordem')->default(0);

            $table->unique(['produto_id', 'grupo_id']);
        });

        // 7. combos
        Schema::create('combos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('nome', 120);
            $table->text('descricao')->nullable();
            $table->unsignedInteger('preco_centavos');
            $table->string('imagem', 255)->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['loja_id', 'ativo']);
        });

        // 8. combo_itens
        Schema::create('combo_itens', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('combo_id')->constrained('combos')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('produto_id')->constrained('produtos')->cascadeOnUpdate()->restrictOnDelete();
            $table->unsignedSmallInteger('qtd')->default(1);
            $table->timestamps();

            $table->index(['combo_id', 'produto_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('combo_itens');
        Schema::dropIfExists('combos');
        Schema::dropIfExists('produto_grupos');
        Schema::dropIfExists('opcoes_adicionais');
        Schema::dropIfExists('grupos_adicionais');
        Schema::dropIfExists('produto_variacoes');
        Schema::dropIfExists('produtos');
        Schema::dropIfExists('categorias');
    }
};
