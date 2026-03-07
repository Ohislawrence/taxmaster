<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'Perfect for getting started with basic tax management',
                'monthly_price' => 0,
                'annual_price' => 0,
                'max_staff_members' => 1,
                'max_returns_per_year' => 5,
                'max_bank_accounts' => 0,
                'storage_gb' => 1,
                'ai_analysis_included' => false,
                'payment_automation' => false,
                'priority_support' => false,
                'custom_branding' => false,
                'features' => [
                    'Basic tax return filing',
                    'Up to 5 returns per year',
                    '1 GB storage',
                    'Community support',
                ],
                'is_active' => true,
                'display_order' => 1,
            ],
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'description' => 'For small businesses with growing tax needs',
                'monthly_price' => 5000,
                'annual_price' => 50000,
                'max_staff_members' => 3,
                'max_returns_per_year' => 50,
                'max_bank_accounts' => 1,
                'storage_gb' => 5,
                'ai_analysis_included' => true,
                'payment_automation' => false,
                'priority_support' => false,
                'custom_branding' => false,
                'features' => [
                    'Basic tax return filing',
                    'Up to 50 returns per year',
                    'Up to 3 staff members',
                    '5 GB storage',
                    'AI tax analysis & insights',
                    'Email support',
                ],
                'is_active' => true,
                'display_order' => 2,
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'description' => 'For established businesses requiring advanced features',
                'monthly_price' => 15000,
                'annual_price' => 150000,
                'max_staff_members' => 10,
                'max_returns_per_year' => 500,
                'max_bank_accounts' => 3,
                'storage_gb' => 50,
                'ai_analysis_included' => true,
                'payment_automation' => true,
                'priority_support' => true,
                'custom_branding' => false,
                'features' => [
                    'Unlimited tax return filing',
                    'Up to 10 staff members',
                    'Up to 500 returns per year',
                    '50 GB storage',
                    'AI tax analysis & optimization',
                    'Automated payment processing',
                    'Priority support (24/7)',
                    'Advanced reporting',
                    'API access',
                ],
                'is_active' => true,
                'display_order' => 3,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Custom solutions for large organizations',
                'monthly_price' => 50000,
                'annual_price' => 500000,
                'max_staff_members' => 999,
                'max_returns_per_year' => 9999,
                'max_bank_accounts' => 999,
                'storage_gb' => 500,
                'ai_analysis_included' => true,
                'payment_automation' => true,
                'priority_support' => true,
                'custom_branding' => true,
                'features' => [
                    'Unlimited everything',
                    'Dedicated account manager',
                    'Custom AI models for your business',
                    'White-label solution',
                    'Custom integrations',
                    'Advanced analytics & reporting',
                    '24/7 priority support',
                    'SLA guarantee',
                    'On-premise deployment option',
                ],
                'is_active' => true,
                'display_order' => 4,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::firstOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}
