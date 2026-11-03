<?php

declare(strict_types=1);

namespace App\Domain\Assinaturas\Controllers;

use App\Domain\Assinaturas\Models\Assinatura;
use App\Domain\Assinaturas\Models\Auditoria;
use App\Domain\Assinaturas\Models\Fatura;
use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Assinaturas\Models\Plano;
use App\Domain\Assinaturas\Services\ReguaInadimplenciaService;
use App\Domain\Autenticacao\Models\Usuario;
use App\Domain\Catalogo\Models\Categoria;
use App\Domain\Catalogo\Models\Produto;
use App\Domain\Entregas\Models\AreaEntrega;
use App\Domain\Mesas\Models\Mesa;
use App\Domain\Operacao\Models\Horario;
use App\Domain\Pedidos\Models\Cupom;
use App\Domain\Pedidos\Models\Pedido;
use App\Support\Monitoring\SystemMonitorService;
use App\Support\Security\BlindIndexService;
use Carbon\CarbonImmutable;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class SuperAdminController
{
    public function __construct(
        private readonly BlindIndexService $blindIndex
    ) {}

    private function autorizarAdmin(Request $request, bool $exigir2faEstrito = false, bool $somenteSuperAdmin = false): ?JsonResponse
    {
        /** @var Usuario|null $user */
        $user = $request->user();

        if ($user === null || (!$user->isSuperAdmin() && !$user->isSuporte())) {
            return response()->json([
                'error' => 'FORBIDDEN',
                'message' => 'Acesso exclusivo à administração master do SaaS.',
            ], Response::HTTP_FORBIDDEN);
        }

        if ($somenteSuperAdmin && !$user->isSuperAdmin()) {
            return response()->json([
                'error' => 'FORBIDDEN',
                'message' => 'Esta operação é restrita exclusivamente ao Super Administrador do SaaS.',
            ], Response::HTTP_FORBIDDEN);
        }

        if ($exigir2faEstrito && $user->isSuperAdmin() && $user->two_factor_secret === null) {
            return response()->json([
                'error' => '2FA_OBRIGATORIO_SUPER_ADMIN',
                'message' => 'Super Administradores precisam estar com 2FA TOTP configurado para acessar métricas globais.',
            ], Response::HTTP_FORBIDDEN);
        }

        return null;
    }

    /**
     * Platform SaaS KPIs (MRR, ARR, GMV, Active Stores, Trial Stores, Delinquency Rate, Plans & Audit Logs).
     */
    public function metricas(Request $request): JsonResponse
    {
        if ($erro = $this->autorizarAdmin($request, $request->boolean('strict_2fa', false))) {
            return $erro;
        }

        $totalLojas = Loja::withoutGlobalScopes()->count();
        $lojasAtivas = Loja::withoutGlobalScopes()->where('status', 'ativa')->count();
        $lojasTrial = Loja::withoutGlobalScopes()->where('status', 'trial')->count();
        $lojasInadimplentes = Loja::withoutGlobalScopes()->where('status', 'inadimplente')->count();
        $lojasSuspensas = Loja::withoutGlobalScopes()->where('status', 'suspensa')->count();

        // Calculate MRR across active subscriptions and active stores
        $mrrAssinaturas = (int) Assinatura::withoutGlobalScopes()
            ->whereIn('status', ['ativa', 'trial'])
            ->with('plano')
            ->get()
            ->sum(fn (Assinatura $a) => $a->plano?->preco_centavos ?? 0);

        $mrrLojas = (int) Loja::withoutGlobalScopes()
            ->whereIn('status', ['ativa', 'trial'])
            ->with('plano')
            ->get()
            ->sum(fn (Loja $l) => $l->plano?->preco_centavos ?? 0);

        $mrrCentavos = max($mrrAssinaturas, $mrrLojas);

        $faturasPagasMesCentavos = (int) Fatura::withoutGlobalScopes()
            ->where('status', 'paga')
            ->where('pago_em', '>=', now()->startOfMonth())
            ->sum('valor_centavos');

        $totalPedidosPlataforma = Pedido::withoutGlobalScopes()->count();
        $gmvTotalCentavos = (int) Pedido::withoutGlobalScopes()
            ->where('status', '!=', 'cancelado')
            ->sum('total');

        $totalUsuarios = Usuario::withoutGlobalScopes()->count();

        $planos = Plano::query()->orderBy('preco_centavos')->get()->map(function (Plano $plano): array {
            $qtdLojas = Loja::withoutGlobalScopes()->where('plano_id', $plano->id)->count();
            return [
                'id' => $plano->id,
                'nome' => $plano->nome,
                'preco_centavos' => $plano->preco_centavos,
                'limite_usuarios' => $plano->limite_usuarios,
                'recursos_json' => $plano->recursos_json,
                'lojas_count' => $qtdLojas,
                'mrr_plano_centavos' => $qtdLojas * $plano->preco_centavos,
            ];
        });

        $auditorias = Auditoria::withoutGlobalScopes()
            ->orderByDesc('id')
            ->limit(25)
            ->get()
            ->map(fn (Auditoria $a) => [
                'id' => $a->id,
                'loja_id' => $a->loja_id,
                'usuario_id' => $a->usuario_id,
                'acao' => $a->acao,
                'entidade' => $a->entidade,
                'entidade_id' => $a->entidade_id,
                'depois_json' => $a->depois_json,
                'created_at' => $a->created_at?->toIso8601String(),
            ]);

        return response()->json([
            'total_lojas' => $totalLojas,
            'lojas_ativas' => $lojasAtivas,
            'lojas_trial' => $lojasTrial,
            'lojas_inadimplentes' => $lojasInadimplentes,
            'lojas_suspensas' => $lojasSuspensas,
            'mrr_centavos' => $mrrCentavos,
            'arr_centavos' => $mrrCentavos * 12,
            'receita_realizada_mes_centavos' => $faturasPagasMesCentavos ?: $mrrCentavos,
            'total_pedidos_plataforma' => $totalPedidosPlataforma,
            'gmv_total_centavos' => $gmvTotalCentavos,
            'total_usuarios_plataforma' => $totalUsuarios,
            'planos' => $planos,
            'auditorias_recentes' => $auditorias,
        ]);
    }

    public function listarLojas(Request $request): JsonResponse
    {
        if ($erro = $this->autorizarAdmin($request)) {
            return $erro;
        }

        $lojas = Loja::withoutGlobalScopes()
            ->with('plano')
            ->orderByDesc('id')
            ->limit(100)
            ->get()
            ->map(function (Loja $loja): array {
                $dono = Usuario::withoutGlobalScopes()
                    ->where('loja_id', $loja->id)
                    ->where('papel', 'dono_loja')
                    ->first();

                $pedidosCount = Pedido::withoutGlobalScopes()
                    ->where('loja_id', $loja->id)
                    ->count();

                $gmvLoja = (int) Pedido::withoutGlobalScopes()
                    ->where('loja_id', $loja->id)
                    ->where('status', '!=', 'cancelado')
                    ->sum('total');

                return [
                    'id' => $loja->id,
                    'slug' => $loja->slug,
                    'dominio_proprio' => $loja->dominio_proprio,
                    'nome' => $loja->nome,
                    'telefone_whatsapp' => $loja->telefone_whatsapp,
                    'endereco' => $loja->endereco,
                    'cidade' => $loja->cidade,
                    'uf' => $loja->uf,
                    'status' => $loja->status,
                    'plano_id' => $loja->plano_id,
                    'plano_nome' => $loja->plano?->nome ?? 'premium',
                    'plano_preco_centavos' => $loja->plano?->preco_centavos ?? 24990,
                    'mp_conectado' => $loja->mp_access_token !== null,
                    'total_pedidos' => $pedidosCount,
                    'gmv_centavos' => $gmvLoja,
                    'dono_nome' => $dono?->nome ?? 'Administrador',
                    'dono_email' => $dono?->email ?? '',
                    'created_at' => $loja->created_at?->toIso8601String(),
                ];
            });

        return response()->json([
            'lojas' => $lojas,
        ]);
    }

    public function atualizarLoja(Request $request, int $id): JsonResponse
    {
        if ($erro = $this->autorizarAdmin($request, false, true)) {
            return $erro;
        }

        $validated = $request->validate([
            'nome' => ['sometimes', 'string', 'max:120'],
            'status' => ['sometimes', 'string', 'in:ativa,trial,inadimplente,suspensa,cancelada'],
            'plano_id' => ['sometimes', 'integer', 'exists:planos,id'],
            'telefone_whatsapp' => ['sometimes', 'string', 'max:30'],
            'endereco' => ['sometimes', 'nullable', 'string', 'max:255'],
            'cidade' => ['sometimes', 'nullable', 'string', 'max:100'],
            'uf' => ['sometimes', 'nullable', 'string', 'max:2'],
        ]);

        $loja = Loja::withoutGlobalScopes()->findOrFail($id);
        $antes = ['status' => $loja->status, 'plano_id' => $loja->plano_id, 'nome' => $loja->nome];

        $loja->update($validated);

        if (isset($validated['plano_id']) || isset($validated['status'])) {
            Assinatura::withoutGlobalScopes()
                ->where('loja_id', $loja->id)
                ->update([
                    'plano_id' => $loja->plano_id,
                    'status' => in_array($loja->status, ['ativa', 'trial'], true) ? $loja->status : 'inadimplente',
                ]);
        }

        Auditoria::create([
            'loja_id' => $loja->id,
            'usuario_id' => $request->user()?->id,
            'acao' => 'ATUALIZAR_LOJA_ADMIN',
            'entidade' => 'lojas',
            'entidade_id' => $loja->id,
            'antes_json' => $antes,
            'depois_json' => $validated,
            'ip_hash' => hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')),
        ]);

        return response()->json([
            'message' => 'Loja atualizada com sucesso!',
            'loja' => $loja->fresh('plano'),
        ]);
    }

    public function criarLoja(Request $request): JsonResponse
    {
        if ($erro = $this->autorizarAdmin($request, false, true)) {
            return $erro;
        }

        $validated = $request->validate([
            'nome_loja' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:80'],
            'cnpj_cpf' => ['nullable', 'string', 'max:25'],
            'plano_id' => ['required', 'integer', 'exists:planos,id'],
            'status' => ['required', 'string', 'in:ativa,trial'],
            'telefone_whatsapp' => ['required', 'string', 'max:30'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'cidade' => ['nullable', 'string', 'max:100'],
            'uf' => ['nullable', 'string', 'max:2'],
            'nome_dono' => ['required', 'string', 'max:120'],
            'email_dono' => ['required', 'string', 'email', 'max:120'],
            'senha_dono' => ['required', 'string', 'min:6'],
            'popular_exemplo' => ['nullable', 'boolean'],
        ]);

        $slugBase = !empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['nome_loja']);

        $slug = $slugBase;
        if (Loja::withoutGlobalScopes()->where('slug', $slug)->exists()) {
            $slug = $slugBase . '-' . strtolower(Str::random(3));
        }

        $emailBidx = $this->blindIndex->generateForEmail($validated['email_dono']);
        if (Usuario::withoutGlobalScopes()->where('email_bidx', $emailBidx)->exists()) {
            return response()->json(['message' => 'Este e-mail de proprietário já está em uso.'], 422);
        }

        $loja = DB::transaction(function () use ($validated, $slug, $emailBidx, $request): Loja {
            $docRaw = !empty($validated['cnpj_cpf'])
                ? trim((string) $validated['cnpj_cpf'])
                : (string) random_int(10000000000000, 99999999999999);

            $loja = Loja::create([
                'slug' => $slug,
                'nome' => $validated['nome_loja'],
                'cnpj_cpf' => $docRaw,
                'cnpj_cpf_bidx' => $this->blindIndex->generateForDocument($docRaw),
                'telefone_whatsapp' => $validated['telefone_whatsapp'],
                'endereco' => $validated['endereco'] ?? null,
                'cidade' => $validated['cidade'] ?: 'Coronel Fabriciano',
                'uf' => strtoupper($validated['uf'] ?: 'MG'),
                'lat' => -19.5186000,
                'lng' => -42.6295000,
                'plano_id' => (int) $validated['plano_id'],
                'status' => $validated['status'],
                'trial_ate' => $validated['status'] === 'trial' ? now()->addDays(14) : null,
                'config_json' => [
                    'timezone' => 'America/Sao_Paulo',
                    'tempo_medio_preparo_min' => 35,
                    'aceita_pedidos' => true,
                    'estrategia_pizza_meio_a_meio' => 'maior_preco',
                    'taxa_servico_pct' => 10,
                    'cashback_pct' => 5.0,
                ],
            ]);

            Assinatura::create([
                'loja_id' => $loja->id,
                'plano_id' => $loja->plano_id,
                'status' => $loja->status,
                'ciclo' => 'mensal',
                'proximo_vencimento' => $validated['status'] === 'trial'
                    ? now()->addDays(14)->toDateString()
                    : now()->addMonth()->toDateString(),
            ]);

            Usuario::create([
                'loja_id' => $loja->id,
                'nome' => $validated['nome_dono'],
                'email' => $validated['email_dono'],
                'email_bidx' => $emailBidx,
                'telefone' => $validated['telefone_whatsapp'],
                'senha_hash' => Hash::make($validated['senha_dono']),
                'papel' => 'dono_loja',
                'ativo' => true,
            ]);

            $todosCanais = ['delivery', 'retirada', 'mesa', 'balcao'];

            // Horários padrão de funcionamento (Todos os dias das 08:00 às 23:59 para operar imediatamente)
            for ($dia = 0; $dia <= 6; $dia++) {
                foreach ($todosCanais as $canalHorario) {
                    Horario::withoutGlobalScopes()->create([
                        'loja_id' => $loja->id,
                        'canal' => $canalHorario,
                        'dia_semana' => $dia,
                        'abre' => '08:00:00',
                        'fecha' => '23:59:00',
                    ]);
                }
            }

            AreaEntrega::withoutGlobalScopes()->create([
                'loja_id' => $loja->id,
                'tipo' => 'raio',
                'raio_km_de' => 0.0,
                'raio_km_ate' => 25.0,
                'taxa_centavos' => 600,
                'pedido_minimo_centavos' => 0,
                'tempo_estimado_min' => 35,
                'ativa' => true,
            ]);

            AreaEntrega::withoutGlobalScopes()->create([
                'loja_id' => $loja->id,
                'tipo' => 'bairro',
                'nome_bairro' => 'Centro',
                'taxa_centavos' => 500,
                'pedido_minimo_centavos' => 0,
                'tempo_estimado_min' => 25,
                'ativa' => true,
            ]);

            Mesa::withoutGlobalScopes()->create([
                'loja_id' => $loja->id,
                'numero' => 1,
                'nome' => 'Mesa 01',
                'qr_token' => Str::random(32),
                'capacidade' => 4,
                'status' => 'livre',
            ]);

            // Só popula produtos exemplo se explicitamente solicitado pelo administrador
            if (!empty($validated['popular_exemplo'])) {
                $catPrincipal = Categoria::withoutGlobalScopes()->create([
                    'loja_id' => $loja->id,
                    'nome' => 'Pratos & Lanches',
                    'descricao' => 'Destaques preparados na hora',
                    'ordem' => 1,
                    'ativa' => true,
                    'canais' => $todosCanais,
                ]);

                Produto::withoutGlobalScopes()->create([
                    'loja_id' => $loja->id,
                    'categoria_id' => $catPrincipal->id,
                    'nome' => 'Produto Exemplo (Edite ou Exclua)',
                    'descricao' => 'Edite este item no painel da loja em Cardápio e Cupons.',
                    'preco_centavos' => 2990,
                    'controla_estoque' => false,
                    'ativo' => true,
                    'esgotado' => false,
                    'ordem' => 1,
                    'tempo_preparo_min' => 20,
                    'estacao_kds' => 'geral',
                    'canais' => $todosCanais,
                ]);
            }

            Auditoria::create([
                'loja_id' => $loja->id,
                'usuario_id' => $request->user()?->id,
                'acao' => 'CRIAR_LOJA_ADMIN',
                'entidade' => 'lojas',
                'entidade_id' => $loja->id,
                'antes_json' => null,
                'depois_json' => ['nome' => $loja->nome, 'slug' => $loja->slug, 'plano_id' => $loja->plano_id],
                'ip_hash' => hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')),
            ]);

            return $loja;
        });

        return response()->json([
            'message' => 'Nova loja criada com sucesso!',
            'loja' => $loja,
        ], Response::HTTP_CREATED);
    }

    public function atualizarPlano(Request $request, int $id): JsonResponse
    {
        if ($erro = $this->autorizarAdmin($request, false, true)) {
            return $erro;
        }

        $validated = $request->validate([
            'preco_centavos' => ['required', 'integer', 'min:0'],
            'limite_usuarios' => ['required', 'integer', 'min:1'],
        ]);

        $plano = Plano::findOrFail($id);
        $plano->update($validated);

        Auditoria::create([
            'loja_id' => null,
            'usuario_id' => $request->user()?->id,
            'acao' => 'ALTERAR_PLANO_ADMIN',
            'entidade' => 'planos',
            'entidade_id' => $plano->id,
            'antes_json' => null,
            'depois_json' => $validated,
            'ip_hash' => hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')),
        ]);

        return response()->json([
            'message' => 'Plano atualizado com sucesso!',
            'plano' => $plano,
        ]);
    }

    public function processarReguaInadimplencia(Request $request, ReguaInadimplenciaService $regua): JsonResponse
    {
        if ($erro = $this->autorizarAdmin($request, false, true)) {
            return $erro;
        }

        $resultado = $regua->processarInadimplencia(CarbonImmutable::now());

        return response()->json([
            'message' => 'Régua de cobrança processada com sucesso!',
            'resultado' => $resultado,
        ]);
    }

    /**
     * GET /api/admin/monitoramento
     * Diagnóstico completo de saúde da infraestrutura, incidentes 500 e lista de backups.
     */
    public function monitoramento(Request $request, SystemMonitorService $monitor): JsonResponse
    {
        if ($erro = $this->autorizarAdmin($request)) {
            return $erro;
        }

        return response()->json($monitor->verificarSaudeSistema(false));
    }

    /**
     * POST /api/admin/monitoramento/config
     * Salva e-mail e WhatsApp de alerta de queda e preferências de backup diário.
     */
    public function salvarConfigMonitoramento(Request $request, SystemMonitorService $monitor): JsonResponse
    {
        if ($erro = $this->autorizarAdmin($request, false, true)) {
            return $erro;
        }

        $validated = $request->validate([
            'alert_email' => ['nullable', 'email', 'max:150'],
            'alert_whatsapp' => ['nullable', 'string', 'max:30'],
            'whatsapp_instance' => ['nullable', 'string', 'max:80'],
            'alerta_queda_ativo' => ['nullable', 'boolean'],
            'alerta_erros_500_ativo' => ['nullable', 'boolean'],
            'backup_auto_ativo' => ['nullable', 'boolean'],
            'backup_enviar_email' => ['nullable', 'boolean'],
            'backup_enviar_resumo_whatsapp' => ['nullable', 'boolean'],
            'backup_horario' => ['nullable', 'string', 'max:10'],
        ]);

        $cfg = $monitor->salvarConfiguracao($validated);

        return response()->json([
            'message' => 'Configurações de Monitoramento, Alertas e Backup salvas com sucesso!',
            'config' => $cfg,
        ]);
    }

    /**
     * POST /api/admin/monitoramento/testar-alerta
     * Dispara imediatamente um alerta de teste no E-mail e no WhatsApp do administrador.
     */
    public function testarAlerta(Request $request, SystemMonitorService $monitor): JsonResponse
    {
        if ($erro = $this->autorizarAdmin($request, false, true)) {
            return $erro;
        }

        if ($request->filled('alert_email') || $request->filled('alert_whatsapp')) {
            $monitor->salvarConfiguracao($request->only(['alert_email', 'alert_whatsapp', 'whatsapp_instance']));
        }

        $texto = "✅ *TESTE DE MONITORAMENTO — PEDELIVRE OS*\n\n"
            . "Seu canal de emergência para alertas de queda, falhas críticas e envio diário de backups está configurado e operando perfeitamente!\n\n"
            . "🌐 *Ambiente:* " . config('app.url') . "\n"
            . "🕒 *Data/Hora:* " . now()->format('d/m/Y H:i:s');

        $res = $monitor->dispararAlertaIncidente(
            '✅ [PedeLivre OS] Teste de Alerta de Monitoramento & Suporte',
            $texto,
            null
        );

        $canais = [];
        if ($res['whatsapp_enviado']) $canais[] = 'WhatsApp';
        if ($res['email_enviado']) $canais[] = 'E-mail';

        $msg = !empty($canais)
            ? 'Alerta de teste enviado com sucesso via ' . implode(' e ', $canais) . '!'
            : 'Alerta processado! Verifique se o E-mail SMTP ou WhatsApp estão conectados.';

        return response()->json([
            'message' => $msg,
            'resultado' => $res,
        ]);
    }

    /**
     * POST /api/admin/backups/gerar
     * Gera um backup completo (.zip com SQL + JSON) imediatamente e envia por e-mail.
     */
    public function gerarBackupAgora(Request $request, SystemMonitorService $monitor): JsonResponse
    {
        if ($erro = $this->autorizarAdmin($request, false, true)) {
            return $erro;
        }

        $enviarEmail = $request->boolean('enviar_email', true);
        $res = $monitor->gerarBackupCompleto($enviarEmail);

        $msgExtra = $res['email_enviado']
            ? ' e enviado para o seu e-mail!'
            : ' (salvo no servidor pronto para download)!';

        return response()->json([
            'message' => "Backup [{$res['arquivo']}] ({$res['tamanho_humano']}) gerado com sucesso{$msgExtra}",
            'backup' => $res,
            'backups' => $monitor->listarBackups(),
        ]);
    }

    /**
     * GET /api/admin/backups/{arquivo}/download
     * Faz o download seguro de um arquivo .zip de backup.
     */
    public function baixarBackup(Request $request, string $arquivo, SystemMonitorService $monitor): Response
    {
        $user = $request->user();
        if ($user === null && $request->filled('token')) {
            $accessToken = PersonalAccessToken::findToken((string) $request->query('token'));
            if ($accessToken !== null && $accessToken->tokenable instanceof Usuario) {
                $user = $accessToken->tokenable;
            }
        }

        if ($user === null || !$user->isSuperAdmin()) {
            return response()->json(['error' => 'FORBIDDEN'], Response::HTTP_FORBIDDEN);
        }

        $caminho = $monitor->obterCaminhoBackupSeguro($arquivo);
        if ($caminho === null) {
            return response()->json(['error' => 'ARQUIVO_NAO_ENCONTRADO'], Response::HTTP_NOT_FOUND);
        }

        return response()->download($caminho, basename($caminho));
    }
}
