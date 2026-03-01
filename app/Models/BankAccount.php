<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'bank_name',
        'account_name',
        'account_number',
        'currency',
        'mono_account_id',
        'mono_access_token',
        'balance',
        'last_synced_at',
        'is_active',
        'auto_sync',
        'meta',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'last_synced_at' => 'datetime',
        'is_active' => 'boolean',
        'auto_sync' => 'boolean',
        'meta' => 'array',
        'mono_access_token' => 'encrypted',
        'token_encrypted' => 'boolean',
    ];

    protected $hidden = [
        'mono_access_token',
    ];

    /**
     * Get the business that owns the bank account
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get transactions for this account
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Check if account needs sync
     */
    public function needsSync(): bool
    {
        if (!$this->last_synced_at) {
            return true;
        }

        // Sync if last sync was more than 6 hours ago
        return $this->last_synced_at->lt(now()->subHours(6));
    }

    /**
     * Get masked account number
     */
    public function getMaskedAccountNumberAttribute(): string
    {
        $number = $this->account_number;
        if (strlen($number) <= 4) {
            return $number;
        }
        return str_repeat('*', strlen($number) - 4) . substr($number, -4);
    }

    /**
     * Scope: Active accounts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Accounts needing sync
     */
    public function scopeNeedsSync($query)
    {
        return $query->where('auto_sync', true)
            ->where(function ($q) {
                $q->whereNull('last_synced_at')
                  ->orWhere('last_synced_at', '<', now()->subHours(6));
            });
    }
}
