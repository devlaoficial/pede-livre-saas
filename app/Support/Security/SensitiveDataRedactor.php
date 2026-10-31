<?php

declare(strict_types=1);

namespace App\Support\Security;

final class SensitiveDataRedactor
{
    private const SENSITIVE_KEYS = [
        'password',
        'senha',
        'senha_hash',
        'password_confirmation',
        'token',
        'mp_access_token',
        'mp_refresh_token',
        'secret',
        'two_factor_secret',
        'pix_copia_cola',
        'authorization',
        'credit_card',
        'cvv',
        'card_number',
    ];

    /**
     * Recursively scrubs sensitive keys from payload before writing to logs or audit trail.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function redact(array $data): array
    {
        foreach ($data as $key => $value) {
            $lowerKey = strtolower((string) $key);

            if ($this->isSensitiveKey($lowerKey)) {
                $data[$key] = '[REDACTED]';
                continue;
            }

            if (is_array($value)) {
                $data[$key] = $this->redact($value);
            }
        }

        return $data;
    }

    private function isSensitiveKey(string $key): bool
    {
        foreach (self::SENSITIVE_KEYS as $sensitive) {
            if (str_contains($key, $sensitive)) {
                return true;
            }
        }

        return false;
    }
}
