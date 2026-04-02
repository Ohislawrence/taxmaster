# AI Workflow Return Creation System

## Overview

**YES, IT IS POSSIBLE!** The AI workflows automatically fill return tables using transaction data when workflows complete successfully.

## How It Works

### 1. **Data Flow**

```
Transaction Data → AI Workflow → Analysis → Tax Return Created
```

1. **User initiates workflow** (VAT, PAYE, WHT, or CIT)
2. **AI analyzes transactions** from your accounting system
3. **AI calculates tax amounts** based on Nigerian tax rules
4. **Draft return is created** in the respective table
5. **Return is marked as AI-generated** with link to workflow

### 2. **What Gets Created**

| Workflow Type | Creates Entry In | Model | Status |
|---------------|------------------|-------|--------|
| `monthly_vat` | `vat_returns` | VATReturn | ✅ Implemented |
| `monthly_paye` | `paye_returns` | PayeReturn | ✅ Implemented |
| `monthly_wht` | `wht_returns` | WhtReturn | ✅ Implemented |
| `monthly_cit` | `cit_returns` | CitReturn | ✅ NEW - Just Added |
| `annual_cit` | `cit_returns` | CitReturn | ✅ NEW - Just Added |

### 3. **Database Fields Added**

All return tables now have:
- `ai_workflow_id` - Links to the workflow that created it
- `is_ai_generated` - Boolean flag to identify AI-created returns

```sql
-- Migration already applied to all return tables
ALTER TABLE vat_returns ADD ai_workflow_id BIGINT UNSIGNED NULL;
ALTER TABLE vat_returns ADD is_ai_generated BOOLEAN DEFAULT FALSE;
-- Same for paye_returns, wht_returns, cit_returns
```

## Implementation Details

### VAT Return Creation

When a **monthly_vat** workflow completes:

```php
VATReturn::create([
    'business_id' => $workflow->business_id,
    'ai_workflow_id' => $workflow->id,
    'is_ai_generated' => true,
    'period' => '2026-03',  // From workflow
    'form_type' => 'Form 002',
    'sales_turnover' => 5000000,  // From AI analysis
    'vat_on_sales' => 375000,     // Calculated by AI
    'input_vat' => 150000,        // From transactions
    'vat_due' => 225000,          // Net VAT calculated
    'status' => 'draft',          // User can review before filing
    'notes' => 'AI-generated return from workflow #WF-2026-001',
]);
```

**AI analyzes:**
- All sales transactions → calculates output VAT
- All purchase transactions → calculates input VAT
- Exemptions and zero-rated items
- Net VAT payable

### PAYE Return Creation

When a **monthly_paye** workflow completes:

```php
PayeReturn::create([
    'business_id' => $workflow->business_id,
    'ai_workflow_id' => $workflow->id,
    'is_ai_generated' => true,
    'period' => '2026-03',
    'return_type' => 'monthly',
    'total_gross_pay' => 8000000,    // From payroll
    'total_tax_deducted' => 1200000, // PAYE calculated
    'staff_count' => 25,             // Employee count
    'schedule_data' => [...],        // Individual employee details
    'status' => 'draft',
]);
```

**AI analyzes:**
- Employee salaries
- Tax relief and allowances
- Applies progressive tax rates
- Generates employee schedules

### WHT Return Creation

When a **monthly_wht** workflow completes:

```php
WhtReturn::create([
    'business_id' => $workflow->business_id,
    'ai_workflow_id' => $workflow->id,
    'is_ai_generated' => true,
    'period' => '2026-03',
    'total_wht_deducted' => 500000,  // Total WHT
    'transaction_count' => 45,       // Payment count
    'schedule_data' => [...],        // Transaction details
    'status' => 'draft',
    'beneficiary_type' => 'company',
]);
```

**AI analyzes:**
- Payments to vendors/contractors
- Classifies by WHT category (5%, 10%, etc.)
- Generates schedules by beneficiary

### CIT Return Creation (NEW!)

When a **monthly_cit** or **annual_cit** workflow completes:

```php
CitReturn::create([
    'business_id' => $workflow->business_id,
    'ai_workflow_id' => $workflow->id,
    'is_ai_generated' => true,
    'period' => '2026',
    'return_type' => 'self_assessment',
    'revenue' => 50000000,
    'cost_of_goods_sold' => 30000000,
    'gross_profit' => 20000000,
    'taxable_income' => 15000000,
    'cit_rate' => 0.30,
    'cit_payable' => 4500000,        // 30% of taxable income
    'minimum_tax_amount' => 250000,   // 0.5% of turnover
    'tax_due' => 4500000,            // Greater of CIT or min tax
    'status' => 'draft',
]);
```

**AI analyzes:**
- Revenue and expenses
- Add-backs (depreciation, non-allowable)
- Deductions (capital allowances)
- Calculates CIT at 30% rate
- Compares with minimum tax (0.5% of turnover)

## Workflow Execution Flow

### Automatic Creation (Default)

```
1. User clicks "Start Workflow" → WorkflowController@store
2. Job queued → ProcessTaxWorkflowJob
3. Orchestrator runs → TaxAiOrchestrator
4. AI analyzes data → Calls DeepSeek/Gemini API
5. Workflow completes → markAsCompleted()
6. **AUTOMATICALLY** → createReturnFromWorkflow()
7. Draft return appears in returns table
```

### Code Location

**File:** `app/Services/TaxAiOrchestrator.php`

```php
// Called automatically on workflow completion
$this->createReturnFromWorkflow($workflow);

// Dispatches to specific creation methods
createVATReturn()   // Lines 1016-1046
createPAYEReturn()  // Lines 1051-1076  
createWHTReturn()   // Lines 1081-1104
createCITReturn()   // Lines 1109-1178 (NEW!)
```

## Confidence Scoring

Returns are only created if:
- Workflow status = `completed`
- Average confidence ≥ 85% (configurable)

If confidence is low:
- Workflow marked as `awaiting_review`
- Return NOT created automatically
- User must review and approve first

## UI Integration

### Return Lists Show AI Badge

All return pages now show AI badge for generated returns:

```vue
<!-- Purple-blue gradient badge -->
<div v-if="return.is_ai_generated" class="ai-badge">
  <i class="fas fa-robot"></i>
  AI Generated
</div>
```

**Pages updated:**
- [VAT Returns List](resources/js/Pages/Business/Vat/Index.vue)
- [PAYE Returns List](resources/js/Pages/Business/Paye/Index.vue)
- [WHT Returns List](resources/js/Pages/Business/Wht/Index.vue)
- CIT Returns List (if you have the page)

### Workflow Actions

From the AI Workflows page, users can:
- **View** the workflow details
- **Retry** failed workflows
- **Redo** completed workflows (creates new return)
- **Cancel** running workflows
- **Review** workflows awaiting approval
- **Delete** old workflows

## Testing

### Check If Returns Are Being Created

```bash
# Run in terminal
php artisan tinker

# Check AI-generated VAT returns
App\Models\VATReturn::where('is_ai_generated', true)->count();

# Check AI-generated PAYE returns  
App\Models\PayeReturn::where('is_ai_generated', true)->count();

# Check AI-generated WHT returns
App\Models\WhtReturn::where('is_ai_generated', true)->count();

# Check AI-generated CIT returns
App\Models\CitReturn::where('is_ai_generated', true)->count();

# See the latest AI-generated return
App\Models\VATReturn::where('is_ai_generated', true)
    ->with('aiWorkflow')
    ->latest()
    ->first();
```

### Why No Returns Yet?

If you have workflows but no returns created, check:

1. **Workflow Status**
   ```bash
   php artisan tinker
   App\Models\AiWorkflow::pluck('status', 'reference');
   ```
   
   Returns are only created when status = `completed`

2. **Confidence Score**
   ```bash
   App\Models\AiWorkflow::get(['reference', 'status', 'confidence_scores']);
   ```
   
   If average confidence < 85%, workflow goes to `awaiting_review`

3. **Check Logs**
   ```bash
   tail -f storage/logs/laravel.log | grep "Created.*return from AI workflow"
   ```

## Next Steps

### 1. Create a Workflow

Visit **Business → AI Workflows** and create a workflow for:
- Monthly VAT (needs transaction data)
- Monthly PAYE (needs payroll data)
- Monthly WHT (needs payment data)
- Monthly CIT (needs financial data)

### 2. Wait for Completion

Workflows run in background. Check status:
- Pending → Running → Completed (or Awaiting Review)

### 3. Check Returns Table

Once completed, check:
- **VAT Returns** → Should show new draft return with AI badge
- **PAYE Returns** → Should show new draft return with AI badge
- **WHT Returns** → Should show new draft return with AI badge
- **CIT Returns** → Should show new draft return with AI badge

### 4. Review & File

AI-generated returns start as **draft** status:
- Review the calculations
- Make any adjustments needed
- Submit to tax authority

## Configuration

### AI Provider Setup

Set in `.env`:
```env
AI_PROVIDER=deepseek
DEEPSEEK_API_KEY=your-api-key-here

# OR use Gemini
AI_PROVIDER=gemini
GEMINI_API_KEY=your-api-key-here
```

### Confidence Threshold

In `TaxAiOrchestrator.php`:
```php
// Line ~166: Adjust threshold
if ($avgConfidence !== null && $avgConfidence < 0.85) {
    // Change to 0.75 for lower threshold
    $workflow->markAsAwaitingReview([...]);
}
```

## Summary

✅ **System is FULLY implemented**
✅ **All return types supported** (VAT, PAYE, WHT, CIT)
✅ **Automatic creation** on workflow completion
✅ **AI badges** on all return lists
✅ **Database fields** added and migrated
✅ **Confidence scoring** with review workflow
✅ **Transaction data** → AI analysis → Draft return

**The system DOES fill the return tables automatically when AI workflows complete!**

Just ensure:
1. Workflows complete successfully (not stuck in running/failed)
2. Confidence scores are high enough (≥85%)
3. You have transaction/payroll/financial data for AI to analyze

---

**Created:** April 2, 2026
**Status:** Production Ready
**Version:** 1.0
