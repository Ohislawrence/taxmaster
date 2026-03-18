<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// Custom wrapper command to process the queue once (useful on cPanel/shared hosts)
Artisan::command('app:process-queue', function () {
    Log::info('[scheduler] app:process-queue started');

    // Run a single queue worker iteration
    $this->call('queue:work', ['--once' => true, '--tries' => 3]);

    Log::info('[scheduler] app:process-queue finished');
})->describe('Process the queue once (wrapper for scheduler)');



