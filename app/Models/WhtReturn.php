<?php

namespace App\Models;

use App\Traits\TracksStatusChanges;
use App\Traits\HasStandardStatus;
use App\Traits\HasTaxAuthority;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class WhtReturn extends Model
{
    use HasFactory, TracksStatusChanges, HasStandardStatus, HasTaxAuthority;

    /**
     * WHT tax authority depends on beneficiary type:
     * - company → FIRS (federal)
     * - individual → SIRS (state)
     */
    public function getDefaultTaxAuthority(): string
    {
        return $this->beneficiary_type === 'individual'
            ? self::TAX_AUTHORITY_SIRS
            : self::TAX_AUTHORITY_FIRS;
    }

    protected $fillable = [
        'business_id',
        'period',
        'total_wht_deducted',
        'transaction_count',
        'schedule_data',
        'filed_date',
        'status',
        'tax_authority',
        'beneficiary_type',
        'tax_state',
        'firs_reference',
        'notes',
    ];

    protected $casts = [
        'schedule_data' => 'array',
        'total_wht_deducted' => 'decimal:2',
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
        if (!$this->period) {
            return 'Unknown Period';
        }
        $date = \Carbon\Carbon::createFromFormat('Y-m', $this->period);
        return $date->format('F Y');
    }

    /**
     * Get formatted filed date
     */
    public function getFiledDateFormattedAttribute(): ?string
    {
        if (!$this->filed_date) {
            return null;
        }
        return \Carbon\Carbon::parse($this->filed_date)->format('M d, Y');
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
