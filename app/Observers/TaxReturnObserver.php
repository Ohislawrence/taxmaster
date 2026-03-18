<?php

namespace App\Observers;

use App\Models\TaxReturn;
use App\Models\Business;
use App\Services\ComplianceService;

class TaxReturnObserver
{
    protected function clearForReturn(TaxReturn $return): void
    {
        $business = $return->business ?? Business::find($return->business_id);
        if ($business) {
            app(ComplianceService::class)->clearComplianceStatusCache($business);
        }
    }

    public function created(TaxReturn $return): void
    {
        $this->clearForReturn($return);
    }

    public function updated(TaxReturn $return): void
    {
        $this->clearForReturn($return);
    }

    public function deleted(TaxReturn $return): void
    {
        $this->clearForReturn($return);
    }

    public function restored(TaxReturn $return): void
    {
        $this->clearForReturn($return);
    }
}
