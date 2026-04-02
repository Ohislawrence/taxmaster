<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Events\TransactionCreated;

class Transaction extends Model
{
    use HasFactory;

    /**
     * Transaction Main Categories
     */
    public const CATEGORY_REVENUE = 'REVENUE';
    public const CATEGORY_EXPENSES = 'EXPENSES';
    public const CATEGORY_TAX = 'TAX';
    public const CATEGORY_PERSONAL = 'PERSONAL';
    public const CATEGORY_UNCATEGORIZED = 'UNCATEGORIZED';

    /**
     * Revenue Subcategories
     */
    public const REVENUE_SALES = 'SALES';
    public const REVENUE_SERVICES = 'SERVICES';
    public const REVENUE_CONSULTING = 'CONSULTING';
    public const REVENUE_COMMISSION = 'COMMISSION';
    public const REVENUE_INTEREST = 'INTEREST_INCOME';
    public const REVENUE_DIVIDEND = 'DIVIDEND_INCOME';
    public const REVENUE_RENT = 'RENT_INCOME';
    public const REVENUE_ROYALTY = 'ROYALTY_INCOME';
    public const REVENUE_OTHER = 'OTHER_REVENUE';

    /**
     * Expense Subcategories - General
     */
    public const EXPENSE_SALARIES = 'SALARIES';
    public const EXPENSE_RENT = 'RENT';
    public const EXPENSE_UTILITIES = 'UTILITIES';
    public const EXPENSE_TRANSPORT = 'TRANSPORT';
    public const EXPENSE_FUEL = 'FUEL';
    public const EXPENSE_MARKETING = 'MARKETING';
    public const EXPENSE_ADVERTISING = 'ADVERTISING';
    public const EXPENSE_OFFICE_SUPPLIES = 'OFFICE_SUPPLIES';
    public const EXPENSE_INSURANCE = 'INSURANCE';
    public const EXPENSE_BANK_CHARGES = 'BANK_CHARGES';
    public const EXPENSE_OTHER = 'OTHER_EXPENSES';

    /**
     * Expense Subcategories - WHT Applicable
     */
    public const EXPENSE_PROFESSIONAL_FEES = 'PROFESSIONAL_FEES';
    public const EXPENSE_CONSULTANCY = 'CONSULTANCY';
    public const EXPENSE_TECHNICAL_SERVICES = 'TECHNICAL_SERVICES';
    public const EXPENSE_MANAGEMENT_FEES = 'MANAGEMENT_FEES';
    public const EXPENSE_LEGAL_FEES = 'LEGAL_FEES';
    public const EXPENSE_AUDIT_FEES = 'AUDIT_FEES';
    public const EXPENSE_ACCOUNTING_FEES = 'ACCOUNTING_FEES';
    public const EXPENSE_COMMISSION_PAID = 'COMMISSION_PAID';
    public const EXPENSE_ROYALTY_PAID = 'ROYALTY_PAID';
    public const EXPENSE_INTEREST_PAID = 'INTEREST_PAID';
    public const EXPENSE_DIVIDEND_PAID = 'DIVIDEND_PAID';
    public const EXPENSE_RENT_PAID = 'RENT_PAID';
    public const EXPENSE_BUILDING_CONSTRUCTION = 'BUILDING_CONSTRUCTION';
    public const EXPENSE_CONTRACTS = 'CONTRACTS';
    public const EXPENSE_DIRECTOR_FEES = 'DIRECTOR_FEES';
    public const EXPENSE_AGENCY_ARRANGEMENT = 'AGENCY_ARRANGEMENT';

    /**
     * Expense Subcategories - Operational
     */
    public const EXPENSE_IT_SOFTWARE = 'IT_SOFTWARE';
    public const EXPENSE_IT_HARDWARE = 'IT_HARDWARE';
    public const EXPENSE_TELECOMMUNICATIONS = 'TELECOMMUNICATIONS';
    public const EXPENSE_INTERNET = 'INTERNET';
    public const EXPENSE_ELECTRICITY = 'ELECTRICITY';
    public const EXPENSE_WATER = 'WATER';
    public const EXPENSE_CLEANING = 'CLEANING';
    public const EXPENSE_SECURITY = 'SECURITY';
    public const EXPENSE_REPAIRS_MAINTENANCE = 'REPAIRS_MAINTENANCE';
    public const EXPENSE_EQUIPMENT = 'EQUIPMENT';
    public const EXPENSE_FURNITURE = 'FURNITURE';
    public const EXPENSE_TRAINING = 'TRAINING';
    public const EXPENSE_TRAVEL = 'TRAVEL';
    public const EXPENSE_ACCOMMODATION = 'ACCOMMODATION';
    public const EXPENSE_MEALS_ENTERTAINMENT = 'MEALS_ENTERTAINMENT';

    /**
     * Expense Subcategories - Inventory & Production
     */
    public const EXPENSE_RAW_MATERIALS = 'RAW_MATERIALS';
    public const EXPENSE_INVENTORY = 'INVENTORY';
    public const EXPENSE_PACKAGING = 'PACKAGING';
    public const EXPENSE_FREIGHT_SHIPPING = 'FREIGHT_SHIPPING';
    public const EXPENSE_IMPORT_DUTIES = 'IMPORT_DUTIES';
    public const EXPENSE_WAREHOUSING = 'WAREHOUSING';

    /**
     * Tax Subcategories
     */
    public const TAX_VAT_OUTPUT = 'VAT_OUTPUT';
    public const TAX_VAT_INPUT = 'VAT_INPUT';
    public const TAX_VAT_PAYMENT = 'VAT_PAYMENT';
    public const TAX_PAYE = 'SALARY_PAYE';
    public const TAX_PAYE_PAYMENT = 'PAYE_PAYMENT';
    public const TAX_WHT_DEDUCTED = 'WHT_DEDUCTED';
    public const TAX_WHT_PAYMENT = 'WHT_PAYMENT';
    public const TAX_CIT_PAYMENT = 'CIT_PAYMENT';
    public const TAX_PENSION = 'PENSION';
    public const TAX_NHF = 'NHF';
    public const TAX_ITF = 'ITF';
    public const TAX_NSITF = 'NSITF';

    /**
     * VAT Exempt Categories
     */
    public const EXEMPT_MEDICAL = 'EXEMPT_MEDICAL';
    public const EXEMPT_EDUCATION = 'EXEMPT_EDUCATION';
    public const EXEMPT_PHARMACEUTICALS = 'EXEMPT_PHARMACEUTICALS';
    public const EXEMPT_BASIC_FOODS = 'EXEMPT_BASIC_FOODS';
    public const EXEMPT_BOOKS = 'EXEMPT_BOOKS';
    public const EXEMPT_EXPORTS = 'EXEMPT_EXPORTS';

    protected $fillable = [
        'bank_account_id',
        'business_id',
        'mono_transaction_id',
        'type',
        'amount',
        'currency',
        'description',
        'counterparty',
        'transaction_date',
        'balance',
        'category',
        'sub_category',
        'confidence',
        'vat_applicable',
        'vat_exempt',
        'vat_exempt_category',
        'is_business_expense',
        'user_verified',
        'notes',
        'attachments',
        'meta',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'confidence' => 'decimal:2',
        'transaction_date' => 'datetime',
        'vat_applicable' => 'boolean',
        'vat_exempt' => 'boolean',
        'is_business_expense' => 'boolean',
        'user_verified' => 'boolean',
        'attachments' => 'array',
        'meta' => 'encrypted:array',
        'data_encrypted' => 'boolean',
    ];

    /**
     * Events to dispatch
     */
    protected $dispatchesEvents = [
        'created' => TransactionCreated::class,
    ];

    /**
     * Get the bank account that owns the transaction
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    /**
     * Get the business that owns the transaction
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get formatted amount with sign
     */
    public function getFormattedAmountAttribute(): string
    {
        $sign = $this->type === 'credit' ? '+' : '-';
        return $sign . ' ₦' . number_format($this->amount, 2);
    }

    /**
     * Get category label
     */
    public function getCategoryLabelAttribute(): string
    {
        $labels = [
            // Main Categories
            self::CATEGORY_REVENUE => 'Revenue',
            self::CATEGORY_EXPENSES => 'Expenses',
            self::CATEGORY_TAX => 'Tax',
            self::CATEGORY_PERSONAL => 'Personal',
            self::CATEGORY_UNCATEGORIZED => 'Uncategorized',

            // Revenue Subcategories
            self::REVENUE_SALES => 'Product Sales',
            self::REVENUE_SERVICES => 'Service Revenue',
            self::REVENUE_CONSULTING => 'Consulting Revenue',
            self::REVENUE_COMMISSION => 'Commission Income',
            self::REVENUE_INTEREST => 'Interest Income',
            self::REVENUE_DIVIDEND => 'Dividend Income',
            self::REVENUE_RENT => 'Rental Income',
            self::REVENUE_ROYALTY => 'Royalty Income',
            self::REVENUE_OTHER => 'Other Revenue',

            // General Expenses
            self::EXPENSE_SALARIES => 'Salaries & Wages',
            self::EXPENSE_RENT => 'Rent Expense',
            self::EXPENSE_UTILITIES => 'Utilities',
            self::EXPENSE_TRANSPORT => 'Transportation',
            self::EXPENSE_FUEL => 'Fuel & Gas',
            self::EXPENSE_MARKETING => 'Marketing',
            self::EXPENSE_ADVERTISING => 'Advertising',
            self::EXPENSE_OFFICE_SUPPLIES => 'Office Supplies',
            self::EXPENSE_INSURANCE => 'Insurance',
            self::EXPENSE_BANK_CHARGES => 'Bank Charges',
            self::EXPENSE_OTHER => 'Other Expenses',

            // WHT-Applicable Expenses
            self::EXPENSE_PROFESSIONAL_FEES => 'Professional Fees (WHT 5-10%)',
            self::EXPENSE_CONSULTANCY => 'Consultancy Fees (WHT 10%)',
            self::EXPENSE_TECHNICAL_SERVICES => 'Technical Services (WHT 10%)',
            self::EXPENSE_MANAGEMENT_FEES => 'Management Fees (WHT 10%)',
            self::EXPENSE_LEGAL_FEES => 'Legal Fees (WHT 10%)',
            self::EXPENSE_AUDIT_FEES => 'Audit Fees (WHT 10%)',
            self::EXPENSE_ACCOUNTING_FEES => 'Accounting Fees (WHT 10%)',
            self::EXPENSE_COMMISSION_PAID => 'Commission Paid (WHT 10%)',
            self::EXPENSE_ROYALTY_PAID => 'Royalty Paid (WHT 10%)',
            self::EXPENSE_INTEREST_PAID => 'Interest Paid (WHT 10%)',
            self::EXPENSE_DIVIDEND_PAID => 'Dividend Paid (WHT 10%)',
            self::EXPENSE_RENT_PAID => 'Rent Paid (WHT 10%)',
            self::EXPENSE_BUILDING_CONSTRUCTION => 'Building/Construction (WHT 5%)',
            self::EXPENSE_CONTRACTS => 'Contracts (WHT 5%)',
            self::EXPENSE_DIRECTOR_FEES => 'Director Fees (WHT 10%)',
            self::EXPENSE_AGENCY_ARRANGEMENT => 'Agency Arrangement (WHT 10%)',

            // Operational Expenses
            self::EXPENSE_IT_SOFTWARE => 'IT & Software',
            self::EXPENSE_IT_HARDWARE => 'IT Hardware',
            self::EXPENSE_TELECOMMUNICATIONS => 'Telecommunications',
            self::EXPENSE_INTERNET => 'Internet',
            self::EXPENSE_ELECTRICITY => 'Electricity',
            self::EXPENSE_WATER => 'Water',
            self::EXPENSE_CLEANING => 'Cleaning Services',
            self::EXPENSE_SECURITY => 'Security Services',
            self::EXPENSE_REPAIRS_MAINTENANCE => 'Repairs & Maintenance',
            self::EXPENSE_EQUIPMENT => 'Equipment Purchase',
            self::EXPENSE_FURNITURE => 'Furniture & Fixtures',
            self::EXPENSE_TRAINING => 'Training & Development',
            self::EXPENSE_TRAVEL => 'Travel Expenses',
            self::EXPENSE_ACCOMMODATION => 'Accommodation',
            self::EXPENSE_MEALS_ENTERTAINMENT => 'Meals & Entertainment',

            // Inventory & Production
            self::EXPENSE_RAW_MATERIALS => 'Raw Materials',
            self::EXPENSE_INVENTORY => 'Inventory Purchase',
            self::EXPENSE_PACKAGING => 'Packaging Materials',
            self::EXPENSE_FREIGHT_SHIPPING => 'Freight & Shipping',
            self::EXPENSE_IMPORT_DUTIES => 'Import Duties',
            self::EXPENSE_WAREHOUSING => 'Warehousing',

            // Tax Categories
            self::TAX_VAT_OUTPUT => 'VATable Sales',
            self::TAX_VAT_INPUT => 'VATable Expenses',
            self::TAX_VAT_PAYMENT => 'VAT Payment',
            self::TAX_PAYE => 'Salaries & PAYE',
            self::TAX_PAYE_PAYMENT => 'PAYE Payment',
            self::TAX_WHT_DEDUCTED => 'WHT Deducted',
            self::TAX_WHT_PAYMENT => 'WHT Payment',
            self::TAX_CIT_PAYMENT => 'CIT Payment',
            self::TAX_PENSION => 'Pension Contribution',
            self::TAX_NHF => 'NHF Contribution',
            self::TAX_ITF => 'ITF Contribution',
            self::TAX_NSITF => 'NSITF Contribution',

            // VAT Exempt
            self::EXEMPT_MEDICAL => 'Medical Services (VAT Exempt)',
            self::EXEMPT_EDUCATION => 'Education Services (VAT Exempt)',
            self::EXEMPT_PHARMACEUTICALS => 'Pharmaceuticals (VAT Exempt)',
            self::EXEMPT_BASIC_FOODS => 'Basic Food Items (VAT Exempt)',
            self::EXEMPT_BOOKS => 'Books & Educational Materials (VAT Exempt)',
            self::EXEMPT_EXPORTS => 'Export Sales (VAT Exempt)',

            // Legacy support
            'VAT_OUTPUT' => 'VATable Sales',
            'VAT_INPUT' => 'VATable Expenses',
            'EXEMPT_SALES' => 'VAT-exempt Sales',
            'SALARY_PAYE' => 'Salaries & PAYE',
            'RENT' => 'Rent & Facilities',
            'PROFESSIONAL' => 'Professional Fees',
            'MARKETING' => 'Marketing',
            'UTILITIES' => 'Utilities',
            'TRANSPORT' => 'Transport',
            'IT_SOFTWARE' => 'IT & Software',
            'RAW_MATERIALS' => 'Raw Materials',
            'OTHER_EXPENSES' => 'Other Expenses',
            'VAT_PAYMENT' => 'VAT Payment',
            'PAYE_PAYMENT' => 'PAYE Payment',
            'WHT_PAYMENT' => 'WHT Payment',
            'CIT_PAYMENT' => 'CIT Payment',
            'PERSONAL' => 'Personal',
            'UNCATEGORIZED' => 'Uncategorized',
        ];

        return $labels[$this->sub_category ?? ''] ?? $labels[$this->category ?? ''] ?? 'Unknown';
    }

    /**
     * Get all available categories grouped by type
     */
    public static function getCategoriesGrouped(): array
    {
        return [
            'Revenue' => [
                self::REVENUE_SALES => 'Product Sales',
                self::REVENUE_SERVICES => 'Service Revenue',
                self::REVENUE_CONSULTING => 'Consulting Revenue',
                self::REVENUE_COMMISSION => 'Commission Income',
                self::REVENUE_INTEREST => 'Interest Income',
                self::REVENUE_DIVIDEND => 'Dividend Income',
                self::REVENUE_RENT => 'Rental Income',
                self::REVENUE_ROYALTY => 'Royalty Income',
                self::REVENUE_OTHER => 'Other Revenue',
            ],
            'WHT-Applicable Expenses' => [
                self::EXPENSE_PROFESSIONAL_FEES => 'Professional Fees (WHT 5-10%)',
                self::EXPENSE_CONSULTANCY => 'Consultancy Fees (WHT 10%)',
                self::EXPENSE_TECHNICAL_SERVICES => 'Technical Services (WHT 10%)',
                self::EXPENSE_MANAGEMENT_FEES => 'Management Fees (WHT 10%)',
                self::EXPENSE_LEGAL_FEES => 'Legal Fees (WHT 10%)',
                self::EXPENSE_AUDIT_FEES => 'Audit Fees (WHT 10%)',
                self::EXPENSE_ACCOUNTING_FEES => 'Accounting Fees (WHT 10%)',
                self::EXPENSE_COMMISSION_PAID => 'Commission Paid (WHT 10%)',
                self::EXPENSE_ROYALTY_PAID => 'Royalty Paid (WHT 10%)',
                self::EXPENSE_INTEREST_PAID => 'Interest Paid (WHT 10%)',
                self::EXPENSE_DIVIDEND_PAID => 'Dividend Paid (WHT 10%)',
                self::EXPENSE_RENT_PAID => 'Rent Paid (WHT 10%)',
                self::EXPENSE_BUILDING_CONSTRUCTION => 'Building/Construction (WHT 5%)',
                self::EXPENSE_CONTRACTS => 'Contracts (WHT 5%)',
                self::EXPENSE_DIRECTOR_FEES => 'Director Fees (WHT 10%)',
                self::EXPENSE_AGENCY_ARRANGEMENT => 'Agency Arrangement (WHT 10%)',
            ],
            'Staff & Payroll' => [
                self::EXPENSE_SALARIES => 'Salaries & Wages',
                self::TAX_PAYE => 'PAYE Deduction',
                self::TAX_PENSION => 'Pension Contribution',
                self::TAX_NHF => 'NHF Contribution',
                self::TAX_ITF => 'ITF Contribution',
                self::TAX_NSITF => 'NSITF Contribution',
            ],
            'Office & Operations' => [
                self::EXPENSE_RENT => 'Rent Expense',
                self::EXPENSE_UTILITIES => 'Utilities',
                self::EXPENSE_ELECTRICITY => 'Electricity',
                self::EXPENSE_WATER => 'Water',
                self::EXPENSE_INTERNET => 'Internet',
                self::EXPENSE_TELECOMMUNICATIONS => 'Telecommunications',
                self::EXPENSE_OFFICE_SUPPLIES => 'Office Supplies',
                self::EXPENSE_FURNITURE => 'Furniture & Fixtures',
                self::EXPENSE_EQUIPMENT => 'Equipment Purchase',
                self::EXPENSE_CLEANING => 'Cleaning Services',
                self::EXPENSE_SECURITY => 'Security Services',
                self::EXPENSE_REPAIRS_MAINTENANCE => 'Repairs & Maintenance',
            ],
            'IT & Technology' => [
                self::EXPENSE_IT_SOFTWARE => 'IT & Software',
                self::EXPENSE_IT_HARDWARE => 'IT Hardware',
            ],
            'Marketing & Sales' => [
                self::EXPENSE_MARKETING => 'Marketing',
                self::EXPENSE_ADVERTISING => 'Advertising',
            ],
            'Travel & Transport' => [
                self::EXPENSE_TRANSPORT => 'Transportation',
                self::EXPENSE_FUEL => 'Fuel & Gas',
                self::EXPENSE_TRAVEL => 'Travel Expenses',
                self::EXPENSE_ACCOMMODATION => 'Accommodation',
                self::EXPENSE_MEALS_ENTERTAINMENT => 'Meals & Entertainment',
            ],
            'Inventory & Production' => [
                self::EXPENSE_RAW_MATERIALS => 'Raw Materials',
                self::EXPENSE_INVENTORY => 'Inventory Purchase',
                self::EXPENSE_PACKAGING => 'Packaging Materials',
                self::EXPENSE_FREIGHT_SHIPPING => 'Freight & Shipping',
                self::EXPENSE_IMPORT_DUTIES => 'Import Duties',
                self::EXPENSE_WAREHOUSING => 'Warehousing',
            ],
            'Professional Services' => [
                self::EXPENSE_TRAINING => 'Training & Development',
                self::EXPENSE_INSURANCE => 'Insurance',
                self::EXPENSE_BANK_CHARGES => 'Bank Charges',
            ],
            'Tax Payments' => [
                self::TAX_VAT_PAYMENT => 'VAT Payment',
                self::TAX_PAYE_PAYMENT => 'PAYE Payment',
                self::TAX_WHT_PAYMENT => 'WHT Payment',
                self::TAX_CIT_PAYMENT => 'CIT Payment',
            ],
            'VAT Exempt' => [
                self::EXEMPT_MEDICAL => 'Medical Services',
                self::EXEMPT_EDUCATION => 'Education Services',
                self::EXEMPT_PHARMACEUTICALS => 'Pharmaceuticals',
                self::EXEMPT_BASIC_FOODS => 'Basic Food Items',
                self::EXEMPT_BOOKS => 'Books & Educational Materials',
                self::EXEMPT_EXPORTS => 'Export Sales',
            ],
        ];
    }

    /**
     * Get WHT-applicable categories
     */
    public static function getWHTApplicableCategories(): array
    {
        return [
            self::EXPENSE_PROFESSIONAL_FEES,
            self::EXPENSE_CONSULTANCY,
            self::EXPENSE_TECHNICAL_SERVICES,
            self::EXPENSE_MANAGEMENT_FEES,
            self::EXPENSE_LEGAL_FEES,
            self::EXPENSE_AUDIT_FEES,
            self::EXPENSE_ACCOUNTING_FEES,
            self::EXPENSE_COMMISSION_PAID,
            self::EXPENSE_ROYALTY_PAID,
            self::EXPENSE_INTEREST_PAID,
            self::EXPENSE_DIVIDEND_PAID,
            self::EXPENSE_RENT_PAID,
            self::EXPENSE_BUILDING_CONSTRUCTION,
            self::EXPENSE_CONTRACTS,
            self::EXPENSE_DIRECTOR_FEES,
            self::EXPENSE_AGENCY_ARRANGEMENT,
        ];
    }

    /**
     * Check if this transaction is WHT-applicable
     */
    public function isWHTApplicable(): bool
    {
        return in_array($this->sub_category, self::getWHTApplicableCategories()) ||
               in_array($this->category, self::getWHTApplicableCategories());
    }

    /**
     * Get confidence label
     */
    public function getConfidenceLabelAttribute(): string
    {
        if (!$this->confidence) return 'N/A';

        if ($this->confidence >= 0.9) return 'High';
        if ($this->confidence >= 0.7) return 'Medium';
        return 'Low';
    }

    /**
     * Scope: Uncategorized transactions
     */
    public function scopeUncategorized($query)
    {
        return $query->whereNull('category')
            ->orWhere('category', 'UNCATEGORIZED');
    }

    /**
     * Scope: VAT applicable transactions
     */
    public function scopeVatApplicable($query)
    {
        return $query->where('vat_applicable', true);
    }

    /**
     * Scope: Revenue transactions
     */
    public function scopeRevenue($query)
    {
        return $query->where('category', 'REVENUE');
    }

    /**
     * Scope: Expense transactions
     */
    public function scopeExpenses($query)
    {
        return $query->where('category', 'EXPENSES');
    }

    /**
     * Scope: By period
     */
    public function scopePeriod($query, string $period)
    {
        // Period format: 2026-02
        $date = \Carbon\Carbon::createFromFormat('Y-m', $period);
        return $query->whereBetween('transaction_date', [
            $date->startOfMonth(),
            $date->copy()->endOfMonth(),
        ]);
    }

    /**
     * Scope: Needs review
     */
    public function scopeNeedsReview($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('category')
              ->orWhere('confidence', '<', 0.7)
              ->orWhere('user_verified', false);
        });
    }
}
