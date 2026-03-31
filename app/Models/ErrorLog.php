<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    protected $fillable = [
        'exception_class',
        'message',
        'file',
        'line',
        'trace',
        'url',
        'method',
        'user_id',
        'user_agent',
        'ip_address',
        'context',
        'resolved_at',
        'resolved_by',
        'severity',
    ];

    protected $casts = [
        'trace' => 'array',
        'context' => 'array',
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function scopeUnresolved($query)
    {
        return $query->whereNull('resolved_at');
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    public function markAsResolved($userId)
    {
        $this->update([
            'resolved_at' => now(),
            'resolved_by' => $userId,
        ]);
    }
}
