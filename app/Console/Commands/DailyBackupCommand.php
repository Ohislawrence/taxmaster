<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Backup\Commands\BackupCommand;

class DailyBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:run-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run daily encrypted backup of database and files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting daily backup...');

        // Call the backup command from Spatie
        $this->call('backup:run', [
            '--only-db' => false,
            '--only-files' => false,
        ]);

        $this->info('Daily backup completed successfully!');

        return Command::SUCCESS;
    }
}
