# TaxMaster.ng - Product Roadmap & Phase Breakdown

**Vision**: The go-to Tax & Compliance Agent for Nigerian Businesses and Personal Finance  
**Domain**: taxmaster.ng (registered)  
**Model**: User-pays-FIRS-directly + Platform-verifies model  
**Timeline**: 12 months to MVP+ completion

---

## Executive Summary: Current vs. Vision

### Current App Capabilities ✅

| Feature | Status | Coverage |
|---------|--------|----------|
| ✅ Tax Calculation | Built | Business CIT only |
| ✅ Tax Return Management | Built | Draft/Submit locally |
| ✅ Payment Processing | Built | Paystack only, limited |
| ✅ AI Chat Assistant | Built | Deepseek/Gemini, basic |
| ✅ Dashboard | Built | Basic stats |
| ✅ Subscription Plans | Built | 4 tiers |
| ✅ Staff Management | Built | Basic PAYE tracking |
| ✅ User Auth | Built | Jetstream/Fortify |

### Vision Features ❌ (To Build)

| Feature | Status | Priority |
|---------|--------|----------|
| ❌ Compliance Calendar | Not built | P0 |
| ❌ Bank Integration | Not built | P0 |
| ❌ Mono Integration | Not built | P0 |
| ❌ Transaction Auto-Import | Not built | P0 |
| ❌ FIRS VAT Form 002 Generation | Not built | P0 |
| ❌ Email Alerts | Not built | P0 |
| ❌ Tax Computation Schedules | Not built | P1 |
| ❌ Financial Statements | Not built | P1 |
| ❌ CAC Annual Return Forms | Not built | P1 |
| ❌ Advanced AI Agent | Not built | P1 |
| ❌ Personal Finance Features | Not built | P2 |
| ❌ Multi-channel Alerts | Not built | P2 |
| ❌ B2B2C Accounting Features | Not built | P3 |

---

## Phase 1: Foundation & Core MVP (Weeks 1-10)

**Goal**: Launch MVP with auto-import, compliance calendar, and FIRS form generation  
**Timeline**: 10 weeks (2.5 months)  
**Effort**: High  
**Target Users**: Freelancers, Small SMEs (₦10-50M revenue)

### 1.1 Bank Integration - Mono API
**Effort**: 3 weeks  
**Deliverables**:
- [ ] Mono API integration service
- [ ] Authorization flow (connect bank accounts)
- [ ] Transaction sync scheduler
- [ ] Transaction model with categorization fields
- [ ] Webhook handlers for real-time sync
- [ ] Error handling & retry logic
- [ ] Secure credential storage

**Files to Create**:
```
app/Services/MonoIntegrationService.php
app/Http/Controllers/Business/BankAccountController.php
app/Models/BankAccount.php
app/Models/Transaction.php
database/migrations/create_bank_accounts_table.php
database/migrations/create_transactions_table.php
resources/js/Pages/Business/BankAccounts/Connect.vue
resources/js/Pages/Business/BankAccounts/Index.vue
resources/js/Pages/Business/Transactions/Index.vue
```

**Key Features**:
- Connect unlimited bank accounts
- Auto-sync every 6 hours
- Transaction history (6 months)
- Manual sync trigger
- Disconnect/reconnect accounts

**Code Skeleton**:
```php
class MonoIntegrationService {
    public function authorizeAccount($accountId): string
    // Returns authorization URL for user
    
    public function handleCallback($authCode, $userId)
    // Exchange auth code for access token
    
    public function syncTransactions($bankAccount)
    // Fetch latest transactions from Mono
    
    public function categorizeTransaction($transaction)
    // AI categorization (VAT, PAYE, CIT, Personal, etc)
}
```

---

### 1.2 AI Transaction Categorization
**Effort**: 2 weeks  
**Deliverables**:
- [ ] Enhanced AI Agent Service
- [ ] Transaction categorization prompts
- [ ] Category mapping (VAT sales, VAT expenses, PAYE, CIT, Personal)
- [ ] Category rules engine
- [ ] Manual override capability
- [ ] Batch categorization

**Files to Create**:
```
app/Services/TransactionCategorizationService.php
app/Models/TransactionCategory.php
app/Http/Controllers/Business/TransactionCategoryController.php
database/migrations/add_category_to_transactions_table.php
resources/js/Pages/Business/Transactions/Categorize.vue
```

**Categorization Logic**:
```
inputs:
{
  amount: 1500000,
  description: "Sales - Consulting services to ABC Ltd",
  merchant: "Transfer from ABC Ltd",
  date: "2026-02-15"
}

AI Output:
{
  primary_category: "VAT_OUTPUT", // VATable sales
  confidence: 0.95,
  business_purpose: "Professional services",
  vat_applicable: true,
  cit_applicable: true,
  description_normalized: "Consulting services revenue"
}
```

---

### 1.3 Compliance Calendar & Alerts System
**Effort**: 2 weeks  
**Deliverables**:
- [ ] ComplianceDeadline model
- [ ] Compliance Calendar service
- [ ] Email notification system
- [ ] Alert scheduler
- [ ] Countdown dashboard
- [ ] Notification templates

**Files to Create**:
```
app/Models/ComplianceDeadline.php
app/Services/ComplianceAlertService.php
app/Jobs/SendComplianceReminder.php
database/migrations/create_compliance_deadlines_table.php
resources/js/Pages/Business/Compliance/Calendar.vue
resources/js/Components/Business/ComplianceBanner.vue
resources/mails/ComplianceReminder.php
```

**Deadlines Configuration**:
```php
[
    'VAT' => [
        'frequency' => 'monthly',
        'deadline' => '21st of following month',
        'description' => 'VAT Return and Payment',
        'documents' => ['FIRS VAT Form 002']
    ],
    'WHT' => [
        'frequency' => 'monthly',
        'deadline' => '21st of following month',
        'description' => 'Withholding Tax Remittance',
        'documents' => ['WHT Schedule']
    ],
    'PAYE' => [
        'frequency' => 'monthly',
        'deadline' => '10th of following month',
        'description' => 'PAYE/Income Tax Payment',
        'documents' => ['PAYE Declaration']
    ],
    'CIT' => [
        'frequency' => 'annual',
        'deadline' => '6 months after accounting year-end',
        'description' => 'Corporate Income Tax Return',
        'documents' => ['CIT Form', 'Financial Statements']
    ],
    'CAC_ANNUAL' => [
        'frequency' => 'annual',
        'deadline' => 'Anniversary of incorporation',
        'description' => 'CAC Annual Return Filing',
        'documents' => ['Form AR, Notice of Situation']
    ],
    'ITF_PENCOM_NSITF' => [
        'frequency' => 'monthly',
        'deadline' => 'Monthly (employer contribution)',
        'description' => 'Fund remittances',
        'documents' => ['Remittance Schedule']
    ]
]
```

**Alert Rules**:
```
- 14 days before: Initial alert
- 7 days before: Urgent reminder
- 3 days before: Critical alert
- On deadline: Final notice
- After deadline: Overdue notice (daily until filed)
```

---

### 1.4 FIRS VAT Form 002 Generation
**Effort**: 3 weeks  
**Deliverables**:
- [ ] VAT calculation service
- [ ] Form 002 XML generator
- [ ] PDF form generation
- [ ] Pre-filled form template
- [ ] Form 002 model & storage
- [ ] Form validation

**Files to Create**:
```
app/Services/VATCalculationService.php
app/Services/FirsForm002GeneratorService.php
app/Models/VATReturn.php
app/Http/Controllers/Business/VATReturnController.php
database/migrations/create_vat_returns_table.php
resources/js/Pages/Business/Tax/VAT/Index.vue
resources/js/Pages/Business/Tax/VAT/Form002.vue
resources/views/exports/firs-form-002.blade.php
```

**VAT Calculation Flow**:
```
Input: Categorized transactions for April 2026

Processing:
1. Sum all VAT_OUTPUT (VATable sales): ₦4,200,000
2. Calculate Output VAT: ₦4,200,000 × 7.5% = ₦315,000
3. Sum all VAT_INPUT (VATable expenses): ₦1,800,000
4. Calculate Input VAT: ₦1,800,000 × 7.5% = ₦135,000
5. Net VAT: ₦315,000 - ₦135,000 = ₦180,000

Output:
- FIRS Form 002 (pre-filled with values)
- Payment reference (generated by FIRS/Remita)
- Step-by-step payment instructions
- Deadline: May 21, 2026
```

**Form Structure**:
```xml
<Form002>
    <Period>April 2026</Period>
    <TIN>ABC123456</TIN>
    <VATableSales>4200000</VATableSales>
    <OutputVAT>315000</OutputVAT>
    <VATableExpenses>1800000</VATableExpenses>
    <InputVAT>135000</InputVAT>
    <NetVAT>180000</NetVAT>
    <PaymentDeadline>2026-05-21</PaymentDeadline>
</Form002>
```

---

### 1.5 AI Agent Enhancement (Conversational Tax Assistant)
**Effort**: 2 weeks  
**Deliverables**:
- [ ] Enhanced chat interface
- [ ] Context awareness (user's synced data)
- [ ] Tax calculation integration
- [ ] Document generation from chat
- [ ] Query patterns & templates

**Chat Capabilities**:
```
User: "How much VAT do I owe for March?"

Agent Response:
Based on your synced bank transactions from March 2026:

📊 VAT Calculation:
• VATable Sales: ₦4,200,000
• Output VAT @ 7.5%: ₦315,000

• VATable Expenses: ₦1,800,000
• Input VAT @ 7.5%: ₦135,000

💰 Net VAT Payable: ₦180,000
📅 Deadline: April 21st, 2026

Would you like me to:
1. Generate FIRS Form 002
2. Create payment instructions
3. Schedule a reminder
4. Set up auto-payment via Remita
```

---

### 1.6 Database Migrations for Phase 1

```php
// Bank Accounts
Schema::create('bank_accounts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('business_id')->constrained();
    $table->string('bank_name');
    $table->string('account_name');
    $table->string('account_number')->unique();
    $table->string('mono_account_id')->unique();
    $table->string('mono_access_token')->encrypted();
    $table->string('currency', 3)->default('NGN');
    $table->decimal('balance', 20, 2);
    $table->timestamp('last_synced_at')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

// Transactions
Schema::create('transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('bank_account_id')->constrained();
    $table->foreignId('business_id')->constrained();
    $table->string('mono_transaction_id')->unique();
    $table->enum('type', ['debit', 'credit']);
    $table->decimal('amount', 20, 2);
    $table->string('currency', 3)->default('NGN');
    $table->text('description');
    $table->string('counterparty')->nullable();
    $table->timestamp('transaction_date');
    $table->string('category')->nullable();
    $table->decimal('confidence', 3, 2)->nullable();
    $table->boolean('user_verified')->default(false);
    $table->timestamps();
    $table->index(['business_id', 'category', 'transaction_date']);
});

// Compliance Deadlines
Schema::create('compliance_deadlines', function (Blueprint $table) {
    $table->id();
    $table->foreignId('business_id')->constrained();
    $table->string('deadline_type'); // VAT, PAYE, CIT, etc
    $table->string('description');
    $table->date('due_date');
    $table->enum('frequency', ['monthly', 'quarterly', 'annual']);
    $table->json('required_documents');
    $table->enum('status', ['pending', 'completed', 'overdue'])->default('pending');
    $table->timestamp('completed_at')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
    $table->index(['business_id', 'due_date', 'status']);
});

// VAT Returns
Schema::create('vat_returns', function (Blueprint $table) {
    $table->id();
    $table->foreignId('business_id')->constrained();
    $table->string('period'); // 2026-04
    $table->decimal('vat_sales', 20, 2);
    $table->decimal('output_vat', 20, 2);
    $table->decimal('vat_expenses', 20, 2);
    $table->decimal('input_vat', 20, 2);
    $table->decimal('net_vat', 20, 2);
    $table->date('due_date');
    $table->enum('status', ['draft', 'submitted', 'paid', 'overdue'])->default('draft');
    $table->string('form_002_reference')->nullable();
    $table->timestamp('submitted_at')->nullable();
    $table->timestamp('paid_at')->nullable();
    $table->json('form_data')->nullable();
    $table->timestamps();
});
```

---

### 1.7 Phase 1 MVP Checklist

**Backend**:
- [ ] Mono API integration complete
- [ ] Transaction import working
- [ ] AI categorization functional
- [ ] Compliance Calendar service built
- [ ] VAT calculation & Form 002 generation working
- [ ] Email alerts configured
- [ ] All migrations run
- [ ] API endpoints tested

**Frontend**:
- [ ] Bank account connection UI
- [ ] Transaction list with categories
- [ ] Compliance calendar view
- [ ] VAT return dashboard
- [ ] Form 002 preview & download
- [ ] Enhanced AI chat interface
- [ ] Mobile responsive design
- [ ] Loading states & error handling

**Integrations**:
- [ ] Mono API (sandbox → production)
- [ ] Email service (Mailtrap → SendGrid/AWS SES)
- [ ] Deepseek API for AI
- [ ] PDF generation (TCPDF/Snappy)

**Testing**:
- [ ] Unit tests for services
- [ ] Integration tests for flows
- [ ] E2E tests for critical paths
- [ ] Load testing (100+ concurrent users)

---

## Phase 2: Tax Computation & Compliance (Weeks 11-18)

**Goal**: Add tax computation schedules, financial statements, CAC forms  
**Timeline**: 8 weeks (2 months)  
**Effort**: Medium  
**Users**: Small-Mid SMEs (₦50M-500M revenue)

### 2.1 Tax Computation Schedule Generation
**Tax computation in FIRS format**:
```
Starting with:
- Gross Profit from P&L
- Add back: Depreciation, Amortization
- Less: Allowable expenses
- Less: Capital allowances
= Taxable Income
× 30% Corporate Tax Rate
= CIT Payable
```

### 2.2 Financial Statement Generation
- Simplified P&L (from categorized transactions)
- Balance Sheet (basic assets/liabilities/equity)
- Cash flow statement
- Notes to accounts

### 2.3 CAC Annual Return Forms
- Automated form generation
- Pre-filled fields
- PDF export
- Filing instructions

### 2.4 Additional Services
- [ ] PAYE calculation & scheduling
- [ ] WHT calculation
- [ ] Deduction management
- [ ] Capital allowance tracking

---

## Phase 3: Advanced Compliance & Reporting (Weeks 19-26)

**Goal**: Multi-tax support, audit trail, accounting firm features  
**Timeline**: 8 weeks  
**Effort**: Medium-High  
**Users**: Mid-size businesses, Accounting firms

### 3.1 Multi-Tax Support
- [ ] PAYE/Income Tax
- [ ] Withholding Tax
- [ ] Education Tax
- [ ] Corporate Social Responsibility levy
- [ ] Capital Gains Tax

### 3.2 Audit Trail & FIRS Query Management
- [ ] Complete transaction audit trail
- [ ] Document versioning
- [ ] Query tracking
- [ ] Response generator

### 3.3 B2B2C Features (Accounting Firms)
- [ ] Multi-client management
- [ ] Client dashboards
- [ ] Bulk operations
- [ ] Client billing
- [ ] Commission management

---

## Phase 4: Personal Finance & Advanced (Weeks 27-36)

**Goal**: Personal tax, mobile app, advanced AI  
**Timeline**: 10 weeks  
**Effort**: High  
**Users**: Freelancers, Individual taxpayers

### 4.1 Personal Finance Features
- [ ] Personal income tracking
- [ ] Personal expense categorization
- [ ] Annual tax clearance calculation
- [ ] ITR (Individual Tax Return) generation
- [ ] Self-assessment tax computation

### 4.2 Mobile App
- [ ] React Native app
- [ ] Receipt scanning (OCR)
- [ ] Offline transaction logging
- [ ] Push notifications
- [ ] Quick compliance checks

### 4.3 Advanced AI Features
- [ ] Natural language query understanding
- [ ] Proactive tax recommendations
- [ ] Predictive compliance alerts
- [ ] Document scanning & categorization

### 4.4 Multi-channel Alerts
- [ ] SMS alerts
- [ ] WhatsApp notifications
- [ ] In-app notifications
- [ ] Slack integration

---

## Implementation Schedule

```
Timeline:            Weeks  Phase         Users
├─ Phase 1 MVP       1-10   Foundation    Freelancers
├─ Phase 2           11-18  Computation   Small SMEs
├─ Phase 3           19-26  Advanced      Mid-size/Firms
└─ Phase 4           27-36  Personal      All Segments

                      36 weeks = ~9 months to complete
```

**Parallel Activities**:
- Marketing & user acquisition (Week 6 onwards)
- Beta testing (Week 5 onwards)
- Documentation (ongoing)
- Team hiring (ongoing)

---

## Detailed Feature Comparison

### Current App ↔ Vision

```
CURRENT                          VISION

Auth
✅ Email/Password         ➜      ✅ Email/Password
❌ SSO                    ➜      ⏳ Google/Bank SSO (P3)
❌ 2FA                    ➜      ⏳ 2FA (P2)

Tax Management
✅ CIT Calculation        ➜      ✅ CIT, VAT, PAYE, WHT, etc
✅ Manual Tax Return      ➜      ✅ Auto-import + Auto-form
❌ Bank Sync              ➜      ✅ Mono integration
❌ Auto Categorization    ➜      ✅ AI categorization
❌ FIRS Forms             ➜      ✅ Form 002, 001, etc

Compliance
❌ Calendar               ➜      ✅ Compliance Calendar
❌ Alerts                 ➜      ✅ Email alerts → Multi-channel
❌ Due Date Tracking      ➜      ✅ Automatic deadline tracking
❌ Overdue Management     ➜      ✅ Escalation workflow

Reporting
❌ P&L Statement          ➜      ✅ Automated P&L
❌ Balance Sheet          ➜      ✅ Automated Balance Sheet
❌ Cash Flow              ➜      ✅ Automated Cash Flow
❌ Tax Schedules          ➜      ✅ FIRS-format schedules
❌ Audit Trail            ➜      ✅ Complete audit trail

AI Agent
⚠️ Basic Chat            ➜      ✅ Advanced conversational
❌ Data Integration       ➜      ✅ Context-aware (uses user data)
❌ Form Generation        ➜      ✅ Can generate forms from chat
❌ Payment Helper         ➜      ✅ Can guide through payments

Payment
✅ Paystack               ➜      ✅ Paystack (subscriptions)
❌ Government Payment     ➜      ✅ Remita/CSF verification
❌ Receipt Tracking       ➜      ✅ Upload & verify
❌ Payment History        ➜      ✅ Complete history

Integrations
⚠️ Limited               ➜      ✅ Mono (bank)
❌ Accounting Software    ➜      ⏳ QuickBooks, Wave (P3)
❌ Payment Gateways       ➜      ⏳ More payment methods (P3)

Analytics
❌ Tax Insights           ➜      ✅ Tax optimization suggestions
❌ Deduction Tracking     ➜      ✅ Auto deduction tracking
❌ Compliance Score       ➜      ✅ Compliance readiness score

B2B Features
❌ Multi-client          ➜      ✅ Accounting firm dashboard (P3)
❌ Bulk Operations       ➜      ✅ Batch processing (P3)
❌ Client Billing        ➜      ✅ Commission/fee management (P3)
```

---

## Resource Requirements

### Team Size by Phase

**Phase 1**: 4-5 developers
- 2 Backend (Laravel)
- 2 Frontend (Vue.js)
- 1 DevOps/QA

**Phase 2**: 5-6 developers
- Same as Phase 1
- +1 Data analyst (for reporting)

**Phase 3**: 6-8 developers
- Split by domain (tax, compliance, B2B)
- +1 Product Manager

**Phase 4**: 8-10 developers
- Mobile team added
- +1 QA lead

### Infrastructure

**Current Tech Stack** ✅
- PHP/Laravel 11
- Vue.js 3
- PostgreSQL
- Redis
- AWS/Similar hosting

**New Additions Needed**:
- Mono API (bank integration)
- SendGrid/AWS SES (email)
- PDF generation (Snappy/TCPDF)
- WebSocket server (Laravel Reverb, optional)
- S3/Cloud storage (documents)

---

## Revenue Model

### Pricing by Segment

```
FREELANCERS/CONSULTANTS
├─ Revenue: ₦0-10M/year
├─ Users: 1 person
├─ Price: ₦10,000/month
└─ Features: Personal tax, basic compliance

SMALL SMEs (₦10-50M revenue)
├─ Users: 2-10 staff
├─ Price: ₦30,000/month
└─ Features: Full business compliance, PAYE tracking

MID-SIZE (₦50M-500M revenue)
├─ Users: 10-50 staff
├─ Price: ₦80,000-150,000/month
└─ Features: All + advanced reporting, audit trail

ACCOUNTING FIRMS (B2B2C)
├─ Base: ₦200,000/month
├─ Per-client: ₦15,000-25,000/month
└─ Features: Multi-client, bulk ops, white-label

ENTERPRISE
├─ Price: Custom (₦500K+/month)
└─ Features: API access, custom integrations, SLA
```

### Projected Revenue

**Year 1 (MVP)**:
- 500 users × ₦25,000 (blended) = ₦12.5M MRR = ₦150M ARR

**Year 2**:
- 5,000 users × ₦25,000 = ₦125M MRR = ₦1.5B ARR

**Year 3**:
- 20,000 users × ₦25,000 = ₦500M MRR = ₦6B ARR

---

## Success Metrics & KPIs

### Phase 1 (MVP Launch)
- [ ] 500+ registered users
- [ ] 200+ active monthly users
- [ ] <5% churn rate
- [ ] 90%+ form generation success rate
- [ ] <2 hour average support response

### Phase 2 (Expansion)
- [ ] 5,000+ users
- [ ] 40%+ month-over-month growth
- [ ] 85%+ NPS
- [ ] <1% complaint rate on tax forms

### Phase 3 (Market Leader)
- [ ] 20,000+ users
- [ ] Accounting firm partnerships (#10+)
- [ ] 80%+ NPS
- [ ] Tax compliance rate >95%

---

## Risk Mitigation

| Risk | Impact | Mitigation |
|------|--------|-----------|
| Mono API downtime | High | Fallback manual sync, cached data |
| FIRS changes | Medium | Regular API monitoring, partner with FIRS |
| Market adoption | High | Early beta, free tier, partnerships |
| Regulatory changes | Medium | Legal counsel, compliance audit |
| AI categorization errors | Medium | User override, manual verification |

---

## Conclusion

TaxMaster.ng can become the go-to compliance platform for Nigerian businesses through a phased, user-centered approach:

**Phase 1** establishes the core value (auto-import + forms)  
**Phase 2** adds depth (all tax types + reporting)  
**Phase 3** enables scale (B2B2C + advanced)  
**Phase 4** covers all segments (personal + mobile)  

By end of Phase 1 (10 weeks), you'll have an MVP that's immediately valuable to freelancers and startups. By Month 9, you'll be competitive with international platforms.

**Go-to-market strategy**: Launch Phase 1 MVP publicly, acquire 500 users early, iterate based on feedback, then scale systematically.
