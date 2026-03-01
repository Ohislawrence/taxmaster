<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAgentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'action_type',
        'ai_provider',
        'prompt',
        'response',
        'tokens_used',
        'cost',
        'status',
        'error_message',
        'metadata',
    ];

    protected $casts = [
        'cost' => 'decimal:4',
        'tokens_used' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Get the business
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Scope: Get completed logs
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope: Get failed logs
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
