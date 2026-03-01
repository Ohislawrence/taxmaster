# Phase 2 - PAYE & Tax Remittance System

**Start Date:** February 25, 2026  
**Status:** 📝 Planning  
**Estimated Duration:** 2-3 weeks

---

## Overview

Phase 2 builds on Phase 1's foundation by adding comprehensive PAYE (Pay As You Earn) management, Withholding Tax (WHT) calculations, and government payment remittance features. This phase completes the core tax types needed for Nigerian businesses.

---

## 1. PAYE Management System

### 1.1 Database Schema

**New Tables:**
```sql
-- Already exists, needs enhancement
paye_returns
├── business_id
├── period (YYYY-MM)
├── total_gross_pay
├── total_tax_deducted
├── staff_count
├── schedule_data (JSON) -- Staff breakdown
├── filed_date
├── status (draft/filed/paid)
├── firs_reference
└── timestamps

-- New table
paye_schedules (Individual staff records per period)
├── id
├── paye_return_id
├── business_staff_id
├── gross_pay
├── allowances (JSON)
├── tax_reliefs (JSON)
├── taxable_income
├── paye_due
├── cumulative_gross
├── cumulative_tax
└── timestamps
```

### 1.2 PAYE Calculator

**Nigerian Tax Brackets (2026):**
```php
₦0 - ₦300,000: 7%
₦300,001 - ₦600,000: 11%
₦600,001 - ₦1,100,000: 15%
₦1,100,001 - ₦1,600,000: 19%
₦1,600,001 - ₦3,200,000: 21%
Above ₦3,200,000: 24%
```

**Automatic Reliefs:**
- **CRA (Consolidated Relief Allowance):** Greater of (1% of gross income OR ₦200,000 + 20% of gross income)
- **Pension:** 8% of basic salary (max ₦350,000/year)
- **NHF:** 2.5% of basic salary
- **NHIS:** 5% of basic salary
- **Life Assurance:** Actual amount paid

**Features:**
- Progressive tax calculation
- Monthly cumulative tracking
- Annual tax projection
- Relief optimization suggestions

### 1.3 Backend Services

**PAYECalculationService:**
```php
- calculateMonthlyPAYE($staff, $grossPay, $allowances)
- calculateAnnualProjection($staff, $monthlyGross)
- applyReliefs($taxableIncome, $reliefs)
- generateSchedule($business, $period)
- computeCumulativeTax($staff, $currentMonth)
```

**PAYEReturnService:**
```php
- createReturn($business, $period, $staffPayments)
- generateSchedulePDF($return) // FIRS format
- submitReturn($return)
- trackPaymentStatus($return)
```

### 1.4 Controllers

**Business/PAYEController:**
```php
- index() // List all PAYE returns
- create() // Payroll input form
- store() // Calculate and save return
- show($id) // View return details
- downloadSchedule($id) // PDF download
- uploadProof($id) // Payment proof
```

**Admin/PAYEController:**
```php
- index() // All businesses PAYE returns
- show($id) // Return details
- statistics() // PAYE analytics
- overdueReport() // Overdue filings
```

### 1.5 Frontend Pages

**Business Portal:**
1. **PAYE Dashboard** (`/business/paye`)
   - Summary cards (Total PAYE, Filed, Pending)
   - Quick calculator
   - Recent returns list
   - Filing deadline reminder

2. **Create PAYE Return** (`/business/paye/create`)
   - Select period
   - Staff list with salaries
   - Bulk import CSV
   - Allowances input
   - Automatic tax calculation
   - Schedule preview
   - Submit button

3. **PAYE Return Details** (`/business/paye/{id}`)
   - Full schedule breakdown
   - Staff-by-staff listing
   - Total tax due
   - Payment status
   - Download schedule PDF
   - Upload payment proof

4. **PAYE Calculator** (Widget on dashboard)
   - Input gross pay
   - Select reliefs
   - See breakdown
   - Annual projection

**Admin Portal:**
1. **PAYE Returns Management** (`/admin/paye`)
   - All returns across businesses
   - Filter by status, business, period
   - Statistics dashboard
   - Overdue tracking

2. **PAYE Reports** (`/admin/paye/reports`)
   - Monthly collection trends
   - Top contributing businesses
   - Filing compliance rate
   - Regional breakdown

---

## 2. Withholding Tax (WHT) System

### 2.1 Database Schema

**New Tables:**
```sql
wht_transactions
├── business_id
├── transaction_date
├── transaction_type (dividends, interest, rent, contracts, professional_fees)
├── vendor_name
├── vendor_tin
├── gross_amount
├── wht_rate
├── wht_amount
├── net_amount
├── description
├── payment_reference
└── timestamps

wht_returns
├── business_id
├── period (YYYY-MM)
├── total_wht_deducted
├── schedule_data (JSON)
├── filed_date
├── status
├── firs_reference
└── timestamps
```

### 2.2 WHT Calculator

**Nigerian WHT Rates:**
```php
Dividends: 10%
Interest: 10%
Rent: 10%
Royalties: 10%
Commissions: 10%
Consultancy/Professional Fees: 5%
Contracts (Construction): 5%
Management Fees: 10%
Directors Fees: 10%
```

**Features:**
- Transaction-type based rate selection
- Gross-to-net calculation
- Net-to-gross reverse calculation
- Bulk WHT computation
- Vendor TIN validation

### 2.3 Backend Services

**WHTCalculationService:**
```php
- calculateWHT($amount, $transactionType)
- getWHTRate($transactionType)
- reverseCalculate($netAmount, $rate) // Find gross
- bulkCalculate($transactions[])
```

**WHTReturnService:**
```php
- createReturn($business, $period, $whtTransactions)
- generateSchedulePDF($return)
- submitReturn($return)
```

### 2.4 Controllers

**Business/WHTController:**
```php
- index() // List WHT transactions
- create() // Record WHT transaction
- store() // Save transaction
- returns() // List WHT returns
- createReturn() // Generate return
- downloadSchedule($id)
```

**Admin/WHTController:**
```php
- index() // All WHT returns
- transactions() // All WHT transactions
- statistics() // WHT analytics
```

### 2.5 Frontend Pages

**Business Portal:**
1. **WHT Transactions** (`/business/wht`)
   - List all WHT deductions
   - Add new transaction
   - Filter by type, vendor
   - Export to CSV

2. **WHT Returns** (`/business/wht/returns`)
   - List filed returns
   - Create new return (auto-aggregates transactions)
   - Download schedule
   - Payment tracking

**Admin Portal:**
1. **WHT Management** (`/admin/wht`)
   - All WHT transactions
   - Returns tracking
   - Statistics dashboard

---

## 3. Government Payment Remittance

### 3.1 Remita Integration

**Setup:**
```bash
composer require remita/remita-php
```

**Features:**
- Generate RRR (Remita Retrieval Reference)
- Payment instructions
- Status checking
- Receipt download

### 3.2 Database Schema

**New Tables:**
```sql
government_payments
├── business_id
├── tax_type (VAT, PAYE, WHT, CIT)
├── return_id (polymorphic)
├── period
├── amount
├── payment_method (remita, bank_transfer, cash)
├── remita_rrr
├── payment_date
├── receipt_path
├── status (pending, completed, failed)
└── timestamps
```

### 3.3 Backend Services

**RemitaService:**
```php
- generateRRR($business, $taxType, $amount, $returnId)
- checkPaymentStatus($rrr)
- downloadReceipt($rrr)
- sendPaymentInstructions($business, $rrr)
```

### 3.4 Controllers

**Business/PaymentController:**
```php
- initiatePayment($returnId, $taxType) // Generate RRR
- paymentInstructions($paymentId) // Show instructions
- confirmPayment($paymentId) // Check status
- downloadReceipt($paymentId)
```

### 3.5 Frontend Pages

**Business Portal:**
1. **Payment Center** (`/business/payments`)
   - Pending payments list
   - Initiate payment button
   - Payment history
   - Filter by tax type

2. **Payment Instructions** (`/business/payments/{id}/instructions`)
   - Remita RRR display
   - Bank details
   - USSD code
   - Payment deadline
   - Check status button

3. **Payment Receipt** (`/business/payments/{id}/receipt`)
   - Receipt details
   - Download PDF
   - Print option

---

## 4. Financial Reports

### 4.1 Profit & Loss Statement

**Features:**
- Auto-generate from transactions
- Filter by date range
- Compare periods
- Export to PDF/Excel

**Calculations:**
```
Revenue (Sales/Income transactions)
- Cost of Goods Sold (Purchases/COGS)
= Gross Profit
- Operating Expenses (Rent, Salaries, Utilities, etc.)
= Operating Profit
- Interest & Other Expenses
+ Other Income
= Net Profit Before Tax
- Tax Expense (CIT provision)
= Net Profit After Tax
```

### 4.2 Tax Summary Report

**Features:**
- All tax types summary
- Monthly breakdown
- Tax paid vs outstanding
- Compliance status
- Upcoming obligations

### 4.3 Controllers

**Business/ReportController:**
```php
- profitLoss($startDate, $endDate)
- taxSummary($year)
- cashFlow($startDate, $endDate)
- exportPdf($reportType, $params)
```

### 4.4 Frontend Pages

**Business Portal:**
1. **Reports Dashboard** (`/business/reports`)
   - Report type selector
   - Date range picker
   - Generate buttons
   - Recent reports list

2. **Profit & Loss** (`/business/reports/profit-loss`)
   - Interactive report
   - Chart visualization
   - Drill-down capability
   - Export options

3. **Tax Summary** (`/business/reports/tax-summary`)
   - All tax types overview
   - Compliance checklist
   - Payment calendar
   - Outstanding balances

---

## 5. Staff Management Enhancement

### 5.1 Staff Profile

**Already have `business_staff` table, needs:**
- Tax reliefs configuration
- Allowances setup
- Contract type
- Pension registration
- NHF number

### 5.2 Payroll Processing

**Features:**
- Monthly payroll run
- Salary adjustments
- Allowances management
- Deductions tracking
- Payslip generation

### 5.3 Frontend Pages

**Business Portal:**
1. **Staff List** (`/business/staff`)
   - Enhanced with tax info
   - Quick PAYE calculation
   - Payroll history

2. **Staff Profile** (`/business/staff/{id}`)
   - Personal details
   - Tax information
   - Payroll history
   - PAYE summary
   - Generate payslip

---

## 📊 Implementation Timeline

### Week 1: PAYE System
- Day 1-2: Database migrations & models
- Day 3-4: PAYE calculator service
- Day 5-7: Controllers & API endpoints

### Week 2: WHT & Frontend
- Day 1-2: WHT system backend
- Day 3-5: PAYE frontend pages
- Day 6-7: WHT frontend pages

### Week 3: Remittance & Reports
- Day 1-3: Remita integration
- Day 4-5: Financial reports
- Day 6-7: Testing & refinement

---

## 🎯 Success Criteria

### Functionality
- ✅ PAYE calculator produces accurate results
- ✅ WHT rates apply correctly by transaction type
- ✅ Remita RRR generation works
- ✅ Reports show correct figures
- ✅ Staff management integrates with PAYE

### User Experience
- ✅ Intuitive payroll input
- ✅ Clear tax breakdowns
- ✅ Easy payment process
- ✅ Accessible reports

### Compliance
- ✅ FIRS-format schedule generation
- ✅ Correct tax calculations per Nigerian law
- ✅ Deadline tracking
- ✅ Audit trail maintained

---

## 📦 Deliverables

### Backend
- 6 new migrations
- 4 new models
- 3 calculation services
- 4 new controllers (8 methods each)

### Frontend
- 10 new Vue pages
- 5 new components
- Reports module
- Payment center

### Documentation
- PAYE calculation guide
- WHT rates reference
- Remita integration docs
- User guides

---

## 🚀 Future Phases (Phase 3+)

After Phase 2, consider:
- **Phase 3:** CIT returns, advance tax
- **Phase 4:** Multi-currency support
- **Phase 5:** AI tax optimization
- **Phase 6:** Audit management
- **Phase 7:** E-filing integration with FIRS

---

**Status:** Ready to begin Phase 2 upon confirmation  
**Prerequisites:** Phase 1 complete ✅  
**Next Step:** Confirm scope and start implementation
