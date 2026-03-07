<?php

namespace App\Http\Controllers\Business;

use App\Models\BusinessActivityLog;
use App\Models\SubscriptionPlan;
use App\Services\SubscriptionService;
use App\Services\PaymentService;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SettingsController
{
    private SubscriptionService $subscriptionService;
    private PaymentService $paymentService;

    public function __construct(SubscriptionService $subscriptionService, PaymentService $paymentService)
    {
        $this->subscriptionService = $subscriptionService;
        $this->paymentService = $paymentService;
    }

    /**
     * Show settings page
     */
    public function index()
    {
        $business = auth()->user()->ownedBusiness;

        $subscription = $business->subscription()
            ->where('status', 'active')
            ->latest()
            ->first();

        $activityLog = BusinessActivityLog::where('business_id', $business->id)
            ->with('user')
            ->latest()
            ->limit(15)
            ->get();

        return Inertia::render('Business/Settings/Index', [
            'business' => $business,
            'subscription' => $subscription,
            'activityLog' => $activityLog,
        ]);
    }

    /**
     * Update business settings
     */
    public function update(Request $request)
    {
        $business = auth()->user()->ownedBusiness;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:businesses,email,' . $business->id,
            'phone' => 'required|string',
            'state' => 'required|string',
            'city' => 'required|string',
            'address' => 'required|string',
            'business_type' => 'required|string',
            'tax_identification_number' => ['nullable', 'string', 'regex:/^\d{8,14}(-\d{1,4})?$/'],
            'registration_number' => 'nullable|string',
            'annual_revenue' => 'nullable|numeric|min:0',
            'industry' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $business->update($validated);

        BusinessActivityLog::create([
            'business_id' => $business->id,
            'user_id' => auth()->id(),
            'action' => 'settings_updated',
            'description' => 'Business settings were updated',
        ]);

        return back()->with('success', 'Settings updated successfully');
    }



    /**
     * Show activity log
     */
    public function activityLog(Request $request)
    {
        $business = auth()->user()->ownedBusiness ?? auth()->user()->businesses()->first();

        $activities = BusinessActivityLog::where('business_id', $business->id)
            ->with('user')
            ->latest()
            ->paginate(30);

        return Inertia::render('Business/Settings/ActivityLog', [
            'activities' => $activities,
        ]);
    }

    /**
     * Show subscription details
     */
    public function subscription()
    {
        $business = auth()->user()->ownedBusiness ?? auth()->user()->businesses()->first();

        $currentSubscription = $business->subscriptions()
            ->whereIn('status', ['active', 'pending_payment', 'pending'])
            ->latest()
            ->first();

        $availablePlans = $this->subscriptionService->getAvailablePlans()
            ->keyBy('slug')
            ->map(fn ($plan) => [
                'name' => $plan->name,
                'slug' => $plan->slug,
                'description' => $plan->description,
                'monthly_price' => $plan->monthly_price,
                'annual_price' => $plan->annual_price,
                'max_staff' => $plan->max_staff_members,
                'max_returns_per_year' => $plan->max_returns_per_year,
                'features' => [
                    'ai_analysis' => $plan->ai_analysis_included,
                    'payment_automation' => $plan->payment_automation,
                    'staff_management' => true,
                    'priority_support' => $plan->priority_support,
                    'api_access' => $plan->priority_support, // Upgrade feature
                    'custom_branding' => $plan->custom_branding,
                ],
            ])->toArray();

        return Inertia::render('Business/Settings/Subscription', [
            'currentSubscription' => $currentSubscription,
            'availablePlans' => $availablePlans,
        ]);
    }

    /**
     * Upgrade subscription plan
     */
    public function upgradePlan(Request $request)
    {
        $business = auth()->user()->ownedBusiness ?? auth()->user()->businesses()->first();

        if (!$business) {
            return response()->json(['error' => 'Business not found'], 404);
        }

        $validated = $request->validate([
            'plan_type' => 'required|in:basic,professional,enterprise',
            'billing_cycle' => 'required|in:monthly,annual',
        ]);

        $plan = SubscriptionPlan::where('slug', $validated['plan_type'])->first();

        if (!$plan) {
            return response()->json(['error' => 'Invalid plan selected'], 422);
        }

        $currentSubscription = $business->subscriptions()
            ->whereIn('status', ['active', 'pending_payment', 'pending'])
            ->latest()
            ->first();

        if (!$currentSubscription) {
            return response()->json(['error' => 'No active subscription found'], 404);
        }

        // Check if it's a downgrade
        if ($plan->monthly_price < $currentSubscription->monthly_price) {
            return response()->json(['error' => 'Cannot downgrade to a lower tier'], 422);
        }

        // Update the subscription plan with selected billing cycle
        $this->subscriptionService->upgradeSubscription($currentSubscription, $plan, $validated['billing_cycle']);

        // Initialize Paystack payment for non-free plans
        if ($plan->monthly_price > 0) {
            try {
                $paymentUrl = $this->initializePaystackPayment($business, $plan, $currentSubscription, $validated['billing_cycle']);

                if (!$paymentUrl) {
                    return response()->json(['error' => 'Failed to initialize payment'], 500);
                }

                return response()->json([
                    'success' => true,
                    'payment_url' => $paymentUrl,
                    'message' => 'Redirecting to payment...',
                ]);
            } catch (\Exception $e) {
                Log::error('Payment initialization error', [
                    'business_id' => $business->id,
                    'plan_id' => $plan->id,
                    'error' => $e->getMessage(),
                ]);

                return response()->json([
                    'error' => app()->environment('local') ? $e->getMessage() : 'Payment initialization failed'
                ], 500);
            }
        }

        // Free plan upgrade
        return response()->json([
            'success' => true,
            'message' => 'Plan upgraded successfully',
            'redirect' => route('business.subscription'),
        ]);
    }

    /**
     * Initialize Paystack payment for subscription upgrade
     */
    private function initializePaystackPayment($business, $plan, $subscription, $billingCycle = 'monthly')
    {
        // Use annual price if yearly billing selected
        $amount = $billingCycle === 'annual' ? $plan->annual_price : $plan->monthly_price;
        // Prefer business email, fallback to user email
        $email = $business->email ?: auth()->user()->email;

        // Ensure email is valid format
        if (empty($email)) {
            throw new \Exception('Valid email is required for payment. Please ensure your business or account email is set.');
        }

        // Convert .test domain to valid domain for Paystack (used in local development)
        if (str_ends_with($email, '.test')) {
            // Replace .test with .example.com for Paystack (valid test domain)
            $email = str_replace('.test', '.example.com', $email);
            Log::debug('Converted test email for Paystack', [
                'original_email' => $business->email ?: auth()->user()->email,
                'converted_email' => $email,
            ]);
        }

        // Final validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('Invalid email format. Please contact support.');
        }

        $payload = [
            'email' => trim($email),
            'amount' => (int)($amount * 100), // Paystack uses kobo
            'callback_url' => route('business.subscription.payment-callback'),
            'metadata' => [
                'subscription_id' => $subscription->id,
                'business_id' => $business->id,
                'plan_id' => $plan->id,
                'type' => 'subscription_upgrade',
            ],
        ];

        Log::debug('Paystack payment payload', [
            'email' => $payload['email'],
            'amount' => $payload['amount'],
            'billing_cycle' => $billingCycle,
            'business_id' => $business->id,
        ]);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.paystack.co/transaction/initialize',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . config('services.paystack.secret'),
                'Content-Type: application/json',
            ),
        ));

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode !== 200) {
            Log::error('Paystack initialization failed', [
                'status_code' => $httpCode,
                'response' => $response,
            ]);
            return null;
        }

        $result = json_decode($response, true);

        if ($result['status'] && isset($result['data']['authorization_url'])) {
            // Store transaction reference
            $subscription->update([
                'transaction_reference' => $result['data']['reference'],
                'status' => 'pending_payment',
            ]);

            return $result['data']['authorization_url'];
        }

        return null;
    }

    /**
     * Handle Paystack payment callback for subscription upgrade
     */
    public function paymentCallback(Request $request)
    {
        $reference = $request->query('reference');

        if (!$reference) {
            return redirect(route('business.subscription'))->with('error', 'Invalid payment reference.');
        }

        $business = auth()->user()->ownedBusiness ?? auth()->user()->businesses()->first();

        if (!$business) {
            return redirect(route('business.subscription'))->with('error', 'Business not found.');
        }

        $subscription = $business->subscriptions()
            ->where('transaction_reference', $reference)
            ->firstOrFail();

        // Verify payment with Paystack
        $paymentStatus = $this->verifyPaystackPayment($reference);

        if ($paymentStatus && $paymentStatus['status'] && $paymentStatus['data']['status'] === 'success') {
            // Mark subscription as active
            $subscription->update(['status' => 'active']);

            return redirect(route('business.subscription'))
                ->with('success', 'Payment successful! Your subscription has been upgraded.');
        }

        return redirect(route('business.subscription'))
            ->with('error', 'Payment verification failed. Please try again.');
    }

    /**
     * Verify Paystack payment
     */
    private function verifyPaystackPayment($reference)
    {
        try {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.paystack.co/transaction/verify/' . rawurlencode($reference),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . config('services.paystack.secret'),
                ),
            ));

            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($httpCode === 200) {
                return json_decode($response, true);
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Payment verification error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Handle Paystack webhook for subscription payments
     */
    public function webhook(Request $request)
    {
        // Verify webhook signature
        $signature = hash('sha512', json_encode($request->all()) . config('services.paystack.secret'));

        if ($signature !== $request->header('X-Paystack-Signature')) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $event = $request->input('event');
        $data = $request->input('data');

        if ($event === 'charge.success') {
            $reference = $data['reference'];
            $metadata = $data['metadata'];

            // Handle subscription upgrade payment
            if (isset($metadata['type']) && $metadata['type'] === 'subscription_upgrade') {
                $subscription = \App\Models\BusinessSubscription::find($metadata['subscription_id']);

                if ($subscription) {
                    $subscription->update(['status' => 'active']);
                    Log::info('Subscription upgraded via webhook', ['subscription_id' => $subscription->id]);
                }
            }
        }

        return response()->json(['success' => true]);
    }
}
