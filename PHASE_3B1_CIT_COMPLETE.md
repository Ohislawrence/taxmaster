# Phase 3B.1 - CIT (Corporate Income Tax) Implementation Complete ✅

## Overview
Successfully implemented corporate income tax (CIT) return management system for TaxMaster. This phase covers the filing and tracking of CIT returns using Nigeria's 30% standard tax rate with minimum tax provisions.

## Completed Components

### 1. Database Migration ✅
**File:** `database/migrations/2026_02_27_000001_create_cit_returns_table.php`

**Schema:**
- 14 core fields: period, gross_profit, taxable_income, tax_due, minimum_tax_amount
- Status tracking: draft, submitted, accepted, paid, rejected, overdue
- Financial fields: revenue, COGS, depreciation, add-backs, deductions
- Tax calculation fields: cit_payable, advance_tax, withholding_tax, balance_due
- Audit fields: reviewed_by (foreign key to users), firs_reference, form_a_reference
- Date fields: due_date, submitted_at, filed_at, paid_at, reviewed_at
- Relationships: business_id (FK), reviewed_by FK to users

**Indexes:** business_id+period, status+due_date, created_at

---

### 2. Model ✅
**File:** `app/Models/CitReturn.php`

**Key Features:**
- **Relationships:**
  - `business()` - BelongsTo Business
  - `reviewer()` - BelongsTo User (via reviewed_by field)
  - `governmentPayments()` - HasMany GovernmentPayment

- **Calculation Methods:**
  - `calculateGrossProfit()` - Revenue - COGS
  - `calculateTaxableIncome()` - Gross Profit + Add-backs - Deductions
  - `calculateCITPayable()` - Taxable Income × 30%
  - `calculateMinimumTax()` - 0.5% of turnover (or gross assets, or 0.25% of paid-up capital)
  - `calculateTaxDue()` - Max(CIT, Minimum Tax)
  - `calculateBalance()` - Tax due - Credits = Balance due/refund
  - `performCalculations()` - Runs all calculations sequentially

- **Status Methods:**
  - `markAsSubmitted()` - Set status to submitted
  - `markAsAccepted()` - Set status to accepted with FIRS reference
  - `markAsPaid()` - Set status to paid with timestamp
  - `isOverdue()` - Check if due date has passed

- **Field Casting:**
  - All currency fields: decimal:2
  - Date fields: date
  - Structured data: array (form_data, calculation_details, attachments)

---

### 3. Controller ✅
**File:** `app/Http/Controllers/Business/CitController.php`

**Methods Implemented (331 lines):**

1. **index()** - Dashboard with statistics
   - Returns paginated CIT returns (12 per page)
   - Calculates stats: total_returns, total_cit_paid, pending_returns, overdue_returns, this_year_tax
   - Loads reviewer relationships

2. **create()** - Show form to create new CIT return
   - Fetches list of accountants (users with 'accountant' role)
   - Returns Inertia response with accountants data

3. **store()** - Create new CIT return
   - Validates: period, gross_profit, adjustments, reviewer_id, notes, status
   - Maps adjustments to `other_deductions` field
   - Sets `reviewed_by` from reviewer_id
   - Calculates due date (90 days after Dec 31 of period year)
   - Runs full tax calculations via `performCalculations()`
   - Returns: redirect to show page with success message

4. **show()** - View CIT return details
   - Loads relationships: reviewer, governmentPayments
   - Returns: Inertia response with citReturn and calculations

5. **edit()** - Show form to edit CIT return (draft only)
   - Validates ownership and draft status
   - Fetches accountants list
   - Returns: Inertia response with citReturn and accountants

6. **update()** - Update CIT return
   - Validates same fields as store
   - Only allows draft status returns
   - Recalculates all tax values
   - Returns: redirect to show page with success message

7. **generatePaymentRRR()** - Generate government payment RRR
   - Uses GovernmentPaymentService to generate Remita RRR
   - Creates GovernmentPayment record
   - Returns: JSON response with RRR details

8. **updateStatus()** - Update return status
   - Supports: draft, submitted, accepted, paid, rejected, overdue
   - Calls appropriate methods (markAsSubmitted, markAsAccepted, markAsPaid)
   - Returns: redirect with success message

9. **calculatePreview()** - AJAX endpoint for tax calculation preview
   - Validates financial inputs
   - Returns: JSON with calculation summary

---

### 4. Routes ✅
**File:** `routes/business.php` (lines 113-122)

**Registered Routes:**
```php
Route::prefix('cit')->name('cit.')->group(function () {
    Route::get('/', [CitController::class, 'index'])->name('index');
    Route::get('/create', [CitController::class, 'create'])->name('create');
    Route::post('/', [CitController::class, 'store'])->name('store');
    Route::get('/{citReturn}', [CitController::class, 'show'])->name('show');
    Route::get('/{citReturn}/edit', [CitController::class, 'edit'])->name('edit');
    Route::put('/{citReturn}', [CitController::class, 'update'])->name('update');
    Route::put('/{citReturn}/status', [CitController::class, 'updateStatus'])->name('update-status');
    Route::post('/{citReturn}/generate-rrr', [CitController::class, 'generatePaymentRRR'])->name('generate-rrr');
    Route::post('/calculate-preview', [CitController::class, 'calculatePreview'])->name('calculate-preview');
});
```

All routes require authentication and business context via middleware chain.

---

### 5. Vue Pages ✅

#### Index.vue - CIT Dashboard
**File:** `resources/js/Pages/Business/CIT/Index.vue`

**Features:**
- 4 statistics cards: Total Returns, Total CIT Paid, Pending Returns, This Year's CIT
- Overdue returns alert banner
- Paginated table (12 per page) showing: Period, Type, Taxable Income, Tax Due, Status, Due Date
- Quick actions: View, Create New Return buttons
- Uses existing StatusBadge and Pagination components

#### Create.vue - New CIT Return Form
**File:** `resources/js/Pages/Business/CIT/Create.vue`

**Form Fields:**
- Period (month input) - required
- Gross Profit (currency) - required
- Adjustments (currency) - optional
- Taxable Income (auto-calculated, read-only)
- Reviewer/Accountant (select dropdown)
- Notes (textarea)

**Calculations (Client-side Preview):**
- Taxable Income = Gross Profit + Adjustments
- CIT = Taxable Income × 30%
- Minimum Tax = Gross Profit × 0.5%
- Tax Due = Max(CIT, Minimum Tax)

**Actions:**
- Save as Draft button
- Create & Submit button
- Cancel link

**Validation Messages:** Inline error display for validation failures

#### Show.vue - CIT Return Details
**File:** `resources/js/Pages/Business/CIT/Show.vue`

**Display Sections:**
- Status badge with due date
- Financial summary cards: Gross Profit, Taxable Income, CIT Due
- Tax calculation breakdown showing:
  - Gross Profit
  - Adjustments (Other Deductions)
  - Taxable Income
  - 30% CIT calculation
  - Minimum Tax (0.5%)
  - Final CIT Due
- Accountant review info (if assigned)
- Notes section (if present)
- Payment history table (if payments exist)

**Actions:**
- Edit button (if draft status)
- Generate Payment RRR button (if submitted status)
- Back to CIT Returns link

#### Edit.vue - Edit CIT Return
**File:** `resources/js/Pages/Business/CIT/Edit.vue`

**Features:**
- Same form as Create page, pre-filled with existing data
- Read-only when status is not draft
- Same client-side tax calculations
- Update instead of store on submission
- Redirects to show page after save

---

## CIT Calculation Logic

### Tax Rate: 30%
Nigeria corporate income tax is calculated at 30% on assessable profit.

### Minimum Tax: 0.5%
For companies with turnover, a minimum tax of 0.5% applies if it exceeds the CIT.

### Assessable Profit Calculation
```
Gross Profit (Revenue - COGS)
+ Add-backs (depreciation, amortization, etc.)
- Deductions (capital allowances, allowable expenses)
= Taxable Income (Assessable Profit)
```

### CIT Due (The Higher of)
1. **30% CIT:** Taxable Income × 30%
2. **Minimum Tax:** Gross Profit × 0.5%

Example:
- Gross Profit: ₦10,000,000
- Add-backs: ₦500,000
- Deductions: ₦1,000,000
- Taxable Income: ₦9,500,000
- CIT (30%): ₦2,850,000
- Minimum Tax (0.5%): ₦50,000
- **Tax Due: ₦2,850,000** (higher of the two)

---

## Integration Points

### With Government Payment Service
- `GovernmentPaymentService` generates Remita RRR for CIT payments
- Payment records created in `governmentPayments` relationship
- Tracks payment reference and status

### With Accountant/Reviewer System
- CIT returns can be assigned to accountants (users with 'accountant' role)
- Reviewed_by field links to User model
- Reviewer info displayed in Show page

### With Business Context
- All CIT returns linked to business_id
- Business resolution from request->user()->current_business_id
- Role-based access control: users can only view their business CIT

---

## Status Workflow

1. **Draft** - Initial creation, editable, not filed
2. **Submitted** - Submitted to business, ready for payment
3. **Accepted** - Accepted by FIRS with acknowledgment
4. **Paid** - Payment confirmed and recorded
5. **Rejected** - Rejected by FIRS (needs correction)
6. **Overdue** - Due date passed but not paid

### Status Transitions
- Draft → Submitted (via updateStatus)
- Submitted → Accepted (FIRS acknowledgment)
- Accepted/Draft → Paid (payment confirmation)
- Any status → Overdue (automatic if due_date < now and not paid)

---

## Key Features

✅ **Automatic Tax Calculation** - 30% CIT with minimum tax provision
✅ **Due Date Tracking** - 90 days after financial year end
✅ **Accountant Assignment** - Assign reviewer for professional review
✅ **Payment Integration** - Generate payment RRR via Remita
✅ **Draft Status** - Save without submitting
✅ **Status Workflow** - Track through submission → payment
✅ **Balance Calculation** - Tax due minus credits (advance tax, withholding)
✅ **Role-Based Access** - Business owners only see their CIT
✅ **Audit Trail** - reviewed_at, reviewed_by, firs_reference tracking
✅ **Responsive Design** - Mobile-friendly Vue pages

---

## Database Fields Used

### Financial Fields
- `gross_profit` - Profit after COGS
- `revenue` - Total business revenue
- `cost_of_goods_sold` - COGS
- `depreciation` - Depreciation + amortization add-back
- `amortization` - Amortization add-back
- `other_add_backs` - Other add-backs
- `capital_allowances` - Capital allowances deduction
- `allowable_expenses` - Allowable business expenses
- `other_deductions` - Other deductions (maps to "adjustments" in form)

### Tax Calculation Fields
- `taxable_income` - Final assessable profit
- `cit_rate` - 30% (stored as 0.30)
- `cit_payable` - 30% of taxable income
- `turnover` - For minimum tax calculation
- `gross_assets` - For minimum tax calculation
- `paid_up_capital` - For minimum tax calculation
- `minimum_tax_amount` - 0.5% minimum tax
- `tax_due` - Final CIT (higher of CIT or minimum tax)

### Payment Fields
- `advance_tax` - Advance tax paid (quarterly)
- `withholding_tax` - WHT received
- `total_credits` - Total tax credits
- `balance_due` - Amount owed
- `balance_refund` - Amount to be refunded
- `late_filing_penalty` - Penalty for late filing
- `payment_interest` - Interest on late payment

### Reference Fields
- `firs_reference` - FIRS filing acknowledgment
- `form_a_reference` - Form A filing reference
- `period` - Tax year/quarter (e.g., "2026" or "2026-Q1")
- `return_type` - annual/quarterly/provisional

### Status & Audit
- `status` - enum: draft, submitted, accepted, paid, rejected, overdue
- `submitted_at` - When submitted to FIRS
- `due_date` - Payment due date
- `filed_at` - When officially filed
- `paid_at` - When payment received
- `reviewed_at` - When reviewed by accountant
- `reviewed_by` - FK to users (accountant)

---

## Next Steps (Phase 3B.2+)

🔄 **Phase 3B.2 - VAT (Value Added Tax)**
- Form 002 (VAT return)
- Form 001 (Sales invoice register)
- 5% VAT calculation with input/output credits
- Monthly/quarterly filing

📋 **Phase 3B.3 - CGT (Capital Gains Tax)**
- Capital gains transaction tracking
- CGT calculation (10% in Nigeria)
- Schedule of assets
- Annual filing

🔍 **Phase 3B.4 - Audit Trail System**
- Change history for all tax returns
- Version control for submitted documents
- Compliance audit log
- Accountability tracking

—

## Testing Checklist

Before moving to VAT implementation, test:

```
✅ Create CIT return
✅ View CIT return details
✅ Edit draft CIT return
✅ Update status workflow
✅ Tax calculations (30% CIT and 0.5% minimum)
✅ Assign accountant reviewer
✅ Generate payment RRR
✅ View payment history
✅ Overdue date detection
✅ Dashboard statistics
✅ Pagination in list view
```

---

**Status:** Phase 3B.1 Complete ✅
**Date Completed:** 2026-02-27
**Lines of Code:** 331 (CitController) + ~200 (Vue pages) + ~100 (Migration/Model)
**Database Tables:** 1 (cit_returns)
**Vue Components:** 4 (Index, Create, Show, Edit)
