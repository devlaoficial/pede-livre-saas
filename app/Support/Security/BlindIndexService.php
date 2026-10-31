<?php

declare(strict_types=1);

namespace App\Support\Security;

use RuntimeException;

final class BlindIndexService
{
    private string $key;

    public function __construct(?string $key = null)
    {
        $this->key = $key ?? (string) config('app.blind_index_key', '');

        if ($this->key === '') {
            throw new RuntimeException('BLIND_INDEX_KEY is not defined in application configuration.');
        }
    }

    /**
     * Generate an HMAC-SHA256 blind index for exact-match searching on encrypted fields.
     */
    public function generate(string $normalizedValue): string
    {
        return hash_hmac('sha256', $normalizedValue, $this->key);
    }

    /**
     * Normalizes a phone number to E.164 digits-only format.
     * Example: "+55 (31) 98765-4321" -> "5531987654321"
     */
    public function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        // If local Brazilian number without country code, default to 55 prefix if 10 or 11 digits
        if (strlen($digits) === 10 || strlen($digits) === 11) {
            $digits = '55' . $digits;
        }

        return $digits;
    }

    /**
     * Normalizes Brazilian CPF (11 digits) or CNPJ (14 digits) to digits-only.
     */
    public function normalizeDocument(string $doc): string
    {
        return preg_replace('/\D+/', '', $doc) ?? '';
    }

    /**
     * Normalizes an email address to trimmed lowercase.
     */
    public function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    public function generateForPhone(string $phone): string
    {
        return $this->generate($this->normalizePhone($phone));
    }

    public function generateForDocument(string $doc): string
    {
        return $this->generate($this->normalizeDocument($doc));
    }

    public function generateForEmail(string $email): string
    {
        return $this->generate($this->normalizeEmail($email));
    }
}
