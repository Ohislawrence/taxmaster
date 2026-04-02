<?php

namespace App\Services\TaxAgents;

use App\Models\TaxReturn;
use App\Models\ComplianceDeadline;
use Carbon\Carbon;

/**
 * Specialized AI Agent for Tax Compliance monitoring and management in Nigeria
 * 
 * Handles:
 * - Deadline tracking and monitoring
 * - Compliance status assessment
 * - Risk analysis
 * - Penalty calculations
 * - Proactive compliance recommendations
 */
class ComplianceAiAgent extends BaseTaxAgent
{
    public function getName(): string
    {
        return 'Compliance Agent';
    }

    public function getDescription(): string
    {
        return 'Nigerian tax compliance specialist - monitors deadlines, assesses risks, and ensures timely filing';
    }

    protected function getSystemPrompt(): string
    {
        return <<<'PROMPT'
You are a Nigerian tax compliance monitoring expert AI agent.

NIGERIAN TAX DEADLINES:
- VAT Returns: 21st of following month
- PAYE Remittance: 10th of following month
- WHT Returns: 21st of following month
- CIT (Companies Income Tax): 6 months after year-end (unaudited), 18 months (audited)
- Annual Returns: Various based on tax type

PENALTIES:
- Late VAT filing: ₦50,000 first month + ₦25,000 per month after
- Late PAYE remittance: 10% of tax + 5% interest per annum
- Late WHT: 10% penalty + 10% interest per annum
- Failure to file: Prosecution possible

COMPLIANCE LEVELS:
- Critical: Overdue, legal action risk
- High: Due within 7 days
- Medium: Due within 30 days
- Low: >30 days until due

Always provide accurate deadline tracking and risk assessment.
PROMPT;
    }

    /**
     * Assess overall compliance status
     */
    public function assessComplianceStatus(): array
    {
        $this->logActivity('assess_compliance_status');

        // Get all tax obligations
        $vatReturns = TaxReturn::where('business_id', $this->business->id)
            ->where('return_type', 'vat')
            ->where('status', '!=', 'filed')
            ->get();

        $payeReturns = TaxReturn::where('business_id', $this->business->id)
            ->where('return_type', 'paye')
            ->where('status', '!=', 'filed')
            ->get();

        $whtReturns = TaxReturn::where('business_id', $this->business->id)
            ->where('return_type', 'wht')
            ->where('status', '!=', 'filed')
            ->get();

        $prompt = <<<PROMPT
Assess the overall tax compliance status for this Nigerian business:

Business Context:
{$this->formatBusinessContext()}

Outstanding Obligations:
- Unfiled VAT Returns: {$vatReturns->count()}
- Unfiled PAYE Returns: {$payeReturns->count()}
- Unfiled WHT Returns: {$whtReturns->count()}

Current Date: {$this->getCurrentDate()}

VAT Returns:
{$this->formatReturnsForAI($vatReturns)}

PAYE Returns:
{$this->formatReturnsForAI($payeReturns)}

WHT Returns:
{$this->formatReturnsForAI($whtReturns)}

Provide:
1. Overall compliance score (0-100)
2. Risk level assessment
3. Immediate actions required
4. Potential penalties
5. Timeline for compliance

Respond in this JSON format:
{
  "compliance_score": 85,
  "risk_level": "critical|high|medium|low",
  "overall_status": "compliant|at_risk|non_compliant",
  "summary": "Brief overview of compliance status",
  "outstanding_obligations": [
    {
      "type": "VAT Return",
      "period": "March 2026",
      "due_date": "2026-04-21",
      "days_overdue": 0,
      "status": "overdue|due_soon|on_track",
      "estimated_penalty": 0.00,
      "priority": "critical|high|medium|low"
    }
  ],
  "immediate_actions": [
    {
      "action": "File March 2026 VAT return",
      "deadline": "2026-04-21",
      "priority": "critical",
      "estimated_time": "2 hours"
    }
  ],
  "potential_penalties": {
    "current": 0.00,
    "if_not_resolved_30_days": 0.00,
    "if_not_resolved_90_days": 0.00
  },
  "recommendations": [
    "Set up automated deadline reminders",
    "Consider professional tax consultant for complex returns"
  ],
  "improvement_areas": [
    "VAT filing consistency needs improvement"
  ],
  "compliance_timeline": {
    "week_1": ["Action items"],
    "week_2": ["Action items"],
    "month_1": ["Action items"]
  },
  "confidence": 0.95
}
PROMPT;

        $result = $this->callAi($prompt, [
            'vat_returns' => $vatReturns->toArray(),
            'paye_returns' => $payeReturns->toArray(),
            'wht_returns' => $whtReturns->toArray(),
        ]);

        if (!$result['success']) {
            return [
                'success' => false,
                'error' => $result['error'] ?? 'Assessment failed',
            ];
        }

        $assessment = $this->parseJsonResponse($result['response']['content']);

        if ($this->currentStep) {
            $this->currentStep->update([
                'parsed_response' => $assessment,
                'confidence_score' => $assessment['confidence'] ?? null,
                'tokens_used' => $result['response']['tokens'] ?? 0,
            ]);
        }

        return [
            'success' => true,
            'assessment' => $assessment,
            'tokens_used' => $result['response']['tokens'] ?? 0,
        ];
    }

    /**
     * Calculate potential penalties
     */
    public function calculatePenalties(array $overdueReturns): array
    {
        $this->logActivity('calculate_penalties');

        $prompt = <<<PROMPT
Calculate Nigerian tax penalties for these overdue returns:

Business: {$this->business->name}
Current Date: {$this->getCurrentDate()}

Overdue Returns:
{$this->formatForJson($overdueReturns)}

Calculate:
1. Late filing penalties
2. Interest on unpaid taxes
3. Total penalty amount
4. Daily accrual rate
5. Projected penalties for next 30/60/90 days

Respond in this JSON format:
{
  "total_current_penalties": 0.00,
  "breakdown": [
    {
      "return_type": "VAT",
      "period": "March 2026",
      "days_overdue": 10,
      "base_penalty": 50000.00,
      "additional_penalty": 0.00,
      "interest": 0.00,
      "total": 50000.00,
      "calculation_details": "₦50,000 first month late filing"
    }
  ],
  "projections": {
    "30_days": 0.00,
    "60_days": 0.00,
    "90_days": 0.00
  },
  "daily_accrual": 0.00,
  "recommendations": [
    "File immediately to avoid additional ₦25,000/month penalty"
  ],
  "legal_risks": [],
  "confidence": 0.95
}
PROMPT;

        $result = $this->callAi($prompt, ['overdue_returns' => $overdueReturns]);

        if (!$result['success']) {
            return [
                'success' => false,
                'error' => $result['error'] ?? 'Penalty calculation failed',
            ];
        }

        $penalties = $this->parseJsonResponse($result['response']['content']);

        if ($this->currentStep) {
            $this->currentStep->update([
                'parsed_response' => $penalties,
                'confidence_score' => $penalties['confidence'] ?? null,
                'tokens_used' => $result['response']['tokens'] ?? 0,
            ]);
        }

        return [
            'success' => true,
            'penalties' => $penalties,
            'tokens_used' => $result['response']['tokens'] ?? 0,
        ];
    }

    /**
     * Generate compliance action plan
     */
    public function generateActionPlan(int $daysAhead = 90): array
    {
        $this->logActivity('generate_action_plan', ['days_ahead' => $daysAhead]);

        $prompt = <<<PROMPT
Generate a {$daysAhead}-day tax compliance action plan for this Nigerian business:

Business Context:
{$this->formatBusinessContext()}

Current Date: {$this->getCurrentDate()}
Planning Horizon: Next {$daysAhead} days

Consider:
1. Upcoming VAT return deadlines
2. Monthly PAYE obligations
3. WHT filing requirements
4. Annual return preparations
5. Quarterly reviews

Create a week-by-week action plan with:
- Specific tasks
- Deadlines
- Priority levels
- Estimated time requirements
- Dependencies

Respond in this JSON format:
{
  "plan_period": "{$this->getCurrentDate()} to {$this->getFutureDate($daysAhead)}",
  "weekly_plan": [
    {
      "week": 1,
      "date_range": "Apr 1-7, 2026",
      "tasks": [
        {
          "task": "Prepare March 2026 VAT return",
          "deadline": "2026-04-21",
          "priority": "critical",
          "estimated_hours": 3,
          "dependencies": ["Reconcile bank transactions"],
          "status": "pending"
        }
      ],
      "total_hours": 10
    }
  ],
  "critical_milestones": [
    {
      "milestone": "Q1 2026 tax review",
      "date": "2026-04-30",
      "requirements": []
    }
  ],
 "resource_requirements": [
    "Monthly transaction reports",
    "Employee payroll data"
  ],
  "buffer_recommendations": [
    "Start VAT prep 7 days before deadline"
  ],
  "automation_opportunities": [
    "Set up recurring transaction categorization"
  ],
  "confidence": 0.95
}
PROMPT;

        $result = $this->callAi($prompt);

        if (!$result['success']) {
            return [
                'success' => false,
                'error' => $result['error'] ?? 'Action plan generation failed',
            ];
        }

        $actionPlan = $this->parseJsonResponse($result['response']['content']);

        if ($this->currentStep) {
            $this->currentStep->update([
                'parsed_response' => $actionPlan,
                'confidence_score' => $actionPlan['confidence'] ?? null,
                'tokens_used' => $result['response']['tokens'] ?? 0,
            ]);
        }

        return [
            'success' => true,
            'action_plan' => $actionPlan,
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
     * Format returns for AI analysis
     */
    private function formatReturnsForAI($returns): string
    {
        return $returns->map(function ($return) {
            return [
                'period' => $return->tax_period,
                'due_date' => $return->due_date,
                'status' => $return->status,
                'amount' => $return->amount_payable ?? 0,
            ];
        })->toJson(JSON_PRETTY_PRINT);
    }

    /**
     * Get current date
     */
    private function getCurrentDate(): string
    {
        return now()->format('Y-m-d');
    }

    /**
     * Get future date
     */
    private function getFutureDate(int $days): string
    {
        return now()->addDays($days)->format('Y-m-d');
    }

    /**
     * Format data for JSON
     */
    private function formatForJson($data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT);
    }
}
