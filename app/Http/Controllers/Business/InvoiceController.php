<?php
namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Services\EInvoice\EInvoiceService;

class InvoiceController extends Controller
{
    /**
     * List invoices for the authenticated user's default business
     */
    public function index(Request $request)
    {
        $business = $request->user()->defaultBusiness();
        if (! $business) {
            return inertia('Business/Invoices/Index', ['invoices' => []]);
        }

        $invoices = Invoice::where('business_id', $business->id)
            ->orderBy('invoice_date', 'desc')
            ->paginate(20);

        return inertia('Business/Invoices/Index', [
            'invoices' => $invoices,
        ]);
    }

    public function show($id)
    {
        $invoice = Invoice::with('business')->findOrFail($id);
        return inertia('Business/Invoices/InvoiceShow', [
            'invoice' => $invoice,
        ]);
    }

    public function generateJadesInvoice($id)
    {
        $invoice = Invoice::with('business')->findOrFail($id);
        $business = $invoice->business;

        $nrsCredential = config('services.nrs.api_key');

        // Allow ECDSA private key to be provided via env var for ease of local/dev setup.
        $ecdsaPrivateKeyPem = env('ECDSA_PRIVATE_KEY');

        if (empty($ecdsaPrivateKeyPem)) {
            $ecdsaPath = storage_path('app/ecdsa_private.pem');
            if (! file_exists($ecdsaPath)) {
                return response()->json([
                    'error' => 'ECDSA private key not found. Place the PEM file at storage/app/ecdsa_private.pem or set the ECDSA_PRIVATE_KEY environment variable.'
                ], 500);
            }

            $ecdsaPrivateKeyPem = file_get_contents($ecdsaPath);
        }

        $invoiceData = [
            'invoiceNumber' => $invoice->invoice_number ?? $invoice->id,
            'issueDate' => $invoice->invoice_date?->format('Y-m-d') ?? $invoice->created_at?->toDateString(),
            'sellerName' => $business->name,
            'sellerTIN' => $business->tax_identification_number,
            'seller' => [
                'name' => $business->name,
                'tin' => $business->tax_identification_number,
                'registrationNumber' => $business->registration_number ?? null,
                'address' => [
                    'street' => $business->address ?? '',
                    'city' => $business->city ?? '',
                    'state' => $business->state ?? '',
                    'country' => $business->country ?? '',
                ],
                'email' => $business->email ?? null,
                'phone' => $business->phone ?? null,
            ],
            'buyerName' => $invoice->data['buyer_name'] ?? '',
            'buyerTIN' => $invoice->data['buyer_tin'] ?? '',
            'totalAmount' => $invoice->total ?? $invoice->amount ?? 0,
            'vatAmount' => $invoice->tax ?? 0,
            'currency' => 'NGN',
        ];

        $jadesInvoice = EInvoiceService::generateJAdESInvoice($invoiceData, $nrsCredential, $ecdsaPrivateKeyPem);

        return response()->json($jadesInvoice);
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
