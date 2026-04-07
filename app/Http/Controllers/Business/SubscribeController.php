<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\SubscriptionPlan;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class SubscribeController extends Controller
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Display available plans
     */
    public function showPlans()
    {
        $plans = $this->subscriptionService->getAvailablePlans();
        $business = auth()->user()->currentBusiness;

        $currentSubscription = $business
            ? $this->subscriptionService->getActiveSubscription($business)
            : null;

        return Inertia::render('Business/Plans/Pricing', [
            'plans' => $plans,
            'currentSubscription' => $currentSubscription,
            'business' => $business,
        ]);
    }

    /**
     * Show plan details and checkout
     */
    public function selectPlan(SubscriptionPlan $plan)
    {
        $business = auth()->user()->currentBusiness;

        if (!$business) {
            return redirect('/dashboard')->with('error', 'Please select a business first.');
        }

        $currentSubscription = $this->subscriptionService->getActiveSubscription($business);

        return Inertia::render('Business/Plans/Checkout', [
            'plan' => $plan,
            'currentSubscription' => $currentSubscription,
            'business' => $business,
        ]);
    }

    /**
     * Process plan selection and redirect to payment
     */
    public function processCheckout(Request $request, SubscriptionPlan $plan)
    {
        $business = auth()->user()->currentBusiness;

        if (!$business) {
            return response()->json(['error' => 'Business not found'], 404);
        }

        $validated = $request->validate([
            'billing_cycle' => 'required|in:monthly,annual',
        ]);

        // If free plan, create subscription immediately
        if ($plan->isFree()) {
            $subscription = $this->subscriptionService->createSubscription(
                $business,
                $plan,
                $validated['billing_cycle']
            );

            return response()->json([
                'success' => true,
                'message' => 'Subscription activated successfully!',
                'subscription_id' => $subscription->id,
                'redirect' => '/business/dashboard',
            ]);
        }

        // For paid plans, redirect to payment
        $subscription = $this->subscriptionService->createSubscription(
            $business,
            $plan,
            $validated['billing_cycle']
        );

        // Initialize Paystack payment
        $paymentUrl = $this->initializePaystackPayment($business, $plan, $subscription, $validated['billing_cycle']);

        if (!$paymentUrl) {
            return response()->json([
                'error' => 'Failed to initialize payment. Please try again.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'payment_url' => $paymentUrl,
            'subscription_id' => $subscription->id,
        ]);
    }

    /**
     * Handle Paystack payment callback
     */
    public function paymentCallback(Request $request)
    {
        $reference = $request->query('reference');

        if (!$reference) {
            Log::warning('Payment callback received without reference');
            return redirect(route('business.plans.index'))->with('error', 'Invalid payment reference.');
        }

        $business = auth()->user()->currentBusiness;
        $subscription = $business->subscriptions()
            ->where('transaction_reference', $reference)
            ->first();

        if (!$subscription) {
            Log::warning('Subscription not found for reference', ['reference' => $reference]);
            return redirect(route('business.plans.index'))->with('error', 'Subscription not found.');
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
            return redirect(route('business.plans.index'))
                ->with('error', 'Unable to verify payment. Please contact support.');
        }

        if (!isset($paymentStatus['data']) || !isset($paymentStatus['data']['status'])) {
            Log::error('Payment verification returned invalid structure', [
                'reference' => $reference,
                'subscription_id' => $subscription->id,
                'response' => $paymentStatus
            ]);
            return redirect(route('business.plans.index'))
                ->with('error', 'Payment verification failed. Please contact support.');
        }

        $actualPaymentStatus = $paymentStatus['data']['status'];

        // Only activate if payment was successful
        if ($actualPaymentStatus === 'success') {
            Log::info('Payment successful, activating subscription', [
                'reference' => $reference,
                'subscription_id' => $subscription->id,
                'amount' => $paymentStatus['data']['amount'] ?? null,
            ]);

            $this->subscriptionService->activateSubscription($subscription);

            return redirect('/business/dashboard')
                ->with('success', 'Payment successful! Your subscription is now active.');
        }

        // Payment was not successful (failed, abandoned, etc.)
        Log::warning('Payment not successful', [
            'reference' => $reference,
            'subscription_id' => $subscription->id,
            'status' => $actualPaymentStatus,
            'gateway_response' => $paymentStatus['data']['gateway_response'] ?? null,
        ]);

        // Update subscription to indicate payment failed
        $subscription->update([
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

        return redirect(route('business.plans.index'))
            ->with('error', $errorMessage);
    }

    /**
     * Initialize Paystack payment
     */
    private function initializePaystackPayment(Business $business, SubscriptionPlan $plan, $subscription, $billingCycle)
    {
        $amount = $billingCycle === 'annual' ? $plan->annual_price : $plan->monthly_price;
        $email = $business->email ?: $business->owner?->email;

        if (empty($email)) {
            return null;
        }

        if (str_ends_with($email, '.test')) {
            $email = str_replace('.test', '.example.com', $email);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        $payload = [
            'email' => $email,
            'amount' => $amount * 100, // Paystack uses kobo
            'callback_url' => route('business.plans.payment-callback'),
            'metadata' => [
                'subscription_id' => $subscription->id,
                'business_id' => $business->id,
                'plan_id' => $plan->id,
                'billing_cycle' => $billingCycle,
            ],
        ];

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
        curl_close($curl);

        $result = json_decode($response, true);

        if ($result['status'] && isset($result['data']['authorization_url'])) {
            // Store transaction reference
            $subscription->update([
                'transaction_reference' => $result['data']['reference'],
            ]);

            return $result['data']['authorization_url'];
        }

        return null;
    }

    /**
     * Verify Paystack payment
     */
    private function verifyPaystackPayment($reference)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.paystack.co/transaction/verify/' . rawurlencode($reference),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . config('services.paystack.secret'),
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
    }

    /**
     * Upgrade subscription
     */
    public function upgrade(Request $request, SubscriptionPlan $newPlan)
    {
        $business = auth()->user()->currentBusiness;

        if (!$business) {
            return response()->json(['error' => 'Business not found'], 404);
        }

        $currentSubscription = $this->subscriptionService->getActiveSubscription($business);

        if (!$currentSubscription) {
            return response()->json(['error' => 'No active subscription'], 404);
        }

        if ($newPlan->monthly_price <= $currentSubscription->monthly_price) {
            return response()->json([
                'error' => 'This is not an upgrade. Cannot change to a lower or equal tier.',
            ], 422);
        }

        $validated = $request->validate([
            'billing_cycle' => 'required|in:monthly,annual',
        ]);

        $this->subscriptionService->upgradeSubscription(
            $currentSubscription,
            $newPlan,
            $validated['billing_cycle']
        );

        return response()->json([
            'success' => true,
            'message' => 'Subscription upgraded successfully!',
        ]);
    }

    /**
     * Cancel subscription
     */
    public function cancel(Request $request)
    {
        $business = auth()->user()->currentBusiness;

        if (!$business) {
            return response()->json(['error' => 'Business not found'], 404);
        }

        $subscription = $this->subscriptionService->getActiveSubscription($business);

        if (!$subscription) {
            return response()->json(['error' => 'No active subscription'], 404);
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $this->subscriptionService->cancelSubscription(
            $subscription,
            $validated['reason'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Subscription cancelled successfully.',
        ]);
    }
}
