<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplianceReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'tax_type_id',
        'tax_return_id',
        'reminder_type',
        'due_date',
        'reminder_date',
        'status',
        'notification_channel',
        'sent_at',
        'message',
    ];

    protected $casts = [
        'due_date' => 'date',
        'reminder_date' => 'date',
        'sent_at' => 'datetime',
    ];

    /**
     * Get the business this reminder belongs to
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the tax type
     */
    public function taxType(): BelongsTo
    {
        return $this->belongsTo(TaxType::class);
    }

    /**
     * Get the tax return if linked
     */
    public function taxReturn(): BelongsTo
    {
        return $this->belongsTo(TaxReturn::class);
    }

    /**
     * Scope: Get pending reminders
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Get reminders due today or earlier
     */
    public function scopeDueForSending($query)
    {
        return $query->where('reminder_date', '<=', now()->toDateString())
            ->where('status', 'pending');
    }

    /**
     * Mark as sent
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Dismiss reminder
     */
    public function dismiss(): void
    {
        $this->update(['status' => 'dismissed']);
    }
}
