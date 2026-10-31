<?php

declare(strict_types=1);

namespace App\Domain\Pagamentos\Services;

use App\Domain\Assinaturas\Models\Loja;
use DomainException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

final class MercadoPagoOAuthService
{
    private const OAUTH_URL = 'https://auth.mercadopago.com.br/authorization';
    private const TOKEN_URL = 'https://api.mercadopago.com/oauth/token';

    /**
     * Generates CSRF-protected OAuth authorization URL for the store owner.
     */
    public function gerarUrlAutorizacao(Loja $loja): string
    {
        $appId = (string) config('services.mercadopago.client_id');
        $redirectUri = (string) config('services.mercadopago.redirect_uri', url('/api/pagamentos/mercadopago/callback'));

        // Signed state token encoding store ID, timestamp and random nonce
        $payload = "{$loja->id}:" . time() . ':' . Str::random(16);
        $signature = hash_hmac('sha256', $payload, (string) config('app.key'));
        $state = base64_encode("{$payload}:{$signature}");

        $params = http_build_query([
            'client_id' => $appId,
            'response_type' => 'code',
            'platform_id' => 'mp',
            'state' => $state,
            'redirect_uri' => $redirectUri,
        ]);

        return self::OAUTH_URL . '?' . $params;
    }

    /**
     * Validates state token signature to prevent CSRF.
     */
    public function validarStateToken(string $state, Loja $loja): bool
    {
        $decoded = base64_decode($state, true);
        if ($decoded === false) {
            return false;
        }

        $parts = explode(':', $decoded);
        if (count($parts) !== 4) {
            return false;
        }

        [$lojaId, $timestamp, $nonce, $signature] = $parts;

        if ((int) $lojaId !== $loja->id) {
            return false;
        }

        // Expire state token after 15 minutes
        if (time() - (int) $timestamp > 900) {
            return false;
        }

        $expectedPayload = "{$lojaId}:{$timestamp}:{$nonce}";
        $expectedSignature = hash_hmac('sha256', $expectedPayload, (string) config('app.key'));

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Exchanges authorization code for store OAuth tokens and stores them encrypted (AES-256).
     */
    public function trocarCodigoPorTokens(string $code, Loja $loja): void
    {
        $clientId = (string) config('services.mercadopago.client_id');
        $clientSecret = (string) config('services.mercadopago.client_secret');
        $redirectUri = (string) config('services.mercadopago.redirect_uri', url('/api/pagamentos/mercadopago/callback'));

        $response = Http::asForm()->post(self::TOKEN_URL, [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri,
        ]);

        if ($response->failed()) {
            throw new DomainException('Falha na comunicação com o Mercado Pago para conexão de conta: ' . $response->body());
        }

        $data = $response->json();

        // Stored encrypted at rest via Eloquent model casts
        $loja->update([
            'mp_access_token' => $data['access_token'] ?? null,
            'mp_refresh_token' => $data['refresh_token'] ?? null,
            'mp_user_id' => (string) ($data['user_id'] ?? ''),
        ]);
    }

    /**
     * Disconnects Mercado Pago OAuth account.
     */
    public function desconectar(Loja $loja): void
    {
        $loja->update([
            'mp_access_token' => null,
            'mp_refresh_token' => null,
            'mp_user_id' => null,
        ]);
    }
}
