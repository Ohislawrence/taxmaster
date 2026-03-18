<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use App\Models\TaxReturn;
use App\Models\ComplianceDeadline;
use App\Observers\TaxReturnObserver;
use App\Observers\ComplianceDeadlineObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enforce HTTPS in production for NDPA 2023 compliance
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Register model observers to invalidate compliance cache when data changes
        TaxReturn::observe(TaxReturnObserver::class);
        ComplianceDeadline::observe(ComplianceDeadlineObserver::class);
    }
}
