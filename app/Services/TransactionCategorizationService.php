<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

class TransactionCategorizationService
{
    protected $aiAgentService;

    public function __construct(AiAgentService $aiAgentService)
    {
        $this->aiAgentService = $aiAgentService;
    }

    /**
     * Categorize a single transaction using AI
     */
    public function categorize(Transaction $transaction): array
    {
        $prompt = $this->buildCategorizationPrompt($transaction);

        try {
            $response = $this->aiAgentService->chat($prompt);
            $category = $this->parseCategorizationResponse($response);

            $transaction->update([
                'category' => $category['category'],
                'sub_category' => $category['sub_category'] ?? null,
                'confidence' => $category['confidence'] ?? 0.8,
                'vat_applicable' => $category['vat_applicable'] ?? false,
                'is_business_expense' => $category['is_business_expense'] ?? true,
            ]);

            return $category;
        } catch (\Exception $e) {
            Log::error('Transaction categorization failed', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);

            // Fallback to rule-based categorization
            return $this->ruleBasedCategorization($transaction);
        }
    }

    /**
     * Build AI prompt for categorization
     */
    protected function buildCategorizationPrompt(Transaction $transaction): string
    {
        return <<<PROMPT
You are a Nigerian tax expert. Categorize this transaction for tax purposes.

Transaction Details:
- Type: {$transaction->type}
- Amount: ₦{number_format($transaction->amount, 2)}
- Description: {$transaction->description}
- Counterparty: {$transaction->counterparty}
- Date: {$transaction->transaction_date->format('Y-m-d')}

Categories available:
REVENUE:
  - VAT_OUTPUT: VATable sales (goods/services subject to 7.5% VAT)
  - EXEMPT_SALES: VAT-exempt sales (medical, education, etc)
  - NON_OPERATING: Non-operating income (interest, dividends)

EXPENSES:
  - VAT_INPUT: VATable business expenses (claimable VAT)
  - SALARY_PAYE: Staff salaries (subject to PAYE)
  - RENT: Office/shop rent
  - PROFESSIONAL: Legal, accounting, consulting fees
  - MARKETING: Advertising, promotions
  - UTILITIES: Electricity, water, internet
  - TRANSPORT: Fuel, vehicle maintenance, logistics
  - IT_SOFTWARE: Software subscriptions, tech
  - RAW_MATERIALS: Inventory, raw materials
  - OTHER_EXPENSES: Other business costs

TAX:
  - VAT_PAYMENT: Payment to FIRS for VAT
  - PAYE_PAYMENT: Payment to FIRS for PAYE
  - WHT_PAYMENT: Withholding tax payment
  - CIT_PAYMENT: Corporate income tax payment

PERSONAL: Personal/non-business transaction
UNCATEGORIZED: Cannot determine

Respond ONLY in this JSON format:
{
  "category": "EXPENSES",
  "sub_category": "VAT_INPUT",
  "confidence": 0.95,
  "vat_applicable": true,
  "is_business_expense": true,
  "reasoning": "Professional consulting service, subject to VAT"
}
PROMPT;
    }

    /**
     * Parse AI response
     */
    protected function parseCategorizationResponse(string $response): array
    {
        // Extract JSON from response
        preg_match('/\{[^}]+\}/', $response, $matches);

        if (empty($matches)) {
            throw new \Exception('Invalid AI response format');
        }

        $data = json_decode($matches[0], true);

        if (!$data || !isset($data['category'])) {
            throw new \Exception('Missing category in AI response');
        }

        return $data;
    }

    /**
     * Rule-based categorization fallback
     */
    protected function ruleBasedCategorization(Transaction $transaction): array
    {
        $description = strtolower($transaction->description);

        // Revenue patterns
        if ($transaction->type === 'credit') {
            if (preg_match('/transfer|payment received|income|sales|invoice/', $description)) {
                $category = [
                    'category' => 'REVENUE',
                    'sub_category' => 'VAT_OUTPUT',
                    'confidence' => 0.7,
                    'vat_applicable' => true,
                    'is_business_expense' => false,
                ];

                $transaction->update($category);
                return $category;
            }
        }

        // Expense patterns
        if ($transaction->type === 'debit') {
            if (preg_match('/salary|wage|staff|payroll/', $description)) {
                $category = ['category' => 'EXPENSES', 'sub_category' => 'SALARY_PAYE', 'confidence' => 0.8];
            } elseif (preg_match('/rent|lease/', $description)) {
                $category = ['category' => 'EXPENSES', 'sub_category' => 'RENT', 'confidence' => 0.8];
            } elseif (preg_match('/fuel|transport|logistics/', $description)) {
                $category = ['category' => 'EXPENSES', 'sub_category' => 'TRANSPORT', 'confidence' => 0.7];
            } elseif (preg_match('/firs|tax authority|revenue service/', $description)) {
                $category = ['category' => 'TAX', 'sub_category' => 'VAT_PAYMENT', 'confidence' => 0.9];
            } else {
                $category = [
                    'category' => 'UNCATEGORIZED',
                    'sub_category' => null,
                    'confidence' => 0.5,
                ];
            }

            $category['vat_applicable'] = false;
            $category['is_business_expense'] = true;

            $transaction->update($category);
            return $category;
        }

        $category = [
            'category' => 'UNCATEGORIZED',
            'sub_category' => null,
            'confidence' => 0.5,
            'vat_applicable' => false,
            'is_business_expense' => true,
        ];

        $transaction->update($category);
        return $category;
    }

    /**
     * Batch categorize transactions
     */
    public function batchCategorize(array $transactionIds): int
    {
        $count = 0;
        foreach ($transactionIds as $id) {
            $transaction = Transaction::find($id);
            if ($transaction && !$transaction->category) {
                $this->categorize($transaction);
                $count++;
            }
        }
        return $count;
    }

    /**
     * Re-categorize transaction (user override)
     */
    public function recategorize(Transaction $transaction, string $category, ?string $subCategory = null): void
    {
        $transaction->update([
            'category' => $category,
            'sub_category' => $subCategory,
            'user_verified' => true,
            'confidence' => 1.0, // User verified = 100% confidence
        ]);
    }
}
