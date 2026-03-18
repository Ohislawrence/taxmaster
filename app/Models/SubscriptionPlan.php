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
        'max_bank_accounts',
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

    /**
     * Ensure `features` attribute is always an array of display strings.
     * If the DB `features` column is empty, derive a sensible list from flags.
     */
    public function getFeaturesAttribute($value): array
    {
        // If features JSON exists, cast and return it
        if ($value) {
            $decoded = json_decode($value, true);
            if (is_array($decoded) && count($decoded) > 0) {
                return $decoded;
            }
        }

        // Fallback: build feature list from boolean flags and capacities
        $features = [];

        // Capacity / numeric features
        if ($this->max_returns_per_year) {
            $features[] = $this->max_returns_per_year === 9999 ? 'Unlimited tax return filing' : "{$this->max_returns_per_year} Tax Returns per Year";
        }

        if ($this->max_staff_members) {
            $features[] = $this->max_staff_members === 999 ? 'Unlimited staff members' : "Up to {$this->max_staff_members} staff members";
        }

        if ($this->storage_gb) {
            $features[] = "{$this->storage_gb} GB storage";
        }

        // Feature flags
        if ($this->ai_analysis_included) {
            $features[] = 'AI tax analysis & insights';
        }

        if ($this->payment_automation) {
            $features[] = 'Automated payment processing';
        }

        if ($this->priority_support) {
            $features[] = 'Priority support';
        }

        if ($this->custom_branding) {
            $features[] = 'Custom branding';
        }

        return $features;
    }
}
