<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'PedeLivre'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'https://pedelivre.com.br'),
    'timezone' => env('APP_TIMEZONE', 'America/Sao_Paulo'),
    'locale' => 'pt_BR',
    'fallback_locale' => 'en',
    'faker_locale' => 'pt_BR',

    /*
    |--------------------------------------------------------------------------
    | Authenticated Field-Level Encryption Cipher (AES-256-GCM)
    |--------------------------------------------------------------------------
    | Strictly uses AES-256-GCM to guarantee both confidentiality and
    | cryptographic authentication tag integrity at rest.
    */
    'cipher' => 'AES-256-GCM',
    'key' => env('APP_KEY'),
    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Dedicated HMAC-SHA256 Blind Indexing Key
    |--------------------------------------------------------------------------
    */
    'blind_index_key' => env('BLIND_INDEX_KEY', 'default-dev-blind-index-key-32-chars!!'),
];
