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
        'enabled' => env('MONO_ENABLED', false),
        'secret_key' => env('MONO_SECRET_KEY'),
        'public_key' => env('MONO_PUBLIC_KEY'),
        'redirect_url' => env('MONO_REDIRECT_URL', env('APP_URL') . '/business/banks/callback'),
        'webhook_secret' => env('MONO_WEBHOOK_SECRET'),
        'base_url' => env('MONO_BASE_URL', 'https://api.withmono.com'),
    ],

    'quickbooks' => [
        'enabled' => env('QUICKBOOKS_ENABLED', false),
        'client_id' => env('QUICKBOOKS_CLIENT_ID'),
        'client_secret' => env('QUICKBOOKS_CLIENT_SECRET'),
        'redirect_uri' => env('QUICKBOOKS_REDIRECT_URI', env('APP_URL') . '/business/integrations/quickbooks/callback'),
        'environment' => env('QUICKBOOKS_ENVIRONMENT', 'sandbox'), // 'sandbox' or 'production'
        'base_url' => env('QUICKBOOKS_ENVIRONMENT', 'sandbox') === 'production'
            ? 'https://quickbooks.api.intuit.com'
            : 'https://sandbox-quickbooks.api.intuit.com',
    ],

    'remita' => [
        'merchant_id' => env('REMITA_MERCHANT_ID'),
        'api_key' => env('REMITA_API_KEY'),
        'service_type_id' => env('REMITA_SERVICE_TYPE_ID'),
        'base_url' => env('REMITA_BASE_URL', 'https://login.remita.net/remita/exapp/api/v1/send/api'),
        'environment' => env('REMITA_ENVIRONMENT', 'sandbox'), // 'sandbox' or 'production'
    ],

    'ai' => [
        'provider' => env('AI_PROVIDER', 'deepseek'),
        'enabled' => env('AI_ENABLED', true),
        'deepseek_key' => env('DEEPSEEK_API_KEY'),
        'gemini_key' => env('GEMINI_API_KEY'),
    ],


    'paystack' => [
        'secret' => env('PAYSTACK_SECRET_KEY'),
        'public' => env('PAYSTACK_PUBLIC_KEY'),
        'base_url' => env('PAYSTACK_BASE_URL', 'https://api.paystack.co'),
    ],

    'nrs' => [
        'endpoint' => env('NRS_ENDPOINT', 'https://nrs.example.com/api/invoices'),
        'api_key' => env('NRS_API_KEY'),
    ],

    'firs' => [
        'api_url' => env('FIRS_API_URL', 'https://einvoice.firs.gov.ng/api/v1'),
        'api_key' => env('FIRS_API_KEY'),
        'taxpayer_id' => env('FIRS_TAXPAYER_ID'),
        'environment' => env('FIRS_ENVIRONMENT', 'sandbox'), // 'sandbox' or 'production'
        'timeout' => env('FIRS_TIMEOUT', 30),
        'enabled' => env('FIRS_EINVOICING_ENABLED', true),
        'auto_submit' => env('FIRS_AUTO_SUBMIT', true), // Auto-submit invoices to FIRS
        'batch_submit' => env('FIRS_BATCH_SUBMIT', false), // Use batch submission
        'batch_size' => env('FIRS_BATCH_SIZE', 50),
        'vat_rate' => env('FIRS_VAT_RATE', 7.5), // Standard VAT rate percentage
    ],

];
