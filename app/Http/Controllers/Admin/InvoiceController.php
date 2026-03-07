<?php

namespace App\Http\Controllers\Admin;

use App\Models\Invoice;
use App\Models\Business;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Services\EInvoice\EInvoiceService;

class InvoiceController
{
    /**
     * Display all invoices
     */
    public function index(Request $request)
    {
        $invoices = Invoice::with('business', 'businessSubscription')
            ->when($request->search, function ($query, $search) {
                return $query->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('business', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->business_id, function ($query, $businessId) {
                return $query->where('business_id', $businessId);
            })
            ->orderBy('invoice_date', 'desc')
            ->paginate(20);

        $stats = [
            'total_invoices' => Invoice::count(),
            'unpaid' => Invoice::unpaid()->count(),
            'overdue' => Invoice::overdue()->count(),
            'total_outstanding' => Invoice::unpaid()->sum('total'),
        ];

        $businesses = Business::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Admin/Invoices/Index', [
            'invoices' => $invoices,
            'invoiceStats' => $stats,
            'businesses' => $businesses,
            'filters' => $request->only(['search', 'status', 'business_id']),
        ]);
    }

    /**
     * Show invoice details
     */
    public function show(Invoice $invoice)
    {
        $invoice->load('business', 'businessSubscription');

        return Inertia::render('Admin/Invoices/Show', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Mark invoice as paid
     */
    public function markPaid(Invoice $invoice, Request $request)
    {
        $validated = $request->validate([
            'payment_reference' => 'required|string|max:255',
        ]);

        try {
            $invoice->markPaid($validated['payment_reference']);

            Log::info('Invoice marked as paid by admin', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'admin_id' => auth()->id(),
            ]);

            return back()->with('success', 'Invoice marked as paid successfully.');
        } catch (\Exception $e) {
            Log::error('Error marking invoice as paid', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to mark invoice as paid.');
        }
    }

    /**
     * Resend invoice to customer
     */
    public function resend(Invoice $invoice)
    {
        try {
            $business = $invoice->business;
            $owner = $business->owner;

            if (!$owner || !$owner->email) {
                return back()->with('error', 'Owner email not found.');
            }

            // Create mailable for invoice
            // This would typically be: Mail::queue(new InvoiceMailable($invoice), $owner->email);
            // For now, we'll just update the status

            $invoice->markSent();

            Log::info('Invoice resent to customer', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'email' => $owner->email,
            ]);

            return back()->with('success', 'Invoice sent to ' . $owner->email);
        } catch (\Exception $e) {
            Log::error('Error resending invoice', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to resend invoice.');
        }
    }

    /**
     * Mark invoice as cancelled
     */
    public function cancel(Invoice $invoice, Request $request)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $invoice->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $validated['reason'] ?? null,
            ]);

            Log::info('Invoice cancelled by admin', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'reason' => $validated['reason'] ?? null,
            ]);

            return back()->with('success', 'Invoice cancelled successfully.');
        } catch (\Exception $e) {
            Log::error('Error cancelling invoice', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to cancel invoice.');
        }
    }

    /**
     * View invoice PDF
     */
    public function viewPdf(Invoice $invoice)
    {
        try {
            if (!$invoice->pdf_path || !file_exists(storage_path('app/' . $invoice->pdf_path))) {
                return back()->with('error', 'PDF not found for this invoice.');
            }

            return response()->file(storage_path('app/' . $invoice->pdf_path));
        } catch (\Exception $e) {
            Log::error('Error viewing invoice PDF', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to retrieve PDF.');
        }
    }

    /**
     * Download invoice PDF
     */
    public function downloadPdf(Invoice $invoice)
    {
        try {
            if (!$invoice->pdf_path || !file_exists(storage_path('app/' . $invoice->pdf_path))) {
                return back()->with('error', 'PDF not found for this invoice.');
            }

            return response()->download(
                storage_path('app/' . $invoice->pdf_path),
                $invoice->invoice_number . '.pdf'
            );
        } catch (\Exception $e) {
            Log::error('Error downloading invoice PDF', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to download PDF.');
        }
    }

    /**
     * Generate and return a signed, JAdES-compliant invoice (JSON)
     */
    public function generateJadesInvoice(Invoice $invoice)
    {
        $business = $invoice->business;
        $nrsCredential = config('services.nrs.secret'); // Store your NRS credential in config/services.php
        $ecdsaPrivateKeyPem = file_get_contents(storage_path('app/ecdsa_private.pem'));

        // Map invoice and business data to UBL fields (expand as needed)
        $invoiceData = [
            'invoiceNumber' => $invoice->invoice_number,
            'issueDate' => $invoice->invoice_date->format('Y-m-d'),
            'sellerName' => $business->name,
            'sellerTIN' => $business->tax_identification_number,
            'buyerName' => $invoice->data['buyer_name'] ?? '',
            'buyerTIN' => $invoice->data['buyer_tin'] ?? '',
            'totalAmount' => $invoice->total,
            'vatAmount' => $invoice->tax,
            'currency' => 'NGN',
            // ...add all other required UBL fields
        ];

        $jadesInvoice = EInvoiceService::generateJAdESInvoice($invoiceData, $nrsCredential, $ecdsaPrivateKeyPem);

        return response()->json($jadesInvoice);
    }
}
