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
        'annual_revenue',
        'industry',
        'logo_path',
        'status',
        'email_verified',
        'email_verified_at',
        'settings',
        'accounting_year_end',
        'incorporation_date',
        'has_staff',
        'staff_count',
        'mono_customer_id',
    ];

    protected $casts = [
        'email_verified' => 'boolean',
        'email_verified_at' => 'datetime',
        'settings' => 'array',
        'accounting_year_end' => 'date',
        'incorporation_date' => 'date',
        'has_staff' => 'boolean',
        // NDPA 2023 — encrypt PII at rest
        'tax_identification_number' => 'encrypted',
        'registration_number' => 'encrypted',
    ];

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
