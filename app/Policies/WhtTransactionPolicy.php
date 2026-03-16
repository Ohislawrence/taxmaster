<?php

namespace App\Policies;

use App\Models\WhtTransaction;
use App\Models\User;

class WhtTransactionPolicy
{
    /**
     * Determine if the user can view the WHT transaction
     */
    public function view(User $user, WhtTransaction $whtTransaction): bool
    {
        return $user->managesBusiness($whtTransaction->business_id);
    }

    /**
     * Determine if the user can create a WHT transaction
     */
    public function create(User $user): bool
    {
        return $user->ownedBusiness !== null || $user->businesses()->exists();
    }

    /**
     * Determine if the user can update the WHT transaction
     */
    public function update(User $user, WhtTransaction $whtTransaction): bool
    {
        return $user->managesBusiness($whtTransaction->business_id);
    }

    /**
     * Determine if the user can delete the WHT transaction
     */
    public function delete(User $user, WhtTransaction $whtTransaction): bool
    {
        return $user->managesBusiness($whtTransaction->business_id);
    }
}
