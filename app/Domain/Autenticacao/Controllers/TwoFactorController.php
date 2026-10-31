<?php

declare(strict_types=1);

namespace App\Domain\Autenticacao\Controllers;

use App\Domain\Autenticacao\Models\Usuario;
use App\Domain\Autenticacao\Resources\UsuarioResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class TwoFactorController
{
    /**
     * Verify 2FA TOTP code using encrypted two_factor_secret.
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        /** @var Usuario $user */
        $user = $request->user();

        if (!$request->user()->tokenCan('2fa:verify')) {
            return response()->json([
                'error' => 'INVALID_TOKEN_ABILITY',
                'message' => 'Token não autorizado para verificação de 2FA.',
            ], Response::HTTP_FORBIDDEN);
        }

        $secret = $user->two_factor_secret;
        if ($secret === null) {
            return response()->json([
                'error' => '2FA_NOT_ENABLED',
                'message' => '2FA não está configurado para esta conta.',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Verify TOTP code (RFC 6238 implementation check)
        $isValid = $this->verifyTotpCode($secret, $request->string('code')->toString());

        if (!$isValid) {
            return response()->json([
                'error' => 'CODIGO_INVALIDO',
                'message' => 'Código de autenticação inválido ou expirado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Delete temporary 2FA token
        $user->currentAccessToken()?->delete();

        $user->update(['ultimo_login' => now()]);
        $deviceName = $request->string('device_name', 'Web Browser')->toString();
        $token = $user->createToken($deviceName, ["role:{$user->papel}"])->plainTextToken;

        return response()->json([
            'token' => $token,
            'usuario' => new UsuarioResource($user),
        ]);
    }

    private function verifyTotpCode(string $secret, string $code): bool
    {
        // Simple demonstration check or Google Authenticator TOTP window verification
        $timestamp = (int) floor(time() / 30);
        for ($i = -1; $i <= 1; $i++) {
            $computed = $this->calculateTotp($secret, $timestamp + $i);
            if (hash_equals($computed, $code)) {
                return true;
            }
        }

        return false;
    }

    private function calculateTotp(string $secret, int $timeSlice): string
    {
        $binaryTime = pack('N*', 0) . pack('N*', $timeSlice);
        $hash = hash_hmac('sha1', $binaryTime, $secret, true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $unpacked = unpack('N', substr($hash, $offset, 4));
        $value = ($unpacked[1] & 0x7FFFFFFF) % 1000000;

        return str_pad((string) $value, 6, '0', STR_PAD_LEFT);
    }
}
