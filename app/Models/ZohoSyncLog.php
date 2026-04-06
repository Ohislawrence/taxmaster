<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZohoSyncLog extends Model
{
    protected $fillable = [
        'zoho_connection_id',
        'sync_type',
        'status',
        'started_at',
        'completed_at',
        'duration_seconds',
        'success_count',
        'failure_count',
        'error_message',
        'metadata',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the Zoho connection
     */
    public function zohoConnection(): BelongsTo
    {
        return $this->belongsTo(ZohoConnection::class);
    }
}
