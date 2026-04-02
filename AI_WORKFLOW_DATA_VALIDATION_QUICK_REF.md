# AI Workflow Data Validation - Quick Reference

## What Was Implemented

A complete data validation system that checks if required data is available before allowing users to start AI workflows.

## Files Modified/Created

### Backend Files
1. **app/Services/TaxAiOrchestrator.php**
   - Added `checkDataAvailability()` - Main validation method
   - Added `getWorkflowDataRequirements()` - Returns documentation
   - Added 4 specific checkers:
     - `checkVATDataAvailability()` - Validates transactions exist
     - `checkPAYEDataAvailability()` - Validates staff/payroll exist
     - `checkWHTDataAvailability()` - Validates WHT transactions exist
     - `checkComplianceDataAvailability()` - Validates business profile

2. **app/Http/Controllers/Business/AiWorkflowController.php**
   - Added `checkAvailability()` method
   - Endpoint: `POST /business/ai-workflows/check-availability`

3. **routes/business.php**
   - Added route: `post('/check-availability', ...)`

### Frontend Files
4. **resources/js/Pages/Business/AiWorkflows/CreateWorkflowModal.vue**
   - Added data availability checking UI
   - Shows real-time validation when workflow type/period changes
   - Visual indicators: ✅ Green (ready), ⚠️ Yellow (warning), ❌ Red (blocked)
   - Disabled submit button if data not available
   - Collapsible requirements info section

### Documentation
5. **AI_WORKFLOW_DATA_VALIDATION.md**
   - Complete documentation with examples
   - API reference, testing guide, troubleshooting

## Data Requirements by Workflow

| Workflow Type | Required Data | Minimum |
|--------------|---------------|---------|
| **Monthly VAT** | Transactions | 1+ |
| **Monthly PAYE** | Active Staff | 1+ |
| **Monthly WHT** | WHT Transactions | 1+ |
| **Compliance Assessment** | Business Profile (TIN) | - |

## How It Works

### User Flow
```
1. User opens "Start New Workflow" modal
2. Selects workflow type (e.g., "Monthly VAT")
3. Selects tax period (e.g., "January 2024")
   ↓
4. System automatically checks data availability
   - Frontend watches for changes
   - Calls: POST /business/ai-workflows/check-availability
   - Backend queries database for required data
   ↓
5. Display result:
   ✅ GREEN: "Ready to process (45 transactions found)"
   ❌ RED: "No transactions found for 1/2024"
   ↓
6. Submit button:
   - Enabled if data available
   - Disabled if data missing
```

### Technical Flow
```php
// 1. Frontend calls API
axios.post('/business/ai-workflows/check-availability', {
    workflow_type: 'monthly_vat',
    month: 1,
    year: 2024
})

// 2. Controller validates and calls orchestrator
$orchestrator = new TaxAiOrchestrator($business);
$result = $orchestrator->checkDataAvailability('monthly_vat', '1', '2024');

// 3. Orchestrator routes to specific checker
return $this->checkVATDataAvailability('1', '2024');

// 4. Checker queries database
$count = $business->transactions()
    ->whereBetween('transaction_date', ['2024-01-01', '2024-01-31'])
    ->count();

// 5. Return structured response
return [
    'available' => $count > 0,
    'missing' => $count === 0 ? ['No transactions found'] : [],
    'data_counts' => ['transactions' => $count],
    'requirements' => [...],
];
```

## API Quick Reference

### Request
```http
POST /business/ai-workflows/check-availability
Content-Type: application/json

{
  "workflow_type": "monthly_vat",
  "month": 1,
  "year": 2024
}
```

### Response
```json
{
  "available": true,
  "missing": [],
  "data_counts": {
    "transactions": 45,
    "invoices": 12
  },
  "period_formatted": "January 2024",
  "requirements": {
    "name": "Monthly VAT Processing",
    "description": "Analyze transactions, calculate VAT, and generate Form VAT 001",
    "required_data": [...]
  }
}
```

## Testing Commands

### Test with Laravel Tinker
```php
php artisan tinker

// Get business
$business = \App\Models\Business::find(1);

// Check VAT data
$orchestrator = new \App\Services\TaxAiOrchestrator($business);
$result = $orchestrator->checkDataAvailability('monthly_vat', '1', '2024');
print_r($result);

// Expected output:
// Array (
//     [available] => false
//     [missing] => Array ( [0] => No transactions found for 1/2024 )
//     [data_counts] => Array ( [transactions] => 0 )
// )
```

### Test API with cURL
```bash
# Get CSRF token and cookie first, then:
curl -X POST http://taxmaster.test/business/ai-workflows/check-availability \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{"workflow_type":"monthly_vat","month":1,"year":2024}'
```

### Add Test Data
```php
// Add transactions for testing
\App\Models\Transaction::create([
    'business_id' => 1,
    'transaction_date' => '2024-01-15',
    'amount' => 10000,
    'category' => 'sales',
    'description' => 'Test transaction',
    'type' => 'income',
]);

// Add staff for PAYE testing
\App\Models\BusinessStaff::create([
    'business_id' => 1,
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'john@example.com',
    'status' => 'active',
    'salary' => 50000,
]);
```

## UI States

### Loading State
```
🔄 Checking data availability...
```

### Success State (Green)
```
✅ Ready to process
   📊 Transactions: 45
   📄 Invoices: 12
   📅 Period: January 2024
[Start Workflow] ← Enabled
```

### Error State (Red)
```
⚠️ Missing required data
   • No transactions found for 1/2024
   📊 Current data:
      Transactions: 0
[Data Required] ← Disabled
```

### Requirements (Collapsible)
```
ℹ️ Data Requirements ▼
   Monthly VAT Processing
   Analyze transactions, calculate VAT, and generate Form VAT 001
   
   Required:
   • transactions: Sales and purchase transactions with VAT (min: 1)
   • invoices: Invoices issued to customers (optional)
```

## Benefits

### User Experience
- ✅ **Immediate Feedback**: Know instantly if you can run a workflow
- ✅ **Clear Guidance**: See exactly what data is needed
- ✅ **No Wasted Time**: Don't start workflows that will fail
- ✅ **Data Quality**: Encourages maintaining complete records

### System Performance
- ✅ **Fewer Failed Jobs**: Don't queue workflows without data
- ✅ **Resource Optimization**: Save API calls and processing time
- ✅ **Error Reduction**: Prevent runtime errors from missing data

## Common Issues & Solutions

### Issue: "No transactions found" but data exists
**Solution:**
```php
// Check transaction dates
SELECT * FROM transactions
WHERE business_id = 1
AND transaction_date BETWEEN '2024-01-01' AND '2024-01-31';

// Verify not soft-deleted
SELECT * FROM transactions WHERE business_id = 1 AND deleted_at IS NULL;
```

### Issue: "No active employees" but staff exist
**Solution:**
```php
// Check staff status
SELECT * FROM business_staff
WHERE business_id = 1 AND status = 'active';

// Update status if needed
UPDATE business_staff SET status = 'active' WHERE business_id = 1;
```

### Issue: Check endpoint returns 404
**Solution:**
```bash
# Verify route exists
php artisan route:list | grep check-availability

# Clear route cache
php artisan route:clear
php artisan route:cache
```

## Next Steps

To use the validation system:

1. **Start a workflow**:
   - Navigate to "AI Workflows" page
   - Click "Start New Workflow"
   - Select workflow type and period
   - System will automatically check data

2. **Review availability result**:
   - Green = Ready to proceed
   - Red = Must add data first

3. **Add missing data** (if needed):
   - Go to Transactions page to add transactions
   - Go to Staff page to add employees
   - Return to workflow creation

4. **Start workflow**:
   - Once data is available
   - Click "Start Workflow"
   - Monitor progress on Show page

## Summary

**What it does**: Checks if required data exists before starting workflows  
**Why it matters**: Prevents failed workflows and improves user experience  
**How to use**: Automatic - just select workflow type and period  
**Key files**:
- Backend: `TaxAiOrchestrator.php`, `AiWorkflowController.php`
- Frontend: `CreateWorkflowModal.vue`
- Route: `POST /business/ai-workflows/check-availability`

**Result**: Users get instant feedback on data availability with clear guidance on what's needed.
