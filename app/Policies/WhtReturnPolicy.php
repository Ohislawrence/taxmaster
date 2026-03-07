<?php

namespace App\Policies;

use App\Models\WhtReturn;
use App\Models\User;

class WhtReturnPolicy
{
    /**
     * Determine if the user can view the WHT return
     */
    public function view(User $user, WhtReturn $whtReturn): bool
    {
        return $user->ownedBusiness?->id === $whtReturn->business_id;
    }

    /**
     * Determine if the user can create a WHT return
     */
    public function create(User $user): bool
    {
        return $user->ownedBusiness !== null;
    }

    /**
     * Determine if the user can update the WHT return
     */
    public function update(User $user, WhtReturn $whtReturn): bool
    {
        return $user->ownedBusiness?->id === $whtReturn->business_id;
    }

    /**
     * Determine if the user can delete the WHT return
     */
    public function delete(User $user, WhtReturn $whtReturn): bool
    {
        return $user->ownedBusiness?->id === $whtReturn->business_id
            && $whtReturn->status === 'draft';
    }
}
