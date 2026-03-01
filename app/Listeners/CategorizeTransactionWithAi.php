<?php

namespace App\Listeners;

use App\Events\TransactionCreated;
use App\Services\AiAutomationService;
use App\Models\AiSuggestion;

class CategorizeTransactionWithAi
{
    public function __construct(private AiAutomationService $aiService)
    {
    }

    public function handle(TransactionCreated $event)
    {
        if (!config('ai-automation.features.auto_categorize_transactions')) {
            return;
        }

        try {
            $transaction = $event->transaction;
            $business = $transaction->business ?? $transaction->bankAccount?->business;

            if (!$business) {
                return;
            }

            $suggestion = $this->aiService->categorizeTransaction($transaction, $business);

            if ($suggestion) {
                $aiSuggestion = AiSuggestion::create([
                    'type' => 'categorization',
                    'suggestible_type' => 'App\Models\Transaction',
                    'suggestible_id' => $transaction->id,
                    'data' => $suggestion,
                    'confidence' => $suggestion['confidence'] ?? 0,
                    'status' => ($suggestion['confidence'] ?? 0) >= 0.90 ? 'applied' : 'pending',
                ]);

                // Auto-apply if high confidence
                if (($suggestion['confidence'] ?? 0) >= 0.90) {
                    $transaction->update([
                        'category' => $suggestion['category'] ?? null,
                        'categorized_by' => 'ai',
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('AI categorization failed', [
                'transaction_id' => $event->transaction->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
