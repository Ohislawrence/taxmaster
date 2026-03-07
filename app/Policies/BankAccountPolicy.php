<?php

namespace App\Policies;

use App\Models\BankAccount;
use App\Models\User;

class BankAccountPolicy
{
    /**
     * Determine whether the user can view the bank account.
     */
    public function view(User $user, BankAccount $bankAccount): bool
    {
        return $user->ownedBusiness?->id === $bankAccount->business_id;
    }

    /**
     * Determine whether the user can update the bank account.
     */
    public function update(User $user, BankAccount $bankAccount): bool
    {
        return $user->ownedBusiness?->id === $bankAccount->business_id;
    }

    /**
     * Determine whether the user can delete the bank account.
     */
    public function delete(User $user, BankAccount $bankAccount): bool
    {
        return $user->ownedBusiness?->id === $bankAccount->business_id;
    }
}
