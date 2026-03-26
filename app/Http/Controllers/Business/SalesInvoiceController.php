<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\BusinessSubscription;
use Inertia\Inertia;

class SalesInvoiceController extends Controller
{
    /**
     * Show create invoice form
     */
    public function create()
    {
        $business = auth()->user()->defaultBusiness();
        $bankAccounts = [];
        if ($business) {
            $bankAccounts = $business->bankAccounts()->where('is_active', true)->get(['id', 'bank_name', 'account_number']);
        }

        return Inertia::render('Business/Invoices/InvoiceCreate', [
            'bankAccounts' => $bankAccounts,
        ]);
    }

    /**
     * Store a new sales invoice
     */
    public function store(Request $request)
    {
        $request->validate([
            'buyer_name' => 'required|string|max:255',
            'buyer_tin' => 'nullable|string|max:100',
            'due_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:1000',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
        ]);

        $business = $request->user()->defaultBusiness();
        if (! $business) {
            return back()->with('error', 'Business not found');
        }

        $items = $request->input('items', []);

        // Calculate line totals, subtotal, tax and total
        $calculatedItems = [];
        $subtotal = 0.0;
        $tax = 0.0;

        foreach ($items as $it) {
            $qty = floatval($it['quantity']);
            $unit = floatval($it['unit_price']);
            $rate = isset($it['tax_rate']) ? floatval($it['tax_rate']) : 0.0; // percentage (e.g., 7.5)
            $lineNet = round($qty * $unit, 2);
            $lineTax = round($lineNet * ($rate / 100.0), 2);
            $lineTotal = round($lineNet + $lineTax, 2);

            $calculatedItems[] = [
                'description' => $it['description'],
                'quantity' => $qty,
                'unit_price' => $unit,
                'tax_rate' => $rate,
                'line_net' => $lineNet,
                'line_tax' => $lineTax,
                'line_total' => $lineTotal,
            ];

            $subtotal += $lineNet;
            $tax += $lineTax;
        }

        $subtotal = round($subtotal, 2);
        $tax = round($tax, 2);
        $total = round($subtotal + $tax, 2);

        // Ensure we have a business subscription id (DB migration requires non-null)
        $subscription = $business->activeSubscription() ?? BusinessSubscription::where('business_id', $business->id)->latest()->first();

        if (! $subscription) {
            // Create a minimal default subscription so invoices can reference it
            $basicPlan = config('taxmaster.pricing.plans.basic', []);
            $subscription = BusinessSubscription::create([
                'business_id' => $business->id,
                'plan_type' => 'basic',
                'monthly_price' => $basicPlan['monthly_price'] ?? 0,
                'annual_price' => $basicPlan['annual_price'] ?? 0,
                'status' => 'active',
                'started_at' => now(),
                'renews_at' => now()->addMonth(),
            ]);
        }

        $invoice = Invoice::create([
            'business_subscription_id' => $subscription->id,
            'business_id' => $business->id,
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'invoice_date' => now()->toDateString(),
            'due_date' => $request->input('due_date') ?: now()->addDays(15)->toDateString(),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'period_start' => now()->toDateString(),
            'period_end' => now()->toDateString(),
            'status' => 'sent',
            'data' => [
                'buyer_name' => $request->input('buyer_name'),
                'buyer_tin' => $request->input('buyer_tin'),
                'items' => $calculatedItems,
                'bank_account_id' => $request->input('bank_account_id'),
            ],
        ]);

        return redirect()->route('business.invoices.show', $invoice->id)->with('success', 'Invoice created');
    }

    /**
     * Business marks invoice paid (creates transaction via Invoice::markPaid)
     */
    public function markPaid(Request $request, Invoice $invoice)
    {
        $this->ensureBusinessAccess($request, $invoice);

        $validated = $request->validate([
            'payment_reference' => 'required|string|max:255',
        ]);

        $invoice->markPaid($validated['payment_reference']);

        return back()->with('success', 'Invoice marked as paid. Transaction created.');
    }

    /**
     * Show edit form for an invoice
     */
    public function edit(Request $request, Invoice $invoice)
    {
        $this->ensureBusinessAccess($request, $invoice);

        $business = $request->user()->defaultBusiness();
        $bankAccounts = $business ? $business->bankAccounts()->where('is_active', true)->get(['id', 'bank_name', 'account_number']) : [];

        return Inertia::render('Business/Invoices/InvoiceEdit', [
            'invoice' => $invoice->load('business'),
            'bankAccounts' => $bankAccounts,
        ]);
    }

    /**
     * Update an existing invoice
     */
    public function update(Request $request, Invoice $invoice)
    {
        $this->ensureBusinessAccess($request, $invoice);

        $request->validate([
            'buyer_name' => 'required|string|max:255',
            'buyer_tin' => 'nullable|string|max:100',
            'due_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:1000',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
        ]);

        $items = $request->input('items', []);
        $calculatedItems = [];
        $subtotal = 0.0;
        $tax = 0.0;

        foreach ($items as $it) {
            $qty = floatval($it['quantity']);
            $unit = floatval($it['unit_price']);
            $rate = isset($it['tax_rate']) ? floatval($it['tax_rate']) : 0.0;
            $lineNet = round($qty * $unit, 2);
            $lineTax = round($lineNet * ($rate / 100.0), 2);
            $lineTotal = round($lineNet + $lineTax, 2);

            $calculatedItems[] = [
                'description' => $it['description'],
                'quantity' => $qty,
                'unit_price' => $unit,
                'tax_rate' => $rate,
                'line_net' => $lineNet,
                'line_tax' => $lineTax,
                'line_total' => $lineTotal,
            ];

            $subtotal += $lineNet;
            $tax += $lineTax;
        }

        $subtotal = round($subtotal, 2);
        $tax = round($tax, 2);
        $total = round($subtotal + $tax, 2);

        $invoice->update([
            'due_date' => $request->input('due_date') ?: $invoice->due_date,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'data' => array_merge($invoice->data ?? [], [
                'buyer_name' => $request->input('buyer_name'),
                'buyer_tin' => $request->input('buyer_tin'),
                'items' => $calculatedItems,
                'bank_account_id' => $request->input('bank_account_id'),
            ]),
        ]);

        return redirect()->route('business.invoices.show', $invoice->id)->with('success', 'Invoice updated');
    }

    /**
     * Update invoice status (inline from index)
     */
    public function updateStatus(Request $request, Invoice $invoice)
    {
        $this->ensureBusinessAccess($request, $invoice);

        $validated = $request->validate(['status' => 'required|in:draft,sent,viewed,paid,cancelled']);

        $invoice->update(['status' => $validated['status']]);

        return response()->json(['success' => true, 'status' => $validated['status']]);
    }

    /**
     * Ensure the current user belongs to the invoice's business
     */
    protected function ensureBusinessAccess(Request $request, Invoice $invoice): void
    {
        $business = $request->user()->defaultBusiness();
        if (! $business || $business->id !== $invoice->business_id) {
            abort(403, 'This action is unauthorized.');
        }
    }
}
