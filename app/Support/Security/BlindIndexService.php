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
     * Strip WhatsApp JID suffix (@s.whatsapp.net, @c.us, :device) first.
     * Example: "+55 (31) 98765-4321" -> "5531987654321"
     */
    public function normalizePhone(string $phone): string
    {
        // Strip WhatsApp JID domain and multi-device suffix (e.g. 5531988887777:12@s.whatsapp.net)
        $clean = explode('@', trim($phone))[0];
        $clean = explode(':', $clean)[0];

        $digits = preg_replace('/\D+/', '', $clean) ?? '';

        // Strip leading trunk zero if user typed 031988887777 (11 or 12 digits starting with 0)
        if ((strlen($digits) === 11 || strlen($digits) === 12) && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        // If local Brazilian number without country code, default to 55 prefix if 10 or 11 digits
        if (strlen($digits) === 10 || strlen($digits) === 11) {
            $digits = '55' . $digits;
        }

        return $digits;
    }

    /**
     * Returns all valid Brazilian phone number variations (with and without 9th mobile digit,
     * and with/without 55 country code) so WhatsApp JIDs (often 12 digits: 55 + DDD + 8 digits)
     * always match numbers typed on Checkout (often 13 digits: 55 + DDD + 9 + 8 digits).
     *
     * @return list<string>
     */
    public function normalizePhoneVariants(string $phone): array
    {
        $canonical = $this->normalizePhone($phone);
        if ($canonical === '') {
            return [];
        }

        $variants = [$canonical];

        // Case 1: 13 digits starting with 55 (55 + 2-digit DDD + 9 + 8 digits)
        if (strlen($canonical) === 13 && str_starts_with($canonical, '55') && $canonical[4] === '9') {
            $ddd = substr($canonical, 2, 2);
            $subscriber8 = substr($canonical, 5, 8);
            $without9 = '55' . $ddd . $subscriber8; // 12 digits (WhatsApp legacy JID format)
            $localWith9 = $ddd . '9' . $subscriber8; // 11 digits
            $localWithout9 = $ddd . $subscriber8; // 10 digits
            $variants[] = $without9;
            $variants[] = $localWith9;
            $variants[] = $localWithout9;
        }

        // Case 2: 12 digits starting with 55 (55 + 2-digit DDD + 8 digits)
        if (strlen($canonical) === 12 && str_starts_with($canonical, '55')) {
            $ddd = substr($canonical, 2, 2);
            $subscriber8 = substr($canonical, 4, 8);
            $with9 = '55' . $ddd . '9' . $subscriber8; // 13 digits
            $localWith9 = $ddd . '9' . $subscriber8; // 11 digits
            $localWithout9 = $ddd . $subscriber8; // 10 digits
            $variants[] = $with9;
            $variants[] = $localWith9;
            $variants[] = $localWithout9;
        }

        return array_values(array_unique($variants));
    }

    /**
     * Generates HMAC-SHA256 blind index hashes for all Brazilian phone variations of the input.
     *
     * @return list<string>
     */
    public function generatePhoneCandidates(string $phone): array
    {
        $variants = $this->normalizePhoneVariants($phone);
        return array_values(array_unique(array_map(fn (string $v): string => $this->generate($v), $variants)));
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
