# Phase 1 Implementation Guide - TaxMaster.ng MVP

**Timeline**: 10 weeks  
**Goal**: Launch MVP with bank sync, tax forms, and compliance calendar  
**Target Users**: Freelancers + Small SMEs (both)

---

## Sprint Breakdown

### Sprint 1: Database & Mono Integration (Weeks 1-2)

**Deliverables**:
- [ ] Database migrations for bank accounts, transactions, categories
- [ ] Mono API service setup
- [ ] Bank account connection flow
- [ ] Initial transaction sync

**Files to Create**:
```
database/migrations/2026_02_25_create_bank_accounts_table.php
database/migrations/2026_02_25_create_transactions_table.php
database/migrations/2026_02_25_create_transaction_categories_table.php
app/Models/BankAccount.php
app/Models/Transaction.php
app/Models/TransactionCategory.php
app/Services/MonoIntegrationService.php
app/Http/Controllers/Business/BankAccountController.php
resources/js/Pages/Business/BankAccounts/Index.vue
resources/js/Pages/Business/BankAccounts/Connect.vue
```

---

### Sprint 2: Transaction Categorization (Weeks 3-4)

**Deliverables**:
- [ ] AI categorization service
- [ ] Transaction list with categories
- [ ] Manual category override
- [ ] Category rules engine

**Files to Create**:
```
app/Services/TransactionCategorizationService.php
app/Http/Controllers/Business/TransactionController.php
resources/js/Pages/Business/Transactions/Index.vue
resources/js/Pages/Business/Transactions/Categorize.vue
resources/js/Components/Business/TransactionCard.vue
```

---

### Sprint 3: Compliance Calendar (Weeks 5-6)

**Deliverables**:
- [ ] Compliance deadline model
- [ ] Calendar view
- [ ] Email alert system
- [ ] Deadline tracking

**Files to Create**:
```
database/migrations/2026_03_15_create_compliance_deadlines_table.php
app/Models/ComplianceDeadline.php
app/Services/ComplianceCalendarService.php
app/Jobs/GenerateComplianceDeadlines.php
app/Jobs/SendComplianceReminder.php
app/Http/Controllers/Business/ComplianceController.php
resources/js/Pages/Business/Compliance/Calendar.vue
resources/js/Components/Business/ComplianceBanner.vue
resources/views/emails/compliance-reminder.blade.php
```

---

### Sprint 4: VAT Form Generation (Weeks 7-8)

**Deliverables**:
- [ ] VAT calculation service
- [ ] Form 002 generator
- [ ] PDF export
- [ ] Payment instructions

**Files to Create**:
```
database/migrations/2026_04_01_create_vat_returns_table.php
app/Models/VATReturn.php
app/Services/VATCalculationService.php
app/Services/FirsForm002GeneratorService.php
app/Http/Controllers/Business/VATController.php
resources/js/Pages/Business/Tax/VAT/Index.vue
resources/js/Pages/Business/Tax/VAT/Form002.vue
resources/views/exports/firs-form-002.blade.php
```

---

### Sprint 5: Polish & Testing (Weeks 9-10)

**Deliverables**:
- [ ] Enhanced AI chat with transaction context
- [ ] User testing
- [ ] Bug fixes
- [ ] Performance optimization
- [ ] Documentation

---

## Technical Specifications

### 1. Mono API Integration

**API Docs**: https://docs.mono.co/reference/authentication  
**Sandbox**: https://app.withmono.com/sandbox

**Flow**:
```
1. User clicks "Connect Bank Account"
2. App generates Mono Connect URL
3. User authorizes bank account
4. Mono redirects with auth code
5. App exchanges code for access token
6. App stores encrypted token
7. App syncs transactions (last 6 months)
8. App schedules auto-sync (every 6 hours)
```

**Environment Variables Needed**:
```env
MONO_SECRET_KEY=test_sk_xxxxxxxxxxxx
MONO_PUBLIC_KEY=test_pk_xxxxxxxxxxxx
MONO_REDIRECT_URL=https://taxmaster.ng/business/banks/callback
MONO_WEBHOOK_SECRET=your_webhook_secret
```

**Mono API Service**:
```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\BankAccount;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

class MonoIntegrationService
{
    protected $baseUrl = 'https://api.withmono.com';
    protected $secretKey;

    public function __construct()
    {
        $this->secretKey = config('services.mono.secret_key');
    }

    /**
     * Exchange authorization code for account ID and token
     */
    public function exchangeToken(string $code): array
    {
        $response = Http::withHeaders([
            'mono-sec-key' => $this->secretKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/account/auth", [
            'code' => $code,
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to exchange Mono token: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Get account details
     */
    public function getAccountDetails(string $accountId): array
    {
        $response = Http::withHeaders([
            'mono-sec-key' => $this->secretKey,
        ])->get("{$this->baseUrl}/accounts/{$accountId}");

        if (!$response->successful()) {
            throw new \Exception('Failed to fetch account details: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Sync transactions for a bank account
     */
    public function syncTransactions(BankAccount $bankAccount, ?string $startDate = null, ?string $endDate = null): int
    {
        if (!$startDate) {
            $startDate = now()->subMonths(6)->format('Y-m-d');
        }
        if (!$endDate) {
            $endDate = now()->format('Y-m-d');
        }

        $response = Http::withHeaders([
            'mono-sec-key' => $this->secretKey,
        ])->get("{$this->baseUrl}/accounts/{$bankAccount->mono_account_id}/transactions", [
            'start' => $startDate,
            'end' => $endDate,
            'paginate' => false,
        ]);

        if (!$response->successful()) {
            Log::error('Failed to sync transactions', [
                'account_id' => $bankAccount->id,
                'error' => $response->body(),
            ]);
            throw new \Exception('Failed to sync transactions: ' . $response->body());
        }

        $data = $response->json();
        $transactions = $data['data'] ?? [];
        $syncedCount = 0;

        foreach ($transactions as $txn) {
            Transaction::updateOrCreate(
                [
                    'mono_transaction_id' => $txn['_id'],
                ],
                [
                    'bank_account_id' => $bankAccount->id,
                    'business_id' => $bankAccount->business_id,
                    'type' => $txn['type'], // debit or credit
                    'amount' => abs($txn['amount']),
                    'currency' => $txn['currency'] ?? 'NGN',
                    'description' => $txn['narration'] ?? '',
                    'counterparty' => $txn['meta']['sender'] ?? $txn['meta']['recipient'] ?? null,
                    'transaction_date' => $txn['date'],
                    'balance' => $txn['balance'] ?? null,
                ]
            );
            $syncedCount++;
        }

        $bankAccount->update([
            'last_synced_at' => now(),
        ]);

        return $syncedCount;
    }

    /**
     * Unlink account
     */
    public function unlinkAccount(string $accountId): bool
    {
        $response = Http::withHeaders([
            'mono-sec-key' => $this->secretKey,
        ])->post("{$this->baseUrl}/accounts/{$accountId}/unlink");

        return $response->successful();
    }

    /**
     * Get account statement (for PDF download)
     */
    public function getAccountStatement(string $accountId, string $startDate, string $endDate): ?string
    {
        $response = Http::withHeaders([
            'mono-sec-key' => $this->secretKey,
        ])->get("{$this->baseUrl}/accounts/{$accountId}/statement", [
            'start' => $startDate,
            'end' => $endDate,
            'output' => 'pdf',
        ]);

        if ($response->successful()) {
            return $response->json()['path'] ?? null;
        }

        return null;
    }
}
```

---

### 2. Transaction Categorization AI

**Categories**:
```php
[
    'REVENUE' => [
        'VAT_OUTPUT' => 'VATable Sales (7.5%)',
        'EXEMPT_SALES' => 'VAT-exempt Sales',
        'NON_OPERATING' => 'Non-operating Income',
    ],
    'EXPENSES' => [
        'VAT_INPUT' => 'VATable Expenses (7.5%)',
        'SALARY_PAYE' => 'Salaries & PAYE',
        'RENT' => 'Rent & Facilities',
        'PROFESSIONAL' => 'Professional Fees',
        'MARKETING' => 'Marketing & Advertising',
        'UTILITIES' => 'Utilities',
        'TRANSPORT' => 'Transport & Logistics',
        'IT_SOFTWARE' => 'IT & Software',
        'RAW_MATERIALS' => 'Raw Materials/Inventory',
        'OTHER_EXPENSES' => 'Other Business Expenses',
    ],
    'TAX' => [
        'VAT_PAYMENT' => 'VAT Payment to FIRS',
        'PAYE_PAYMENT' => 'PAYE Payment to FIRS',
        'WHT_PAYMENT' => 'WHT Payment to FIRS',
        'CIT_PAYMENT' => 'CIT Payment to FIRS',
    ],
    'PERSONAL' => 'Personal/Non-business',
    'UNCATEGORIZED' => 'Needs Review',
]
```

**Categorization Service**:
```php
<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
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
- Date: {$transaction->transaction_date}

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
                return [
                    'category' => 'REVENUE',
                    'sub_category' => 'VAT_OUTPUT',
                    'confidence' => 0.7,
                    'vat_applicable' => true,
                    'is_business_expense' => false,
                ];
            }
        }

        // Expense patterns
        if ($transaction->type === 'debit') {
            if (preg_match('/salary|wage|staff|payroll/', $description)) {
                return ['category' => 'EXPENSES', 'sub_category' => 'SALARY_PAYE', 'confidence' => 0.8];
            }
            if (preg_match('/rent|lease/', $description)) {
                return ['category' => 'EXPENSES', 'sub_category' => 'RENT', 'confidence' => 0.8];
            }
            if (preg_match('/fuel|transport|logistics/', $description)) {
                return ['category' => 'EXPENSES', 'sub_category' => 'TRANSPORT', 'confidence' => 0.7];
            }
            if (preg_match('/firs|tax authority|revenue service/', $description)) {
                return ['category' => 'TAX', 'sub_category' => 'VAT_PAYMENT', 'confidence' => 0.9];
            }
        }

        return [
            'category' => 'UNCATEGORIZED',
            'sub_category' => null,
            'confidence' => 0.5,
            'vat_applicable' => false,
            'is_business_expense' => true,
        ];
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
}
```

---

### 3. Compliance Calendar System

**Deadline Configuration**:
```php
<?php

namespace App\Services;

use App\Models\ComplianceDeadline;
use App\Models\Business;
use Carbon\Carbon;

class ComplianceCalendarService
{
    protected $deadlineTypes = [
        'VAT' => [
            'frequency' => 'monthly',
            'day' => 21,
            'description' => 'VAT Return and Payment',
            'forms' => ['FIRS VAT Form 002'],
            'applies_to' => ['all'], // all businesses
        ],
        'WHT' => [
            'frequency' => 'monthly',
            'day' => 21,
            'description' => 'Withholding Tax Remittance',
            'forms' => ['WHT Schedule'],
            'applies_to' => ['all'],
        ],
        'PAYE' => [
            'frequency' => 'monthly',
            'day' => 10,
            'description' => 'PAYE/Income Tax Payment',
            'forms' => ['PAYE Declaration'],
            'applies_to' => ['with_staff'], // only if has staff
        ],
        'CIT' => [
            'frequency' => 'annual',
            'months_after_year_end' => 6,
            'description' => 'Corporate Income Tax Return',
            'forms' => ['CIT Form', 'Financial Statements', 'Audit Report'],
            'applies_to' => ['all'],
        ],
        'CAC_ANNUAL' => [
            'frequency' => 'annual',
            'based_on' => 'incorporation_date',
            'description' => 'CAC Annual Return Filing',
            'forms' => ['Form AR', 'Notice of Situation'],
            'applies_to' => ['all'],
        ],
        'ITF' => [
            'frequency' => 'monthly',
            'day' => 30,
            'description' => 'Industrial Training Fund (1% of payroll)',
            'forms' => ['ITF Remittance Schedule'],
            'applies_to' => ['with_staff'],
        ],
        'PENCOM' => [
            'frequency' => 'monthly',
            'day' => 10,
            'description' => 'Pension Contribution (8% employer + 8% employee)',
            'forms' => ['PENCOM Schedule'],
            'applies_to' => ['with_staff'],
        ],
        'NSITF' => [
            'frequency' => 'monthly',
            'day' => 30,
            'description' => 'NSITF Contribution (1% of payroll)',
            'forms' => ['NSITF Schedule'],
            'applies_to' => ['with_staff'],
        ],
    ];

    /**
     * Generate deadlines for a business for next 12 months
     */
    public function generateDeadlines(Business $business): int
    {
        $count = 0;
        $startDate = now();
        $endDate = now()->addYear();

        foreach ($this->deadlineTypes as $type => $config) {
            if (!$this->appliesToBusiness($business, $config['applies_to'])) {
                continue;
            }

            if ($config['frequency'] === 'monthly') {
                $count += $this->generateMonthlyDeadlines($business, $type, $config, $startDate, $endDate);
            } elseif ($config['frequency'] === 'annual') {
                $count += $this->generateAnnualDeadline($business, $type, $config);
            }
        }

        return $count;
    }

    /**
     * Check if deadline applies to business
     */
    protected function appliesToBusiness(Business $business, array $appliesTo): bool
    {
        if (in_array('all', $appliesTo)) {
            return true;
        }

        if (in_array('with_staff', $appliesTo)) {
            return $business->staff()->count() > 0;
        }

        return false;
    }

    /**
     * Generate monthly deadlines
     */
    protected function generateMonthlyDeadlines(Business $business, string $type, array $config, Carbon $start, Carbon $end): int
    {
        $count = 0;
        $current = $start->copy();

        while ($current <= $end) {
            $dueDate = $current->copy()->day($config['day']);
            
            // If deadline is in the past for current month, skip to next month
            if ($dueDate < now()) {
                $current->addMonth();
                continue;
            }

            ComplianceDeadline::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'deadline_type' => $type,
                    'period' => $current->format('Y-m'),
                ],
                [
                    'description' => $config['description'],
                    'due_date' => $dueDate,
                    'frequency' => 'monthly',
                    'required_documents' => $config['forms'],
                    'status' => 'pending',
                ]
            );

            $count++;
            $current->addMonth();
        }

        return $count;
    }

    /**
     * Generate annual deadline
     */
    protected function generateAnnualDeadline(Business $business, string $type, array $config): int
    {
        if (isset($config['based_on']) && $config['based_on'] === 'incorporation_date') {
            $dueDate = Carbon::parse($business->incorporation_date ?? $business->created_at)
                ->addYear()
                ->startOfDay();
        } else {
            // CIT - 6 months after accounting year-end
            $yearEnd = $business->accounting_year_end 
                ?? Carbon::parse($business->created_at)->endOfYear();
            $dueDate = $yearEnd->copy()->addMonths(6);
        }

        // Only create if due date is within next 12 months
        if ($dueDate > now() && $dueDate <= now()->addYear()) {
            ComplianceDeadline::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'deadline_type' => $type,
                    'period' => $dueDate->format('Y'),
                ],
                [
                    'description' => $config['description'],
                    'due_date' => $dueDate,
                    'frequency' => 'annual',
                    'required_documents' => $config['forms'],
                    'status' => 'pending',
                ]
            );
            return 1;
        }

        return 0;
    }

    /**
     * Get upcoming deadlines
     */
    public function getUpcomingDeadlines(Business $business, int $days = 30): array
    {
        return ComplianceDeadline::where('business_id', $business->id)
            ->where('status', 'pending')
            ->where('due_date', '>=', now())
            ->where('due_date', '<=', now()->addDays($days))
            ->orderBy('due_date')
            ->get()
            ->toArray();
    }

    /**
     * Mark deadline as completed
     */
    public function markCompleted(ComplianceDeadline $deadline): void
    {
        $deadline->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }
}
```

---

### 4. VAT Calculation & Form 002

**VAT Calculation Service**:
```php
<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Transaction;
use App\Models\VATReturn;
use Carbon\Carbon;

class VATCalculationService
{
    /**
     * Calculate VAT for a period
     */
    public function calculateForPeriod(Business $business, string $period): array
    {
        // Period format: 2026-02 (YYYY-MM)
        $startDate = Carbon::createFromFormat('Y-m', $period)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Get VATable sales (Output VAT)
        $vatSales = Transaction::where('business_id', $business->id)
            ->where('category', 'REVENUE')
            ->where('sub_category', 'VAT_OUTPUT')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $outputVat = $vatSales * 0.075; // 7.5%

        // Get VATable expenses (Input VAT)
        $vatExpenses = Transaction::where('business_id', $business->id)
            ->where('category', 'EXPENSES')
            ->where('sub_category', 'VAT_INPUT')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $inputVat = $vatExpenses * 0.075; // 7.5%

        // Net VAT
        $netVat = $outputVat - $inputVat;

        return [
            'period' => $period,
            'vat_sales' => $vatSales,
            'output_vat' => $outputVat,
            'vat_expenses' => $vatExpenses,
            'input_vat' => $inputVat,
            'net_vat' => max(0, $netVat), // Can't be negative (no refunds in Nigeria)
            'due_date' => $endDate->copy()->addMonth()->day(21),
        ];
    }

    /**
     * Create or update VAT return
     */
    public function createReturn(Business $business, string $period): VATReturn
    {
        $calculation = $this->calculateForPeriod($business, $period);

        return VATReturn::updateOrCreate(
            [
                'business_id' => $business->id,
                'period' => $period,
            ],
            [
                'vat_sales' => $calculation['vat_sales'],
                'output_vat' => $calculation['output_vat'],
                'vat_expenses' => $calculation['vat_expenses'],
                'input_vat' => $calculation['input_vat'],
                'net_vat' => $calculation['net_vat'],
                'due_date' => $calculation['due_date'],
                'status' => 'draft',
            ]
        );
    }

    /**
     * Submit VAT return (mark as submitted)
     */
    public function submitReturn(VATReturn $return): void
    {
        $return->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
    }
}
```

---

## Environment Setup

### 1. Add to `.env`:
```env
# Mono Integration
MONO_SECRET_KEY=test_sk_xxxxxxxxxxxx
MONO_PUBLIC_KEY=test_pk_xxxxxxxxxxxx
MONO_REDIRECT_URL=http://localhost:8000/business/banks/callback
MONO_WEBHOOK_SECRET=your_webhook_secret

# Email Service (SendGrid or AWS SES)
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your_sendgrid_api_key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=alerts@taxmaster.ng
MAIL_FROM_NAME="TaxMaster.ng"

# Queue Configuration (for background jobs)
QUEUE_CONNECTION=database
```

### 2. Add to `config/services.php`:
```php
'mono' => [
    'secret_key' => env('MONO_SECRET_KEY'),
    'public_key' => env('MONO_PUBLIC_KEY'),
    'redirect_url' => env('MONO_REDIRECT_URL'),
    'webhook_secret' => env('MONO_WEBHOOK_SECRET'),
    'base_url' => env('MONO_BASE_URL', 'https://api.withmono.com'),
],
```

---

## Testing Checklist

### Sprint 1: Bank Integration
- [ ] User can connect bank account via Mono
- [ ] Transactions sync successfully
- [ ] Transaction list displays correctly
- [ ] Auto-sync runs every 6 hours
- [ ] User can manually trigger sync
- [ ] User can disconnect account

### Sprint 2: Categorization
- [ ] AI categorizes 80%+ transactions correctly
- [ ] User can override categories
- [ ] Categories persist after sync
- [ ] Confidence scores are reasonable
- [ ] VAT-applicable flag is accurate

### Sprint 3: Compliance Calendar
- [ ] All deadlines generate correctly
- [ ] Email alerts send 14/7/3 days before
- [ ] Calendar view shows upcoming deadlines
- [ ] Overdue items are highlighted
- [ ] User can mark deadline as completed

### Sprint 4: VAT Forms
- [ ] VAT calculation is accurate
- [ ] Form 002 generates with correct values
- [ ] PDF export works
- [ ] Payment instructions are clear
- [ ] User can download form

---

## Next Steps After Phase 1

Once Phase 1 is complete and tested:
1. Launch beta with 50 users
2. Gather feedback
3. Fix critical bugs
4. Add payment receipt upload feature
5. Prepare for Phase 2 (Tax computation schedules)

---

## Resources Needed

**Development Team**:
- 2 Backend developers (Laravel)
- 2 Frontend developers (Vue.js)
- 1 QA engineer
- 1 DevOps (part-time for deployment)

**External Services**:
- Mono API account (production)
- SendGrid or AWS SES (email)
- Cloud storage (S3 or DigitalOcean Spaces)
- Server (AWS/DigitalOcean - 4GB RAM minimum)

**Budget Estimate (10 weeks)**:
- Development: ₦2M - 3M
- Services/Infrastructure: ₦300K - 500K
- Testing/QA: ₦500K
- **Total: ₦2.8M - 4M**

---

## Success Metrics

**Week 10 Goals**:
- [ ] 50+ beta users registered
- [ ] 30+ bank accounts connected
- [ ] 10,000+ transactions synced
- [ ] 80%+ categorization accuracy
- [ ] 100+ VAT forms generated
- [ ] <5% error rate
- [ ] 90%+ user satisfaction

Ready to start building! 🚀
