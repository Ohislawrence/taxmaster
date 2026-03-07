<?php

namespace App\Services;

use App\Models\Business;
use App\Models\BankAccount;
use App\Models\SubscriptionPlan;
use App\Models\BusinessSubscription;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class SubscriptionService
{
    /**
     * Get all active plans available for subscription
     */
    public function getAvailablePlans(): Collection
    {
        return SubscriptionPlan::where('is_active', true)
            ->orderBy('display_order')
            ->get();
    }

    /**
     * Get a plan by slug
     */
    public function getPlanBySlug(string $slug): ?SubscriptionPlan
    {
        return SubscriptionPlan::where('slug', $slug)->first();
    }

    /**
     * Create a new subscription for a business
     */
    public function createSubscription(
        Business $business,
        SubscriptionPlan $plan,
        string $billingCycle = 'monthly',
        ?string $paymentMethod = null,
        ?string $transactionRef = null
    ): BusinessSubscription {
        // Cancel any existing subscriptions (active, pending_payment, and pending)
        $business->subscriptions()
            ->whereIn('status', ['active', 'pending_payment', 'pending'])
            ->update(['status' => 'cancelled', 'cancelled_at' => now()]);

        $renews_at = $billingCycle === 'annual'
            ? now()->addYear()
            : now()->addMonth();

        $trialEndsAt = null;
        $startedAt = null;
        $status = 'pending';
        $paymentStatus = 'pending';

        // For free plans, start immediately and activate
        if ($plan->isFree()) {
            $status = 'active';
            $paymentStatus = 'completed';
            $startedAt = now();
            // Free plan doesn't have trial
            $trialEndsAt = null;
        } else {
            // Paid plans have trial period
            $trialEndsAt = now()->addDays(14);
        }

        return BusinessSubscription::create([
            'business_id' => $business->id,
            'plan_id' => $plan->id,
            'plan_type' => $plan->slug,
            'monthly_price' => $plan->monthly_price,
            'annual_price' => $plan->annual_price,
            'max_staff_members' => $plan->max_staff_members,
            'max_returns_per_year' => $plan->max_returns_per_year,
            'ai_analysis_included' => $plan->ai_analysis_included,
            'payment_automation' => $plan->payment_automation,
            'billing_cycle' => $billingCycle,
            'status' => $status,
            'payment_status' => $paymentStatus,
            'payment_method' => $paymentMethod,
            'transaction_reference' => $transactionRef,
            'started_at' => $startedAt,
            'renews_at' => $renews_at,
            'trial_days' => 14,
            'trial_ends_at' => $trialEndsAt,
            'grace_days' => 3,
            'grace_ends_at' => null,
            'payment_failures' => 0,
        ]);
    }

    /**
     * Activate a pending subscription (after payment)
     */
    public function activateSubscription(BusinessSubscription $subscription): BusinessSubscription
    {
        $subscription->update([
            'status' => 'active',
            'payment_status' => 'completed',
            'started_at' => now(),
        ]);

        return $subscription;
    }

    /**
     * Cancel a subscription
     */
    public function cancelSubscription(BusinessSubscription $subscription, ?string $reason = null): BusinessSubscription
    {
        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'metadata' => array_merge(
                $subscription->metadata ?? [],
                ['cancellation_reason' => $reason]
            ),
        ]);

        return $subscription;
    }

    /**
     * Upgrade an existing subscription to a new plan
     */
    public function upgradeSubscription(
        BusinessSubscription $subscription,
        SubscriptionPlan $newPlan,
        string $billingCycle = 'monthly'
    ): BusinessSubscription {
        $renews_at = $billingCycle === 'annual'
            ? now()->addYear()
            : now()->addMonth();

        $subscription->update([
            'plan_id' => $newPlan->id,
            'plan_type' => $newPlan->slug,
            'monthly_price' => $newPlan->monthly_price,
            'annual_price' => $newPlan->annual_price,
            'max_staff_members' => $newPlan->max_staff_members,
            'max_returns_per_year' => $newPlan->max_returns_per_year,
            'ai_analysis_included' => $newPlan->ai_analysis_included,
            'payment_automation' => $newPlan->payment_automation,
            'billing_cycle' => $billingCycle,
            'renews_at' => $renews_at,
        ]);

        return $subscription;
    }

    /**
     * Downgrade a subscription to a lower tier plan
     */
    public function downgradeSubscription(
        BusinessSubscription $subscription,
        SubscriptionPlan $newPlan,
        string $billingCycle = 'monthly'
    ): BusinessSubscription {
        return $this->upgradeSubscription($subscription, $newPlan, $billingCycle);
    }

    /**
     * Get the active subscription for a business
     */
    public function getActiveSubscription(Business $business): ?BusinessSubscription
    {
        return $business->subscriptions()
            ->with('plan')
            ->whereIn('status', ['active', 'pending_payment', 'pending'])
            ->latest('created_at')
            ->first();
    }

    /**
     * Check if business has a feature available
     */
    public function hasFeature(Business $business, string $feature): bool
    {
        $subscription = $this->getActiveSubscription($business);

        if (!$subscription) {
            return false;
        }

        return match ($feature) {
            'ai_analysis' => $subscription->ai_analysis_included,
            'payment_automation' => $subscription->payment_automation,
            'priority_support' => $subscription->plan?->priority_support ?? false,
            'custom_branding' => $subscription->plan?->custom_branding ?? false,
            default => false,
        };
    }

    /**
     * Check if subscription is about to expire
     */
    public function isExpiringsoon(BusinessSubscription $subscription, int $daysThreshold = 7): bool
    {
        return $subscription->renews_at
            && $subscription->renews_at->diffInDays(now()) <= $daysThreshold;
    }

    /**
     * Renew an expired subscription
     */
    public function renewSubscription(BusinessSubscription $subscription): BusinessSubscription
    {
        $renews_at = $subscription->billing_cycle === 'annual'
            ? $subscription->renews_at->addYear()
            : $subscription->renews_at->addMonth();

        $subscription->update([
            'status' => 'active',
            'renews_at' => $renews_at,
        ]);

        return $subscription;
    }

    /**
     * Get subscription usage stats for a business
     */
    public function getUsageStats(Business $business): array
    {
        $subscription = $this->getActiveSubscription($business);

        if (!$subscription) {
            return [
                'has_subscription' => false,
                'plan_name' => 'No Plan',
            ];
        }

        $staffCount = $business->staff()->count();
        $returnsThisYear = $business->taxReturns()
            ->whereYear('created_at', now()->year)
            ->count();

        return [
            'has_subscription' => true,
            'plan_name' => $subscription->plan?->name ?? $subscription->plan_type,
            'status' => $subscription->status,
            'billing_cycle' => $subscription->billing_cycle,
            'renews_at' => $subscription->renews_at,
            'staff_count' => $staffCount,
            'staff_limit' => $subscription->max_staff_members,
            'staff_percentage' => round(($staffCount / $subscription->max_staff_members) * 100),
            'returns_this_year' => $returnsThisYear,
            'returns_limit' => $subscription->max_returns_per_year,
            'returns_percentage' => round(($returnsThisYear / $subscription->max_returns_per_year) * 100),
            'bank_accounts_count' => BankAccount::where('business_id', $business->id)->where('is_active', true)->count(),
            'bank_accounts_limit' => $subscription->plan?->max_bank_accounts ?? 0,
            'ai_analysis_available' => $subscription->ai_analysis_included,
            'payment_automation_available' => $subscription->payment_automation,
        ];
    }

    /**
     * Check if business can perform an action based on subscription limits
     */
    public function canPerformAction(Business $business, string $action): bool
    {
        $subscription = $this->getActiveSubscription($business);

        // Allow features for 'active', 'pending_payment', and 'pending' subscriptions
        if (!$subscription) {
            return $action === 'view' || $action === 'manage_profile';
        }

        if (!in_array($subscription->status, ['active', 'pending_payment', 'pending'])) {
            return $action === 'view' || $action === 'manage_profile';
        }

        // Use plan_type as authoritative source (set at subscription time)
        $planSlug = $subscription->plan_type ?: ($subscription->plan?->slug ?? 'free');

        return match ($action) {
            // Staff management
            'add_staff' => $business->staff()->count() < $subscription->max_staff_members,

            // Tax returns general
            'file_return' => $business->taxReturns()
                ->whereYear('created_at', now()->year)
                ->count() < $subscription->max_returns_per_year,

            // Specific tax return types by plan
            'file_cit' => in_array($planSlug, ['basic', 'professional', 'enterprise']),
            'file_vat' => in_array($planSlug, ['basic', 'professional', 'enterprise']),
            'file_cgt' => in_array($planSlug, ['professional', 'enterprise']),
            'file_paye' => true, // All plans
            'file_wht' => true, // All plans

            // AI features
            'use_ai_analysis' => $subscription->ai_analysis_included,
            'use_ai_chat' => in_array($planSlug, ['professional', 'enterprise']),
            'use_ai_optimization' => in_array($planSlug, ['professional', 'enterprise']),

            // Payment automation
            'use_payment_automation' => $subscription->payment_automation,
            'use_payment_recovery' => in_array($planSlug, ['professional', 'enterprise']),

            // Bank integration
            'link_bank_account' => BankAccount::where('business_id', $business->id)
                ->where('is_active', true)
                ->count() < ($subscription->plan?->max_bank_accounts ?? 0),
            'auto_sync_transactions' => in_array($planSlug, ['professional', 'enterprise']),

            // Financial statements & reporting
            'generate_financial_statements' => in_array($planSlug, ['professional', 'enterprise']),
            'generate_cac_forms' => in_array($planSlug, ['professional', 'enterprise']),
            'export_pdf' => in_array($planSlug, ['professional', 'enterprise']),
            'advanced_reporting' => in_array($planSlug, ['professional', 'enterprise']),

            // API access
            'use_api' => in_array($planSlug, ['professional', 'enterprise']),

            // Support levels
            'priority_support' => in_array($planSlug, ['professional', 'enterprise']),

            // Enterprise-only features
            'custom_branding' => $planSlug === 'enterprise',
            'white_label' => $planSlug === 'enterprise',
            'multi_business' => $planSlug === 'enterprise',
            'custom_integrations' => $planSlug === 'enterprise',
            'dedicated_account_manager' => $planSlug === 'enterprise',

            // Storage check
            'upload_file' => $this->canUploadFile($business, $subscription),

            // Default
            'access_premium_features' => true,
            default => true,
        };
    }

    /**
     * Check if business can upload more files based on storage limit
     */
    protected function canUploadFile(Business $business, BusinessSubscription $subscription): bool
    {
        $planSlug = $subscription->plan_type ?: ($subscription->plan?->slug ?? 'free');

        $storageLimits = [
            'free' => 1, // 1 GB
            'basic' => 5, // 5 GB
            'professional' => 50, // 50 GB
            'enterprise' => 500, // 500 GB
        ];

        $limitGB = $storageLimits[$planSlug] ?? 1;
        $limitBytes = $limitGB * 1024 * 1024 * 1024;

        // Get current storage usage (you may need to implement this)
        // For now, return true (you can add actual storage tracking later)
        return true;
    }

    /**
     * Handle failed subscription payment
     */
    public function handlePaymentFailure(BusinessSubscription $subscription, string $error): void
    {
        $subscription->recordPaymentFailure();

        // If grace period has expired, deactivate subscription
        if ($subscription->hasGracePeriodExpired()) {
            $subscription->update(['status' => 'suspended']);
        }
    }

    /**
     * Generate invoice for subscription
     */
    public function generateInvoice(BusinessSubscription $subscription): ?\App\Models\Invoice
    {
        // Don't generate invoices for trial period or free plans
        if ($subscription->isInTrial() || $subscription->plan->isFree()) {
            return null;
        }

        $amount = $subscription->billing_cycle === 'annual'
            ? $subscription->annual_price
            : $subscription->monthly_price;

        $periodStart = $subscription->renews_at->subMonths(
            $subscription->billing_cycle === 'annual' ? 12 : 1
        );
        $periodEnd = $subscription->renews_at;

        $invoice = \App\Models\Invoice::create([
            'business_subscription_id' => $subscription->id,
            'business_id' => $subscription->business_id,
            'invoice_number' => \App\Models\Invoice::generateInvoiceNumber(),
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(15)->toDateString(),
            'subtotal' => $amount,
            'tax' => 0,
            'total' => $amount,
            'period_start' => $periodStart->toDateString(),
            'period_end' => $periodEnd->toDateString(),
            'status' => 'draft',
            'data' => [
                'plan_name' => $subscription->plan?->name,
                'billing_cycle' => $subscription->billing_cycle,
                'items' => [
                    [
                        'description' => $subscription->plan?->name . ' - ' . $subscription->billing_cycle,
                        'amount' => $amount,
                    ]
                ],
            ],
        ]);

        return $invoice;
    }

    /**
     * Check and process subscription renewals
     */
    public function processRenewals(): void
    {
        $expiredSubscriptions = BusinessSubscription::where('status', 'active')
            ->where('renews_at', '<=', now())
            ->get();

        foreach ($expiredSubscriptions as $subscription) {
            // Generate invoice if not free plan
            if (!$subscription->plan->isFree()) {
                $this->generateInvoice($subscription);
                // Queue payment collection
                // This would integrate with Paystack to auto-charge
            } else {
                // Renew free subscriptions automatically
                $subscription->update([
                    'renews_at' => now()->addMonth(),
                ]);
            }
        }
    }
}
