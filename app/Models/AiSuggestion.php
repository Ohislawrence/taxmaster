<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AiSuggestion extends Model
{
    protected $fillable = [
        'type',
        'suggestible_type',
        'suggestible_id',
        'data',
        'confidence',
        'status',
        'applied_at',
        'user_feedback',
    ];

    protected $casts = [
        'data' => 'array',
        'confidence' => 'float',
        'applied_at' => 'datetime',
    ];

    /**
     * Get the parent suggestible model
     */
    public function suggestible(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope: Get pending suggestions
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Get applied suggestions
     */
    public function scopeApplied($query)
    {
        return $query->where('status', 'applied');
    }

    /**
     * Scope: Get by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Mark as applied
     */
    public function markAsApplied()
    {
        return $this->update([
            'status' => 'applied',
            'applied_at' => now(),
        ]);
    }

    /**
     * Mark as dismissed
     */
    public function markAsDismissed()
    {
        return $this->update([
            'status' => 'dismissed',
        ]);
    }

    /**
     * Add user feedback
     */
    public function addFeedback($feedback)
    {
        return $this->update([
            'user_feedback' => $feedback,
            'status' => 'reviewed',
        ]);
    }
}
