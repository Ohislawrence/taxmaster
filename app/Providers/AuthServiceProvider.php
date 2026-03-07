<?php

namespace App\Providers;

use App\Models\BankAccount;
use App\Models\BusinessStaff;
use App\Models\TaxReturn;
use App\Models\Transaction;
use App\Policies\BankAccountPolicy;
use App\Policies\BusinessStaffPolicy;
use App\Policies\TaxReturnPolicy;
use App\Policies\TransactionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        TaxReturn::class => TaxReturnPolicy::class,
        BusinessStaff::class => BusinessStaffPolicy::class,
        BankAccount::class => BankAccountPolicy::class,
        Transaction::class => TransactionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
