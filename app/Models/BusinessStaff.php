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
        'taxable_income',
        'monthly_tax_due',
        'annual_tax_due',
        'metadata',
    ];

    protected $casts = [
        'monthly_salary' => 'decimal:2',
        'taxable_income' => 'decimal:2',
        'monthly_tax_due' => 'decimal:2',
        'annual_tax_due' => 'decimal:2',
        'date_employed' => 'date',
        'date_relieved' => 'date',
        'metadata' => 'array',
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
}
