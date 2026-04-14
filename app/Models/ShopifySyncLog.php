<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopifySyncLog extends Model
{
    protected $fillable = [
        'shopify_connection_id',
        'sync_type',
        'entity_type',
        'status',
        'total_records',
        'processed_records',
        'success_count',
        'failure_count',
        'skipped_count',
        'started_at',
        'completed_at',
        'duration_seconds',
        'error_message',
        'errors',
        'sync_from_date',
        'sync_to_date',
        'summary',
        'metadata',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'sync_from_date' => 'date',
        'sync_to_date' => 'date',
        'errors' => 'array',
        'summary' => 'array',
        'metadata' => 'array',
        'total_records' => 'integer',
        'processed_records' => 'integer',
        'success_count' => 'integer',
        'failure_count' => 'integer',
        'skipped_count' => 'integer',
    ];

    /**
     * Get the Shopify connection
     */
    public function shopifyConnection(): BelongsTo
    {
        return $this->belongsTo(ShopifyConnection::class);
    }

    /**
     * Check if sync is complete
     */
    public function isComplete(): bool
    {
        return in_array($this->status, ['completed', 'failed']);
    }

    /**
     * Check if sync was successful
     */
    public function wasSuccessful(): bool
    {
        return $this->status === 'completed' && $this->failure_count === 0;
    }

    /**
     * Get progress percentage
     */
    public function getProgressPercentage(): int
    {
        if ($this->total_records === 0) {
            return 0;
        }
        return (int) (($this->processed_records / $this->total_records) * 100);
    }
}
