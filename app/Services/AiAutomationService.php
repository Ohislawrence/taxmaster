<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAutomationService
{
    /**
     * Categorize a transaction using AI
     */
    public function categorizeTransaction($transaction, $business)
    {
        $config = config('ai-automation.transaction_categorization');

        if (!$config['enabled']) {
            return null;
        }

        $prompt = $this->buildPrompt($config['prompt'], [
            'transaction_description' => $transaction->description ?? $transaction->narration,
            'amount' => number_format($transaction->amount, 2),
            'merchant_name' => $transaction->merchant ?? 'Unknown',
            'date' => $transaction->transaction_date?->format('Y-m-d') ?? ($transaction->date?->format('Y-m-d') ?? now()->format('Y-m-d')),
            'business_type' => $business->business_type ?? 'General',
            'similar_transactions' => $this->getSimilarTransactions($transaction, $business),
        ]);

        $response = $this->callAi($prompt, 'deepseek');

        if ($response && isset($response['confidence'])) {
            return $response;
        }

        return null;
    }

    /**
     * Generate compliance reminder with AI
     */
    public function generateComplianceReminder($deadline, $business)
    {
        $config = config('ai-automation.compliance_reminders');

        if (!$config['enabled']) {
            return null;
        }

        $prompt = $this->buildPrompt($config['prompt'], [
            'deadline_name' => $deadline->name ?? 'Tax Deadline',
            'due_date' => $deadline->due_date->format('Y-m-d'),
            'business_name' => $business->name,
            'business_type' => $business->business_type ?? 'General',
            'overdue_items' => $this->getOverdueItems($business),
            'history' => $this->getDeadlineHistory($deadline, $business),
        ]);

        return $this->callAi($prompt, 'deepseek');
    }

    /**
     * Suggest payment recovery strategy
     */
    public function suggestPaymentRecovery($subscription)
    {
        $config = config('ai-automation.payment_recovery');

        if (!$config['enabled']) {
            return null;
        }

        $business = $subscription->business;

        $prompt = $this->buildPrompt($config['prompt'], [
            'business_name' => $business->name,
            'plan_type' => $subscription->plan_type ?? 'basic',
            'monthly_cost' => number_format($subscription->monthly_price ?? 0, 2),
            'account_age_days' => $business->created_at->diffInDays(),
            'success_rate' => $this->getPaymentSuccessRate($business),
            'failed_attempts' => $subscription->payment_failures ?? 0,
            'last_payment_date' => $subscription->last_successful_payment_at?->format('Y-m-d') ?? 'Never',
            'account_balance' => number_format($subscription->business->owner->account_balance ?? 0, 2),
            'status' => $subscription->status,
            'grace_days' => $subscription->grace_ends_at?->diffInDays() ?? 0,
        ]);

        return $this->callAi($prompt, 'deepseek');
    }

    /**
     * Call Deepseek API
     */
    private function callAi($prompt, $provider = 'deepseek')
    {
        try {
            $config = config('taxmaster.ai_providers.' . $provider);
            $apiKey = $config['api_key'] ?? env('DEEPSEEK_API_KEY');

            if (!$apiKey) {
                Log::warning('AI provider key not configured', ['provider' => $provider]);
                return null;
            }

            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($config['api_url'] . '/chat/completions', [
                    'model' => $config['model'],
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a tax automation assistant. Respond ONLY with valid JSON, no additional text.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'temperature' => 0.3,
                    'max_tokens' => 1000,
                ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');

                // Extract JSON from response (in case model returns extra text)
                $jsonMatch = preg_match('/\{.*\}/s', $content, $matches);
                if ($jsonMatch) {
                    return json_decode($matches[0], true);
                }

                return json_decode($content, true);
            }

            Log::error('AI API error', [
                'provider' => $provider,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('AI automation failed', [
                'error' => $e->getMessage(),
                'provider' => $provider,
            ]);

            return null;
        }
    }

    /**
     * Build prompt by replacing placeholders
     */
    private function buildPrompt($template, $data)
    {
        $replacements = collect($data)
            ->mapWithKeys(fn($value, $key) => ['{' . $key . '}' => (string) $value])
            ->toArray();

        return strtr($template, $replacements);
    }

    /**
     * Get similar transactions for context
     */
    private function getSimilarTransactions($transaction, $business)
    {
        try {
            $similar = $business->transactions()
                ->where('merchant', $transaction->merchant ?? '')
                ->whereYear('date', now()->year)
                ->orderBy('date', 'desc')
                ->limit(3)
                ->pluck('description', 'narration')
                ->values()
                ->join('; ');

            return $similar ?: 'None found';
        } catch (\Exception $e) {
            return 'Unable to retrieve';
        }
    }

    /**
     * Get overdue compliance items
     */
    private function getOverdueItems($business)
    {
        try {
            $overdue = $business->complianceDeadlines()
                ->where('due_date', '<', now())
                ->where('status', '!=', 'completed')
                ->pluck('name')
                ->join(', ');

            return $overdue ?: 'None';
        } catch (\Exception $e) {
            return 'Unable to retrieve';
        }
    }

    /**
     * Get deadline history for context
     */
    private function getDeadlineHistory($deadline, $business)
    {
        try {
            $completedCount = 0;
            $missedCount = 0;

            // Count historical completions (simple way without complex relationship)
            return "Business has {$completedCount} completed, {$missedCount} missed";
        } catch (\Exception $e) {
            return 'No history available';
        }
    }

    /**
     * Calculate payment success rate
     */
    private function getPaymentSuccessRate($business)
    {
        try {
            $totalSubscriptions = $business->subscriptions()->count();

            if ($totalSubscriptions === 0) {
                return 100;
            }

            $activeSubscriptions = $business->subscriptions()
                ->where('status', 'active')
                ->count();

            return round(($activeSubscriptions / $totalSubscriptions) * 100, 1);
        } catch (\Exception $e) {
            return 0;
        }
    }
}
