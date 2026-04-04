<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

class TransactionCategorizationService
{
    /**
     * Categorize a single transaction using AI
     */
    public function categorize(Transaction $transaction): array
    {
        // Try AI categorization if configured
        try {
            $business = $transaction->business;

            if ($business && config('services.deepseek.api_key')) {
                $aiService = new AiAgentService($business);
                $prompt = $this->buildCategorizationPrompt($transaction);
                $response = $aiService->callAiForCategorization($prompt);

                if ($response) {
                    $category = $this->parseCategorizationResponse($response);

                    $transaction->update([
                        'category' => $category['category'],
                        'sub_category' => $category['sub_category'] ?? null,
                        'confidence' => $category['confidence'] ?? 0.8,
                        'vat_applicable' => $category['vat_applicable'] ?? false,
                        'is_business_expense' => $category['is_business_expense'] ?? true,
                    ]);

                    return $category;
                }
            }
        } catch (\Exception $e) {
            Log::warning('AI categorization unavailable, using rule-based', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Fallback to rule-based categorization
        return $this->ruleBasedCategorization($transaction);
    }

    /**
     * Build AI prompt for categorization
     */
    protected function buildCategorizationPrompt(Transaction $transaction): string
    {
        $date = $transaction->transaction_date?->format('Y-m-d') ?? 'Unknown';
        $amount = number_format($transaction->amount, 2);

        return <<<PROMPT
You are a Nigerian tax expert. Categorize this transaction for tax purposes.

Transaction Details:
- Type: {$transaction->type}
- Amount: ₦{$amount}
- Description: {$transaction->description}
- Counterparty: {$transaction->counterparty}
- Date: {$date}

Categories available:
REVENUE:
  - VAT_OUTPUT: VATable sales (goods/services subject to 7.5% VAT)
  - EXEMPT_SALES: VAT-exempt sales (medical, education, etc)
  - INCOME: General business income
  - INTEREST_RECEIVED: Interest earned on deposits
  - ASSET_SALE: Sale of business assets (equipment, vehicles, etc)
  - LOAN_RECEIVED: Loan proceeds from banks/lenders
  - CAPITAL_CONTRIBUTION: Owner capital injection

EXPENSES - Operating:
  - VAT_INPUT: VATable business expenses (claimable VAT)
  - SALARY_PAYE: Staff salaries (subject to PAYE)
  - RENT: Office/shop rent
  - PROFESSIONAL: Legal, accounting, consulting fees (often subject to WHT)
  - MARKETING: Advertising, promotions
  - UTILITIES: Electricity, water, internet
  - TRANSPORT: Fuel, vehicle maintenance, logistics
  - IT_SOFTWARE: Software subscriptions, tech
  - RAW_MATERIALS: Raw materials for production
  - INVENTORY_PURCHASE: Goods for resale
  - COST_OF_GOODS: Cost of goods sold
  - DEPRECIATION: Non-cash depreciation expense
  - OTHER_EXPENSES: Other business costs

EXPENSES - Capital & Financial:
  - ASSET_PURCHASE: Purchase of equipment, machinery, vehicles
  - EQUIPMENT_PURCHASE: Office equipment, computers, furniture
  - LOAN_REPAYMENT: Loan principal repayment
  - INTEREST: Interest paid on loans
  - DIVIDEND: Dividend payments to shareholders

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
        $amount = $transaction->amount;

        // Revenue patterns (CREDIT transactions)
        if ($transaction->type === 'credit') {
            // Loans and financing
            if (preg_match('/loan|financing|credit facility|borrowing/', $description)) {
                $category = [
                    'category' => 'REVENUE',
                    'sub_category' => 'LOAN_RECEIVED',
                    'confidence' => 0.85,
                    'vat_applicable' => false,
                    'is_business_expense' => false,
                ];
            }
            // Capital contribution
            elseif (preg_match('/capital|equity|owner contribution|shareholder/', $description)) {
                $category = [
                    'category' => 'REVENUE',
                    'sub_category' => 'CAPITAL_CONTRIBUTION',
                    'confidence' => 0.9,
                    'vat_applicable' => false,
                    'is_business_expense' => false,
                ];
            }
            // Asset sale
            elseif (preg_match('/asset sale|equipment sale|vehicle sale/', $description)) {
                $category = [
                    'category' => 'REVENUE',
                    'sub_category' => 'ASSET_SALE',
                    'confidence' => 0.85,
                    'vat_applicable' => true,
                    'is_business_expense' => false,
                ];
            }
            // Interest received
            elseif (preg_match('/interest received|interest credit|deposit interest/', $description)) {
                $category = [
                    'category' => 'REVENUE',
                    'sub_category' => 'INTEREST_RECEIVED',
                    'confidence' => 0.9,
                    'vat_applicable' => false,
                    'is_business_expense' => false,
                ];
            }
            // Regular sales/income
            elseif (preg_match('/transfer|payment received|income|sales|invoice/', $description)) {
                $category = [
                    'category' => 'REVENUE',
                    'sub_category' => 'VAT_OUTPUT',
                    'confidence' => 0.7,
                    'vat_applicable' => true,
                    'is_business_expense' => false,
                ];
            }
            else {
                $category = [
                    'category' => 'REVENUE',
                    'sub_category' => 'INCOME',
                    'confidence' => 0.6,
                    'vat_applicable' => false,
                    'is_business_expense' => false,
                ];
            }

            $transaction->update($category);
            return $category;
        }

        // Expense patterns (DEBIT transactions)
        if ($transaction->type === 'debit') {
            // Asset purchases (typically higher amounts)
            if (preg_match('/asset purchase|equipment|machinery|vehicle|generator|furniture|computer/', $description)) {
                $category = ['category' => 'EXPENSES', 'sub_category' => 'ASSET_PURCHASE', 'confidence' => 0.85];
            }
            elseif (preg_match('/laptop|printer|desk|chair|office equipment/', $description)) {
                $category = ['category' => 'EXPENSES', 'sub_category' => 'EQUIPMENT_PURCHASE', 'confidence' => 0.85];
            }
            // Inventory and raw materials
            elseif (preg_match('/inventory|stock|goods for resale|merchandise/', $description)) {
                $category = ['category' => 'EXPENSES', 'sub_category' => 'INVENTORY_PURCHASE', 'confidence' => 0.8];
            }
            elseif (preg_match('/raw material|supplies|materials/', $description)) {
                $category = ['category' => 'EXPENSES', 'sub_category' => 'RAW_MATERIALS', 'confidence' => 0.8];
            }
            // Loans and financing
            elseif (preg_match('/loan repayment|loan payment|principal payment/', $description)) {
                $category = ['category' => 'EXPENSES', 'sub_category' => 'LOAN_REPAYMENT', 'confidence' => 0.9];
            }
            elseif (preg_match('/interest payment|interest charge|loan interest/', $description)) {
                $category = ['category' => 'EXPENSES', 'sub_category' => 'INTEREST', 'confidence' => 0.9];
            }
            // Dividends
            elseif (preg_match('/dividend|profit distribution|shareholder payment/', $description)) {
                $category = ['category' => 'EXPENSES', 'sub_category' => 'DIVIDEND', 'confidence' => 0.9];
            }
            // Operating expenses
            elseif (preg_match('/salary|wage|staff|payroll/', $description)) {
                $category = ['category' => 'EXPENSES', 'sub_category' => 'SALARY_PAYE', 'confidence' => 0.8];
            }
            elseif (preg_match('/rent|lease/', $description)) {
                $category = ['category' => 'EXPENSES', 'sub_category' => 'RENT', 'confidence' => 0.8];
            }
            elseif (preg_match('/fuel|transport|logistics|delivery/', $description)) {
                $category = ['category' => 'EXPENSES', 'sub_category' => 'TRANSPORT', 'confidence' => 0.7];
            }
            elseif (preg_match('/electricity|water|internet|phone|utility/', $description)) {
                $category = ['category' => 'EXPENSES', 'sub_category' => 'UTILITIES', 'confidence' => 0.85];
            }
            elseif (preg_match('/legal|accounting|consultant|professional|audit/', $description)) {
                $category = ['category' => 'EXPENSES', 'sub_category' => 'PROFESSIONAL', 'confidence' => 0.85];
            }
            elseif (preg_match('/advertising|marketing|promotion/', $description)) {
                $category = ['category' => 'EXPENSES', 'sub_category' => 'MARKETING', 'confidence' => 0.85];
            }
            elseif (preg_match('/software|subscription|saas|hosting/', $description)) {
                $category = ['category' => 'EXPENSES', 'sub_category' => 'IT_SOFTWARE', 'confidence' => 0.85];
            }
            // Tax payments
            elseif (preg_match('/firs|tax authority|revenue service|vat payment/', $description)) {
                $category = ['category' => 'TAX', 'sub_category' => 'VAT_PAYMENT', 'confidence' => 0.9];
            }
            elseif (preg_match('/paye|payee/', $description)) {
                $category = ['category' => 'TAX', 'sub_category' => 'PAYE_PAYMENT', 'confidence' => 0.9];
            }
            elseif (preg_match('/withholding|wht/', $description)) {
                $category = ['category' => 'TAX', 'sub_category' => 'WHT_PAYMENT', 'confidence' => 0.9];
            }
            else {
                $category = [
                    'category' => 'UNCATEGORIZED',
                    'sub_category' => null,
                    'confidence' => 0.5,
                ];
            }

            $category['vat_applicable'] = in_array($category['sub_category'] ?? '',
                ['PROFESSIONAL', 'MARKETING', 'IT_SOFTWARE', 'TRANSPORT', 'EQUIPMENT_PURCHASE']);
            $category['is_business_expense'] = !in_array($category['sub_category'] ?? '',
                ['LOAN_REPAYMENT', 'DIVIDEND', 'ASSET_PURCHASE']);

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
