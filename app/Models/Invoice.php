<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Transaction;

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

        // Create a matching Transaction so tax calculations include this sale.
        try {
            // Avoid duplicate transactions by checking known references or similar recent amounts
            $exists = false;
            if ($paymentReference) {
                $exists = Transaction::where('mono_transaction_id', $paymentReference)
                    ->where('business_id', $this->business_id)
                    ->exists();
            }

            if (! $exists) {
                // Also check for a recent transaction with same amount to reduce duplicates
                $recentMatch = Transaction::where('business_id', $this->business_id)
                    ->where('amount', $this->total)
                    ->whereBetween('transaction_date', [now()->subDays(2), now()->addDays(2)])
                    ->exists();

                if (! $recentMatch) {
                    $bankAccountId = $this->data['bank_account_id'] ?? null;

                    Transaction::create([
                        'business_id' => $this->business_id,
                        'bank_account_id' => $bankAccountId,
                        'mono_transaction_id' => $paymentReference,
                        'type' => 'credit',
                        'amount' => $this->total,
                        'currency' => 'NGN',
                        'description' => 'Payment received for invoice ' . ($this->invoice_number ?? $this->id),
                        'counterparty' => $this->business?->name ?? null,
                        'transaction_date' => now(),
                        'category' => 'REVENUE',
                        'sub_category' => ($this->tax && $this->tax > 0) ? 'VAT_OUTPUT' : 'REVENUE',
                        'confidence' => 1.00,
                        'vat_applicable' => ($this->tax && $this->tax > 0),
                        'is_business_expense' => false,
                        'user_verified' => true,
                        'meta' => ['invoice_id' => $this->id, 'invoice_number' => $this->invoice_number],
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Don't break flow if transaction creation fails; log for later
            \Illuminate\Support\Facades\Log::error('Failed to create transaction for paid invoice', ['invoice_id' => $this->id, 'error' => $e->getMessage()]);
        }
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
