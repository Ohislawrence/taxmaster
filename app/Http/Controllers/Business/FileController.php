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
        if (!$path) {
            abort(404);
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
