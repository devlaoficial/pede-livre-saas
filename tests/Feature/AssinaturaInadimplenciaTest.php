<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Assinaturas\Models\Assinatura;
use App\Domain\Assinaturas\Models\Fatura;
use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Assinaturas\Models\Plano;
use App\Domain\Assinaturas\Services\AssinaturaPlataformaService;
use App\Domain\Assinaturas\Services\ReguaInadimplenciaService;
use App\Domain\Autenticacao\Models\Usuario;
use App\Support\Tenancy\LojaContext;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class AssinaturaInadimplenciaTest extends TestCase
{
    use RefreshDatabase;

    private Plano $planoPremium;
    private Loja $loja;
    private Usuario $dono;
    private Assinatura $assinatura;
    private ReguaInadimplenciaService $reguaService;
    private AssinaturaPlataformaService $assinaturaService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->planoPremium = Plano::create([
            'nome' => 'premium',
            'preco_centavos' => 24990,
            'recursos_json' => ['cardapio_online' => true, 'canal_mesa_balcao' => true],
            'limite_usuarios' => 9999,
        ]);

        $this->loja = Loja::create([
            'slug' => 'loja-saas-fase9',
            'nome' => 'Restaurante Fase 9',
            'cnpj_cpf' => '10.101.010/0001-10',
            'telefone_whatsapp' => '31988881010',
            'plano_id' => $this->planoPremium->id,
            'status' => 'ativa',
        ]);

        $this->dono = Usuario::create([
            'loja_id' => $this->loja->id,
            'nome' => 'Dono Fase 9',
            'email' => 'dono@fase9.com',
            'senha_hash' => Hash::make('secret'),
            'papel' => 'dono_loja',
            'ativo' => true,
        ]);

        app(LojaContext::class)->setLoja($this->loja);

        $this->assinatura = Assinatura::create([
            'loja_id' => $this->loja->id,
            'plano_id' => $this->planoPremium->id,
            'status' => 'ativa',
            'ciclo' => 'mensal',
            'proximo_vencimento' => '2026-10-01',
        ]);

        $this->reguaService = app(ReguaInadimplenciaService::class);
        $this->assinaturaService = app(AssinaturaPlataformaService::class);
    }

    public function test_3_7_15_day_delinquency_transitions_and_instant_reactivation(): void
    {
        $fatura = Fatura::create([
            'loja_id' => $this->loja->id,
            'assinatura_id' => $this->assinatura->id,
            'valor_centavos' => 24990,
            'status' => 'pendente',
            'vencimento' => '2026-10-01',
        ]);

        // 1. At +3 days overdue (2026-10-04): Sends reminder banner, store remains 'ativa'
        $res3d = $this->reguaService->processarInadimplencia(CarbonImmutable::parse('2026-10-04'));
        $this->assertSame(1, $res3d['lembretes_enviados']);
        $this->assertSame('ativa', $this->loja->fresh()->status);
        $this->assertNotNull($this->loja->fresh()->config_json['aviso_fatura_vencida'] ?? null);

        // 2. At +7 days overdue (2026-10-08): Store transitions to 'inadimplente' (Read-Only Mode 402)
        $res7d = $this->reguaService->processarInadimplencia(CarbonImmutable::parse('2026-10-08'));
        $this->assertSame(1, $res7d['lojas_inadimplentes_7d']);
        $this->assertSame('inadimplente', $this->loja->fresh()->status);

        // Verify Read-Only Mode blocks mutating POST on catalog with 402 Payment Required
        $responseMutate = $this->actingAs($this->dono, 'sanctum')->postJson('/api/categorias', [
            'nome' => 'Categoria Bloqueada',
            'canais' => ['delivery'],
        ]);
        $responseMutate->assertStatus(402);

        // 3. At +15 days overdue (2026-10-16): Store transitions to 'suspensa' (Public menu blocked 403)
        $res15d = $this->reguaService->processarInadimplencia(CarbonImmutable::parse('2026-10-16'));
        $this->assertSame(1, $res15d['lojas_suspensas_15d']);
        $this->assertSame('suspensa', $this->loja->fresh()->status);

        $responsePublicMenu = $this->getJson("/api/p/{$this->loja->slug}");
        $responsePublicMenu->assertStatus(403);

        // 4. Instant Reactivation upon payment of invoice
        $this->assinaturaService->confirmarPagamentoFatura($fatura);
        $this->assertSame('ativa', $this->loja->fresh()->status);
        $this->assertSame('paga', $fatura->fresh()->status);
        $this->assertNull($this->loja->fresh()->config_json['aviso_fatura_vencida'] ?? null);
    }

    public function test_self_service_cancellation_in_one_request(): void
    {
        $response = $this->actingAs($this->dono, 'sanctum')->postJson('/api/assinatura/cancelar', [
            'motivo' => 'Encerramento temporário das atividades para reforma.',
        ]);

        $response->assertStatus(200);
        $this->assertSame('cancelada', $this->assinatura->fresh()->status);
        $this->assertSame('cancelada', $this->loja->fresh()->status);
    }
}
