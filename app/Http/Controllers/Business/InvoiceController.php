<?php
namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Services\EInvoice\EInvoiceService;

class InvoiceController extends Controller
{
    public function show($id)
    {
        $invoice = Invoice::with('business')->findOrFail($id);
        return inertia('Business/InvoiceShow', [
            'invoice' => $invoice,
        ]);
    }

    public function generateJadesInvoice($id)
    {
        $invoice = Invoice::with('business')->findOrFail($id);
        $service = new EInvoiceService();
        $signedInvoice = $service->generateJadesInvoice($invoice);
        return response()->json($signedInvoice);
    }

    public function qr($id)
    {
        $invoice = Invoice::with('business')->findOrFail($id);
        $qrData = [
            'id' => $invoice->id,
            'number' => $invoice->number ?? $invoice->id,
            'amount' => $invoice->amount,
            'date' => $invoice->created_at?->toDateString(),
            'business' => $invoice->business->name ?? '',
        ];
        $qrString = json_encode($qrData);
        $qr = \App\Services\Invoice\InvoiceQrCodeService::generateQrCode($qrString);
        return response()->json(['qr' => $qr]);
    }
}
