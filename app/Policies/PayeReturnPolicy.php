<?php

namespace App\Policies;

use App\Models\PayeReturn;
use App\Models\User;

class PayeReturnPolicy
{
    /**
     * Determine if the user can view the PAYE return
     */
    public function view(User $user, PayeReturn $payeReturn): bool
    {
        return $user->business_id === $payeReturn->business_id;
    }

    /**
     * Determine if the user can update the PAYE return
     */
    public function update(User $user, PayeReturn $payeReturn): bool
    {
        return $user->business_id === $payeReturn->business_id;
    }

    /**
     * Determine if the user can delete the PAYE return
     */
    public function delete(User $user, PayeReturn $payeReturn): bool
    {
        return $user->business_id === $payeReturn->business_id
            && $payeReturn->status === 'draft';
    }
}
