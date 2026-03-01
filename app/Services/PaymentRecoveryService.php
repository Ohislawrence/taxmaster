<?php

namespace App\Services;

use App\Models\BusinessSubscription;
use App\Models\AiSuggestion;
use App\Notifications\PaymentRecoveryWithAi;
use Illuminate\Support\Facades\Log;

class PaymentRecoveryService
{
    public function __construct(private AiAutomationService $aiService)
    {
    }

    /**
     * Process failed payments and generate AI recovery suggestions
     */
    public function processFailedPayments()
    {
        if (!config('ai-automation.features.payment_recovery_suggestions')) {
            return 0;
        }

        try {
            // Get subscriptions with recent payment failures
            $failedSubscriptions = BusinessSubscription::where('status', 'active')
                ->where('payment_failures', '>', 0)
                ->where('payment_failures', '<', 4) // Less than max retries
                ->whereRaw('last_payment_failure_at > DATE_SUB(NOW(), INTERVAL 1 DAY)')
                ->get();

            $count = 0;

            foreach ($failedSubscriptions as $subscription) {
                try {
                    // Generate AI recovery suggestion
                    $suggestion = $this->aiService->suggestPaymentRecovery($subscription);

                    if ($suggestion) {
                        // Store suggestion
                        AiSuggestion::create([
                            'type' => 'payment_recovery',
                            'suggestible_type' => 'App\Models\BusinessSubscription',
                            'suggestible_id' => $subscription->id,
                            'data' => $suggestion,
                            'confidence' => 1.0,
                            'status' => 'pending',
                        ]);

                        // Apply recovery strategy if confidence high
                        $this->applyRecoveryStrategy($subscription, $suggestion);

                        // Notify business owner
                        $owner = $subscription->business->owner;
                        if ($owner && $owner->email) {
                            $owner->notify(
                                new PaymentRecoveryWithAi($subscription, $suggestion)
                            );
                            $count++;
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Payment recovery AI failed', [
                        'subscription_id' => $subscription->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info("Processed {$count} payment recovery suggestions");
            return $count;
        } catch (\Exception $e) {
            Log::error('Payment recovery service failed', [
                'error' => $e->getMessage(),
            ]);
            return 0;
        }
    }

    /**
     * Apply the recommended recovery strategy
     */
    private function applyRecoveryStrategy($subscription, $suggestion)
    {
        $strategy = $suggestion['recovery_strategy'] ?? 'gentle_reminder';

        switch ($strategy) {
            case 'offer_discount':
                $this->applyDiscount($subscription, $suggestion['suggested_discount'] ?? 10);
                break;

            case 'payment_plan':
                if (isset($suggestion['payment_plan'])) {
                    $this->setPaymentPlan($subscription, $suggestion['payment_plan']);
                }
                break;

            case 'pause_service':
                $this->pauseService($subscription);
                break;

            case 'gentle_reminder':
            default:
                // Just notify, no action needed
                break;
        }
    }

    /**
     * Apply discount to encourage payment
     */
    private function applyDiscount($subscription, $discount)
    {
        try {
            // Create a voucher or adjust the billing
            $subscription->update([
                'discount_percentage' => $discount,
                'discount_applied_at' => now(),
            ]);

            Log::info("Discount applied to subscription", [
                'subscription_id' => $subscription->id,
                'discount' => $discount,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to apply discount", [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Set up payment plan for struggling customers
     */
    private function setPaymentPlan($subscription, $plan)
    {
        try {
            $subscription->update([
                'payment_plan' => json_encode($plan),
                'payment_plan_started_at' => now(),
            ]);

            Log::info("Payment plan set", [
                'subscription_id' => $subscription->id,
                'months' => $plan['months'] ?? 0,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to set payment plan", [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Pause service to prevent further charges
     */
    private function pauseService($subscription)
    {
        try {
            $subscription->update([
                'status' => 'suspended',
                'suspended_at' => now(),
            ]);

            Log::info("Service suspended due to payment failure", [
                'subscription_id' => $subscription->id,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to suspend service", [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
