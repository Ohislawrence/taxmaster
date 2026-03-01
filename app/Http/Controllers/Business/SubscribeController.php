<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\SubscriptionPlan;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
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
            return redirect('/plans')->with('error', 'Invalid payment reference.');
        }

        $business = auth()->user()->currentBusiness;
        $subscription = $business->subscriptions()
            ->where('transaction_reference', $reference)
            ->first();

        if (!$subscription) {
            return redirect('/plans')->with('error', 'Subscription not found.');
        }

        // Verify payment with Paystack
        $paymentStatus = $this->verifyPaystackPayment($reference);

        if ($paymentStatus['status'] && $paymentStatus['data']['status'] === 'success') {
            $this->subscriptionService->activateSubscription($subscription);

            return redirect('/business/dashboard')
                ->with('success', 'Payment successful! Your subscription is now active.');
        }

        return redirect('/plans')
            ->with('error', 'Payment verification failed. Please try again.');
    }

    /**
     * Initialize Paystack payment
     */
    private function initializePaystackPayment(Business $business, SubscriptionPlan $plan, $subscription, $billingCycle)
    {
        $amount = $billingCycle === 'annual' ? $plan->annual_price : $plan->monthly_price;

        $payload = [
            'email' => $business->owner->email,
            'amount' => $amount * 100, // Paystack uses kobo
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
