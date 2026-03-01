<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ComplianceDeadline extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'deadline_type',
        'period',
        'description',
        'due_date',
        'frequency',
        'required_documents',
        'status',
        'completed_at',
        'reminded_at',
        'reminder_count',
        'notes',
        'attachments',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'reminded_at' => 'datetime',
        'reminder_count' => 'integer',
        'required_documents' => 'array',
        'attachments' => 'array',
    ];

    /**
     * Get the business that owns the deadline
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get days until deadline
     */
    public function getDaysUntilAttribute(): int
    {
        return now()->diffInDays($this->due_date, false);
    }

    /**
     * Check if deadline is overdue
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'pending' && $this->due_date->isPast();
    }

    /**
     * Check if deadline is upcoming (within 14 days)
     */
    public function getIsUpcomingAttribute(): bool
    {
        return $this->status === 'pending' &&
               $this->due_date->isFuture() &&
               $this->days_until <= 14;
    }

    /**
     * Get urgency level
     */
    public function getUrgencyAttribute(): string
    {
        if ($this->is_overdue) return 'critical';
        if ($this->days_until <= 3) return 'urgent';
        if ($this->days_until <= 7) return 'high';
        if ($this->days_until <= 14) return 'medium';
        return 'low';
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        $labels = [
            'VAT' => 'VAT Return',
            'WHT' => 'Withholding Tax',
            'PAYE' => 'PAYE/Income Tax',
            'CIT' => 'Corporate Income Tax',
            'CAC_ANNUAL' => 'CAC Annual Return',
            'ITF' => 'Industrial Training Fund',
            'PENCOM' => 'Pension Contribution',
            'NSITF' => 'NSITF Contribution',
        ];

        return $labels[$this->deadline_type] ?? $this->deadline_type;
    }

    /**
     * Scope: Pending deadlines
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Overdue deadlines
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
            ->where('due_date', '<', now());
    }

    /**
     * Scope: Upcoming deadlines
     */
    public function scopeUpcoming($query, int $days = 30)
    {
        return $query->where('status', 'pending')
            ->whereBetween('due_date', [now(), now()->addDays($days)])
            ->orderBy('due_date');
    }

    /**
     * Scope: Needs reminder
     */
    public function scopeNeedsReminder($query)
    {
        return $query->where('status', 'pending')
            ->where(function ($q) {
                $q->where(function ($sq) {
                    // 14 days before
                    $sq->where('due_date', '=', now()->addDays(14)->toDateString())
                       ->where('reminder_count', '<', 1);
                })
                ->orWhere(function ($sq) {
                    // 7 days before
                    $sq->where('due_date', '=', now()->addDays(7)->toDateString())
                       ->where('reminder_count', '<', 2);
                })
                ->orWhere(function ($sq) {
                    // 3 days before
                    $sq->where('due_date', '=', now()->addDays(3)->toDateString())
                       ->where('reminder_count', '<', 3);
                })
                ->orWhere(function ($sq) {
                    // On the day
                    $sq->where('due_date', '=', now()->toDateString())
                       ->where('reminder_count', '<', 4);
                })
                ->orWhere(function ($sq) {
                    // After overdue (daily)
                    $sq->where('due_date', '<', now())
                       ->where(function ($ssq) {
                           $ssq->whereNull('reminded_at')
                              ->orWhere('reminded_at', '<', now()->subDay());
                       });
                });
            });
    }

    /**
     * Mark as completed
     */
    public function markCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark as reminded
     */
    public function markReminded(): void
    {
        $this->increment('reminder_count');
        $this->update(['reminded_at' => now()]);
    }
}
