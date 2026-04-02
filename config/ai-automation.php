<?php

return [
    'features' => [
        'auto_categorize_transactions' => true,
        'smart_compliance_reminders' => true,
        'payment_recovery_suggestions' => true,
    ],

    'transaction_categorization' => [
        'enabled' => true,
        'provider' => 'deepseek',
        'confidence_threshold' => 0.90,
        'categories' => [
            'VAT taxable' => 'Transaction subject to VAT',
            'PAYE deductible' => 'Employee-related expense',
            'WHT applicable' => 'Subject to withholding tax',
            'Business expense' => 'Non-taxable operational expense',
            'Fixed asset' => 'Capital expenditure',
            'Personal expense' => 'Should not be deducted',
        ],
        'prompt' => <<<'PROMPT'
Analyze this transaction and categorize it for Nigerian tax purposes:

Transaction: {transaction_description}
Amount: ₦{amount}
Merchant: {merchant_name}
Date: {date}
Business Type: {business_type}
Previous similar transactions: {similar_transactions}

Respond ONLY in JSON format:
{
  "category": "VAT taxable|PAYE deductible|WHT applicable|Business expense|Fixed asset|Personal expense",
  "confidence": 0.0-1.0,
  "reasoning": "Brief explanation",
  "tax_implications": "Specific Nigerian tax rules that apply",
  "suggested_action": "What user should do"
}
PROMPT,
    ],

    'compliance_reminders' => [
        'enabled' => true,
        'provider' => 'deepseek',
        'days_before' => 30,
        'prompt' => <<<'PROMPT'
The user has an upcoming tax compliance deadline. Generate a prioritized action plan:

Deadline: {deadline_name}
Due Date: {due_date}
Business: {business_name}
Business Type: {business_type}
Overdue items: {overdue_items}
User's history with this deadline: {history}

Respond ONLY in JSON format:
{
  "priority": "critical|high|normal",
  "recommended_actions": [
    {"step": 1, "action": "Submit VAT return", "time_estimate_hours": 2},
    {"step": 2, "action": "Prepare supporting documents", "time_estimate_hours": 1}
  ],
  "documents_needed": ["VAT Form 002", "Bank statements"],
  "common_mistakes": ["Forgetting to include exempted sales", "Wrong exchange rates"],
  "estimated_cost": 0,
  "deadline_risk": "on_track|at_risk|critical"
}
PROMPT,
    ],

    'payment_recovery' => [
        'enabled' => true,
        'provider' => 'deepseek',
        'grace_period_days' => 3,
        'prompt' => <<<'PROMPT'
User's subscription payment failed. Analyze their account and suggest recovery strategy:

Business: {business_name}
Current Plan: {plan_type}
Monthly Cost: ₦{monthly_cost}
Account Age: {account_age_days} days
Historical Payment Success Rate: {success_rate}%
Failed Attempts: {failed_attempts}
Last Successful Payment: {last_payment_date}
Account Balance: ₦{account_balance}
Subscription Status: {status}
Grace Period Days Remaining: {grace_days}

Respond ONLY in JSON format:
{
  "recovery_strategy": "gentle_reminder|offer_discount|payment_plan|pause_service",
  "suggested_discount": 0-100,
  "payment_plan": {"months": 2, "first_payment": 0.5},
  "messaging": "Empathetic message to show user",
  "recommended_channels": ["email", "in_app", "sms"],
  "probability_of_recovery": 0.0-1.0,
  "risk_assessment": "Likelihood they'll churn if not recovered"
}
PROMPT,
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Tax Workflows
    |--------------------------------------------------------------------------
    |
    | Define automated tax compliance workflows that orchestrate multiple
    | AI agents to handle Nigerian tax filing requirements end-to-end.
    |
    */

    'workflows' => [
        'enabled' => true,
        'default_provider' => 'deepseek',
        'confidence_threshold' => 0.85, // Minimum confidence to auto-submit
        'require_review_below_threshold' => true,
        'max_retries' => 3,
        'timeout_minutes' => 10,

        // Predefined workflow definitions
        'definitions' => [
            'monthly_vat_full' => [
                'name' => 'Complete Monthly VAT Return',
                'description' => 'End-to-end VAT return preparation and filing',
                'estimated_duration' => '10-15 minutes',
                'steps' => [
                    [
                        'name' => 'collect_transactions',
                        'agent' => 'vat',
                        'action' => 'collectTransactions',
                        'input' => ['context.month', 'context.year'],
                        'continue_on_failure' => false,
                    ],
                    [
                        'name' => 'categorize_transactions',
                        'agent' => 'vat',
                        'action' => 'categorizeTransactions',
                        'input' => ['result.collect_transactions.data'],
                        'depends_on' => ['collect_transactions'],
                    ],
                    [
                        'name' => 'calculate_vat',
                        'agent' => 'vat',
                        'action' => 'calculateMonthlyVAT',
                        'input' => ['context.month', 'context.year'],
                        'depends_on' => ['categorize_transactions'],
                    ],
                    [
                        'name' => 'validate_calculations',
                        'agent' => 'vat',
                        'action' => 'validateCalculations',
                        'input' => ['result.calculate_vat.calculation'],
                        'depends_on' => ['calculate_vat'],
                    ],
                    [
                        'name' => 'generate_return',
                        'agent' => 'vat',
                        'action' => 'generateVATReturn',
                        'input' => ['result.calculate_vat.calculation'],
                        'depends_on' => ['validate_calculations'],
                    ],
                    [
                        'name' => 'compliance_check',
                        'agent' => 'compliance',
                        'action' => 'checkDeadline',
                        'input' => ['context.month', 'context.year', 'vat'],
                        'depends_on' => ['generate_return'],
                    ],
                ],
            ],

            'monthly_paye_full' => [
                'name' => 'Complete Monthly PAYE Return',
                'description' => 'End-to-end PAYE calculation and remittance',
                'estimated_duration' => '15-20 minutes',
                'steps' => [
                    [
                        'name' => 'collect_payroll',
                        'agent' => 'paye',
                        'action' => 'collectPayrollData',
                        'input' => ['context.month', 'context.year'],
                    ],
                    [
                        'name' => 'calculate_paye',
                        'agent' => 'paye',
                        'action' => 'calculateAllEmployees',
                        'input' => ['result.collect_payroll.employees'],
                        'depends_on' => ['collect_payroll'],
                    ],
                    [
                        'name' => 'validate_calculations',
                        'agent' => 'paye',
                        'action' => 'validateCalculation',
                        'input' => ['result.calculate_paye.data'],
                        'depends_on' => ['calculate_paye'],
                    ],
                    [
                        'name' => 'generate_return',
                        'agent' => 'paye',
                        'action' => 'generateMonthlyReturn',
                        'input' => ['context.month', 'context.year'],
                        'depends_on' => ['validate_calculations'],
                    ],
                ],
            ],

            'quarterly_compliance_review' => [
                'name' => 'Quarterly Compliance Review',
                'description' => 'Comprehensive 90-day compliance assessment',
                'estimated_duration' => '20-30 minutes',
                'steps' => [
                    [
                        'name' => 'assess_vat',
                        'agent' => 'compliance',
                        'action' => 'assessVATCompliance',
                        'input' => [],
                    ],
                    [
                        'name' => 'assess_paye',
                        'agent' => 'compliance',
                        'action' => 'assessPAYECompliance',
                        'input' => [],
                    ],
                    [
                        'name' => 'assess_wht',
                        'agent' => 'compliance',
                        'action' => 'assessWHTCompliance',
                        'input' => [],
                    ],
                    [
                        'name' => 'calculate_penalties',
                        'agent' => 'compliance',
                        'action' => 'calculatePenalties',
                        'input' => ['result.assess_vat', 'result.assess_paye', 'result.assess_wht'],
                        'depends_on' => ['assess_vat', 'assess_paye', 'assess_wht'],
                    ],
                    [
                        'name' => 'generate_action_plan',
                        'agent' => 'compliance',
                        'action' => 'generateActionPlan',
                        'input' => [90],
                        'depends_on' => ['calculate_penalties'],
                    ],
                    [
                        'name' => 'optimization_recommendations',
                        'agent' => 'advisory',
                        'action' => 'recommendOptimizations',
                        'input' => [],
                        'depends_on' => ['generate_action_plan'],
                    ],
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Nigerian Tax Reference Data
    |--------------------------------------------------------------------------
    |
    | Tax rates, thresholds, and deadlines for Nigerian tax compliance
    |
    */

    'nigerian_tax_data' => [
        'vat' => [
            'rate' => 7.5,
            'registration_threshold' => 25000000, // ₦25M
            'filing_deadline_day' => 21, // 21st of following month
        ],

        'paye' => [
            'brackets' => [
                ['min' => 0, 'max' => 300000, 'rate' => 7],
                ['min' => 300001, 'max' => 600000, 'rate' => 11],
                ['min' => 600001, 'max' => 1100000, 'rate' => 15],
                ['min' => 1100001, 'max' => 1600000, 'rate' => 19],
                ['min' => 1600001, 'max' => 3200000, 'rate' => 21],
                ['min' => 3200001, 'max' => PHP_INT_MAX, 'rate' => 24],
            ],
            'minimum_cra' => 200000, // Minimum Consolidated Relief Allowance
            'remittance_deadline_day' => 10, // 10th of following month
        ],

        'wht' => [
            'rates' => [
                'dividends' => 10,
                'interest' => 10,
                'rent' => 10,
                'royalties' => 10,
                'commissions' => 5,
                'consultancy' => 10,
                'contracts' => 5,
                'management_fees' => 10,
                'directors_fees' => 10,
                'professional_fees' => 10,
            ],
            'filing_deadline_day' => 21, // 21st of following month
        ],

        'cit' => [
            'rate' => 30,
            'small_company_rate' => 20, // Companies with turnover < ₦25M
            'filing_deadline_months_unaudited' => 6,
            'filing_deadline_months_audited' => 18,
        ],
    ],
];

