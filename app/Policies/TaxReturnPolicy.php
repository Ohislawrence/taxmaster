<?php

namespace App\Policies;

use App\Models\TaxReturn;
use App\Models\User;

class TaxReturnPolicy
{
    /**
     * Determine whether the user can view the tax return
     */
    public function view(User $user, TaxReturn $taxReturn): bool
    {
        // User must own the business that this tax return belongs to
        return $user->managesBusiness($taxReturn->business_id);
    }

    /**
     * Determine whether the user can create a tax return
     */
    public function create(User $user): bool
    {
        // Any authenticated user with a business can create
        return $user->ownedBusiness !== null || $user->businesses()->exists();
    }

    /**
     * Determine whether the user can update the tax return
     */
    public function update(User $user, TaxReturn $taxReturn): bool
    {
        // Can only update if in draft status and user owns the business
        if ($taxReturn->status !== 'draft') {
            return false;
        }

        return $user->managesBusiness($taxReturn->business_id);
    }

    /**
     * Determine whether the user can delete the tax return
     */
    public function delete(User $user, TaxReturn $taxReturn): bool
    {
        // Can only delete draft returns and user must own the business
        if ($taxReturn->status !== 'draft') {
            return false;
        }

        return $user->managesBusiness($taxReturn->business_id);
    }
}
