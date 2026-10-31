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

        $url = $this->oauthService->gerarUrlAutorizacao($loja);

        return response()->json([
            'authorization_url' => $url,
            'message' => 'Redirecione o usuário para a URL do Mercado Pago.',
        ]);
    }

    /**
     * OAuth Redirect Callback.
     */
    public function callback(Request $request): JsonResponse|RedirectResponse
    {
        $code = $request->string('code')->toString();
        $state = $request->string('state')->toString();

        $loja = $this->context->getLoja();
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

        return response()->json([
            'conectado' => $loja?->mp_access_token !== null,
            'mp_user_id' => $loja?->mp_user_id,
        ]);
    }
}
