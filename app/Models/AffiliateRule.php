<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateRule extends Model
{
    protected $table = 'affiliate_rules';

    protected $fillable = [
        'key',
        'type',
        'mode',
        'value',
        'applies_to',
        'plan_slug',
        'active',
        'meta',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'active' => 'boolean',
        'meta' => 'array',
    ];
}
