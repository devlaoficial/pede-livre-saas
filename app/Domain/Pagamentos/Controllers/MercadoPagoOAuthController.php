<?php

declare(strict_types=1);

namespace App\Domain\Pagamentos\Controllers;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Pagamentos\Services\MercadoPagoOAuthService;
use App\Domain\Pagamentos\Services\MercadoPagoPixService;
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
        private readonly MercadoPagoPixService $pixService,
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
                'message' => 'Para conectar sua conta imediatamente, informe seu Access Token (APP_USR-...) do Mercado Pago ou sua Chave PIX nos campos abaixo.',
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
     * Allows the merchant to register & authorize their Mercado Pago account using their official Access Token
     * OR configure their direct Banco Central PIX key (CPF, CNPJ, E-mail, Celular, Aleatória).
     */
    public function salvarTokenManual(Request $request): JsonResponse
    {
        $loja = $this->context->getLoja();
        Gate::authorize('update', $loja);

        $dados = $request->validate([
            'access_token' => ['nullable', 'string', 'max:255'],
            'public_key' => ['nullable', 'string', 'max:255'],
            'chave_pix' => ['nullable', 'string', 'max:140'],
            'auto_confirmar_pix_chave' => ['nullable', 'boolean'],
            'pagamento_pix' => ['nullable', 'boolean'],
            'pagamento_cartao_credito_entrega' => ['nullable', 'boolean'],
            'pagamento_cartao_debito_entrega' => ['nullable', 'boolean'],
            'pagamento_cartao_online' => ['nullable', 'boolean'],
            'pagamento_dinheiro' => ['nullable', 'boolean'],
            'pagamento_voucher' => ['nullable', 'boolean'],
            'bandeiras_aceitas' => ['nullable', 'string', 'max:180'],
            'link_pagamento_cartao_online' => ['nullable', 'string', 'max:500'],
        ]);

        $token = trim((string) ($dados['access_token'] ?? ''));
        $chavePix = trim((string) ($dados['chave_pix'] ?? ''));
        $temFlagsPagamento = $request->hasAny([
            'pagamento_pix',
            'pagamento_cartao_credito_entrega',
            'pagamento_cartao_debito_entrega',
            'pagamento_cartao_online',
            'pagamento_dinheiro',
            'pagamento_voucher',
        ]);

        if ($token === '' && $chavePix === '' && !$temFlagsPagamento) {
            return response()->json([
                'error' => 'DADOS_OBRIGATORIOS',
                'message' => 'Informe sua Chave PIX principal ou selecione as formas de pagamento aceitas.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $configAtual = is_array($loja->config_json) ? $loja->config_json : [];
        if (!empty($dados['public_key'])) {
            $configAtual['mp_public_key'] = trim((string) $dados['public_key']);
        }
        if ($chavePix !== '' || $request->has('chave_pix')) {
            if ($chavePix !== '') {
                $configAtual['chave_pix'] = $chavePix;
            }
        }
        if ($request->has('auto_confirmar_pix_chave')) {
            $configAtual['auto_confirmar_pix_chave'] = $request->boolean('auto_confirmar_pix_chave');
        } elseif (!isset($configAtual['auto_confirmar_pix_chave'])) {
            $configAtual['auto_confirmar_pix_chave'] = true;
        }

        foreach ([
            'pagamento_pix' => true,
            'pagamento_cartao_credito_entrega' => true,
            'pagamento_cartao_debito_entrega' => true,
            'pagamento_cartao_online' => false,
            'pagamento_dinheiro' => true,
            'pagamento_voucher' => false,
        ] as $campoFlag => $padraoFlag) {
            if ($request->has($campoFlag)) {
                $configAtual[$campoFlag] = $request->boolean($campoFlag);
            } elseif (!isset($configAtual[$campoFlag])) {
                $configAtual[$campoFlag] = $padraoFlag;
            }
        }

        if ($request->has('bandeiras_aceitas')) {
            $configAtual['bandeiras_aceitas'] = trim((string) $request->input('bandeiras_aceitas', ''));
        }
        if ($request->has('link_pagamento_cartao_online')) {
            $configAtual['link_pagamento_cartao_online'] = trim((string) $request->input('link_pagamento_cartao_online', ''));
        }

        // Case 1: No new Access Token provided — save PIX key & accepted payment methods directly
        if ($token === '') {
            $loja->update([
                'config_json' => $configAtual,
            ]);

            return response()->json([
                'message' => 'Formas de pagamento e recebimento PIX salvos com sucesso!',
                'conectado' => !empty($loja->mp_access_token),
                'pix_ativo' => !empty($loja->mp_access_token) || !empty($configAtual['chave_pix']),
                'chave_pix' => $configAtual['chave_pix'] ?? null,
                'auto_confirmar_pix_chave' => (bool) ($configAtual['auto_confirmar_pix_chave'] ?? true),
                'modo' => !empty($loja->mp_access_token) ? 'producao' : 'pix_direto',
            ]);
        }

        // Case 2: Access Token provided — validate in real time against Mercado Pago's /users/me endpoint
        $resp = Http::withToken($token)->timeout(10)->get('https://api.mercadopago.com/users/me');
        if ($resp->failed()) {
            return response()->json([
                'error' => 'TOKEN_INVALIDO',
                'message' => 'O Access Token informado foi recusado pela API do Mercado Pago. Copie o Access Token oficial (iniciado por APP_USR-) em Seu Negócio > Configurações > Credenciais.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $userJson = $resp->json();
        $mpUserId = (string) ($userJson['id'] ?? ('MP_' . $loja->id));
        $mpEmail = (string) ($userJson['email'] ?? '');
        $mpNickname = (string) ($userJson['nickname'] ?? $mpEmail ?: $loja->nome);
        $contaLabel = $mpEmail !== '' ? "{$mpNickname} ({$mpEmail})" : $mpNickname;

        $configAtual['mp_conta_nome'] = $contaLabel;
        $configAtual['mp_conectado_em'] = now()->format('d/m/Y H:i');

        $loja->update([
            'mp_access_token' => $token,
            'mp_user_id' => $mpUserId,
            'config_json' => $configAtual,
        ]);

        return response()->json([
            'message' => "Conta Mercado Pago [{$contaLabel}] validada, autorizada e criptografada com sucesso!",
            'conectado' => true,
            'mp_user_id' => $mpUserId,
            'mp_conta_nome' => $contaLabel,
            'chave_pix' => $configAtual['chave_pix'] ?? null,
            'modo' => str_starts_with($token, 'TEST-') ? 'sandbox' : 'producao',
        ]);
    }

    /**
     * Generates a R$ 1,00 live test PIX QR Code on the merchant's connected Mercado Pago account
     * or via their configured Banco Central PIX Key.
     */
    public function testarPix(): JsonResponse
    {
        $loja = $this->context->getLoja();
        Gate::authorize('update', $loja);

        $token = $loja?->mp_access_token;
        $chavePix = trim((string) ($loja?->config_json['chave_pix'] ?? ''));

        if (empty($token) && $chavePix === '') {
            return response()->json([
                'error' => 'CONTA_NAO_CONECTADA',
                'message' => 'Cadastre seu Access Token do Mercado Pago ou sua Chave PIX primeiro para testar a geração de PIX.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!empty($token)) {
            $resp = Http::withHeaders([
                'Authorization' => "Bearer {$token}",
                'X-Idempotency-Key' => 'test_pix_' . $loja->id . '_' . time(),
            ])->timeout(12)->post('https://api.mercadopago.com/v1/payments', [
                'transaction_amount' => 1.00,
                'description' => "Teste de Conexao PIX - {$loja->nome}",
                'payment_method_id' => 'pix',
                'payer' => [
                    'email' => "teste_{$loja->id}@pedelivre.com.br",
                    'first_name' => 'Teste',
                    'last_name' => 'PedeLivre',
                ],
            ]);

            if ($resp->failed()) {
                return response()->json([
                    'error' => 'FALHA_PIX_MP',
                    'message' => 'O Mercado Pago recusou a emissão do PIX: ' . ($resp->json('message') ?? $resp->body()),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $dados = $resp->json();
            $txData = $dados['point_of_interaction']['transaction_data'] ?? [];

            return response()->json([
                'message' => 'QR Code PIX real do Banco Central gerado com sucesso na sua conta Mercado Pago!',
                'mp_payment_id' => (string) ($dados['id'] ?? ''),
                'pix_copia_cola' => (string) ($txData['qr_code'] ?? ''),
                'pix_qr_base64' => (string) ($txData['qr_code_base64'] ?? ''),
            ]);
        }

        // Direct PIX Key BCB EMVCo BRCode generation (R$ 1,00 = 100 centavos)
        $copiaCola = $this->pixService->montarPayloadEmvPix(
            chavePix: $chavePix,
            nomeRecebedor: $loja->nome ?: 'RESTAURANTE',
            cidadeRecebedor: $loja->cidade ?: 'BRASIL',
            valorCentavos: 100,
            txid: 'TESTEPIX' . $loja->id
        );

        return response()->json([
            'message' => "Código PIX Banco Central (R$ 1,00) gerado com sucesso para a chave [{$chavePix}]!",
            'mp_payment_id' => 'BCB_DIRETO_' . time(),
            'pix_copia_cola' => $copiaCola,
            'pix_qr_base64' => '',
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
        $chavePix = $cfg['chave_pix'] ?? null;

        return response()->json([
            'conectado' => $conectado,
            'pix_ativo' => $conectado || !empty($chavePix),
            'mp_user_id' => $loja?->mp_user_id,
            'mp_conta_nome' => $cfg['mp_conta_nome'] ?? null,
            'mp_conectado_em' => $cfg['mp_conectado_em'] ?? null,
            'chave_pix' => $chavePix,
            'auto_confirmar_pix_chave' => (bool) ($cfg['auto_confirmar_pix_chave'] ?? true),
            'pagamento_pix' => (bool) ($cfg['pagamento_pix'] ?? true),
            'pagamento_cartao_credito_entrega' => (bool) ($cfg['pagamento_cartao_credito_entrega'] ?? true),
            'pagamento_cartao_debito_entrega' => (bool) ($cfg['pagamento_cartao_debito_entrega'] ?? true),
            'pagamento_cartao_online' => (bool) ($cfg['pagamento_cartao_online'] ?? false),
            'pagamento_dinheiro' => (bool) ($cfg['pagamento_dinheiro'] ?? true),
            'pagamento_voucher' => (bool) ($cfg['pagamento_voucher'] ?? false),
            'bandeiras_aceitas' => (string) ($cfg['bandeiras_aceitas'] ?? 'Visa, Mastercard, Elo, Hipercard'),
            'link_pagamento_cartao_online' => (string) ($cfg['link_pagamento_cartao_online'] ?? ''),
            'public_key' => $cfg['mp_public_key'] ?? null,
            'modo' => $conectado ? (str_starts_with((string) $token, 'TEST-') ? 'sandbox' : 'producao') : (!empty($chavePix) ? 'pix_direto' : 'nao_configurado'),
            'oauth_disponivel' => !empty(config('services.mercadopago.client_id')),
            'webhook_url' => url('/api/webhooks/mercadopago'),
        ]);
    }
}
