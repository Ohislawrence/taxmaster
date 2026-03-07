<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessStaff extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'business_staff';

    protected $fillable = [
        'business_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'tax_identification_number',
        'monthly_salary',
        'employment_type',
        'designation',
        'date_employed',
        'date_relieved',
        'status',
        'tax_state',
        'taxable_income',
        'monthly_tax_due',
        'annual_tax_due',
        'metadata',
    ];

    protected $appends = ['full_name'];

    protected $casts = [
        'monthly_salary' => 'decimal:2',
        'taxable_income' => 'decimal:2',
        'monthly_tax_due' => 'decimal:2',
        'annual_tax_due' => 'decimal:2',
        'date_employed' => 'date',
        'date_relieved' => 'date',
        'metadata' => 'array',
        // NDPA 2023 — encrypt PII at rest
        'tax_identification_number' => 'encrypted',
    ];

    /**
     * Get the business that owns the staff member
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get full name attribute
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get job title attribute (alias for designation)
     */
    public function getJobTitleAttribute(): string
    {
        return $this->designation;
    }

    /**
     * Get the tax state name (e.g., "Lagos", "FCT").
     * Falls back to business state if staff has no explicit tax_state.
     */
    public function getTaxStateNameAttribute(): string
    {
        $code = $this->tax_state ?? $this->business?->state;
        if (!$code) {
            return 'Not Set';
        }
        return config("nigerian_states.state_options.{$code}", $code);
    }

    /**
     * Get the effective tax state code.
     * Falls back to business state if not explicitly set.
     */
    public function getEffectiveTaxStateAttribute(): ?string
    {
        return $this->tax_state ?? $this->business?->state;
    }
}
