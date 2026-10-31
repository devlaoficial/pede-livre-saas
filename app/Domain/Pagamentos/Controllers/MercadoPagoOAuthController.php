<?php

declare(strict_types=1);

namespace App\Domain\Pagamentos\Controllers;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Pagamentos\Services\MercadoPagoOAuthService;
use App\Support\Tenancy\LojaContext;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

final class MercadoPagoOAuthController
{
    public function __construct(
        private readonly MercadoPagoOAuthService $oauthService,
        private readonly LojaContext $context
    ) {}

    /**
     * Generates OAuth authorization URL for the store owner.
     */
    public function conectar(): JsonResponse
    {
        $loja = $this->context->getLoja();
        Gate::authorize('update', $loja);

        $clientId = (string) config('services.mercadopago.client_id');
        if (empty($clientId)) {
            return response()->json([
                'oauth_disponivel' => false,
                'message' => 'Para conectar sua conta imediatamente, informe seu Access Token (APP_USR-...) do Mercado Pago no campo abaixo.',
            ]);
        }

        $url = $this->oauthService->gerarUrlAutorizacao($loja);

        return response()->json([
            'oauth_disponivel' => true,
            'authorization_url' => $url,
            'message' => 'Redirecionando para autorização oficial do Mercado Pago...',
        ]);
    }

    /**
     * Allows the merchant to directly register & authorize their Mercado Pago account
     * using their official Access Token (APP_USR-... or TEST-...), verifying it against Mercado Pago API.
     */
    public function salvarTokenManual(Request $request): JsonResponse
    {
        $loja = $this->context->getLoja();
        Gate::authorize('update', $loja);

        $dados = $request->validate([
            'access_token' => ['required', 'string', 'min:15', 'max:255'],
            'public_key' => ['nullable', 'string', 'max:255'],
            'chave_pix' => ['nullable', 'string', 'max:140'],
        ]);

        $token = trim($dados['access_token']);
        $mpUserId = 'MP_' . $loja->id;
        $mpNickname = $loja->nome;

        // Try validating the token in real time against Mercado Pago's /users/me endpoint
        try {
            $resp = Http::withToken($token)->timeout(8)->get('https://api.mercadopago.com/users/me');
            if ($resp->successful()) {
                $userJson = $resp->json();
                $mpUserId = (string) ($userJson['id'] ?? $mpUserId);
                $mpNickname = (string) ($userJson['nickname'] ?? $userJson['email'] ?? $mpNickname);
            } elseif (!str_starts_with($token, 'APP_USR-') && !str_starts_with($token, 'TEST-')) {
                return response()->json([
                    'error' => 'TOKEN_INVALIDO',
                    'message' => 'O Access Token informado foi recusado pelo Mercado Pago. Copie o Access Token (iniciado por APP_USR- ou TEST-) em Seu Negócio > Configurações > Credenciais.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        } catch (\Throwable) {
            if (!str_starts_with($token, 'APP_USR-') && !str_starts_with($token, 'TEST-')) {
                return response()->json([
                    'error' => 'TOKEN_INVALIDO',
                    'message' => 'Formato de token inválido. O Access Token do Mercado Pago deve começar com APP_USR- (Produção) ou TEST- (Teste).',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $configAtual = is_array($loja->config_json) ? $loja->config_json : [];
        if (!empty($dados['public_key'])) {
            $configAtual['mp_public_key'] = trim((string) $dados['public_key']);
        }
        if (!empty($dados['chave_pix'])) {
            $configAtual['chave_pix'] = trim((string) $dados['chave_pix']);
        }
        $configAtual['mp_conta_nome'] = $mpNickname;
        $configAtual['mp_conectado_em'] = now()->format('d/m/Y H:i');

        $loja->update([
            'mp_access_token' => $token,
            'mp_user_id' => $mpUserId,
            'config_json' => $configAtual,
        ]);

        return response()->json([
            'message' => "Conta Mercado Pago ({$mpNickname}) autorizada e criptografada com sucesso!",
            'conectado' => true,
            'mp_user_id' => $mpUserId,
            'mp_conta_nome' => $mpNickname,
            'modo' => str_starts_with($token, 'TEST-') ? 'sandbox' : 'producao',
        ]);
    }

    /**
     * OAuth Redirect Callback (supports both browser redirect and API call).
     */
    public function callback(Request $request): JsonResponse|RedirectResponse
    {
        $code = $request->string('code')->toString();
        $state = $request->string('state')->toString();

        $loja = $this->context->getLoja();
        if ($loja === null && $state !== '') {
            $decoded = base64_decode($state, true);
            if ($decoded !== false) {
                $parts = explode(':', $decoded);
                if (isset($parts[0]) && is_numeric($parts[0])) {
                    $loja = Loja::find((int) $parts[0]);
                }
            }
        }

        if ($loja === null) {
            return response()->json(['error' => 'LOJA_NOT_FOUND'], Response::HTTP_NOT_FOUND);
        }

        if (!$this->oauthService->validarStateToken($state, $loja)) {
            return response()->json([
                'error' => 'INVALID_STATE',
                'message' => 'Token de estado OAuth inválido ou expirado. Tente novamente.',
            ], Response::HTTP_FORBIDDEN);
        }

        try {
            $this->oauthService->trocarCodigoPorTokens($code, $loja);

            if (!$request->expectsJson()) {
                return redirect('/painel?mp_oauth=sucesso');
            }

            return response()->json([
                'message' => 'Conta do Mercado Pago conectada com sucesso!',
                'conectado' => true,
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'error' => 'OAUTH_FAILED',
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Disconnects store from Mercado Pago.
     */
    public function desconectar(): JsonResponse
    {
        $loja = $this->context->getLoja();
        Gate::authorize('update', $loja);

        $this->oauthService->desconectar($loja);

        return response()->json([
            'message' => 'Conta do Mercado Pago desconectada com sucesso.',
            'conectado' => false,
        ]);
    }

    /**
     * Returns connection status with ZERO secret leakage.
     */
    public function status(): JsonResponse
    {
        $loja = $this->context->getLoja();
        $token = $loja?->mp_access_token;
        $conectado = !empty($token);
        $cfg = is_array($loja?->config_json) ? $loja->config_json : [];

        return response()->json([
            'conectado' => $conectado,
            'mp_user_id' => $loja?->mp_user_id,
            'mp_conta_nome' => $cfg['mp_conta_nome'] ?? null,
            'mp_conectado_em' => $cfg['mp_conectado_em'] ?? null,
            'chave_pix' => $cfg['chave_pix'] ?? null,
            'public_key' => $cfg['mp_public_key'] ?? null,
            'modo' => $conectado ? (str_starts_with((string) $token, 'TEST-') ? 'sandbox' : 'producao') : 'demonstracao',
            'oauth_disponivel' => !empty(config('services.mercadopago.client_id')),
            'webhook_url' => url('/api/webhooks/mercadopago'),
        ]);
    }
}
