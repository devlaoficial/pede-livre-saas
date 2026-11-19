<?php

declare(strict_types=1);

namespace App\Domain\Autenticacao\Controllers;

use App\Domain\Assinaturas\Models\Assinatura;
use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Assinaturas\Models\Plano;
use App\Domain\Autenticacao\Models\Usuario;
use App\Domain\Autenticacao\Requests\LoginRequest;
use App\Domain\Autenticacao\Resources\UsuarioResource;
use App\Domain\Entregas\Models\AreaEntrega;
use App\Domain\Mesas\Models\Mesa;
use App\Domain\Operacao\Models\Horario;
use App\Support\Security\BlindIndexService;
use App\Support\Tenancy\LojaContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class LoginController
{
    public function __construct(
        private readonly BlindIndexService $blindIndexService
    ) {}

    /**
     * Authenticate a user via email blind index + Argon2id password hash.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $email = $request->string('email')->toString();
        $emailBidx = $this->blindIndexService->generateForEmail($email);
        $throttleKey = 'login:' . $request->ip() . '|' . $emailBidx;

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'error' => 'TOO_MANY_ATTEMPTS',
                'message' => "Muitas tentativas de login. Tente novamente em {$seconds} segundos.",
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        // Query by email blind index
        $usuario = Usuario::withoutGlobalScopes()
            ->where('email_bidx', $emailBidx)
            ->first();

        if ($usuario === null || !Hash::check($request->string('senha')->toString(), $usuario->senha_hash)) {
            RateLimiter::hit($throttleKey, 60);
            return response()->json([
                'error' => 'CREDENCIAIS_INVALIDAS',
                'message' => 'Email ou senha incorretos.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!$usuario->ativo) {
            return response()->json([
                'error' => 'CONTA_DESATIVADA',
                'message' => 'Sua conta de usuário foi desativada pelo administrador da loja.',
            ], Response::HTTP_FORBIDDEN);
        }

        RateLimiter::clear($throttleKey);

        // Check if 2FA is required
        if ($usuario->two_factor_secret !== null) {
            $tempToken = $usuario->createToken('2fa-pending', ['2fa:verify'], now()->addMinutes(10))->plainTextToken;
            return response()->json([
                'requires_2fa' => true,
                'temp_token' => $tempToken,
                'message' => 'Digite o código de verificação em duas etapas.',
            ]);
        }

        $usuario->update(['ultimo_login' => now()]);

        $deviceName = $request->string('device_name', 'Web Browser')->toString();
        $token = $usuario->createToken($deviceName, ["role:{$usuario->papel}"])->plainTextToken;

        return response()->json([
            'token' => $token,
            'usuario' => new UsuarioResource($usuario),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logout realizado com sucesso.']);
    }

    /**
     * Self-service 14-day free trial store registration.
     */
    public function cadastrar(Request $request, LojaContext $context): JsonResponse
    {
        $validated = $request->validate([
            'plano' => ['required', 'string', 'in:delivery,presencial,premium'],
            'nome_loja' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:80', 'alpha_dash', 'unique:lojas,slug'],
            'cnpj_cpf' => ['required', 'string', 'max:20'],
            'telefone' => ['required', 'string', 'max:30'],
            'cidade' => ['nullable', 'string', 'max:100'],
            'uf' => ['nullable', 'string', 'max:2'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'nome_dono' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:120'],
            'senha' => ['required', 'string', 'min:8'],
        ]);

        $emailBidx = $this->blindIndexService->generateForEmail($validated['email']);
        if (Usuario::withoutGlobalScopes()->where('email_bidx', $emailBidx)->exists()) {
            return response()->json([
                'message' => 'Este e-mail já está cadastrado.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $docBidx = $this->blindIndexService->generateForDocument($validated['cnpj_cpf']);
        if (Loja::withoutGlobalScopes()->where('cnpj_cpf_bidx', $docBidx)->exists()) {
            $docBidx = hash_hmac('sha256', $validated['cnpj_cpf'] . ':' . $validated['slug'], (string) config('app.key'));
        }
        $plano = Plano::where('nome', $validated['plano'])->first() ?? Plano::firstOrFail();

        [$loja, $usuario] = DB::transaction(function () use ($validated, $plano, $emailBidx, $docBidx, $context): array {
            $loja = Loja::create([
                'slug' => $validated['slug'],
                'nome' => $validated['nome_loja'],
                'cnpj_cpf' => $validated['cnpj_cpf'],
                'cnpj_cpf_bidx' => $docBidx,
                'telefone_whatsapp' => $validated['telefone'],
                'endereco' => $validated['endereco'] ?? null,
                'cidade' => $validated['cidade'] ?: 'Coronel Fabriciano',
                'uf' => strtoupper($validated['uf'] ?: 'MG'),
                'lat' => -19.5186000,
                'lng' => -42.6295000,
                'plano_id' => $plano->id,
                'status' => 'trial',
                'trial_ate' => now()->addDays(14),
                'config_json' => [
                    'timezone' => 'America/Sao_Paulo',
                    'tempo_medio_preparo_min' => 35,
                    'aceita_pedidos' => true,
                    'auto_aceitar_pedidos' => true,
                    'notificar_whatsapp_automatico' => true,
                    'auto_finalizar_pedidos' => true,
                    'impressao_automatica_ao_aceitar' => true,
                    'estrategia_pizza_meio_a_meio' => 'maior_preco',
                    'taxa_servico_pct' => 10,
                    'cashback_pct' => 5.0,
                    'whatsapp' => [
                        'notificar_pedidos' => true,
                        'notificar_motoboy' => true,
                        'alerta_atraso_ativo' => true,
                        'minutos_alerta_atraso' => 35,
                        'pesquisa_satisfacao_ativa' => true,
                        'recuperacao_pix_ativa' => true,
                        'recompra_um_clique_ativa' => true,
                        'winback_ativo' => true,
                    ],
                ],
            ]);

            $context->setLoja($loja);

            Assinatura::create([
                'loja_id' => $loja->id,
                'plano_id' => $plano->id,
                'status' => 'trial',
                'ciclo' => 'mensal',
                'proximo_vencimento' => now()->addDays(14)->toDateString(),
            ]);

            $usuario = Usuario::create([
                'loja_id' => $loja->id,
                'nome' => $validated['nome_dono'],
                'email' => $validated['email'],
                'email_bidx' => $emailBidx,
                'telefone' => $validated['telefone'],
                'senha_hash' => Hash::make($validated['senha']),
                'papel' => 'dono_loja',
                'ativo' => true,
            ]);

            $todosCanais = ['delivery', 'retirada', 'mesa', 'balcao'];

            for ($dia = 0; $dia <= 6; $dia++) {
                foreach ($todosCanais as $canalHorario) {
                    Horario::create([
                        'loja_id' => $loja->id,
                        'canal' => $canalHorario,
                        'dia_semana' => $dia,
                        'abre' => '08:00:00',
                        'fecha' => '23:59:00',
                    ]);
                }
            }

            AreaEntrega::create([
                'loja_id' => $loja->id,
                'tipo' => 'raio',
                'raio_km_de' => 0.0,
                'raio_km_ate' => 25.0,
                'taxa_centavos' => 600,
                'pedido_minimo_centavos' => 0,
                'tempo_estimado_min' => 35,
                'ativa' => true,
            ]);

            AreaEntrega::create([
                'loja_id' => $loja->id,
                'tipo' => 'bairro',
                'nome_bairro' => 'Centro',
                'taxa_centavos' => 500,
                'pedido_minimo_centavos' => 0,
                'tempo_estimado_min' => 25,
                'ativa' => true,
            ]);

            Mesa::create([
                'loja_id' => $loja->id,
                'numero' => 1,
                'nome' => 'Mesa 01',
                'qr_token' => Str::random(32),
                'capacidade' => 4,
                'status' => 'livre',
            ]);

            // Cardápio inicial de boas-vindas para operação imediata (editável com 1 clique no painel)
            $catInicial = \App\Domain\Catalogo\Models\Categoria::create([
                'loja_id' => $loja->id,
                'nome' => 'Destaques da Casa',
                'descricao' => 'Nossas especialidades preparadas na hora',
                'ordem' => 1,
                'ativa' => true,
                'canais' => $todosCanais,
            ]);

            \App\Domain\Catalogo\Models\Produto::create([
                'loja_id' => $loja->id,
                'categoria_id' => $catInicial->id,
                'nome' => 'Especial da Casa (Exemplo — Edite no Painel)',
                'descricao' => 'Produto demonstrativo para você testar seu cardápio digital agora mesmo. Edite ou substitua na aba Cardápio.',
                'preco_centavos' => 3490,
                'preco_promocional_centavos' => 2990,
                'controla_estoque' => false,
                'ativo' => true,
                'esgotado' => false,
                'destaque' => true,
                'ordem' => 1,
                'tempo_preparo_min' => 25,
                'estacao_kds' => 'geral',
                'canais' => $todosCanais,
            ]);

            \App\Domain\Pedidos\Models\Cupom::create([
                'loja_id' => $loja->id,
                'codigo' => 'BEMVINDO10',
                'tipo' => 'percentual',
                'valor' => 10,
                'minimo_centavos' => 2500,
                'validade' => now()->addDays(30),
                'limite_por_cliente' => 1,
                'ativo' => true,
                'canais' => ['delivery', 'retirada'],
            ]);

            return [$loja, $usuario];
        });

        // Notificação Autônoma em Tempo Real: avisa o Dono do SaaS sobre o novo cliente e dá boas-vindas ao lojista
        if (!app()->environment('testing')) {
            try {
                $monitor = app(\App\Support\Monitoring\SystemMonitorService::class);
                $cfg = $monitor->obterConfiguracao();
                if (!empty($cfg['alerta_novas_lojas_ativo'])) {
                    $linkCardapio = url("/p/{$loja->slug}");
                    $resumoLead = "🚀 *NOVA LOJA CADASTRADA NO SAAS PEDELIVRE!*\n\n"
                        . "🏪 *Restaurante:* {$loja->nome}\n"
                        . "👤 *Proprietário:* {$usuario->nome}\n"
                        . "📱 *WhatsApp:* {$loja->telefone_whatsapp}\n"
                        . "📧 *E-mail:* {$usuario->email}\n"
                        . "💎 *Plano:* " . strtoupper((string) $plano->nome) . " (Trial 14 dias)\n"
                        . "🔗 *Cardápio:* {$linkCardapio}";

                    $monitor->dispararAlertaIncidente(
                        "🚀 [PedeLivre SaaS] Nova Loja Cadastrada: {$loja->nome}",
                        $resumoLead,
                        null
                    );
                }
            } catch (\Throwable) {}
        }

        $token = $usuario->createToken('cadastro-web', ['role:dono_loja'])->plainTextToken;

        return response()->json([
            'token' => $token,
            'access_token' => $token,
            'loja_slug' => $loja->slug,
            'usuario' => new UsuarioResource($usuario),
        ], Response::HTTP_CREATED);
    }
}
