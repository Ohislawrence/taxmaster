<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'plan_id',
        'plan_type',
        'monthly_price',
        'annual_price',
        'max_staff_members',
        'max_returns_per_year',
        'ai_analysis_included',
        'payment_automation',
        'billing_cycle',
        'status',
        'payment_status',
        'payment_method',
        'transaction_reference',
        'started_at',
        'renews_at',
        'cancelled_at',
        'trial_days',
        'trial_ends_at',
        'grace_days',
        'grace_ends_at',
        'payment_failures',
        'last_payment_failure_at',
        'metadata',
    ];

    protected $casts = [
        'monthly_price' => 'float',
        'annual_price' => 'float',
        'ai_analysis_included' => 'boolean',
        'payment_automation' => 'boolean',
        'started_at' => 'datetime',
        'renews_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'grace_ends_at' => 'datetime',
        'last_payment_failure_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the business
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the subscription plan
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    /**
     * Scope: Get active subscriptions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Check if subscription is within renewal period
     */
    public function shouldRenew(): bool
    {
        return $this->renews_at <= now();
    }

    /**
     * Check if subscription is in trial period
     */
    public function isInTrial(): bool
    {
        return $this->status === 'active' && $this->trial_ends_at && now()->lt($this->trial_ends_at);
    }

    /**
     * Check if subscription is in grace period
     */
    public function isInGracePeriod(): bool
    {
        return $this->grace_ends_at && now()->lt($this->grace_ends_at);
    }

    /**
     * Get days remaining in trial
     */
    public function getDaysRemainingInTrialAttribute(): ?int
    {
        if (!$this->isInTrial()) {
            return null;
        }
        return (int) now()->diffInDays($this->trial_ends_at);
    }

    /**
     * Get days remaining in grace period
     */
    public function getDaysRemainingInGraceAttribute(): ?int
    {
        if (!$this->isInGracePeriod()) {
            return null;
        }
        return (int) now()->diffInDays($this->grace_ends_at);
    }

    /**
     * Record a payment failure
     */
    public function recordPaymentFailure(): void
    {
        $this->update([
            'payment_failures' => $this->payment_failures + 1,
            'last_payment_failure_at' => now(),
            'grace_ends_at' => now()->addDays($this->grace_days),
        ]);
    }

    /**
     * Check if grace period has expired
     */
    public function hasGracePeriodExpired(): bool
    {
        return $this->grace_ends_at && now()->gt($this->grace_ends_at);
    }
}

