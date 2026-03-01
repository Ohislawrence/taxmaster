<?php

namespace App\Jobs;

use App\Models\Business;
use App\Services\ComplianceCalendarService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateComplianceDeadlines implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;
    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Business $business
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ComplianceCalendarService $service): void
    {
        try {
            $count = $service->generateDeadlines($this->business);

            Log::info('Compliance deadlines generated', [
                'business_id' => $this->business->id,
                'deadlines_created' => $count,
            ]);
        } catch (\Exception $e) {
            Log::error('Compliance deadline generation failed', [
                'business_id' => $this->business->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Compliance deadline generation job failed permanently', [
            'business_id' => $this->business->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
