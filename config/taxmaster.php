<?php

return [
    // Paystack Configuration
    'paystack' => [
        'secret_key' => env('PAYSTACK_SECRET_KEY'),
        'public_key' => env('PAYSTACK_PUBLIC_KEY'),
        'base_url' => 'https://api.paystack.co',
        'verify_webhook' => env('PAYSTACK_VERIFY_WEBHOOK', true),
    ],

    // AI Providers Configuration
    'ai_providers' => [
        'deepseek' => [
            'api_key' => env('DEEPSEEK_API_KEY'),
            'api_url' => 'https://api.deepseek.com/v1',
            'model' => 'deepseek-chat',
            'timeout' => 30,
        ],
        'gemini' => [
            'api_key' => env('GEMINI_API_KEY'),
            'api_url' => 'https://generativelanguage.googleapis.com/v1beta/models',
            'model' => 'gemini-1.5-pro',
            'timeout' => 30,
        ],
    ],

    // Default AI Provider
    'default_ai_provider' => env('DEFAULT_AI_PROVIDER', 'deepseek'),

    // Tax Configuration
    'tax' => [
        'country' => 'NG',
        'currency' => 'NGN',
        'standard_rate' => 0.10, // 10% standard tax rate for Nigeria
        'personal_reliefs' => [
            'personal' => 500000, // Personal relief
            'dependent' => 200000, // Per dependent
        ],
    ],

    // Service Pricing
    'pricing' => [
        'plans' => [
            'basic' => [
                'name' => 'Basic Plan',
                'monthly_price' => 5000,
                'annual_price' => 50000,
                'max_staff' => 5,
                'max_returns_per_year' => 12,
                'features' => [
                    'ai_analysis' => true,
                    'payment_automation' => false,
                    'staff_management' => true,
                ],
            ],
            'professional' => [
                'name' => 'Professional Plan',
                'monthly_price' => 15000,
                'annual_price' => 150000,
                'max_staff' => 50,
                'max_returns_per_year' => 12,
                'features' => [
                    'ai_analysis' => true,
                    'payment_automation' => true,
                    'staff_management' => true,
                    'priority_support' => true,
                ],
            ],
            'enterprise' => [
                'name' => 'Enterprise Plan',
                'monthly_price' => 50000,
                'annual_price' => 500000,
                'max_staff' => 1000,
                'max_returns_per_year' => 24,
                'features' => [
                    'ai_analysis' => true,
                    'payment_automation' => true,
                    'staff_management' => true,
                    'priority_support' => true,
                    'api_access' => true,
                    'custom_branding' => true,
                ],
            ],
        ],
    ],
];
