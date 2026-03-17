<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;

class AutoVerifyUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * --before= : only verify users created before this date (YYYY-MM-DD)
     * --batch= : number of users to process per run (default 500)
     */
    protected $signature = 'users:autoverify {--before=} {--batch=500} {--dry-run}';

    /**
     * The console command description.
     */
    protected $description = 'Automatically mark existing users as email verified if they are not verified yet';

    public function handle()
    {
        $before = $this->option('before');
        $batch = (int) $this->option('batch');
        $dry = $this->option('dry-run');

        $query = User::whereNull('email_verified_at');

        if ($before) {
            try {
                $dt = Carbon::parse($before)->endOfDay();
                $query->where('created_at', '<=', $dt);
            } catch (\Exception $e) {
                $this->error('Invalid --before date format. Use YYYY-MM-DD');
                return 1;
            }
        }

        $count = $query->count();

        if ($count === 0) {
            $this->info('No unverified users found.');
            return 0;
        }

        $this->info("Found {$count} unverified users. Processing up to {$batch}.");

        $users = $query->take($batch)->get();

        foreach ($users as $user) {
            if ($dry) {
                $this->line("Would verify: {$user->id} {$user->email}");
                continue;
            }

            $user->email_verified_at = now();
            $user->saveQuietly();
            $this->line("Verified: {$user->id} {$user->email}");
        }

        $this->info('Done.');

        return 0;
    }
}
