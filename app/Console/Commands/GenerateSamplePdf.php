<?php

namespace App\Console\Commands;

use App\Models\TaxReturn;
use App\Services\TaxReturnPdfGenerator;
use Illuminate\Console\Command;

class GenerateSamplePdf extends Command
{
    protected $signature = 'pdf:generate {id?}';
    protected $description = 'Generate a PDF for a tax return';

    public function handle(TaxReturnPdfGenerator $pdfGenerator)
    {
        $id = $this->argument('id');
        
        $this->info('Starting PDF generation...');

        if (!$id) {
            // Find first tax return
            $taxReturn = TaxReturn::first();
            if (!$taxReturn) {
                $this->error('No tax returns found. Create one first.');
                return;
            }
            $this->info("Using tax return ID: {$taxReturn->id}");
        } else {
            $taxReturn = TaxReturn::findOrFail($id);
            $this->info("Found tax return ID: {$taxReturn->id}");
        }

        try {
            $this->info("Generating PDF for tax return {$taxReturn->id}...");
            $pdf = $pdfGenerator->generate($taxReturn);
            $this->info("PDF generated, size: " . strlen($pdf) . " bytes");
            
            $filename = storage_path('app/pdf/tax-return-' . $taxReturn->id . '.pdf');
            
            // Create directory if it doesn't exist
            if (!is_dir(storage_path('app/pdf'))) {
                mkdir(storage_path('app/pdf'), 0755, true);
                $this->info("Created PDF directory");
            }
            
            file_put_contents($filename, $pdf);
            
            $this->info("✅ PDF generated successfully!");
            $this->info("File saved to: $filename");
            $this->info("File size: " . filesize($filename) . " bytes");
            
        } catch (\Exception $e) {
            $this->error("❌ PDF generation failed: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }

        return 0;
    }
}
