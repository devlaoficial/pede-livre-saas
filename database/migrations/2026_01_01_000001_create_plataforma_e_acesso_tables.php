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
        // 1. planos
        Schema::create('planos', function (Blueprint $table): void {
            $table->id();
            $table->string('nome', 50)->unique(); // delivery, presencial, premium
            $table->unsignedInteger('preco_centavos');
            $table->json('recursos_json');
            $table->unsignedSmallInteger('limite_usuarios')->default(2);
            $table->timestamps();
        });

        // 2. lojas
        Schema::create('lojas', function (Blueprint $table): void {
            $table->id();
            $table->string('slug', 80)->unique();
            $table->string('dominio_proprio', 120)->nullable()->unique();
            $table->string('nome', 120);
            $table->text('cnpj_cpf'); // Encrypted at rest
            $table->char('cnpj_cpf_bidx', 64)->index(); // HMAC-SHA256 blind index
            $table->string('telefone_whatsapp', 30);
            $table->string('logo', 255)->nullable();
            $table->string('capa', 255)->nullable();
            $table->string('cor_primaria', 10)->default('#E11D48');
            $table->string('endereco', 255)->nullable();
            $table->string('cidade', 100)->nullable();
            $table->char('uf', 2)->nullable();
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            $table->foreignId('plano_id')->constrained('planos')->cascadeOnUpdate()->restrictOnDelete();
            $table->enum('status', ['trial', 'ativa', 'inadimplente', 'suspensa', 'cancelada'])->default('trial')->index();
            $table->timestamp('trial_ate')->nullable();
            $table->json('config_json')->nullable();
            $table->text('mp_access_token')->nullable(); // Encrypted at rest
            $table->text('mp_refresh_token')->nullable(); // Encrypted at rest
            $table->text('mp_user_id')->nullable(); // Encrypted at rest
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. usuarios
        Schema::create('usuarios', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loja_id')->nullable()->constrained('lojas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('nome', 120);
            $table->text('email'); // Encrypted at rest
            $table->char('email_bidx', 64)->unique(); // Blind index
            $table->text('telefone')->nullable(); // Encrypted at rest
            $table->string('senha_hash', 255); // Argon2id
            $table->enum('papel', [
                'super_admin',
                'suporte',
                'dono_loja',
                'gerente',
                'caixa',
                'cozinha',
                'garcom',
                'entregador'
            ])->default('dono_loja');
            $table->text('two_factor_secret')->nullable(); // Encrypted at rest
            $table->boolean('ativo')->default(true);
            $table->timestamp('ultimo_login')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['loja_id', 'papel', 'ativo']);
        });

        // 4. assinaturas
        Schema::create('assinaturas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('plano_id')->constrained('planos')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('status', 30)->default('ativa');
            $table->enum('ciclo', ['mensal', 'anual'])->default('mensal');
            $table->date('proximo_vencimento');
            $table->text('mp_subscription_id')->nullable(); // Encrypted at rest
            $table->timestamp('cancelada_em')->nullable();
            $table->string('motivo_cancelamento', 255)->nullable();
            $table->timestamps();

            $table->index(['loja_id', 'status']);
        });

        // 5. faturas
        Schema::create('faturas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('assinatura_id')->constrained('assinaturas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedInteger('valor_centavos');
            $table->enum('status', ['pendente', 'paga', 'vencida', 'estornada'])->default('pendente');
            $table->date('vencimento');
            $table->timestamp('pago_em')->nullable();
            $table->string('mp_payment_id', 100)->nullable()->index();
            $table->text('pix_copia_cola')->nullable(); // Encrypted at rest
            $table->timestamps();

            $table->index(['loja_id', 'status', 'vencimento']);
        });

        // 6. auditoria
        Schema::create('auditoria', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loja_id')->nullable()->constrained('lojas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->cascadeOnUpdate()->nullOnDelete();
            $table->string('acao', 80);
            $table->string('entidade', 80);
            $table->unsignedBigInteger('entidade_id')->nullable();
            $table->text('antes_json')->nullable(); // Redacted & Encrypted at rest
            $table->text('depois_json')->nullable(); // Redacted & Encrypted at rest
            $table->char('ip_hash', 64);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['loja_id', 'entidade', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auditoria');
        Schema::dropIfExists('faturas');
        Schema::dropIfExists('assinaturas');
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('lojas');
        Schema::dropIfExists('planos');
    }
};
