<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class WhtReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'period',
        'total_wht_deducted',
        'transaction_count',
        'schedule_data',
        'filed_date',
        'status',
        'firs_reference',
        'notes',
    ];

    protected $casts = [
        'schedule_data' => 'array',
        'total_wht_deducted' => 'decimal:2',
        'filed_date' => 'date',
    ];

    /**
     * Get the business that owns the WHT return
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the government payments
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(GovernmentPayment::class, 'return');
    }

    /**
     * Get the period label
     */
    public function getPeriodLabelAttribute(): string
    {
        $date = \Carbon\Carbon::createFromFormat('Y-m', $this->period);
        return $date->format('F Y');
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
     * Get status color
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
     * Scope for overdue returns
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'paid')
            ->whereRaw("STR_TO_DATE(CONCAT(period, '-21'), '%Y-%m-%d') < ?", [now()->toDateString()]);
    }
}
