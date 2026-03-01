<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_subscription_id',
        'business_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'subtotal',
        'tax',
        'total',
        'period_start',
        'period_end',
        'status',
        'paid_at',
        'payment_reference',
        'pdf_path',
        'data',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
        'data' => 'array',
    ];

    /**
     * Get the subscription
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(BusinessSubscription::class);
    }

    /**
     * Get the business
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Check if invoice is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status !== 'paid' && $this->due_date->isPast();
    }

    /**
     * Mark invoice as sent
     */
    public function markSent(): void
    {
        $this->update(['status' => 'sent']);
    }

    /**
     * Mark invoice as paid
     */
    public function markPaid(string $paymentReference): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_reference' => $paymentReference,
        ]);
    }

    /**
     * Scope: Unpaid invoices
     */
    public function scopeUnpaid($query)
    {
        return $query->where('status', '!=', 'paid')->where('status', '!=', 'cancelled');
    }

    /**
     * Scope: Overdue invoices
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'paid')
            ->where('due_date', '<', now()->toDateString());
    }

    /**
     * Generate next invoice number
     */
    public static function generateInvoiceNumber(): string
    {
        $lastInvoice = self::orderBy('id', 'desc')->first();
        $nextNumber = ($lastInvoice?->id ?? 0) + 1;
        return 'INV-' . now()->format('Y') . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
