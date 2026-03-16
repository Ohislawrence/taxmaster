<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\AffiliatePayout;

class AffiliatePayoutPaid extends Notification
{
    use Queueable;

    public AffiliatePayout $payout;

    public function __construct(AffiliatePayout $payout)
    {
        $this->payout = $payout;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => 'Your affiliate payout of ₦' . number_format($this->payout->amount, 2) . ' has been paid.',
            'payout_id' => $this->payout->id,
            'paid_at' => $this->payout->paid_at,
        ];
    }
}
