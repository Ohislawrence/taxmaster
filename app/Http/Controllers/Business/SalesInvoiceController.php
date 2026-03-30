<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\BusinessSubscription;
use App\Jobs\SubmitInvoiceToFirs;
use App\Services\EInvoice\TinValidationService;
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
            'buyer_email' => 'nullable|email|max:255',
            'buyer_phone' => 'nullable|string|max:50',
            'buyer_address' => 'nullable|string|max:500',
            'buyer_city' => 'nullable|string|max:100',
            'buyer_state' => 'nullable|string|max:100',
            'buyer_postal_code' => 'nullable|string|max:20',
            'due_date' => 'nullable|date',
            'payment_terms' => 'nullable|string|max:500',
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

        // Validate buyer TIN if provided
        if ($request->filled('buyer_tin')) {
            $tinValidator = new TinValidationService();
            $tinValidation = $tinValidator->validate($request->input('buyer_tin'));

            if (!$tinValidation['valid']) {
                return back()->withErrors(['buyer_tin' => 'Invalid TIN format'])->withInput();
            }
        }

        $items = $request->input('items', []);

        // Calculate line totals, subtotal, tax and total
        $calculatedItems = [];
        $subtotal = 0.0;
        $tax = 0.0;
        $defaultVatRate = config('services.firs.vat_rate', 7.5);

        foreach ($items as $it) {
            $qty = floatval($it['quantity']);
            $unit = floatval($it['unit_price']);
            $rate = isset($it['tax_rate']) ? floatval($it['tax_rate']) : $defaultVatRate;
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
            'invoice_type_code' => '380', // Standard invoice
            'invoice_date' => now()->toDateString(),
            'due_date' => $request->input('due_date') ?: now()->addDays(15)->toDateString(),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'vat_rate' => $defaultVatRate,
            'period_start' => now()->toDateString(),
            'period_end' => now()->toDateString(),
            'status' => 'sent',
            'firs_status' => 'pending',

            // Buyer information
            'buyer_email' => $request->input('buyer_email'),
            'buyer_phone' => $request->input('buyer_phone'),
            'buyer_address' => $request->input('buyer_address'),
            'buyer_city' => $request->input('buyer_city'),
            'buyer_state' => $request->input('buyer_state'),
            'buyer_postal_code' => $request->input('buyer_postal_code'),
            'buyer_country' => 'NG',

            // Payment information
            'payment_terms' => $request->input('payment_terms'),
            'payment_means_code' => '30', // Credit transfer

            'data' => [
                'buyer_name' => $request->input('buyer_name'),
                'buyer_tin' => $request->input('buyer_tin'),
                'items' => $calculatedItems,
                'bank_account_id' => $request->input('bank_account_id'),
            ],
        ]);

        // Automatically submit to FIRS if enabled
        if (config('services.firs.enabled', true) && config('services.firs.auto_submit', true)) {
            SubmitInvoiceToFirs::dispatch($invoice);
        }

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
     * Manually submit or resubmit invoice to FIRS
     */
    public function submitToFirs(Request $request, Invoice $invoice)
    {
        $this->ensureBusinessAccess($request, $invoice);

        // Check if FIRS is enabled
        if (!config('services.firs.enabled', true)) {
            return back()->with('error', 'FIRS e-invoicing is not enabled');
        }

        // Dispatch submission job
        SubmitInvoiceToFirs::dispatch($invoice, true);

        return back()->with('success', 'Invoice submission to FIRS has been queued');
    }

    /**
     * Get FIRS submission status for an invoice
     */
    public function firsStatus(Request $request, Invoice $invoice)
    {
        $this->ensureBusinessAccess($request, $invoice);

        return response()->json([
            'status' => $invoice->firs_status,
            'reference' => $invoice->firs_reference,
            'submission_id' => $invoice->firs_submission_id,
            'submitted_at' => $invoice->firs_submitted_at,
            'approved_at' => $invoice->firs_approved_at,
            'validation_errors' => $invoice->firs_validation_errors ? json_decode($invoice->firs_validation_errors, true) : null,
        ]);
    }

    /**
     * Validate TIN via AJAX
     */
    public function validateTin(Request $request)
    {
        $request->validate([
            'tin' => 'required|string',
        ]);

        $tinValidator = new TinValidationService();
        $result = $tinValidator->validate($request->input('tin'));

        return response()->json($result);
    }

    /**
     * Export invoice as UBL XML for manual FIRS submission
     */
    public function exportUblXml(Request $request, Invoice $invoice)
    {
        $this->ensureBusinessAccess($request, $invoice);

        try {
            $invoiceData = $this->prepareInvoiceDataForExport($invoice);

            // Generate IRN if not exists
            if (empty($invoiceData['irn'])) {
                $nrsCredential = config('services.firs.api_key', 'MANUAL_SUBMISSION');
                $invoiceData['irn'] = \App\Services\EInvoice\IRNGenerator::generate(
                    $invoiceData['invoiceNumber'],
                    $invoiceData['sellerTIN'],
                    $nrsCredential
                );
            }

            $ublInvoice = new \App\Services\EInvoice\UBLInvoice($invoiceData);
            $ublArray = $ublInvoice->toArray();

            // Convert to XML
            $xml = $this->arrayToXml($ublArray, 'Invoice');

            $filename = 'FIRS_UBL_' . $invoice->invoice_number . '.xml';

            return response($xml, 200, [
                'Content-Type' => 'application/xml',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to export UBL XML: ' . $e->getMessage());
        }
    }

    /**
     * Export invoice as JSON for manual FIRS submission
     */
    public function exportUblJson(Request $request, Invoice $invoice)
    {
        $this->ensureBusinessAccess($request, $invoice);

        try {
            $invoiceData = $this->prepareInvoiceDataForExport($invoice);

            // Generate IRN if not exists
            if (empty($invoiceData['irn'])) {
                $nrsCredential = config('services.firs.api_key', 'MANUAL_SUBMISSION');
                $invoiceData['irn'] = \App\Services\EInvoice\IRNGenerator::generate(
                    $invoiceData['invoiceNumber'],
                    $invoiceData['sellerTIN'],
                    $nrsCredential
                );
            }

            $ublInvoice = new \App\Services\EInvoice\UBLInvoice($invoiceData);
            $ublArray = $ublInvoice->toArray();

            $filename = 'FIRS_UBL_' . $invoice->invoice_number . '.json';

            return response()->json($ublArray, 200, [
                'Content-Type' => 'application/json',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to export UBL JSON: ' . $e->getMessage());
        }
    }

    /**
     * Prepare invoice data for export
     */
    protected function prepareInvoiceDataForExport(Invoice $invoice): array
    {
        $business = $invoice->business;
        $data = $invoice->data ?? [];

        return [
            'invoiceNumber' => $invoice->invoice_number,
            'issueDate' => $invoice->invoice_date->format('Y-m-d'),
            'dueDate' => $invoice->due_date?->format('Y-m-d'),
            'invoiceTypeCode' => $invoice->invoice_type_code ?? '380',
            'currency' => 'NGN',

            // Seller (Business)
            'sellerName' => $business->name ?? '',
            'sellerTIN' => $business->tax_identification_number ?? '',
            'sellerRegistrationNumber' => $business->registration_number ?? '',
            'sellerStreet' => $business->address ?? '',
            'sellerCity' => $business->city ?? '',
            'sellerState' => $business->state ?? '',
            'sellerCountry' => $business->country ?? 'NG',
            'sellerPostalCode' => $business->postal_code ?? '',
            'sellerEmail' => $business->email ?? '',
            'sellerPhone' => $business->phone ?? '',

            // Buyer
            'buyerName' => $data['buyer_name'] ?? '',
            'buyerTIN' => $data['buyer_tin'] ?? '',
            'buyerEmail' => $invoice->buyer_email ?? '',
            'buyerPhone' => $invoice->buyer_phone ?? '',
            'buyerStreet' => $invoice->buyer_address ?? '',
            'buyerCity' => $invoice->buyer_city ?? '',
            'buyerState' => $invoice->buyer_state ?? '',
            'buyerPostalCode' => $invoice->buyer_postal_code ?? '',
            'buyerCountry' => $invoice->buyer_country ?? 'NG',

            // Amounts
            'lineExtensionAmount' => $invoice->subtotal ?? 0,
            'taxExclusiveAmount' => $invoice->subtotal ?? 0,
            'vatAmount' => $invoice->tax ?? 0,
            'vatRate' => $invoice->vat_rate ?? 7.5,
            'taxInclusiveAmount' => $invoice->total ?? 0,
            'totalAmount' => $invoice->total ?? 0,
            'prepaidAmount' => 0.0,
            'payableAmount' => $invoice->total ?? 0,

            // Payment
            'paymentMeansCode' => $invoice->payment_means_code ?? '30',
            'paymentTerms' => $invoice->payment_terms ?? null,

            // Bank account details if available
            'bankAccountNumber' => null,
            'bankAccountName' => null,
            'bankName' => null,
            'bankCode' => null,

            // Invoice lines
            'invoiceLines' => $this->prepareInvoiceLinesForExport($invoice),

            // IRN and signature (if exist)
            'irn' => $invoice->firs_irn ?? '',
            'signature' => $invoice->digital_signature ?? '',

            // Notes
            'notes' => 'Generated by TaxMaster NG for manual FIRS submission',
        ];
    }

    /**
     * Prepare invoice line items for export
     */
    protected function prepareInvoiceLinesForExport(Invoice $invoice): array
    {
        $data = $invoice->data ?? [];
        $items = $data['items'] ?? [];
        $lines = [];

        foreach ($items as $index => $item) {
            $quantity = $item['quantity'] ?? 1;
            $price = $item['price'] ?? $item['unit_price'] ?? 0;
            $lineAmount = $quantity * $price;
            $vatAmount = $lineAmount * (($invoice->vat_rate ?? 7.5) / 100);

            $lines[] = [
                'id' => $index + 1,
                'invoicedQuantity' => [
                    'value' => $quantity,
                    'unitCode' => 'EA', // Each
                ],
                'lineExtensionAmount' => [
                    'value' => number_format($lineAmount, 2, '.', ''),
                    'currencyID' => 'NGN',
                ],
                'item' => [
                    'description' => $item['description'] ?? '',
                    'name' => $item['description'] ?? '',
                    'sellersItemIdentification' => [
                        'id' => $item['code'] ?? ($index + 1),
                    ],
                ],
                'price' => [
                    'priceAmount' => [
                        'value' => number_format($price, 2, '.', ''),
                        'currencyID' => 'NGN',
                    ],
                ],
                'taxTotal' => [
                    'taxAmount' => [
                        'value' => number_format($vatAmount, 2, '.', ''),
                        'currencyID' => 'NGN',
                    ],
                    'taxSubtotal' => [
                        'taxableAmount' => [
                            'value' => number_format($lineAmount, 2, '.', ''),
                            'currencyID' => 'NGN',
                        ],
                        'taxAmount' => [
                            'value' => number_format($vatAmount, 2, '.', ''),
                            'currencyID' => 'NGN',
                        ],
                        'taxCategory' => [
                            'id' => 'S',
                            'percent' => $invoice->vat_rate ?? 7.5,
                            'taxScheme' => [
                                'id' => 'VAT',
                            ],
                        ],
                    ],
                ],
            ];
        }

        return $lines;
    }

    /**
     * Convert array to XML
     */
    protected function arrayToXml(array $data, string $rootElement = 'root'): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><' . $rootElement . ' xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"></' . $rootElement . '>');

        $this->arrayToXmlRecursive($data, $xml);

        return $xml->asXML();
    }

    /**
     * Recursively convert array to XML
     */
    protected function arrayToXmlRecursive(array $data, \SimpleXMLElement $xml): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (isset($value[0])) {
                    // Numeric array - create multiple elements
                    foreach ($value as $item) {
                        $subnode = $xml->addChild($key);
                        if (is_array($item)) {
                            $this->arrayToXmlRecursive($item, $subnode);
                        } else {
                            $subnode[0] = $item;
                        }
                    }
                } else {
                    // Associative array
                    $subnode = $xml->addChild($key);
                    $this->arrayToXmlRecursive($value, $subnode);
                }
            } else {
                $xml->addChild($key, htmlspecialchars((string)$value));
            }
        }
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
