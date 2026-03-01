<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'monthly_price',
        'annual_price',
        'max_staff_members',
        'max_returns_per_year',
        'storage_gb',
        'ai_analysis_included',
        'payment_automation',
        'priority_support',
        'custom_branding',
        'features',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'monthly_price' => 'float',
        'annual_price' => 'float',
        'ai_analysis_included' => 'boolean',
        'payment_automation' => 'boolean',
        'priority_support' => 'boolean',
        'custom_branding' => 'boolean',
        'is_active' => 'boolean',
        'features' => 'array',
    ];

    /**
     * Get all subscriptions for this plan
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(BusinessSubscription::class, 'plan_id');
    }

    /**
     * Get active subscriptions for this plan
     */
    public function activeSubscriptions(): HasMany
    {
        return $this->subscriptions()->where('status', 'active');
    }

    /**
     * Scope: Get active plans
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('display_order');
    }

    /**
     * Scope: Get plans ordered by display order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }

    /**
     * Check if this is the free plan
     */
    public function isFree(): bool
    {
        return $this->monthly_price == 0 && $this->annual_price == 0;
    }

    /**
     * Get feature list as array
     */
    public function getFeaturesList(): array
    {
        return $this->features ?? [
            'AI Analysis' => $this->ai_analysis_included,
            'Payment Automation' => $this->payment_automation,
            'Priority Support' => $this->priority_support,
            'Custom Branding' => $this->custom_branding,
        ];
    }
}
