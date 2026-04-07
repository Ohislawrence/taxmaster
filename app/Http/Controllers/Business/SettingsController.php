<?php

namespace App\Http\Controllers\Business;

use App\Models\BusinessActivityLog;
use App\Models\SubscriptionPlan;
use App\Services\SubscriptionService;
use App\Services\PaymentService;
use App\Services\VatExemptionService;
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
        $business = auth()->user()->defaultBusiness();

        $subscription = $business->subscription()
            ->where('status', 'active')
            ->latest()
            ->first();

        $activityLog = BusinessActivityLog::where('business_id', $business->id)
            ->with('user')
            ->latest()
            ->limit(15)
            ->get();

        return Inertia::render('Business/Settings', [
            'business' => $business,
            'subscription' => $subscription,
            'activityLog' => $activityLog,
            'exemptGoods' => VatExemptionService::getExemptGoodsCategories(),
            'exemptServices' => VatExemptionService::getExemptServicesCategories(),
        ]);
    }

    /**
     * Update business settings
     */
    public function update(Request $request)
    {
        $business = auth()->user()->defaultBusiness();

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

        // Get all available plans except 'free' (since free is auto-assigned on signup)
        $availablePlans = $this->subscriptionService->getAvailablePlans()
            ->filter(fn ($plan) => $plan->slug !== 'free') // Exclude free plan from upgrades
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
            Log::warning('Payment callback received without reference');
            return redirect(route('business.subscription'))->with('error', 'Invalid payment reference.');
        }

        $business = auth()->user()->ownedBusiness ?? auth()->user()->businesses()->first();

        if (!$business) {
            Log::warning('Payment callback - business not found');
            return redirect(route('business.subscription'))->with('error', 'Business not found.');
        }

        $subscription = $business->subscriptions()
            ->where('transaction_reference', $reference)
            ->first();

        if (!$subscription) {
            Log::warning('Subscription not found for reference', ['reference' => $reference]);
            return redirect(route('business.subscription'))->with('error', 'Subscription not found.');
        }

        // Verify payment with Paystack
        $paymentStatus = $this->verifyPaystackPayment($reference);

        // Check if verification was successful and payment is complete
        if (!$paymentStatus || !isset($paymentStatus['status']) || !$paymentStatus['status']) {
            Log::error('Payment verification API call failed', [
                'reference' => $reference,
                'subscription_id' => $subscription->id,
                'response' => $paymentStatus
            ]);
            return redirect(route('business.subscription'))
                ->with('error', 'Unable to verify payment. Please contact support.');
        }

        if (!isset($paymentStatus['data']) || !isset($paymentStatus['data']['status'])) {
            Log::error('Payment verification returned invalid structure', [
                'reference' => $reference,
                'subscription_id' => $subscription->id,
                'response' => $paymentStatus
            ]);
            return redirect(route('business.subscription'))
                ->with('error', 'Payment verification failed. Please contact support.');
        }

        $actualPaymentStatus = $paymentStatus['data']['status'];

        // Only activate if payment was successful
        if ($actualPaymentStatus === 'success') {
            Log::info('Payment successful, activating subscription upgrade', [
                'reference' => $reference,
                'subscription_id' => $subscription->id,
                'amount' => $paymentStatus['data']['amount'] ?? null,
            ]);

            // Mark subscription as active and payment as completed
            $subscription->update([
                'status' => 'active',
                'payment_status' => 'completed',
                'started_at' => $subscription->started_at ?? now(),
            ]);

            return redirect(route('business.subscription'))
                ->with('success', 'Payment successful! Your subscription has been upgraded.');
        }

        // Payment was not successful (failed, abandoned, etc.)
        Log::warning('Payment not successful', [
            'reference' => $reference,
            'subscription_id' => $subscription->id,
            'status' => $actualPaymentStatus,
            'gateway_response' => $paymentStatus['data']['gateway_response'] ?? null,
        ]);

        // Update subscription to indicate payment failed and revert to previous state
        $subscription->update([
            'status' => 'active', // Keep previous active status since upgrade failed
            'payment_status' => 'failed',
            'payment_failures' => $subscription->payment_failures + 1,
        ]);

        $errorMessage = 'Payment failed. ';
        if ($actualPaymentStatus === 'failed') {
            $errorMessage .= 'Your payment was declined. Please check your card details and try again.';
        } elseif ($actualPaymentStatus === 'abandoned') {
            $errorMessage .= 'Payment was not completed. Please try again.';
        } else {
            $errorMessage .= 'Please try again or contact support.';
        }

        return redirect(route('business.subscription'))
            ->with('error', $errorMessage);
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

    /**
     * Update VAT exempt status
     * Per Nigerian VAT Act and Finance Acts 2019/2020
     */
    public function updateVatExemptStatus(Request $request)
    {
        $business = auth()->user()->defaultBusiness();

        $validated = $request->validate([
            'is_vat_exempt' => 'required|boolean',
            'vat_exempt_category' => 'required_if:is_vat_exempt,true|nullable|string',
            'vat_exempt_reason' => 'nullable|string|max:1000',
        ]);

        // Validate category if provided
        if ($validated['is_vat_exempt'] && $validated['vat_exempt_category']) {
            if (!VatExemptionService::isValidExemptCategory($validated['vat_exempt_category'])) {
                return back()->withErrors(['vat_exempt_category' => 'Invalid VAT exempt category selected.']);
            }
        }

        // Clear category and reason if not exempt
        if (!$validated['is_vat_exempt']) {
            $validated['vat_exempt_category'] = null;
            $validated['vat_exempt_reason'] = null;
        }

        $business->update($validated);

        BusinessActivityLog::create([
            'business_id' => $business->id,
            'user_id' => auth()->id(),
            'action' => 'vat_exempt_status_updated',
            'description' => $validated['is_vat_exempt']
                ? 'Business marked as VAT exempt: ' . VatExemptionService::getCategoryDisplayName($validated['vat_exempt_category'])
                : 'Business VAT exempt status removed',
        ]);

        return back()->with('success',
            $validated['is_vat_exempt']
                ? 'Business marked as VAT exempt. Transactions will no longer have VAT applied.'
                : 'VAT exempt status removed. Normal VAT will apply to transactions.'
        );
    }
}
