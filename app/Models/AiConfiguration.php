<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'api_provider',
        'api_key',
        'model',
        'max_tokens',
        'temperature',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'temperature' => 'decimal:2',
        'is_active' => 'boolean',
        'settings' => 'array',
        'max_tokens' => 'integer',
    ];

    /**
     * Get the business that owns this configuration
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get active configuration
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
