<?php

declare(strict_types=1);

return [
    'mercadopago' => [
        // Merchant OAuth Application Credentials (Non-Custodial PIX)
        'client_id' => env('MP_CLIENT_ID'),
        'client_secret' => env('MP_CLIENT_SECRET'),
        'redirect_uri' => env('MP_REDIRECT_URI', 'https://pedelivre.com.br/api/pagamentos/mercadopago/callback'),
        'webhook_secret' => env('MP_WEBHOOK_SECRET'),

        // Platform SaaS Subscription Credentials (Strictly separated from Merchant OAuth)
        'platform_access_token' => env('MP_PLATFORM_ACCESS_TOKEN'),
        'platform_webhook_secret' => env('MP_PLATFORM_WEBHOOK_SECRET'),
    ],

    'whatsapp_meta' => [
        'app_secret' => env('META_WHATSAPP_APP_SECRET'),
        'verify_token' => env('META_WHATSAPP_VERIFY_TOKEN'),
        'access_token' => env('META_WHATSAPP_ACCESS_TOKEN'),
        'phone_number_id' => env('META_WHATSAPP_PHONE_ID'),
        'api_version' => env('META_WHATSAPP_API_VERSION', 'v20.0'),
    ],
];
