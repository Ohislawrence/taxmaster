<?php

namespace App\Services\TaxAgents;

use App\Models\WhtTransaction;
use Carbon\Carbon;

/**
 * Specialized AI Agent for WHT (Withholding Tax) compliance in Nigeria
 * 
 * Handles:
 * - WHT rate determination
 * - Transaction classification
 * - Certificate validation
 * - Monthly WHT return generation
 * - Vendor/client compliance
 */
class WHTAiAgent extends BaseTaxAgent
{
    public function getName(): string
    {
        return 'WHT Agent';
    }

    public function getDescription(): string
    {
        return 'Nigerian WHT specialist - classifies transactions, applies correct rates, and ensures withholding tax compliance';
    }

    protected function getSystemPrompt(): string
    {
        return <<<'PROMPT'
You are a Nigerian Withholding Tax (WHT) expert AI agent.

NIGERIAN WHT RATES:
- Dividends: 10%
- Interest: 10%
- Rent: 10%
- Royalties: 10%
- Commissions: 5%
- Consultancy: 10%
- Contracts: 5%
- Management Fees: 10%
- Directors Fees: 10%
- Professional Fees: 10%

KEY RULES:
- WHT is deducted at source by payer
- Certificate must be issued within 30 days
- Monthly returns filed to FIRS
- Excess WHT can be credited against other taxes
- Non-resident rates may differ
- Some transactions have exemptions

Always respond in valid JSON with accurate classifications.
PROMPT;
    }

    /**
     * Classify transaction for WHT
     */
    public function classifyTransaction(array $transactionData): array
    {
        $this->logActivity('classify_transaction');

        $prompt = <<<PROMPT
Classify this transaction for Nigerian WHT purposes:

Transaction Details:
{$this->formatForJson($transactionData)}

Business Context:
{$this->formatBusinessContext()}

Determine:
1. Transaction type (Dividends, Interest, Rent, etc.)
2. Applicable WHT rate
3. Whether WHT should be withheld
4. If payer or recipient
5. Certificate requirements

Respond in this JSON format:
{
  "transaction_type": "dividends|interest|rent|royalties|commissions|consultancy|contracts|management_fees|directors_fees|professional_fees|other",
  "wht_applicable": true/false,
  "wht_rate": 10.0,
  "business_role": "payer|recipient",
  "gross_amount": 0.00,
  "wht_amount": 0.00,
  "net_amount": 0.00,
  "certificate_required": true/false,
  "exemptions": [],
  "reasoning": "Explanation",
  "confidence": 0.95,
  "recommendations": [
    "Issue WHT certificate within 30 days",
    "Include in monthly WHT return"
  ]
}
PROMPT;

        $result = $this->callAi($prompt, ['transaction' => $transactionData]);

        if (!$result['success']) {
            return [
                'success' => false,
                'error' => $result['error'] ?? 'Classification failed',
            ];
        }

        $classification = $this->parseJsonResponse($result['response']['content']);

        if ($this->currentStep) {
            $this->currentStep->update([
                'parsed_response' => $classification,
                'confidence_score' => $classification['confidence'] ?? null,
                'tokens_used' => $result['response']['tokens'] ?? 0,
            ]);
        }

        return [
            'success' => true,
            'classification' => $classification,
            'tokens_used' => $result['response']['tokens'] ?? 0,
        ];
    }

    /**
     * Generate monthly WHT return
     */
    public function generateMonthlyReturn(string $month, string $year): array
    {
        $this->logActivity('generate_monthly_return', [
            'month' => $month,
            'year' => $year,
        ]);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Get all WHT transactions for the month
        $transactions = WhtTransaction::where('business_id', $this->business->id)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->get();

        $prompt = <<<PROMPT
Generate Nigerian monthly WHT return for {$month}/{$year}:

Business Details:
{$this->formatBusinessContext()}

WHT Transactions:
- Total Transactions: {$transactions->count()}
- Total Gross Amount: ₦{$transactions->sum('gross_amount')}
- Total WHT Withheld: ₦{$transactions->sum('tax_amount')}

Detailed Transactions:
{$this->formatTransactionsForAI($transactions)}

Generate:
1. Complete WHT return by transaction type
2. Vendor/beneficiary breakdown
3. Certificate issuance requirements
4. FIRS submission format

Respond in this JSON format:
{
  "return_summary": {
    "tax_period": "{$month}/{$year}",
    "withholding_agent_tin": "Business TIN",
    "total_transactions": 0,
    "total_gross_payments": 0.00,
    "total_wht_withheld": 0.00
  },
  "breakdown_by_type": [
    {
      "transaction_type": "Consultancy",
      "rate": 10.0,
      "number_of_transactions": 0,
      "total_gross": 0.00,
      "total_wht": 0.00
    }
  ],
  "beneficiary_schedule": [
    {
      "beneficiary_name": "",
      "beneficiary_tin": "",
      "transaction_type": "",
      "gross_amount": 0.00,
      "wht_rate": 0.0,
      "wht_amount": 0.00,
      "certificate_issued": false
    }
  ],
  "certificates_to_issue": [
    {
      "beneficiary": "",
      "amount": 0.00,
      "status": "pending|issued"
    }
  ],
  "remittance_details": {
    "total_to_remit": 0.00,
    "due_date": "21st of following month",
    "payment_channel": "FIRS Remita"
  },
  "validations": [
    {"rule": "All beneficiaries have TIN", "passed": true}
  ],
  "warnings": [],
  "confidence": 0.95
}
PROMPT;

        $result = $this->callAi($prompt, ['transactions' => $transactions->toArray()]);

        if (!$result['success']) {
            return [
                'success' => false,
                'error' => $result['error'] ?? 'Return generation failed',
            ];
        }

        $returnData = $this->parseJsonResponse($result['response']['content']);

        if ($this->currentStep) {
            $this->currentStep->update([
                'parsed_response' => $returnData,
                'confidence_score' => $returnData['confidence'] ?? null,
                'tokens_used' => $result['response']['tokens'] ?? 0,
            ]);
        }

        return [
            'success' => true,
            'return' => $returnData,
            'tokens_used' => $result['response']['tokens'] ?? 0,
        ];
    }

    /**
     * Format business context for AI
     */
    private function formatBusinessContext(): string
    {
        $context = $this->getBusinessContext();
        return json_encode($context, JSON_PRETTY_PRINT);
    }

    /**
     * Format transactions for AI analysis
     */
    private function formatTransactionsForAI($transactions): string
    {
        if ($transactions->count() > 100) {
            return "Large dataset - {$transactions->count()} transactions. Analyze by type.";
        }

        return $transactions->map(function ($txn) {
            return [
                'date' => $txn->transaction_date,
                'type' => $txn->transaction_type,
                'beneficiary' => $txn->beneficiary_name,
                'gross_amount' => $txn->gross_amount,
                'wht_rate' => $txn->wht_rate,
                'wht_amount' => $txn->tax_amount,
            ];
        })->toJson(JSON_PRETTY_PRINT);
    }

    /**
     * Format data for JSON
     */
    private function formatForJson($data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT);
    }
}
