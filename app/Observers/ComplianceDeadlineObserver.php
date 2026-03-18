<?php

namespace App\Observers;

use App\Models\ComplianceDeadline;
use App\Models\Business;
use App\Services\ComplianceService;

class ComplianceDeadlineObserver
{
    protected function clearForDeadline(ComplianceDeadline $deadline): void
    {
        $business = $deadline->business ?? Business::find($deadline->business_id);
        if ($business) {
            app(ComplianceService::class)->clearComplianceStatusCache($business);
        }
    }

    public function created(ComplianceDeadline $deadline): void
    {
        $this->clearForDeadline($deadline);
    }

    public function updated(ComplianceDeadline $deadline): void
    {
        $this->clearForDeadline($deadline);
    }

    public function deleted(ComplianceDeadline $deadline): void
    {
        $this->clearForDeadline($deadline);
    }

    public function restored(ComplianceDeadline $deadline): void
    {
        $this->clearForDeadline($deadline);
    }
}
