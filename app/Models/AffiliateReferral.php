<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateReferral extends Model
{
    protected $fillable = [
        'accountant_id',
        'business_id',
        'source',
        'commission_percent',
        'starts_at',
        'expires_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'commission_percent' => 'decimal:2',
    ];

    public function accountant()
    {
        return $this->belongsTo(User::class, 'accountant_id');
    }

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function payouts()
    {
        return $this->hasMany(AffiliatePayout::class, 'referral_id');
    }
}
