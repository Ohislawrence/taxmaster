<?php

namespace App\Policies;

use App\Models\BusinessStaff;
use App\Models\User;

class BusinessStaffPolicy
{
    /**
     * Determine whether the user can view the staff member.
     */
    public function view(User $user, BusinessStaff $staff): bool
    {
        return $user->ownedBusiness?->id === $staff->business_id;
    }

    /**
     * Determine whether the user can create staff members.
     */
    public function create(User $user): bool
    {
        return $user->ownedBusiness !== null;
    }

    /**
     * Determine whether the user can update the staff member.
     */
    public function update(User $user, BusinessStaff $staff): bool
    {
        return $user->ownedBusiness?->id === $staff->business_id;
    }

    /**
     * Determine whether the user can delete the staff member.
     */
    public function delete(User $user, BusinessStaff $staff): bool
    {
        return $user->ownedBusiness?->id === $staff->business_id;
    }
}
