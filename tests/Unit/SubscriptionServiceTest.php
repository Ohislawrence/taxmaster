<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Business;
use App\Models\SubscriptionPlan;

class SubscriptionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_create_subscription_for_externally_billed_business()
    {
        $user = User::factory()->create();

        $business = Business::create([
            'owner_id' => $user->id,
            'name' => 'Acct Client',
            'slug' => 'acct-client',
            'registration_number' => 'REG-TEST-001',
            'business_type' => 'company',
            'email' => 'client+acct@example.test',
            'phone' => '+234000000010',
            'state' => 'Lagos',
            'city' => 'Ikeja',
            'address' => '10 Test Road',
            'industry' => 'services',
            'created_by_accountant_id' => $user->id,
            'billing_managed_by_platform' => false,
        ]);

        $plan = SubscriptionPlan::create([
            'name' => 'Basic',
            'slug' => 'basic',
            'description' => 'Basic plan',
            'monthly_price' => 1000,
            'annual_price' => 10000,
            'max_staff_members' => 5,
            'max_returns_per_year' => 50,
            'max_bank_accounts' => 1,
            'storage_gb' => 5,
            'ai_analysis_included' => false,
            'payment_automation' => false,
            'priority_support' => false,
            'custom_branding' => false,
            'features' => [],
            'is_active' => true,
            'display_order' => 1,
        ]);

        $this->expectException(\RuntimeException::class);

        app(\App\Services\SubscriptionService::class)->createSubscription($business, $plan);
    }
}
