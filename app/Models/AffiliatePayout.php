<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliatePayout extends Model
{
    protected $fillable = [
        'referral_id',
        'business_subscription_id',
        'amount',
        'paid',
        'paid_at',
        'approved',
    ];

    protected $casts = [
        'paid' => 'boolean',
        'approved' => 'boolean',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function referral()
    {
        return $this->belongsTo(AffiliateReferral::class, 'referral_id');
    }

    public function subscription()
    {
        return $this->belongsTo(BusinessSubscription::class, 'business_subscription_id');
    }
}
