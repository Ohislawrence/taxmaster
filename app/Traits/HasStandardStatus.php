<?php

namespace App\Traits;

/**
 * Standardised return statuses across all tax return models.
 *
 * Standard statuses:
 *  - draft       : Return created, not yet sent to authorities
 *  - submitted   : Sent / filed with the relevant tax authority
 *  - accepted    : Acknowledged / accepted by the tax authority
 *  - paid        : Tax liability settled
 *  - rejected    : Returned / queried by the tax authority
 *  - overdue     : Past the statutory deadline and still unpaid
 */
trait HasStandardStatus
{
    /**
     * All valid statuses in lifecycle order.
     */
    public static function validStatuses(): array
    {
        return ['draft', 'submitted', 'accepted', 'paid', 'rejected', 'overdue'];
    }

    /**
     * Human-readable label for the current status.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'submitted' => 'Submitted',
            'filed' => 'Submitted', // legacy alias
            'accepted' => 'Accepted',
            'paid' => 'Paid',
            'rejected' => 'Rejected',
            'refund_pending' => 'Refund Pending',
            'overdue' => 'Overdue',
            default => ucfirst($this->status ?? 'Unknown'),
        };
    }

    /**
     * Tailwind colour token for UI badges.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'gray',
            'submitted', 'filed' => 'blue',
            'accepted' => 'indigo',
            'paid' => 'green',
            'rejected' => 'red',
            'refund_pending' => 'yellow',
            'overdue' => 'red',
            default => 'gray',
        };
    }
}
