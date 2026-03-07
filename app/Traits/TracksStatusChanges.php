<?php

namespace App\Traits;

use App\Models\BusinessActivityLog;

/**
 * Trait to automatically log status changes on return models.
 *
 * The model must have:
 *  - a 'status' attribute
 *  - a 'business_id' attribute
 *
 * Usage: `use TracksStatusChanges;` in your model class.
 */
trait TracksStatusChanges
{
    /**
     * Boot the trait — register the model event listener.
     */
    public static function bootTracksStatusChanges(): void
    {
        static::updating(function ($model) {
            if ($model->isDirty('status')) {
                $model->logStatusChange(
                    $model->getOriginal('status'),
                    $model->status
                );
            }
        });
    }

    /**
     * Write an entry to the business_activity_logs table.
     */
    protected function logStatusChange(?string $from, string $to): void
    {
        $returnType = class_basename(static::class);
        $period = $this->period ?? $this->tax_period ?? $this->id;

        BusinessActivityLog::create([
            'business_id' => $this->business_id,
            'user_id' => auth()->id(),
            'action' => 'return_status_changed',
            'subject_type' => static::class,
            'subject_id' => $this->id,
            'description' => "{$returnType} ({$period}) status changed from '{$from}' to '{$to}'",
            'changes' => [
                'field' => 'status',
                'from' => $from,
                'to' => $to,
                'changed_at' => now()->toIso8601String(),
            ],
            'ip_address' => request()?->ip(),
        ]);
    }

    /**
     * Get audit trail for this return.
     */
    public function auditTrail()
    {
        return BusinessActivityLog::where('subject_type', static::class)
            ->where('subject_id', $this->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
