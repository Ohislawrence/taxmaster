<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuickBooksSyncLog extends Model
{
    use HasFactory;
    protected $table = 'quickbooks_sync_logs';

    protected $fillable = [
        'quickbooks_connection_id',
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
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'sync_from_date' => 'date',
        'sync_to_date' => 'date',
        'errors' => 'array',
        'summary' => 'array',
    ];

    /**
     * Get the connection that owns this log
     */
    public function connection(): BelongsTo
    {
        return $this->belongsTo(QuickBooksConnection::class, 'quickbooks_connection_id');
    }

    /**
     * Mark sync as started
     */
    public function markStarted(): void
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);
    }

    /**
     * Mark sync as completed
     */
    public function markCompleted(array $summary = []): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'duration_seconds' => $this->started_at ? now()->diffInSeconds($this->started_at) : null,
            'summary' => $summary,
        ]);
    }

    /**
     * Mark sync as failed
     */
    public function markFailed(string $error, array $errors = []): void
    {
        $this->update([
            'status' => 'failed',
            'completed_at' => now(),
            'duration_seconds' => $this->started_at ? now()->diffInSeconds($this->started_at) : null,
            'error_message' => $error,
            'errors' => $errors,
        ]);
    }

    /**
     * Increment processed count
     */
    public function incrementProcessed(string $result = 'success'): void
    {
        $this->increment('processed_records');

        match($result) {
            'success' => $this->increment('success_count'),
            'failure' => $this->increment('failure_count'),
            'skipped' => $this->increment('skipped_count'),
            default => null,
        };
    }

    /**
     * Calculate progress percentage
     */
    public function getProgressPercentage(): int
    {
        if ($this->total_records === 0) {
            return 0;
        }

        return (int) (($this->processed_records / $this->total_records) * 100);
    }

    /**
     * Check if sync is in progress
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * Check if sync was successful
     */
    public function wasSuccessful(): bool
    {
        return $this->status === 'completed' && $this->failure_count === 0;
    }
}
