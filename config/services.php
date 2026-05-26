<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'telegram-bot-api' => [
        'token' => env('TELEGRAM_BOT_TOKEN', 'YOUR BOT TOKEN HERE'),
        'webhook_url' => env('TELEGRAM_WEBHOOK_URL'),
    ],

    'e-imzo-server' => [
        'url' => env('E_IMZO_SERVER_URL', 'http://10.10.20.14:8080'),
    ],

    'e-imzo-mobile' => [
        // Your Site ID registered in E-IMZO Mobile system
        'site_id' => env('E_IMZO_MOBILE_SITE_ID', '0000'),

        // Redis configuration for mobile session storage
        'redis' => [
            'host' => env('E_IMZO_REDIS_HOST', '127.0.0.1'),
            'password' => env('E_IMZO_REDIS_PASSWORD', null),
            'port' => env('E_IMZO_REDIS_PORT', 6379),
            'database' => env('E_IMZO_REDIS_DB', 1),
        ],

        // Document TTL in seconds (how long to keep documentId in Redis)
        'document_ttl' => env('E_IMZO_DOCUMENT_TTL', 300), // 5 minutes

        // Time window for signature verification (in minutes)
        'valid_minutes_window' => env('E_IMZO_VALID_MINUTES_WINDOW', 5),
    ],
];
