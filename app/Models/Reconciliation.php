<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reconciliation extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'invoice_id',
        'transaction_id',
        'match_method',
        'confidence',
        'status',
        'matched_at',
        'data',
    ];

    protected $casts = [
        'confidence' => 'decimal:2',
        'matched_at' => 'datetime',
        'data' => 'array',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
