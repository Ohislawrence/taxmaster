<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Reconciliation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Dompdf\Dompdf;

class FileController extends Controller
{
    /**
     * Return a signed URL for an invoice PDF (valid for a short time)
     */
    public function invoicePdfSigned(Request $request, Invoice $invoice)
    {
        $user = $request->user();
        if (!$user || $user->defaultBusiness()->id !== $invoice->business_id) {
            abort(403);
        }

        $path = $invoice->pdf_path;

        // If no stored PDF, attempt to generate a simple PDF on-demand (requires dompdf)
        if (! $path) {
            try {
                $filename = 'invoices/invoice-' . $invoice->id . '.pdf';

                // Render a minimal invoice HTML view if available, otherwise build inline HTML
                if (file_exists(resource_path('views/invoices/simple_pdf.blade.php'))) {
                    $html = view('invoices.simple_pdf', ['invoice' => $invoice])->render();
                } else {
                    $html = '<html><head><meta charset="utf-8"><style>body{font-family:Arial,Helvetica,sans-serif} .h{font-size:18px;font-weight:700} .muted{color:#666}</style></head><body>';
                    $html .= '<div class="h">Invoice ' . e($invoice->invoice_number ?? $invoice->id) . '</div>';
                    $html .= '<p class="muted">Date: ' . e($invoice->invoice_date?->toDateString() ?? $invoice->created_at?->toDateString()) . '</p>';
                    $html .= '<p>Bill To: ' . e($invoice->data['buyer_name'] ?? $invoice->business?->name) . '</p>';
                    $html .= '<table width="100%" style="border-collapse:collapse;margin-top:10px">';
                    $html .= '<tr><th style="text-align:left">Description</th><th style="text-align:right">Qty</th><th style="text-align:right">Unit</th><th style="text-align:right">Total</th></tr>';
                    if (is_array($invoice->data['items'] ?? null)) {
                        foreach ($invoice->data['items'] as $it) {
                            $html .= '<tr><td>' . e($it['description'] ?? '') . '</td><td style="text-align:right">' . e($it['quantity'] ?? '') . '</td><td style="text-align:right">' . e(number_format($it['unit_price'] ?? 0,2)) . '</td><td style="text-align:right">' . e(number_format($it['line_total'] ?? 0,2)) . '</td></tr>';
                        }
                    }
                    $html .= '</table>';
                    $html .= '<p style="text-align:right;font-weight:700;margin-top:10px">Total: ' . number_format($invoice->total ?? 0,2) . '</p>';
                    $html .= '</body></html>';
                }

                $dompdf = new Dompdf();
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();

                // Save using the configured filesystem disk so Storage::exists() can find it
                $diskName = config('filesystems.default');
                $disk = Storage::disk($diskName);
                // Ensure directory exists on disk
                $dir = dirname($filename);
                if ($dir && $dir !== '.') {
                    $disk->makeDirectory($dir);
                }

                $disk->put($filename, $dompdf->output());

                $invoice->pdf_path = $filename;
                $invoice->save();

                $path = $filename;
            } catch (\Throwable $e) {
                \Log::error('Invoice PDF generation failed', ['invoice_id' => $invoice->id, 'error' => $e->getMessage()]);
                return response()->json(['error' => 'PDF generation not available: ' . ($e->getMessage() ?: 'unknown error')], 500);
            }
        }

        return response()->json(['url' => $this->signedUrlForPath($path)]);
    }

    /**
     * Return a signed URL for a reconciliation transaction attachment by index
     */
    public function reconciliationAttachment(Request $request, Reconciliation $reconciliation, $index)
    {
        $user = $request->user();
        if (!$user || $user->defaultBusiness()->id !== $reconciliation->business_id) {
            abort(403);
        }

        $attachments = $reconciliation->transaction?->attachments ?? [];
        $idx = (int) $index;

        if (!isset($attachments[$idx])) {
            abort(404);
        }

        $path = $attachments[$idx];
        return response()->json(['url' => $this->signedUrlForPath($path)]);
    }

    /**
     * Serve a file for a signed temporary route (base64-encoded path)
     */
    public function serve(Request $request, $encodedPath)
    {
        if (!$request->hasValidSignature()) {
            abort(403);
        }

        $path = base64_decode($encodedPath);

        // External URL
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return redirect()->away($path);
        }

        // Public storage disk (storage/app/public)
        if (Storage::disk('public')->exists($path)) {
            $full = storage_path('app/public/' . $path);
            return response()->file($full);
        }

        // Any other disk (attempt generic download)
        if (Storage::exists($path)) {
            return Storage::download($path);
        }

        abort(404);
    }

    protected function signedUrlForPath(string $path): string
    {
        // If externally-hosted URL, return as-is
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        // Prefer cloud temporary URLs when available
        try {
            // If using S3 or a driver that supports temporaryUrl
            if (method_exists(Storage::class, 'temporaryUrl')) {
                // Attempt to generate on default disk
                $disk = Storage::disk(config('filesystems.default'));
                if (method_exists($disk, 'temporaryUrl')) {
                    return $disk->temporaryUrl($path, now()->addMinutes(30));
                }
            }
        } catch (\Exception $e) {
            // fall back to signed route
        }

        // Fallback to a signed application route that serves the file
        $encoded = base64_encode($path);
        return URL::temporarySignedRoute('files.serve', now()->addMinutes(30), ['encodedPath' => $encoded]);
    }
}
