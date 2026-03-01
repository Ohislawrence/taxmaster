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
];
