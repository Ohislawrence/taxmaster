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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'mono' => [
        'secret_key' => env('MONO_SECRET_KEY'),
        'public_key' => env('MONO_PUBLIC_KEY'),
        'redirect_url' => env('MONO_REDIRECT_URL', env('APP_URL') . '/business/banks/callback'),
        'webhook_secret' => env('MONO_WEBHOOK_SECRET'),
        'base_url' => env('MONO_BASE_URL', 'https://api.withmono.com'),
    ],

    'remita' => [
        'merchant_id' => env('REMITA_MERCHANT_ID'),
        'api_key' => env('REMITA_API_KEY'),
        'service_type_id' => env('REMITA_SERVICE_TYPE_ID'),
        'base_url' => env('REMITA_BASE_URL', 'https://login.remita.net/remita/exapp/api/v1/send/api'),
    ],

    'ai' => [
        'provider' => env('AI_PROVIDER', 'deepseek'),
        'enabled' => env('AI_ENABLED', true),
        'deepseek_key' => env('DEEPSEEK_API_KEY'),
        'gemini_key' => env('GEMINI_API_KEY'),
    ],

];
