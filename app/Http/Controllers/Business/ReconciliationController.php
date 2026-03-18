<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Reconciliation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReconciliationController extends Controller
{
    public function index(Request $request)
    {
        $business = $request->user()->defaultBusiness();

        $reconciliations = Reconciliation::where('business_id', $business->id)
            ->with(['invoice', 'transaction'])
            ->orderBy('matched_at', 'desc')
            ->paginate(30);

        return Inertia::render('Business/Reconciliations/Index', [
            'reconciliations' => $reconciliations,
        ]);
    }

    public function confirm(Request $request, Reconciliation $reconciliation)
    {
        $this->authorize('update', $reconciliation);

        $reconciliation->update(['status' => 'confirmed']);

        // If invoice exists and not paid, mark paid using transaction reference if present
        if ($reconciliation->invoice && $reconciliation->invoice->status !== 'paid') {
            $reference = $reconciliation->transaction?->mono_transaction_id ?? null;
            if ($reference) {
                $reconciliation->invoice->markPaid($reference);
            }
        }

        return back()->with('success', 'Reconciliation confirmed.');
    }

    public function reject(Request $request, Reconciliation $reconciliation)
    {
        $this->authorize('update', $reconciliation);

        $reconciliation->update(['status' => 'rejected']);

        return back()->with('success', 'Reconciliation rejected.');
    }
}
