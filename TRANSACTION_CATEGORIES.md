# Transaction Categories Reference

## Overview

TaxMaster NG now supports **100+ transaction categories** organized for Nigerian tax compliance, including comprehensive WHT (Withholding Tax) categories.

## Main Categories

| Category | Code | Description |
|----------|------|-------------|
| **Revenue** | `REVENUE` | All income/sales transactions |
| **Expenses** | `EXPENSES` | All business expenses |
| **Tax** | `TAX` | Tax payments and deductions |
| **Personal** | `PERSONAL` | Personal (non-business) transactions |
| **Uncategorized** | `UNCATEGORIZED` | Not yet categorized |

## Revenue Subcategories

| Subcategory | Constant | WHT on Income? |
|-------------|----------|----------------|
| Product Sales | `SALES` | No |
| Service Revenue | `SERVICES` | No |
| Consulting Revenue | `CONSULTING` | Subject to WHT if received from corporate clients |
| Commission Income | `COMMISSION` | Yes - 10% WHT |
| Interest Income | `INTEREST_INCOME` | Yes - 10% WHT |
| Dividend Income | `DIVIDEND_INCOME` | Yes - 10% WHT |
| Rental Income | `RENT_INCOME` | Yes - 10% WHT |
| Royalty Income | `ROYALTY_INCOME` | Yes - 10% WHT |
| Other Revenue | `OTHER_REVENUE` | Depends on nature |

## WHT-Applicable Expense Categories

### Professional & Consultancy Services (10% WHT)

| Category | Constant | WHT Rate |
|----------|----------|----------|
| Professional Fees | `PROFESSIONAL_FEES` | 5-10% |
| Consultancy Fees | `CONSULTANCY` | 10% |
| Technical Services | `TECHNICAL_SERVICES` | 10% |
| Management Fees | `MANAGEMENT_FEES` | 10% |
| Legal Fees | `LEGAL_FEES` | 10% |
| Audit Fees | `AUDIT_FEES` | 10% |
| Accounting Fees | `ACCOUNTING_FEES` | 10% |
| Director Fees | `DIRECTOR_FEES` | 10% |

### Financial Payments (10% WHT)

| Category | Constant | WHT Rate |
|----------|----------|----------|
| Commission Paid | `COMMISSION_PAID` | 10% |
| Royalty Paid | `ROYALTY_PAID` | 10% |
| Interest Paid | `INTEREST_PAID` | 10% |
| Dividend Paid | `DIVIDEND_PAID` | 10% |
| Rent Paid | `RENT_PAID` | 10% |
| Agency Arrangement | `AGENCY_ARRANGEMENT` | 10% |

### Construction & Contracts (5% WHT)

| Category | Constant | WHT Rate |
|----------|----------|----------|
| Building/Construction | `BUILDING_CONSTRUCTION` | 5% |
| Contracts | `CONTRACTS` | 5% |

## General Expense Categories

### Office & Operations

| Category | Constant | VAT? |
|----------|----------|------|
| Rent Expense | `RENT` | Yes (7.5%) |
| Utilities | `UTILITIES` | Yes |
| Electricity | `ELECTRICITY` | Yes |
| Water | `WATER` | Yes |
| Internet | `INTERNET` | Yes |
| Telecommunications | `TELECOMMUNICATIONS` | Yes |
| Office Supplies | `OFFICE_SUPPLIES` | Yes |
| Furniture & Fixtures | `FURNITURE` | Yes |
| Equipment Purchase | `EQUIPMENT` | Yes |
| Cleaning Services | `CLEANING` | Yes |
| Security Services | `SECURITY` | Yes |
| Repairs & Maintenance | `REPAIRS_MAINTENANCE` | Yes |

### IT & Technology

| Category | Constant | VAT? |
|----------|----------|------|
| IT & Software | `IT_SOFTWARE` | Yes (7.5%) |
| IT Hardware | `IT_HARDWARE` | Yes (7.5%) |

### Marketing & Sales

| Category | Constant | VAT? |
|----------|----------|------|
| Marketing | `MARKETING` | Yes (7.5%) |
| Advertising | `ADVERTISING` | Yes (7.5%) |

### Travel & Transport

| Category | Constant | VAT? |
|----------|----------|------|
| Transportation | `TRANSPORT` | Yes (7.5%) |
| Fuel & Gas | `FUEL` | Yes (7.5%) |
| Travel Expenses | `TRAVEL` | Yes (7.5%) |
| Accommodation | `ACCOMMODATION` | Yes (7.5%) |
| Meals & Entertainment | `MEALS_ENTERTAINMENT` | Yes (7.5%) |

### Inventory & Production

| Category | Constant | VAT? |
|----------|----------|------|
| Raw Materials | `RAW_MATERIALS` | Yes (7.5%) |
| Inventory Purchase | `INVENTORY` | Yes (7.5%) |
| Packaging Materials | `PACKAGING` | Yes (7.5%) |
| Freight & Shipping | `FREIGHT_SHIPPING` | Yes (7.5%) |
| Import Duties | `IMPORT_DUTIES` | No |
| Warehousing | `WAREHOUSING` | Yes (7.5%) |

### Staff & Payroll

| Category | Constant | Tax Type |
|----------|----------|----------|
| Salaries & Wages | `SALARIES` | PAYE (7-24%) |
| PAYE Deduction | `SALARY_PAYE` | PAYE |
| Pension Contribution | `PENSION` | 8% (employer) + 10% (employee) |
| NHF Contribution | `NHF` | 2.5% |
| ITF Contribution | `ITF` | 1% |
| NSITF Contribution | `NSITF` | 1% |

### Professional Services

| Category | Constant | VAT? |
|----------|----------|------|
| Training & Development | `TRAINING` | Yes (7.5%) |
| Insurance | `INSURANCE` | VAT Exempt |
| Bank Charges | `BANK_CHARGES` | VAT Exempt |

## Tax Payment Categories

| Category | Constant | Description |
|----------|----------|-------------|
| VAT Payment | `VAT_PAYMENT` | Monthly VAT remittance to FIRS |
| PAYE Payment | `PAYE_PAYMENT` | Monthly PAYE remittance |
| WHT Payment | `WHT_PAYMENT` | Monthly WHT remittance |
| CIT Payment | `CIT_PAYMENT` | Annual/quarterly CIT payment |

## VAT Exempt Categories

| Category | Constant | Description |
|----------|----------|-------------|
| Medical Services | `EXEMPT_MEDICAL` | Healthcare services |
| Education Services | `EXEMPT_EDUCATION` | Educational services |
| Pharmaceuticals | `EXEMPT_PHARMACEUTICALS` | Pharmaceutical products |
| Basic Food Items | `EXEMPT_BASIC_FOODS` | Basic/essential foods |
| Books & Educational Materials | `EXEMPT_BOOKS` | Books, newspapers |
| Export Sales | `EXEMPT_EXPORTS` | Export transactions |

## Usage in Code

### Assigning Categories

```php
use App\Models\Transaction;

// Create transaction with WHT-applicable category
Transaction::create([
    'business_id' => $businessId,
    'amount' => 500000,
    'description' => 'Legal consultation services',
    'category' => Transaction::CATEGORY_EXPENSES,
    'sub_category' => Transaction::EXPENSE_LEGAL_FEES, // 10% WHT
    'transaction_date' => now(),
]);

// Create revenue transaction
Transaction::create([
    'business_id' => $businessId,
    'amount' => 1000000,
    'description' => 'Consulting project payment',
    'category' => Transaction::CATEGORY_REVENUE,
    'sub_category' => Transaction::REVENUE_CONSULTING,
    'transaction_date' => now(),
]);
```

### Get All Categories

```php
// Get categories grouped by type
$categories = Transaction::getCategoriesGrouped();

// Get only WHT-applicable categories
$whtCategories = Transaction::getWHTApplicableCategories();

// Check if transaction is WHT-applicable
$transaction = Transaction::find($id);
if ($transaction->isWHTApplicable()) {
    // Apply WHT calculation
}
```

### Filter WHT Transactions

```php
// Get all WHT-applicable transactions for March 2026
$whtTransactions = Transaction::whereIn('sub_category', Transaction::getWHTApplicableCategories())
    ->whereDate('transaction_date', '>=', '2026-03-01')
    ->whereDate('transaction_date', '<=', '2026-03-31')
    ->get();
```

## AI Workflow Integration

The AI Tax workflows will now:

### Monthly VAT
- Automatically categorize transactions as VATable or Exempt
- Apply 7.5% VAT rate correctly
- Identify VAT-exempt transactions

### Monthly WHT
- **Analyze ALL transactions** in the period
- **Identify WHT-applicable transactions** automatically
- Calculate correct WHT rates (5% or 10%)
- Generate WHT schedules by category

### Monthly PAYE
- Identify salary payments
- Calculate PAYE, Pension, NHF, ITF, NSITF
- Generate monthly returns

## Category Selection Best Practices

### When Importing Transactions

1. **Let AI categorize first**: The AI workflow can analyze transaction descriptions
2. **Review and correct**: Verify AI suggestions, especially for WHT transactions
3. **Be specific**: Use subcategories (e.g., `LEGAL_FEES` instead of just `PROFESSIONAL_FEES`)

### For Manual Entry

1. **Choose the most specific category**: `AUDIT_FEES` better than `PROFESSIONAL_FEES`
2. **Mark WHT-applicable transactions**: Use WHT categories to trigger automatic calculations
3. **Separate personal transactions**: Use `PERSONAL` category for non-business items

## API/Frontend Support

To get categories for dropdowns:

```php
// Controller
public function getCategories()
{
    return response()->json([
        'categories' => Transaction::getCategoriesGrouped(),
        'wht_categories' => Transaction::getWHTApplicableCategories(),
    ]);
}
```

## Benefits

✅ **100+ predefined categories** covering Nigerian business operations  
✅ **WHT rates included** in category names for clarity  
✅ **VAT status indicated** for each expense type  
✅ **AI-powered classification** works with detailed categories  
✅ **Compliance-ready** aligned with FIRS requirements  
✅ **Easy filtering** for tax return preparation  
✅ **Automatic calculations** based on category selection  

## Summary

The enhanced category system ensures that:
- All **WHT-applicable transactions** are properly labeled
- **VAT treatment** is clear for each category
- **PAYE and payroll taxes** are separated
- **AI workflows** can accurately classify and calculate taxes
- **Compliance reporting** is simplified with structured data
