<?php

namespace App\Services\TaxAgents;

use App\Models\PayeSchedule;
use App\Models\PayeReturn;
use Carbon\Carbon;

/**
 * Specialized AI Agent for PAYE (Pay As You Earn) compliance in Nigeria
 * 
 * Handles:
 * - Employee salary tax calculations
 * - Tax reliefs and deductions (CRA, Pension, NHF, NHIS)
 * - Progressive tax brackets validation
 * - Monthly PAYE return generation
 * - Annual tax projections
 */
class PAYEAiAgent extends BaseTaxAgent
{
    public function getName(): string
    {
        return 'PAYE Agent';
    }

    public function getDescription(): string
    {
        return 'Nigerian PAYE specialist - calculates employee taxes, validates reliefs, and ensures accurate payroll tax compliance';
    }

    protected function getSystemPrompt(): string
    {
        return <<<'PROMPT'
You are a Nigerian PAYE (Pay As You Earn) tax expert AI agent.

NIGERIAN TAX BRACKETS (2026):
- First ₦300,000: 7%
- Next ₦300,000 (₦300,001 - ₦600,000): 11%
- Next ₦500,000 (₦600,001 - ₦1,100,000): 15%
- Next ₦500,000 (₦1,100,001 - ₦1,600,000): 19%
- Next ₦1,600,000 (₦1,600,001 - ₦3,200,000): 21%
- Above ₦3,200,000: 24%

TAX RELIEFS:
- Consolidated Relief Allowance (CRA): Higher of ₦200,000 or 1% of gross income + 20% of gross income
- Pension: Up to 8% of basic, housing, and transport
- National Housing Fund (NHF): 2.5% of basic salary
- National Health Insurance (NHIS): Variable
- Life Insurance: Premium paid (up to limits)

COMPLIANCE:
- Monthly remittance required
- File monthly returns to FIRS
- Annual reconciliation mandatory
- Minimum wage: ₦70,000 (check current rate)

Always respond in valid JSON with accurate calculations.
PROMPT;
    }

    /**
     * Calculate PAYE for an employee
     */
    public function calculateEmployeePAYE(array $employeeData, string $month, string $year): array
    {
        $this->logActivity('calculate_employee_paye', [
            'employee' => $employeeData['name'] ?? 'Unknown',
            'period' => "{$month}/{$year}",
        ]);

        $prompt = <<<PROMPT
Calculate Nigerian PAYE for this employee for {$month}/{$year}:

Employee Details:
{$this->formatForJson($employeeData)}

Apply:
1. Progressive tax brackets (7% to 24%)
2. All applicable reliefs (CRA, Pension, NHF, NHIS, etc.)
3. Cumulative PAYE if year-to-date data provided
4. Validate minimum wage compliance

Respond in this JSON format:
{
  "employee": {
    "name": "Employee name",
    "employee_number": "ID"
  },
  "period": "{$month}/{$year}",
  "gross_income": {
    "basic_salary": 0.00,
    "housing_allowance": 0.00,
    "transport_allowance": 0.00,
    "other_allowances": 0.00,
    "total_gross": 0.00
  },
  "reliefs": {
    "cra": {
      "calculation": "Formula used",
      "amount": 0.00
    },
    "pension": {
      "rate": 0.08,
      "amount": 0.00
    },
    "nhf": {
      "rate": 0.025,
      "amount": 0.00
    },
    "nhis": 0.00,
    "life_insurance": 0.00,
    "total_reliefs": 0.00
  },
  "taxable_income": 0.00,
  "tax_breakdown": [
    {"bracket": "First ₦300,000", "rate": 0.07, "tax": 0.00},
    {"bracket": "Next ₦300,000", "rate": 0.11, "tax": 0.00}
  ],
  "total_tax": 0.00,
  "net_pay": 0.00,
  "validations": [
    {"rule": "Minimum wage compliance", "passed": true}
  ],
  "warnings": [],
  "confidence": 0.95
}
PROMPT;

        $result = $this->callAi($prompt, ['employee' => $employeeData]);

        if (!$result['success']) {
            return [
                'success' => false,
                'error' => $result['error'] ?? 'Calculation failed',
            ];
        }

        $calculation = $this->parseJsonResponse($result['response']['content']);

        if ($this->currentStep) {
            $this->currentStep->update([
                'parsed_response' => $calculation,
                'confidence_score' => $calculation['confidence'] ?? null,
                'tokens_used' => $result['response']['tokens'] ?? 0,
            ]);
        }

        return [
            'success' => true,
            'calculation' => $calculation,
            'tokens_used' => $result['response']['tokens'] ?? 0,
        ];
    }

    /**
     * Generate monthly PAYE return
     */
    public function generateMonthlyReturn(string $month, string $year): array
    {
        $this->logActivity('generate_monthly_return', [
            'month' => $month,
            'year' => $year,
        ]);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Get all PAYE schedules for the month
        $schedules = PayeSchedule::where('business_id', $this->business->id)
            ->where('month', $month)
            ->where('year', $year)
            ->with('employee')
            ->get();

        $prompt = <<<PROMPT
Generate Nigerian monthly PAYE return for {$month}/{$year}:

Business Details:
{$this->formatBusinessContext()}

Employee Count: {$schedules->count()}
Total Gross Pay: ₦{$schedules->sum('gross_income')}
Total Tax Deducted: ₦{$schedules->sum('tax_due')}

Individual Schedules:
{$this->formatSchedulesForAI($schedules)}

Generate:
1. Complete PAYE return summary
2. Employee-by-employee breakdown
3. Monthly remittance details
4. Validation checks
5. FIRS submission requirements

Respond in this JSON format:
{
  "return_summary": {
    "tax_period": "{$month}/{$year}",
    "employer_tin": "Business TIN",
    "total_employees": 0,
    "total_gross_emoluments": 0.00,
    "total_tax_deducted": 0.00,
    "total_reliefs": 0.00,
    "net_tax_payable": 0.00
  },
  "employee_breakdown": [
    {
      "employee_name": "",
      "tin": "",
      "gross_income": 0.00,
      "total_reliefs": 0.00,
      "taxable_income": 0.00,
      "tax_deducted": 0.00
    }
  ],
  "remittance_details": {
    "amount_to_remit": 0.00,
    "due_date": "10th of following month",
    "payment_method": "FIRS Remita",
    "rrr_required": true
  },
  "validations": [
    {"rule": "All employees have TIN", "passed": true},
    {"rule": "Tax calculations accurate", "passed": true}
  ],
  "compliance_checklist": [
    "Upload employee schedules to FIRS portal",
    "Generate Remita RRR",
    "Make payment within 10 days"
  ],
  "warnings": [],
  "confidence": 0.95
}
PROMPT;

        $result = $this->callAi($prompt, [
            'schedules' => $schedules->toArray(),
        ]);

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
     * Validate PAYE calculation
     */
    public function validateCalculation(array $calculationData): array
    {
        $this->logActivity('validate_calculation');

        $prompt = <<<PROMPT
Validate this PAYE calculation for accuracy and Nigerian tax law compliance:

Calculation Data:
{$this->formatForJson($calculationData)}

Check:
1. Correct tax bracket application
2. Relief calculations accurate
3. CRA calculation (higher of ₦200,000 or 1% + 20% formula)
4. Pension relief (8% limit)
5. NHF (2.5% of basic only)
6. Minimum wage compliance
7. Net pay calculation
8. Rounding and precision

Respond in this JSON format:
{
  "validation_result": "passed|failed|warning",
  "checks": [
    {
      "rule": "Tax bracket application",
      "passed": true,
      "details": "Correctly applied progressive rates",
      "severity": "critical|high|medium|low"
    }
  ],
  "errors": [
    {
      "field": "Field with error",
      "issue": "Description",
      "expected": "Expected value",
      "actual": "Actual value",
      "correction": "How to fix"
    }
  ],
  "warnings": [],
  "overall_accuracy": 0.98,
  "confidence": 0.95
}
PROMPT;

        $result = $this->callAi($prompt, ['calculation' => $calculationData]);

        if (!$result['success']) {
            return [
                'success' => false,
                'error' => $result['error'] ?? 'Validation failed',
            ];
        }

        $validation = $this->parseJsonResponse($result['response']['content']);

        if ($this->currentStep) {
            $this->currentStep->update([
                'parsed_response' => $validation,
                'confidence_score' => $validation['confidence'] ?? null,
                'tokens_used' => $result['response']['tokens'] ?? 0,
            ]);
        }

        return [
            'success' => true,
            'validation' => $validation,
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
     * Format PAYE schedules for AI analysis
     */
    private function formatSchedulesForAI($schedules): string
    {
        if ($schedules->count() > 50) {
            return "Large dataset - {$schedules->count()} employees. Analyze patterns and totals.";
        }

        return $schedules->map(function ($schedule) {
            return [
                'employee' => $schedule->employee->name ?? 'N/A',
                'gross_income' => $schedule->gross_income,
                'total_reliefs' => $schedule->total_reliefs,
                'taxable_income' => $schedule->taxable_income,
                'tax_due' => $schedule->tax_due,
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
