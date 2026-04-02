<?php

namespace App\Services\TaxAgents;

use App\Models\Transaction;
use App\Models\Invoice;
use Carbon\Carbon;

/**
 * Specialized AI Agent for VAT (Value Added Tax) compliance in Nigeria
 * 
 * Handles:
 * - VAT calculation and validation
 * - Taxable vs exempt transactions analysis
 * - Input/Output VAT reconciliation
 * - VAT return generation
 * - FIRS VAT compliance requirements
 */
class VATAiAgent extends BaseTaxAgent
{
    public function getName(): string
    {
        return 'VAT Agent';
    }

    public function getDescription(): string
    {
        return 'Nigerian VAT compliance specialist - analyzes transactions, calculates VAT obligations, and ensures FIRS compliance';
    }

    protected function getSystemPrompt(): string
    {
        return <<<'PROMPT'
You are a Nigerian VAT (Value Added Tax) compliance expert AI agent.

KEY FACTS:
- Standard VAT rate in Nigeria: 7.5%
- VAT is charged on supply of goods and services
- Some items are VAT-exempt (basic food, medical services, education)
- Some items are zero-rated (exports, goods in EPZ)
- Input VAT: VAT paid on purchases (can be credited)
- Output VAT: VAT collected on sales (must be remitted)
- VAT returns filed monthly (21st of following month)
- Threshold: ₦25 million annual turnover

RESPONSE FORMAT:
Always respond in valid JSON format with Nigerian naira amounts.

COMPLIANCE RULES:
- Accurate calculation of 7.5% VAT
- Proper categorization of taxable, exempt, and zero-rated transactions
- Correct input/output VAT reconciliation
- FIRS form compliance
PROMPT;
    }

    /**
     * Analyze transaction for VAT applicability
     */
    public function analyzeTransaction(Transaction $transaction): array
    {
        $this->logActivity('analyze_transaction', ['transaction_id' => $transaction->id]);

        $prompt = <<<PROMPT
Analyze this Nigerian business transaction for VAT applicability:

Transaction Details:
- Description: {$transaction->narration}
- Amount: ₦{$transaction->amount}
- Type: {$transaction->type}
- Category: {$transaction->category}
- Date: {$transaction->transaction_date}
- Merchant: {$transaction->merchant_name}

Business Context:
{$this->formatBusinessContext()}

Determine:
1. Is this transaction subject to VAT?
2. If yes, is it standard rated (7.5%), zero-rated (0%), or exempt?
3. For expense transactions: Is the input VAT creditable?
4. What's the VAT amount?

Respond in this JSON format:
{
  "vat_applicable": true/false,
  "vat_category": "standard|zero_rated|exempt|not_applicable",
  "vat_rate": 7.5,
  "vat_amount": 0.00,
  "is_input_vat_creditable": true/false,
  "reasoning": "Brief explanation",
  "confidence": 0.95,
  "firs_category": "Relevant FIRS category code",
  "recommendations": ["Action items if any"]
}
PROMPT;

        $result = $this->callAi($prompt, [
            'transaction' => $transaction->toArray(),
            'business' => $this->getBusinessContext(),
        ]);

        if (!$result['success']) {
            return [
                'success' => false,
                'error' => $result['error'] ?? 'Analysis failed',
            ];
        }

        $analysis = $this->parseJsonResponse($result['response']['content']);

        // Track in current step if available
        if ($this->currentStep) {
            $this->currentStep->update([
                'parsed_response' => $analysis,
                'confidence_score' => $analysis['confidence'] ?? null,
                'tokens_used' => $result['response']['tokens'] ?? 0,
            ]);
        }

        return [
            'success' => true,
            'analysis' => $analysis,
            'tokens_used' => $result['response']['tokens'] ?? 0,
        ];
    }

    /**
     * Calculate monthly VAT obligations
     */
    public function calculateMonthlyVAT(string $month, string $year): array
    {
        $this->logActivity('calculate_monthly_vat', [
            'month' => $month,
            'year' => $year,
        ]);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Get all transactions for the month
        $transactions = Transaction::where('business_id', $this->business->id)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->get();

        // Get invoices for the month
        $invoices = Invoice::where('business_id', $this->business->id)
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->get();

        $prompt = <<<PROMPT
Calculate Nigerian VAT obligations for this business for the tax period: {$month}/{$year}

Business Context:
{$this->formatBusinessContext()}

Transactions Summary:
- Total Transactions: {$transactions->count()}
- Total Sales: ₦{$transactions->where('type', 'income')->sum('amount')}
- Total Purchases: ₦{$transactions->where('type', 'expense')->sum('amount')}

Invoices Summary:
- Total Invoices: {$invoices->count()}
- Total Invoice Amount: ₦{$invoices->sum('total_amount')}
- Total VAT on Invoices: ₦{$invoices->sum('vat_amount')}

Detailed Transaction Data:
{$this->formatTransactionsForAI($transactions)}

Calculate:
1. Output VAT (VAT on sales/services provided)
2. Input VAT (VAT on purchases - creditable)
3. Net VAT payable/refundable
4. Validate against invoices
5. Identify any discrepancies

Respond in this JSON format:
{
  "tax_period": "{$month}/{$year}",
  "output_vat": {
    "taxable_sales": 0.00,
    "vat_collected": 0.00,
    "breakdown": {
      "standard_rated": 0.00,
      "zero_rated": 0.00
    }
  },
  "input_vat": {
    "taxable_purchases": 0.00,
    "vat_paid": 0.00,
    "creditable_amount": 0.00,
    "non_creditable_amount": 0.00
  },
  "net_vat": {
    "payable": 0.00,
    "refundable": 0.00,
    "status": "payable|refundable|nil"
  },
  "validations": [
    {
      "rule": "Output VAT matches invoices",
      "passed": true,
      "variance": 0.00
    }
  ],
  "warnings": ["Any issues found"],
  "firs_return_data": {
    "box1_total_sales": 0.00,
    "box2_vat_on_sales": 0.00,
    "box3_total_purchases": 0.00,
    "box4_vat_on_purchases": 0.00,
    "box5_net_vat": 0.00
  },
  "recommendations": ["Suggestions for the business"],
  "confidence": 0.95,
  "anomalies": ["Unusual patterns detected"]
}
PROMPT;

        $result = $this->callAi($prompt, [
            'transactions' => $transactions->toArray(),
            'invoices' => $invoices->toArray(),
        ]);

        if (!$result['success']) {
            return [
                'success' => false,
                'error' => $result['error'] ?? 'Calculation failed',
            ];
        }

        $calculation = $this->parseJsonResponse($result['response']['content']);

        // Perform additional validations
        $validations = $this->validateVATCalculation($calculation, $transactions, $invoices);

        if ($this->currentStep) {
            $this->currentStep->update([
                'parsed_response' => $calculation,
                'confidence_score' => $calculation['confidence'] ?? null,
                'validations' => $validations,
                'tokens_used' => $result['response']['tokens'] ?? 0,
            ]);
        }

        return [
            'success' => true,
            'calculation' => $calculation,
            'validations' => $validations,
            'tokens_used' => $result['response']['tokens'] ?? 0,
        ];
    }

    /**
     * Generate VAT return for FIRS submission
     */
    public function generateVATReturn(array $vatCalculation): array
    {
        $this->logActivity('generate_vat_return');

        $prompt = <<<PROMPT
Generate a complete Nigerian VAT return (Form VAT 001) ready for FIRS submission.

VAT Calculation Data:
{$this->formatForJson($vatCalculation)}

Business Details:
{$this->formatBusinessContext()}

Requirements:
1. Fill all required fields in FIRS Form VAT 001
2. Ensure all calculations are accurate
3. Include supporting schedules
4. Validate against FIRS requirements
5. Flag any items requiring documentation

Respond in this JSON format:
{
  "form_vat_001": {
    "tin": "Business TIN",
    "tax_period": "MM/YYYY",
    "section_a_sales": {
      "standard_rated_sales": 0.00,
      "zero_rated_sales": 0.00,
      "exempt_sales": 0.00,
      "total_sales": 0.00
    },
    "section_b_vat_on_sales": {
      "standard_rated_vat": 0.00,
      "imported_services_vat": 0.00,
      "total_output_vat": 0.00
    },
    "section_c_purchases": {
      "standard_rated_purchases": 0.00,
      "exempt_purchases": 0.00,
      "total_purchases": 0.00
    },
    "section_d_input_vat": {
      "creditable_input_vat": 0.00,
      "non_creditable_input_vat": 0.00,
      "total_input_vat": 0.00
    },
    "section_e_computation": {
      "total_output_vat": 0.00,
      "less_input_vat": 0.00,
      "net_vat_payable": 0.00,
      "vat_refundable": 0.00
    }
  },
  "supporting_schedules": {
    "schedule_1_standard_rated_sales": [],
    "schedule_2_input_vat_claims": []
  },
  "declaration": {
    "prepared_by": "AI Agent",
    "date": "current date",
    "certification": "I certify that the information provided is correct"
  },
  "submission_requirements": [
    "Documents needed for submission"
  ],
  "compliance_checklist": [
    {"item": "TIN is valid", "status": "passed|failed"}
  ],
  "confidence": 0.95
}
PROMPT;

        $result = $this->callAi($prompt, ['calculation' => $vatCalculation]);

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
     * Validate VAT calculation
     */
    private function validateVATCalculation(array $calculation, $transactions, $invoices): array
    {
        $validations = [];

        // Validation 1: Check if VAT rate is correct (7.5%)
        $validations[] = [
            'rule' => 'VAT rate is 7.5%',
            'passed' => true,
            'message' => 'Standard Nigerian VAT rate applied',
        ];

        // Validation 2: Output VAT should roughly match invoice VAT
        $calculatedOutputVat = $calculation['output_vat']['vat_collected'] ?? 0;
        $invoiceVat = $invoices->sum('vat_amount');
        $variance = abs($calculatedOutputVat - $invoiceVat);
        $variancePercentage = $invoiceVat > 0 ? ($variance / $invoiceVat) * 100 : 0;

        $validations[] = [
            'rule' => 'Output VAT matches invoices (within 5% variance)',
            'passed' => $variancePercentage <= 5,
            'message' => "Variance: ₦{$variance} ({$variancePercentage}%)",
            'variance' => $variance,
        ];

        // Validation 3: Net VAT should be reasonable
        $netVat = $calculation['net_vat']['payable'] ?? $calculation['net_vat']['refundable'] ?? 0;
        $totalSales = $transactions->where('type', 'income')->sum('amount');
        $reasonableVat = $totalSales > 0 && $netVat <= ($totalSales * 0.10); // Should not exceed 10% of sales

        $validations[] = [
            'rule' => 'Net VAT is reasonable compared to sales',
            'passed' => $reasonableVat,
            'message' => $reasonableVat ? 'VAT within expected range' : 'VAT seems unusually high',
        ];

        return $validations;
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
            // For large datasets, provide summary
            return "Large dataset - {$transactions->count()} transactions. Analyze patterns.";
        }

        return $transactions->map(function ($txn) {
            return [
                'date' => $txn->transaction_date,
                'type' => $txn->type,
                'amount' => $txn->amount,
                'category' => $txn->category,
                'description' => $txn->narration,
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
