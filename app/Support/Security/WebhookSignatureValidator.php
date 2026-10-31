<?php

declare(strict_types=1);

namespace App\Support\Security;

final class WebhookSignatureValidator
{
    private const MAX_AGE_SECONDS = 300; // 5 minutes replay attack prevention

    /**
     * Validates Mercado Pago webhook signature header (x-signature) and timestamp window.
     *
     * @param string|null $signatureHeader Format: "ts=1700000000,v1=abcdef123456..."
     * @param string|null $xRequestId Header x-request-id
     * @param string|int|null $dataId Payload data.id (e.g. payment ID)
     * @param string|null $secretKey Dedicated webhook secret from Mercado Pago
     * @return bool
     */
    public function isValid(
        ?string $signatureHeader,
        ?string $xRequestId,
        string|int|null $dataId,
        ?string $secretKey = null
    ): bool {
        if (empty($signatureHeader) || empty($xRequestId) || $dataId === null) {
            return false;
        }

        $secret = $secretKey ?? (string) config('services.mercadopago.webhook_secret', '');
        if ($secret === '') {
            return false;
        }

        // 1. Parse signature header parts: ts and v1
        $parts = [];
        foreach (explode(',', $signatureHeader) as $part) {
            $sub = explode('=', trim($part), 2);
            if (count($sub) === 2) {
                $parts[trim($sub[0])] = trim($sub[1]);
            }
        }

        $ts = $parts['ts'] ?? null;
        $hashV1 = $parts['v1'] ?? null;

        if ($ts === null || $hashV1 === null || !ctype_digit($ts)) {
            return false;
        }

        // 2. Replay attack prevention: verify timestamp freshness
        $timestamp = (int) $ts;
        $currentTimestamp = time();

        if (abs($currentTimestamp - $timestamp) > self::MAX_AGE_SECONDS) {
            return false;
        }

        // 3. Compute manifest: "id:[data.id];request-id:[x-request-id];ts:[ts];"
        $manifest = "id:{$dataId};request-id:{$xRequestId};ts:{$ts};";

        // 4. Calculate HMAC-SHA256 signature
        $calculatedHash = hash_hmac('sha256', $manifest, $secret);

        // 5. Constant-time string comparison to prevent timing attacks
        return hash_equals($calculatedHash, $hashV1);
    }
}
