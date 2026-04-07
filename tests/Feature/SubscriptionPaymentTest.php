<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Business;
use App\Models\SubscriptionPlan;
use App\Models\BusinessSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class SubscriptionPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create subscription plans
        SubscriptionPlan::create([
            'name' => 'Free',
            'slug' => 'free',
            'description' => 'Free plan',
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
            'is_active' => true,
            'display_order' => 1,
        ]);

        SubscriptionPlan::create([
            'name' => 'Basic',
            'slug' => 'basic',
            'description' => 'Basic plan',
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
            'is_active' => true,
            'display_order' => 2,
        ]);
    }

    /** @test */
    public function payment_callback_requires_reference()
    {
        $user = User::factory()->create();
        $business = Business::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->get(route('business.plans.payment-callback'));

        $response->assertRedirect(route('business.plans.index'));
        $response->assertSessionHas('error', 'Invalid payment reference.');
    }

    /** @test */
    public function payment_callback_requires_valid_subscription()
    {
        $user = User::factory()->create();
        $business = Business::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->get(route('business.plans.payment-callback', ['reference' => 'invalid_ref']));

        $response->assertRedirect(route('business.plans.index'));
        $response->assertSessionHas('error', 'Subscription not found.');
    }

    /** @test */
    public function subscription_service_activates_subscription_correctly()
    {
        $user = User::factory()->create();
        $business = Business::factory()->create(['user_id' => $user->id]);
        $plan = SubscriptionPlan::where('slug', 'basic')->first();

        $subscription = BusinessSubscription::create([
            'business_id' => $business->id,
            'plan_id' => $plan->id,
            'plan_type' => $plan->slug,
            'monthly_price' => $plan->monthly_price,
            'annual_price' => $plan->annual_price,
            'max_staff_members' => $plan->max_staff_members,
            'max_returns_per_year' => $plan->max_returns_per_year,
            'ai_analysis_included' => $plan->ai_analysis_included,
            'payment_automation' => $plan->payment_automation,
            'billing_cycle' => 'monthly',
            'status' => 'pending',
            'payment_status' => 'pending',
            'transaction_reference' => 'test_ref',
            'renews_at' => now()->addMonth(),
        ]);

        $this->assertEquals('pending', $subscription->status);
        $this->assertEquals('pending', $subscription->payment_status);

        // Activate the subscription
        $subscriptionService = app(\App\Services\SubscriptionService::class);
        $subscriptionService->activateSubscription($subscription);

        $subscription->refresh();

        $this->assertEquals('active', $subscription->status);
        $this->assertEquals('completed', $subscription->payment_status);
        $this->assertNotNull($subscription->started_at);
    }

    /** @test */
    public function pending_subscription_remains_pending_when_not_activated()
    {
        $user = User::factory()->create();
        $business = Business::factory()->create(['user_id' => $user->id]);
        $plan = SubscriptionPlan::where('slug', 'basic')->first();

        $subscription = BusinessSubscription::create([
            'business_id' => $business->id,
            'plan_id' => $plan->id,
            'plan_type' => $plan->slug,
            'monthly_price' => $plan->monthly_price,
            'annual_price' => $plan->annual_price,
            'max_staff_members' => $plan->max_staff_members,
            'max_returns_per_year' => $plan->max_returns_per_year,
            'ai_analysis_included' => $plan->ai_analysis_included,
            'payment_automation' => $plan->payment_automation,
            'billing_cycle' => 'monthly',
            'status' => 'pending',
            'payment_status' => 'pending',
            'transaction_reference' => 'test_ref',
            'renews_at' => now()->addMonth(),
        ]);

        $this->assertEquals('pending', $subscription->status);
        $this->assertEquals('pending', $subscription->payment_status);

        // Update payment status to failed
        $subscription->update([
            'payment_status' => 'failed',
            'payment_failures' => 1,
        ]);

        $subscription->refresh();

        // Should still be pending, not active
        $this->assertEquals('pending', $subscription->status);
        $this->assertEquals('failed', $subscription->payment_status);
        $this->assertEquals(1, $subscription->payment_failures);
    }
}
