<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Business extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'owner_id',
        'name',
        'slug',
        'created_by_accountant_id',
        'registration_number',
        'description',
        'business_type',
        'email',
        'phone',
        'country',
        'state',
        'city',
        'address',
        'tax_identification_number',
        'is_vat_exempt',
        'vat_exempt_category',
        'vat_exempt_reason',
        'annual_revenue',
        'industry',
        'logo_path',
        'status',
        'email_verified',
        'email_verified_at',
        'registration_number_hash',
        'tax_identification_number_hash',
        'settings',
        'accounting_year_end',
        'incorporation_date',
        'has_staff',
        'staff_count',
        'mono_customer_id',
        'billing_managed_by_platform',
    ];

    protected $casts = [
        'created_by_accountant_id' => 'integer',
        'email_verified_at' => 'datetime',
        'settings' => 'array',
        'accounting_year_end' => 'date',
        'incorporation_date' => 'date',
        'has_staff' => 'boolean',
        'is_vat_exempt' => 'boolean',
        // NDPA 2023 — encrypt PII at rest
        'tax_identification_number' => 'encrypted',
        'registration_number' => 'encrypted',
        'billing_managed_by_platform' => 'boolean',
    ];

    /**
     * Who (accountant) created this business, if any
     */
    public function createdByAccountant()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by_accountant_id');
    }

    /**
     * Get the owner of the business
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get all staff members of the business
     */
    public function staff(): HasMany
    {
        return $this->hasMany(BusinessStaff::class);
    }

    /**
     * Get all tax returns for the business
     */
    public function taxReturns(): HasMany
    {
        return $this->hasMany(TaxReturn::class);
    }

    /**
     * Get all payments for the business
     */
    public function taxPayments(): HasMany
    {
        return $this->hasMany(TaxPayment::class);
    }

    /**
     * Get the subscription for the business
     */
    public function subscription(): HasMany
    {
        return $this->hasMany(BusinessSubscription::class);
    }

    /**
     * Alias for subscription() for convenience
     */
    public function subscriptions(): HasMany
    {
        return $this->subscription();
    }

    /**
     * Get active subscription
     */
    public function activeSubscription(): ?BusinessSubscription
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where('renews_at', '>', now())
            ->latest()
            ->first();
    }

    /**
     * Get activity logs
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(BusinessActivityLog::class);
    }

    /**
     * Get AI configuration
     */
    public function aiConfig(): HasMany
    {
        return $this->hasMany(AiConfiguration::class);
    }

    /**
     * Accountants assigned to this business
     */
    public function accountants()
    {
        return $this->belongsToMany(\App\Models\User::class, 'accountant_business', 'business_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Get AI agent logs
     */
    public function aiLogs(): HasMany
    {
        return $this->hasMany(AiAgentLog::class);
    }

    /**
     * Get bank accounts
     */
    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class);
    }

    /**
     * Get transactions
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get financial positions (balance sheet snapshots)
     */
    public function financialPositions(): HasMany
    {
        return $this->hasMany(FinancialPosition::class);
    }

    /**
     * Get compliance deadlines
     */
    public function complianceDeadlines(): HasMany
    {
        return $this->hasMany(ComplianceDeadline::class);
    }

    /**
     * Get VAT returns
     */
    public function vatReturns(): HasMany
    {
        return $this->hasMany(VATReturn::class);
    }

    /**
     * Get PAYE returns
     */
    public function payeReturns(): HasMany
    {
        return $this->hasMany(PayeReturn::class);
    }

    /**
     * Get WHT returns
     */
    public function whtReturns(): HasMany
    {
        return $this->hasMany(WhtReturn::class);
    }

    /**
     * Get CIT returns
     */
    public function citReturns(): HasMany
    {
        return $this->hasMany(CitReturn::class);
    }

    /**
     * Get government payments
     */
    public function governmentPayments(): HasMany
    {
        return $this->hasMany(GovernmentPayment::class);
    }

    /**
     * Get active bank accounts
     */
    public function activeBankAccounts(): HasMany
    {
        return $this->bankAccounts()->where('is_active', true);
    }

    /**
     * Get Get Started progress tracking
     */
    public function getStartedProgress()
    {
        return $this->hasOne(GetStartedProgress::class);
    }

    /**
     * Get upcoming deadlines (next 30 days)
     */
    public function upcomingDeadlines(): HasMany
    {
        return $this->complianceDeadlines()
            ->where('status', 'pending')
            ->whereBetween('due_date', [now(), now()->addDays(30)])
            ->orderBy('due_date');
    }
}
