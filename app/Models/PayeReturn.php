<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PayeReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'period',
        'total_gross_pay',
        'total_tax_deducted',
        'staff_count',
        'schedule_data',
        'filed_date',
        'status',
        'firs_reference',
        'notes',
    ];

    protected $casts = [
        'schedule_data' => 'array',
        'total_gross_pay' => 'float',
        'total_tax_deducted' => 'float',
        'staff_count' => 'integer',
        'filed_date' => 'date',
    ];

    protected $attributes = [
        'total_gross_pay' => 0,
        'total_tax_deducted' => 0,
        'staff_count' => 0,
        'status' => 'draft',
    ];

    protected $appends = [
        'period_label',
        'status_label',
        'status_color',
    ];

    /**
     * Get the business that owns the PAYE return
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the schedules for this PAYE return
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(PayeSchedule::class);
    }

    /**
     * Get the government payments
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(GovernmentPayment::class, 'return');
    }

    /**
     * Get the period label (e.g., "February 2026")
     */
    public function getPeriodLabelAttribute(): string
    {
        if (!$this->period) {
            return 'Unknown Period';
        }
        try {
            $date = \Carbon\Carbon::createFromFormat('Y-m', $this->period);
            return $date->format('F Y');
        } catch (\Exception $e) {
            return 'Invalid Period';
        }
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'filed' => 'Filed',
            'paid' => 'Paid',
            'overdue' => 'Overdue',
            default => 'Unknown',
        };
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'filed' => 'blue',
            'paid' => 'green',
            'overdue' => 'red',
            default => 'gray',
        };
    }

    /**
     * Scope for filtering by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by period
     */
    public function scopePeriod($query, string $period)
    {
        return $query->where('period', $period);
    }

    /**
     * Scope for overdue returns
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'paid')
            ->whereRaw("STR_TO_DATE(CONCAT(period, '-10'), '%Y-%m-%d') < ?", [now()->toDateString()]);
    }
}
