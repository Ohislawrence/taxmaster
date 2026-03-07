<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Services\Invoice\InvoiceNrsReporter;
use Carbon\Carbon;

class ReportInvoicesToNrs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:report-nrs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Report all invoices issued in the last 24 hours to the NRS.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $since = Carbon::now()->subHours(24);
        $invoices = Invoice::where('created_at', '>=', $since)->get();
        $this->info('Reporting ' . $invoices->count() . ' invoices to NRS...');
        $success = 0;
        foreach ($invoices as $invoice) {
            if (InvoiceNrsReporter::reportInvoice($invoice)) {
                $success++;
            }
        }
        $this->info("Successfully reported $success invoices to NRS.");
    }
}
