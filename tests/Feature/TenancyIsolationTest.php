<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Assinaturas\Models\Plano;
use App\Domain\Autenticacao\Models\Usuario;
use App\Support\Tenancy\LojaContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class TenancyIsolationTest extends TestCase
{
    use RefreshDatabase;

    private Plano $plano;
    private Loja $lojaA;
    private Loja $lojaB;
    private Usuario $userA;
    private Usuario $userB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plano = Plano::create([
            'nome' => 'premium',
            'preco_centavos' => 14990,
            'recursos_json' => ['cardapio_online' => true, 'kds' => true],
            'limite_usuarios' => 10,
        ]);

        $this->lojaA = Loja::create([
            'slug' => 'pizzaria-loja-a',
            'nome' => 'Pizzaria Loja A',
            'cnpj_cpf' => '12.345.678/0001-90',
            'cnpj_cpf_bidx' => hash_hmac('sha256', '12345678000190', (string) config('app.blind_index_key')),
            'telefone_whatsapp' => '31988887777',
            'plano_id' => $this->plano->id,
            'status' => 'ativa',
        ]);

        $this->lojaB = Loja::create([
            'slug' => 'hamburgueria-loja-b',
            'nome' => 'Hamburgueria Loja B',
            'cnpj_cpf' => '98.765.432/0001-10',
            'cnpj_cpf_bidx' => hash_hmac('sha256', '98765432000110', (string) config('app.blind_index_key')),
            'telefone_whatsapp' => '31977778888',
            'plano_id' => $this->plano->id,
            'status' => 'ativa',
        ]);

        $this->userA = Usuario::create([
            'loja_id' => $this->lojaA->id,
            'nome' => 'Dono Loja A',
            'email' => 'dono@lojaa.com.br',
            'email_bidx' => hash_hmac('sha256', 'dono@lojaa.com.br', (string) config('app.blind_index_key')),
            'senha_hash' => Hash::make('SecretPassword123!'),
            'papel' => 'dono_loja',
            'ativo' => true,
        ]);

        $this->userB = Usuario::create([
            'loja_id' => $this->lojaB->id,
            'nome' => 'Dono Loja B',
            'email' => 'dono@lojab.com.br',
            'email_bidx' => hash_hmac('sha256', 'dono@lojab.com.br', (string) config('app.blind_index_key')),
            'senha_hash' => Hash::make('SecretPassword123!'),
            'papel' => 'dono_loja',
            'ativo' => true,
        ]);
    }

    public function test_user_a_cannot_read_or_access_store_b_resources(): void
    {
        // Set tenant context to Store A
        app(LojaContext::class)->setLoja($this->lojaA);

        // Store A context should not find user B via scoped query
        $foundUser = Usuario::where('id', $this->userB->id)->first();
        $this->assertNull($foundUser, 'Store A context must not find users belonging to Store B.');

        // Direct Eloquent find should also fail (returns null or 404 in controller)
        $this->assertNull(Usuario::find($this->userB->id));
    }

    public function test_automatic_injection_of_loja_id_on_creating(): void
    {
        app(LojaContext::class)->setLoja($this->lojaA);

        $newStaff = Usuario::create([
            'nome' => 'Garçom Loja A',
            'email' => 'garcom@lojaa.com.br',
            'email_bidx' => hash_hmac('sha256', 'garcom@lojaa.com.br', (string) config('app.blind_index_key')),
            'senha_hash' => Hash::make('SecretPassword123!'),
            'papel' => 'garcom',
            'ativo' => true,
        ]);

        $this->assertSame($this->lojaA->id, $newStaff->loja_id, 'BelongsToLoja must automatically inject active loja_id.');
    }

    public function test_cross_tenant_api_attempt_returns_404_not_found(): void
    {
        $this->actingAs($this->userA, 'sanctum');

        // Setting context to Store A
        app(LojaContext::class)->setLoja($this->lojaA);

        // Attempting to access Store B's user endpoint
        $response = $this->getJson("/api/usuarios/{$this->userB->id}");

        // Must return 404 Not Found to prevent leaking existence of foreign records
        $response->assertStatus(404);
    }
}
