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

    'evolution' => [
        'url' => env('EVOLUTION_API_URL', 'http://127.0.0.1:8085'),
        'api_key' => env('EVOLUTION_API_KEY', 'devla_evolution_secret_2026'),
        'webhook_secret' => env('EVOLUTION_WEBHOOK_SECRET', 'devla_pedelivre_wpp_secret_2026'),
    ],

    'groq' => [
        'api_key' => env('GROQ_API_KEY'),
        'chat_model' => env('GROQ_CHAT_MODEL', 'llama-3.3-70b-versatile'),
        'whisper_model' => env('GROQ_WHISPER_MODEL', 'whisper-large-v3-turbo'),
    ],

    'monitoring' => [
        'alert_email' => env('ADMIN_ALERT_EMAIL', ''),
        'alert_whatsapp' => env('ADMIN_ALERT_WHATSAPP', ''),
        'whatsapp_instance' => env('ADMIN_WHATSAPP_INSTANCE', ''),
    ],
];
