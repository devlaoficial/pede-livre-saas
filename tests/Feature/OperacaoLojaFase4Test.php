<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Assinaturas\Models\Plano;
use App\Domain\Autenticacao\Models\Usuario;
use App\Domain\Catalogo\Services\HorarioFuncionamentoService;
use App\Domain\Entregas\Models\AreaEntrega;
use App\Domain\Entregas\Models\Entregador;
use App\Domain\Mesas\Events\GarcomChamado;
use App\Domain\Mesas\Models\Mesa;
use App\Domain\Operacao\Models\ExcecaoHorario;
use App\Domain\Operacao\Models\Horario;
use App\Support\Tenancy\LojaContext;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class OperacaoLojaFase4Test extends TestCase
{
    use RefreshDatabase;

    private Loja $loja;
    private Usuario $dono;
    private Usuario $garcom;
    private HorarioFuncionamentoService $horarioService;

    protected function setUp(): void
    {
        parent::setUp();

        $plano = Plano::create([
            'nome' => 'premium',
            'preco_centavos' => 24990,
            'recursos_json' => ['cardapio_online' => true, 'canal_mesa_balcao' => true],
            'limite_usuarios' => 10,
        ]);

        $this->loja = Loja::create([
            'slug' => 'pizzaria-fase4',
            'nome' => 'Pizzaria Nobre',
            'cnpj_cpf' => '44.444.444/0001-44',
            'cnpj_cpf_bidx' => 'bidx_doc_fase4',
            'telefone_whatsapp' => '31988884444',
            'plano_id' => $plano->id,
            'status' => 'ativa',
            'config_json' => ['timezone' => 'America/Sao_Paulo', 'tempo_medio_preparo_min' => 30],
        ]);

        $this->dono = Usuario::create([
            'loja_id' => $this->loja->id,
            'nome' => 'Dono Nobre',
            'email' => 'dono@nobre.com',
            'email_bidx' => 'bidx_dono_nobre',
            'senha_hash' => Hash::make('secret123'),
            'papel' => 'dono_loja',
            'ativo' => true,
        ]);

        $this->garcom = Usuario::create([
            'loja_id' => $this->loja->id,
            'nome' => 'Garçom Nobre',
            'email' => 'garcom@nobre.com',
            'email_bidx' => 'bidx_garcom_nobre',
            'senha_hash' => Hash::make('secret123'),
            'papel' => 'garcom',
            'ativo' => true,
        ]);

        app(LojaContext::class)->setLoja($this->loja);
        $this->horarioService = new HorarioFuncionamentoService();
    }

    public function test_operating_hours_service_detects_open_and_closed_shifts(): void
    {
        Horario::create([
            'loja_id' => $this->loja->id,
            'canal' => 'delivery',
            'dia_semana' => 1,
            'abre' => '18:00:00',
            'fecha' => '23:00:00',
        ]);

        $horaAberta = CarbonImmutable::parse('2026-11-02 19:30:00', 'America/Sao_Paulo');
        $statusAberto = $this->horarioService->avaliarStatusLoja($this->loja, 'delivery', $horaAberta);
        $this->assertTrue($statusAberto['aberto_agora']);

        $horaFechada = CarbonImmutable::parse('2026-11-02 15:00:00', 'America/Sao_Paulo');
        $statusFechado = $this->horarioService->avaliarStatusLoja($this->loja, 'delivery', $horaFechada);
        $this->assertFalse($statusFechado['aberto_agora']);
        $this->assertSame('hoje às 18:00', $statusFechado['proximo_horario_abertura']);
    }

    public function test_table_qr_token_resolution_and_waiter_call_rate_limiter(): void
    {
        Event::fake([GarcomChamado::class]);

        $mesa = Mesa::create([
            'loja_id' => $this->loja->id,
            'numero' => 7,
            'nome' => 'Mesa VIP 7',
            'capacidade' => 4,
            'status' => 'livre',
        ]);

        $response = $this->getJson("/api/p/{$this->loja->slug}/mesa/{$mesa->qr_token}");
        $response->assertStatus(200);

        $call1 = $this->postJson("/api/p/{$this->loja->slug}/mesa/{$mesa->qr_token}/chamar");
        $call1->assertStatus(200);
        Event::assertDispatched(GarcomChamado::class);

        $call2 = $this->postJson("/api/p/{$this->loja->slug}/mesa/{$mesa->qr_token}/chamar");
        $call2->assertStatus(429);
    }

    public function test_delivery_area_crud_and_validation(): void
    {
        // Dono can create delivery zone
        $response = $this->actingAs($this->dono, 'sanctum')->postJson('/api/areas-entrega', [
            'tipo' => 'raio',
            'raio_km_de' => 0.0,
            'raio_km_ate' => 5.0,
            'taxa_centavos' => 700,
            'tempo_estimado_min' => 35,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('areas_entrega', ['taxa_centavos' => 700, 'loja_id' => $this->loja->id]);

        // Garçom is forbidden
        $responseForbidden = $this->actingAs($this->garcom, 'sanctum')->postJson('/api/areas-entrega', [
            'tipo' => 'bairro',
            'nome_bairro' => 'Centro',
            'taxa_centavos' => 500,
        ]);
        $responseForbidden->assertStatus(403);
    }

    public function test_table_admin_qr_token_regeneration(): void
    {
        $mesa = Mesa::create([
            'loja_id' => $this->loja->id,
            'numero' => 12,
            'status' => 'livre',
        ]);
        $oldToken = $mesa->qr_token;

        $response = $this->actingAs($this->dono, 'sanctum')->postJson("/api/mesas/{$mesa->id}/regenerar-qr");
        $response->assertStatus(200);

        $mesa->refresh();
        $this->assertNotEquals($oldToken, $mesa->qr_token);
        $this->assertSame(32, strlen($mesa->qr_token));
    }

    public function test_courier_creation_encrypts_phone_and_generates_blind_index(): void
    {
        $rawPhone = '31988889999';

        $response = $this->actingAs($this->dono, 'sanctum')->postJson('/api/entregadores', [
            'nome' => 'João Motoboy Rápido',
            'telefone' => $rawPhone,
            'tipo' => 'freelancer',
            'valor_por_entrega_centavos' => 800,
        ]);

        $response->assertStatus(201);

        $entregador = Entregador::where('loja_id', $this->loja->id)->first();
        $this->assertNotNull($entregador);

        // Model cast decrypts seamlessly
        $this->assertSame($rawPhone, $entregador->telefone);

        // Raw database check proves ciphertext
        $rawRow = DB::table('entregadores')->where('id', $entregador->id)->first();
        $this->assertNotNull($rawRow);
        $this->assertNotEquals($rawPhone, $rawRow->telefone);
        $this->assertNotEmpty($rawRow->telefone_bidx);
    }
}
