<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayeSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'paye_return_id',
        'business_staff_id',
        'gross_pay',
        'allowances',
        'tax_reliefs',
        'taxable_income',
        'paye_due',
        'cumulative_gross',
        'cumulative_tax',
    ];

    protected $casts = [
        'allowances' => 'array',
        'tax_reliefs' => 'array',
        'gross_pay' => 'decimal:2',
        'taxable_income' => 'decimal:2',
        'paye_due' => 'decimal:2',
        'cumulative_gross' => 'decimal:2',
        'cumulative_tax' => 'decimal:2',
    ];

    /**
     * Get the PAYE return this schedule belongs to
     */
    public function payeReturn(): BelongsTo
    {
        return $this->belongsTo(PayeReturn::class);
    }

    /**
     * Get the staff member
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(BusinessStaff::class, 'business_staff_id');
    }

    /**
     * Get total allowances
     */
    public function getTotalAllowancesAttribute(): float
    {
        if (!$this->allowances) {
            return 0;
        }
        return array_sum(array_values($this->allowances));
    }

    /**
     * Get total reliefs
     */
    public function getTotalReliefsAttribute(): float
    {
        if (!$this->tax_reliefs) {
            return 0;
        }
        return array_sum(array_values($this->tax_reliefs));
    }
}
