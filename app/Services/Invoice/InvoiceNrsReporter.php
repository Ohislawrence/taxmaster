<?php

namespace App\Services\Invoice;

use App\Models\Invoice;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class InvoiceNrsReporter
{
    /**
     * Report invoice to NRS within 24 hours of issuance
     */
    public static function reportInvoice(Invoice $invoice): bool
    {
        try {
            // TODO: Replace with actual NRS API endpoint and payload
            $payload = [
                'invoice_id' => $invoice->id,
                'amount' => $invoice->amount,
                'issued_at' => $invoice->created_at,
                // Add more fields as required by NRS
            ];
            $response = Http::post(config('services.nrs.endpoint'), $payload);
            return $response->successful();
        } catch (\Exception $e) {
            Log::error('NRS invoice reporting failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
