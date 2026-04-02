# AI Workflow Data Validation System

## Overview

The AI Workflow system now includes **data availability validation** to prevent workflows from starting without the required data. This ensures a better user experience by checking data availability before workflow execution.

## How It Works

### 1. Pre-Flight Data Checks

Before a workflow can be started, the system:
- **Checks if required data exists** (transactions, staff, invoices, etc.)
- **Counts available records** for the specified period
- **Shows visual indicators** of data readiness
- **Blocks workflow creation** if critical data is missing

### 2. Workflow Data Requirements

#### Monthly VAT Workflow
**Required Data:**
- ✅ **Transactions**: At least 1 sales or purchase transaction for the period
- Optional: Invoices with VAT details

**Example Check:**
```php
$transactionsCount = Business::find($id)
    ->transactions()
    ->whereBetween('transaction_date', ['2024-01-01', '2024-01-31'])
    ->count();

if ($transactionsCount === 0) {
    // Cannot process VAT - no transactions
}
```

#### Monthly PAYE Workflow
**Required Data:**
- ✅ **Staff Records**: At least 1 active employee
- ✅ **Payroll Data**: Salary records for the period
- Optional: Pension contributions, NHF contributions

**Example Check:**
```php
$staffCount = Business::find($id)
    ->staff()
    ->where('status', 'active')
    ->count();

if ($staffCount === 0) {
    // Cannot process PAYE - no employees
}
```

#### Monthly WHT Workflow
**Required Data:**
- ✅ **WHT Transactions**: At least 1 transaction subject to withholding tax
- Categories: Dividends, Interest, Rent, Royalties, Commissions, etc.
- Optional: Beneficiary TIN numbers

**Example Check:**
```php
$whtTransactions = Business::find($id)
    ->transactions()
    ->whereBetween('transaction_date', ['2024-01-01', '2024-01-31'])
    ->whereIn('category', ['dividends', 'rent', 'interest', ...])
    ->count();

if ($whtTransactions === 0) {
    // Cannot process WHT - no applicable transactions
}
```

#### Compliance Assessment
**Required Data:**
- ✅ **Business Profile**: TIN and registration details
- Optional: Tax filing history, payment records

**Note:** This workflow can run with minimal data and will report on what's missing.

## API Endpoint

### Check Data Availability
```http
POST /business/ai-workflows/check-availability
Content-Type: application/json

{
  "workflow_type": "monthly_vat",
  "month": 1,
  "year": 2024
}
```

### Response Format

#### ✅ Data Available
```json
{
  "available": true,
  "missing": [],
  "requirements": {
    "name": "Monthly VAT Processing",
    "description": "Analyze transactions, calculate VAT, and generate Form VAT 001",
    "required_data": [
      {
        "type": "transactions",
        "description": "Sales and purchase transactions with VAT",
        "minimum": 1,
        "note": "At least one transaction is needed to process VAT"
      }
    ],
    "optional_data": [
      "Bank account linked for automatic transaction import",
      "Previous VAT returns for historical comparison"
    ],
    "time_range": "Specific month and year"
  },
  "data_counts": {
    "transactions": 45,
    "invoices": 12
  },
  "period": "1/2024",
  "period_formatted": "January 2024"
}
```

#### ❌ Data Not Available
```json
{
  "available": false,
  "missing": [
    "No transactions found for 1/2024"
  ],
  "requirements": { ... },
  "data_counts": {
    "transactions": 0,
    "invoices": 0
  },
  "period": "1/2024",
  "period_formatted": "January 2024"
}
```

## Backend Implementation

### TaxAiOrchestrator Methods

```php
// Main validation method
public function checkDataAvailability(
    string $workflowType,
    ?string $month = null,
    ?string $year = null
): array

// Get requirements documentation
public function getWorkflowDataRequirements(string $workflowType): array

// Specific checks
protected function checkVATDataAvailability(?string $month, ?string $year): array
protected function checkPAYEDataAvailability(?string $month, ?string $year): array
protected function checkWHTDataAvailability(?string $month, ?string $year): array
protected function checkComplianceDataAvailability(): array
```

### Controller Method

```php
// File: app/Http/Controllers/Business/AiWorkflowController.php

public function checkAvailability(Request $request)
{
    $business = $request->user()->defaultBusiness();
    
    $request->validate([
        'workflow_type' => 'required|string|in:monthly_vat,monthly_paye,monthly_wht,compliance_assessment',
        'month' => 'nullable|integer|min:1|max:12',
        'year' => 'nullable|integer|min:2020|max:2100',
    ]);

    $orchestrator = new TaxAiOrchestrator($business);
    
    $result = $orchestrator->checkDataAvailability(
        $request->workflow_type,
        $request->month,
        $request->year
    );

    return response()->json($result);
}
```

## Frontend Implementation

### Vue Component (CreateWorkflowModal.vue)

The modal automatically checks data availability when:
1. **Workflow type is selected**
2. **Tax period is changed**

```vue
<template>
  <!-- Data Availability Check UI -->
  <div v-if="form.workflow_type && form.tax_period">
    <!-- Loading State -->
    <div v-if="availability.checking">
      <i class="fas fa-spinner fa-spin"></i>
      Checking data availability...
    </div>

    <!-- Available -->
    <div v-else-if="availability.result && availability.result.available" class="bg-green-50">
      <i class="fas fa-check-circle text-green-600"></i>
      Ready to process
      <div>
        <span>Transactions: {{ availability.result.data_counts.transactions }}</span>
      </div>
    </div>

    <!-- Not Available -->
    <div v-else-if="availability.result && !availability.result.available" class="bg-amber-50">
      <i class="fas fa-exclamation-triangle text-amber-600"></i>
      Missing required data
      <ul>
        <li v-for="missing in availability.result.missing">{{ missing }}</li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { watch } from 'vue';
import axios from 'axios';

// Watch for changes and re-check
watch(
  () => [form.workflow_type, form.tax_period],
  ([workflowType, taxPeriod]) => {
    if (workflowType && taxPeriod) {
      checkDataAvailability(workflowType, taxPeriod);
    }
  }
);

async function checkDataAvailability(workflowType, taxPeriod) {
  availability.checking = true;
  
  const [year, month] = taxPeriod.split('-');
  
  const response = await axios.post(
    route('business.ai-workflows.check-availability'),
    { workflow_type: workflowType, month: parseInt(month), year: parseInt(year) }
  );
  
  availability.result = response.data;
  availability.checking = false;
}
</script>
```

### Submit Button Logic

```vue
<button
  type="submit"
  :disabled="!canSubmit"
>
  {{ availability.result && !availability.result.available ? 'Data Required' : 'Start Workflow' }}
</button>

<script>
const canSubmit = computed(() => {
  return (
    !form.processing &&
    form.workflow_type &&
    form.tax_period &&
    (!availability.result || availability.result.available)
  );
});
</script>
```

## User Experience

### Visual Indicators

1. **🟢 Green (Ready to run)**
   - All required data is available
   - Shows count of transactions/staff/etc.
   - "Start Workflow" button enabled

2. **🟡 Yellow (Warning)**
   - Data exists but below recommended threshold
   - Shows what data is available
   - User can still proceed with caution

3. **🔴 Red (Cannot run)**
   - Missing critical data
   - Shows specific requirements
   - "Data Required" button disabled

### Example User Flow

1. User clicks "Start New Workflow"
2. Selects **"Monthly VAT"**
3. Chooses **"January 2024"**
4. System checks availability:
   - ✅ Found 45 transactions
   - ✅ Found 12 invoices
5. Shows: **"Ready to process"** with green checkmark
6. User clicks **"Start Workflow"**
7. Workflow begins processing

### Example Error Flow

1. User selects **"Monthly PAYE"**
2. Chooses **"January 2024"**
3. System checks availability:
   - ❌ No active employees found
4. Shows: **"Missing required data"**
   - "No active employees found. Add employees to process PAYE."
5. Button shows **"Data Required"** and is disabled
6. User must add employees before proceeding

## Benefits

### For Users
- ✅ **No Failed Workflows**: Cannot start workflows that will fail
- ✅ **Clear Guidance**: Know exactly what data is needed
- ✅ **Real-Time Feedback**: Instant validation as you select options
- ✅ **Time Savings**: Don't waste time on incomplete workflows

### For System
- ✅ **Reduced Errors**: Fewer failed workflow executions
- ✅ **Better Data Quality**: Encourages users to maintain complete records
- ✅ **Optimized Resources**: Don't queue jobs that will fail
- ✅ **Improved UX**: Users trust the system more

## Testing

### Manual Testing

1. **Test VAT with No Data**:
   ```sql
   -- Remove all transactions
   DELETE FROM transactions WHERE business_id = 1;
   ```
   - Try creating VAT workflow
   - Should show "No transactions found" error

2. **Test PAYE with No Staff**:
   ```sql
   -- Remove all staff
   DELETE FROM business_staff WHERE business_id = 1;
   ```
   - Try creating PAYE workflow
   - Should show "No active employees" error

3. **Test with Data Present**:
   ```sql
   -- Add transactions
   INSERT INTO transactions (business_id, transaction_date, amount, ...)
   VALUES (1, '2024-01-15', 1000, ...);
   ```
   - Create VAT workflow
   - Should show green checkmark with transaction count

### API Testing

```bash
# Test VAT availability (no data)
curl -X POST http://taxmaster.test/business/ai-workflows/check-availability \
  -H "Content-Type: application/json" \
  -d '{"workflow_type":"monthly_vat","month":1,"year":2024}'

# Expected: {"available":false,"missing":["No transactions found"]}
```

## Future Enhancements

### Planned Features

1. **Smart Recommendations**:
   - "You have 5 transactions. Consider adding more for better accuracy."
   - "Link your bank account for automatic transaction import."

2. **Data Quality Scores**:
   - "Data Quality: 85% (12/15 invoices have TIN numbers)"
   - Show warnings for incomplete data

3. **Historical Comparison**:
   - "This is 30% less than last month's data"
   - Alert if data seems unusually low

4. **Automated Data Import**:
   - "Run data sync before starting workflow"
   - One-click import from connected accounts

## Troubleshooting

### "No transactions found" but I have data

**Check:**
1. Verify transaction dates match the selected period
2. Ensure transactions belong to the correct business
3. Check if transactions are soft-deleted

```sql
SELECT COUNT(*) FROM transactions
WHERE business_id = ? AND transaction_date BETWEEN ? AND ?;
```

### "No active employees" but staff exists

**Check:**
1. Verify staff status is 'active' (not 'inactive' or 'terminated')
2. Check business_staff table, not users table

```sql
SELECT * FROM business_staff
WHERE business_id = ? AND status = 'active';
```

### Checking endpoint doesn't work

**Verify:**
1. Route is registered: `php artisan route:list | grep check-availability`
2. User has business access
3. Middleware allows the request

## Summary

The data validation system is a crucial feature that:
- ✅ Prevents failed workflow executions
- ✅ Improves user experience with real-time feedback
- ✅ Guides users to maintain complete tax records
- ✅ Saves system resources by avoiding doomed workflows

**Key Principle**: *Don't let users start what they can't finish.*
