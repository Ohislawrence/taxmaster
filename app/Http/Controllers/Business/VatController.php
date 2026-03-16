<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\VatReturn;
use App\Models\Business;
use App\Models\User;
use App\Services\GovernmentPaymentService;
use App\Services\ReturnPdfGenerator;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VatController extends Controller
{
    public function __construct(
        private GovernmentPaymentService $paymentService,
        private SubscriptionService $subscriptionService
    ) {}

    /**
     * Display VAT returns dashboard
     */
    public function index(Request $request)
    {
        $business = $this->resolveBusiness($request);

        $returns = VatReturn::where('business_id', $business->id)
            ->with('reviewer')
            ->orderBy('period', 'desc')
            ->paginate(12);

        $stats = [
            'total_returns' => VatReturn::where('business_id', $business->id)->count(),
            'total_vat_paid' => VatReturn::where('business_id', $business->id)
                ->where('status', 'paid')
                ->sum('settlement_amount') ?? 0,
            'pending_returns' => VatReturn::where('business_id', $business->id)
                ->whereIn('status', ['draft', 'submitted'])
                ->count(),
            'overdue_returns' => VatReturn::where('business_id', $business->id)
                ->where('due_date', '<', now())
                ->whereIn('status', ['draft', 'submitted'])
                ->count(),
            'this_month_vat' => VatReturn::where('business_id', $business->id)
                ->where('period', now()->format('Y-m'))
                ->sum('settlement_amount') ?? 0,
            'pending_refunds' => VatReturn::where('business_id', $business->id)
                ->where('settlement_type', 'refund')
                ->where('status', 'refund_pending')
                ->count(),
        ];

        $latestReturn = VatReturn::where('business_id', $business->id)
            ->latest('period')
            ->first();

        return Inertia::render('Business/VAT/Index', [
            'returns' => $returns,
            'stats' => $stats,
            'latestReturn' => $latestReturn,
        ]);
    }

    /**
     * Show form to create new VAT return
     */
    public function create(Request $request)
    {
        $business = $this->resolveBusiness($request);

        // Check subscription feature
        if (!$this->subscriptionService->canPerformAction($business, 'file_vat')) {
            return redirect()->route('business.plans.index')
                ->with('error', 'Your current plan does not include VAT filing. Please upgrade to Basic or higher.');
        }

        // Get accountants (users with accountant role)
        $accountants = User::role('accountant')
            ->select('id', 'name', 'email')
            ->get();

        return Inertia::render('Business/VAT/Create', [
            'accountants' => $accountants,
        ]);
    }

    /**
     * Store new VAT return
     */
    public function store(Request $request)
    {
        $business = $this->resolveBusiness($request);

        // Check subscription feature
        if (!$this->subscriptionService->canPerformAction($business, 'file_vat')) {
            return redirect()->route('business.plans.index')
                ->with('error', 'Your current plan does not include VAT filing. Please upgrade to Basic or higher.');
        }

        $validated = $request->validate([
            'period' => 'required|string|max:7',
            'form_type' => 'required|in:Form 002,Form 001',
            'sales_turnover' => 'required|numeric|min:0',
            'exempt_sales' => 'nullable|numeric|min:0',
            'zero_rated_sales' => 'nullable|numeric|min:0',
            'export_sales' => 'nullable|numeric|min:0',
            'purchases_turnover' => 'required|numeric|min:0',
            'capital_goods_purchases' => 'nullable|numeric|min:0',
            'services_purchases' => 'nullable|numeric|min:0',
            'credit_notes_issued' => 'nullable|numeric|min:0',
            'credit_notes_received' => 'nullable|numeric|min:0',
            'bad_debt_relief' => 'nullable|numeric|min:0',
            'reviewer_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:draft,submitted',
        ]);

        $vatReturn = VatReturn::create([
            'business_id' => $business->id,
            'period' => $validated['period'],
            'form_type' => $validated['form_type'],
            'reporting_period' => 'monthly',
            'sales_turnover' => $validated['sales_turnover'],
            'exempt_sales' => $validated['exempt_sales'] ?? 0,
            'zero_rated_sales' => $validated['zero_rated_sales'] ?? 0,
            'export_sales' => $validated['export_sales'] ?? 0,
            'purchases_turnover' => $validated['purchases_turnover'],
            'capital_goods_purchases' => $validated['capital_goods_purchases'] ?? 0,
            'services_purchases' => $validated['services_purchases'] ?? 0,
            'credit_notes_issued' => $validated['credit_notes_issued'] ?? 0,
            'credit_notes_received' => $validated['credit_notes_received'] ?? 0,
            'bad_debt_relief' => $validated['bad_debt_relief'] ?? 0,
            'reviewed_by' => $validated['reviewer_id'],
            'notes' => $validated['notes'],
            'status' => $validated['status'] ?? 'draft',
            'due_date' => $this->calculateDueDate($validated['period']),
            'form_data' => [],
        ]);

        // Perform tax calculations
        $vatReturn->performCalculations();
        $vatReturn->save();

        return redirect()->route('business.vat.show', $vatReturn->id)
            ->with('success', 'VAT return created successfully');
    }

    /**
     * Calculate due date based on period (21 days after month end)
     */
    private function calculateDueDate(string $period): string
    {
        list($year, $month) = explode('-', $period);

        return \Carbon\Carbon::createFromDate($year, $month, 1)
            ->endOfMonth()
            ->addDays(21)
            ->toDateString();
    }

    /**
     * Show VAT return details
     */
    public function show(Request $request, VatReturn $vatReturn)
    {
        $business = $this->resolveBusiness($request);

        // Ensure the return belongs to this business
        if ($vatReturn->business_id !== $business->id) {
            abort(403, 'Unauthorized');
        }

        $vatReturn->load(['reviewer', 'governmentPayments']);

        return Inertia::render('Business/VAT/Show', [
            'vatReturn' => $vatReturn,
            'calculations' => $vatReturn->getCalculationSummary(),
            'governmentPayments' => $vatReturn->governmentPayments,
        ]);
    }

    /**
     * Show form to edit VAT return
     */
    public function edit(Request $request, VatReturn $vatReturn)
    {
        $business = $this->resolveBusiness($request);

        if ($vatReturn->business_id !== $business->id) {
            abort(403, 'Unauthorized');
        }

        // Can only edit if in draft status
        if ($vatReturn->status !== 'draft') {
            abort(403, 'Cannot edit submitted returns');
        }

        // Get accountants (users with accountant role)
        $accountants = User::role('accountant')
            ->select('id', 'name', 'email')
            ->get();

        return Inertia::render('Business/VAT/Edit', [
            'vatReturn' => $vatReturn,
            'accountants' => $accountants,
        ]);
    }

    /**
     * Update VAT return
     */
    public function update(Request $request, VatReturn $vatReturn)
    {
        $business = $this->resolveBusiness($request);

        if ($vatReturn->business_id !== $business->id) {
            abort(403, 'Unauthorized');
        }

        if ($vatReturn->status !== 'draft') {
            abort(403, 'Cannot edit submitted returns');
        }

        $validated = $request->validate([
            'period' => 'required|string|max:7',
            'form_type' => 'required|in:Form 002,Form 001',
            'sales_turnover' => 'required|numeric|min:0',
            'exempt_sales' => 'nullable|numeric|min:0',
            'zero_rated_sales' => 'nullable|numeric|min:0',
            'export_sales' => 'nullable|numeric|min:0',
            'purchases_turnover' => 'required|numeric|min:0',
            'capital_goods_purchases' => 'nullable|numeric|min:0',
            'services_purchases' => 'nullable|numeric|min:0',
            'credit_notes_issued' => 'nullable|numeric|min:0',
            'credit_notes_received' => 'nullable|numeric|min:0',
            'bad_debt_relief' => 'nullable|numeric|min:0',
            'reviewer_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $vatReturn->update([
            'period' => $validated['period'],
            'form_type' => $validated['form_type'],
            'sales_turnover' => $validated['sales_turnover'],
            'exempt_sales' => $validated['exempt_sales'] ?? 0,
            'zero_rated_sales' => $validated['zero_rated_sales'] ?? 0,
            'export_sales' => $validated['export_sales'] ?? 0,
            'purchases_turnover' => $validated['purchases_turnover'],
            'capital_goods_purchases' => $validated['capital_goods_purchases'] ?? 0,
            'services_purchases' => $validated['services_purchases'] ?? 0,
            'credit_notes_issued' => $validated['credit_notes_issued'] ?? 0,
            'credit_notes_received' => $validated['credit_notes_received'] ?? 0,
            'bad_debt_relief' => $validated['bad_debt_relief'] ?? 0,
            'reviewed_by' => $validated['reviewer_id'],
            'notes' => $validated['notes'],
            'due_date' => $this->calculateDueDate($validated['period']),
        ]);

        // Recalculate
        $vatReturn->performCalculations();
        $vatReturn->save();

        return redirect()->route('business.vat.show', $vatReturn->id)
            ->with('success', 'VAT return updated successfully');
    }

    /**
     * Generate government payment RRR
     */
    public function generatePaymentRRR(Request $request, VatReturn $vatReturn)
    {
        $business = $this->resolveBusiness($request);

        if ($vatReturn->business_id !== $business->id) {
            abort(403, 'Unauthorized');
        }

        try {
            $rrr = $this->paymentService->generateRemitaRRR(
                reference: "VAT-{$vatReturn->period}-{$business->id}",
                amount: $vatReturn->settlement_amount ?? 0,
                description: "VAT Payment for {$vatReturn->period} ({$vatReturn->form_type})",
                paymentType: 'vat',
                externalReference: "VAT-{$vatReturn->id}"
            );

            // Record the payment
            $vatReturn->governmentPayments()->create([
                'reference' => $rrr['reference'],
                'amount' => $vatReturn->settlement_amount,
                'payment_type' => 'vat',
                'rrr' => $rrr['rrr'] ?? null,
                'remita_reference' => $rrr['remita_reference'] ?? null,
                'status' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'rrr' => $rrr,
                'message' => 'Payment RRR generated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate RRR: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update return status
     */
    public function updateStatus(Request $request, VatReturn $vatReturn)
    {
        $business = $this->resolveBusiness($request);

        if ($vatReturn->business_id !== $business->id) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'status' => 'required|in:draft,submitted,accepted,paid,rejected,refund_pending,overdue',
            'firs_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        if ($validated['status'] === 'submitted') {
            $vatReturn->markAsSubmitted();
        } elseif ($validated['status'] === 'accepted') {
            $vatReturn->markAsAccepted($validated['firs_reference'] ?? '');
        } elseif ($validated['status'] === 'paid') {
            $vatReturn->markAsPaid();
        } elseif ($validated['status'] === 'refund_pending') {
            $vatReturn->markRefundPending();
        } else {
            $vatReturn->update([
                'status' => $validated['status'],
                'notes' => $validated['notes'],
            ]);
        }

        return redirect()->route('business.vat.show', $vatReturn->id)
            ->with('success', 'VAT return status updated successfully');
    }

    /**
     * Calculate preview without saving
     */
    public function calculatePreview(Request $request)
    {
        $business = $this->resolveBusiness($request);

        $validated = $request->validate([
            'sales_turnover' => 'required|numeric|min:0',
            'exempt_sales' => 'nullable|numeric|min:0',
            'zero_rated_sales' => 'nullable|numeric|min:0',
            'export_sales' => 'nullable|numeric|min:0',
            'purchases_turnover' => 'required|numeric|min:0',
            'capital_goods_purchases' => 'nullable|numeric|min:0',
            'services_purchases' => 'nullable|numeric|min:0',
            'credit_notes_issued' => 'nullable|numeric|min:0',
            'credit_notes_received' => 'nullable|numeric|min:0',
            'bad_debt_relief' => 'nullable|numeric|min:0',
        ]);

        // Create temporary instance for calculation (don't save)
        $vatReturn = new VatReturn($validated);
        $vatReturn->performCalculations();

        return response()->json([
            'success' => true,
            'calculations' => $vatReturn->getCalculationSummary(),
        ]);
    }

    /**
     * Export VAT return as PDF
     */
    public function exportPdf(VatReturn $vatReturn)
    {
        $business = auth()->user()->defaultBusiness();

        if ($vatReturn->business_id !== $business->id) {
            abort(403, 'Unauthorized');
        }

        $generator = new ReturnPdfGenerator();
        $pdf = $generator->generateVatReturnPdf($vatReturn);

        $filename = 'vat-return-' . $vatReturn->period . '.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Export VAT Form 002 (bulk) for this business in CSV or XML.
     */
    public function exportForm002(Request $request)
    {
        $business = $this->resolveBusiness($request);

        $returns = VatReturn::where('business_id', $business->id)
            ->when($request->period, fn($q, $p) => $q->where('period', $p))
            ->when($request->year, fn($q, $y) => $q->whereRaw('YEAR(STR_TO_DATE(period, "%Y-%m")) = ?', [$y]))
            ->orderBy('period', 'desc')
            ->get();

        $format = strtolower($request->get('format', 'csv'));

        if ($format === 'csv') {
            $csv = "Business Name,TIN,Period,Total Sales,Output VAT,Input VAT,Net VAT,Form002Ref\n";

            foreach ($returns as $r) {
                $csv .= sprintf(
                    '"%s","%s","%s",%s,%s,%s,%s,"%s"\n',
                    $business->name ?? 'N/A',
                    $business->tax_identification_number ?? 'N/A',
                    $r->period_label,
                    $r->vat_sales ?? 0,
                    $r->vat_on_sales ?? 0,
                    $r->input_vat ?? 0,
                    $r->vat_due ?? 0,
                    $r->form_002_reference ?? ''
                );
            }

            return response($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="vat-form002-' . date('Y-m-d') . '.csv"',
            ]);
        }

        $xml = new \SimpleXMLElement('<Form002s/>');

        foreach ($returns as $r) {
            $node = $xml->addChild('Form002');
            $node->addChild('BusinessName', $business->name ?? '');
            $node->addChild('TIN', $business->tax_identification_number ?? '');
            $node->addChild('Period', $r->period);
            $node->addChild('TotalSales', (string)($r->vat_sales ?? 0));
            $node->addChild('OutputVAT', (string)($r->vat_on_sales ?? 0));
            $node->addChild('InputVAT', (string)($r->input_vat ?? 0));
            $node->addChild('NetVAT', (string)($r->vat_due ?? 0));
            $node->addChild('Form002Reference', $r->form_002_reference ?? '');
        }

        return response($xml->asXML(), 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => 'attachment; filename="vat-form002-' . date('Y-m-d') . '.xml"',
        ]);
    }

    /**
     * Export VAT Form 002 for a single return (business)
     */
    public function exportForm002ForReturn(Request $request, VatReturn $vatReturn)
    {
        $business = $this->resolveBusiness($request);

        if ($vatReturn->business_id !== $business->id) {
            abort(403, 'Unauthorized');
        }

        $format = strtolower($request->get('format', 'csv'));

        if ($format === 'csv') {
            $csv = "Business Name,TIN,Period,Total Sales,Output VAT,Input VAT,Net VAT,Form002Ref\n";
            $csv .= sprintf(
                '"%s","%s","%s",%s,%s,%s,%s,"%s"\n',
                $business->name ?? 'N/A',
                $business->tax_identification_number ?? 'N/A',
                $vatReturn->period_label,
                $vatReturn->vat_sales ?? 0,
                $vatReturn->vat_on_sales ?? 0,
                $vatReturn->input_vat ?? 0,
                $vatReturn->vat_due ?? 0,
                $vatReturn->form_002_reference ?? ''
            );

            return response($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="vat-form002-' . $vatReturn->period . '.csv"',
            ]);
        }

        $xml = new \SimpleXMLElement('<Form002/>');
        $xml->addChild('BusinessName', $business->name ?? '');
        $xml->addChild('TIN', $business->tax_identification_number ?? '');
        $xml->addChild('Period', $vatReturn->period);
        $xml->addChild('TotalSales', (string)($vatReturn->vat_sales ?? 0));
        $xml->addChild('OutputVAT', (string)($vatReturn->vat_on_sales ?? 0));
        $xml->addChild('InputVAT', (string)($vatReturn->input_vat ?? 0));
        $xml->addChild('NetVAT', (string)($vatReturn->vat_due ?? 0));
        $xml->addChild('Form002Reference', $vatReturn->form_002_reference ?? '');

        return response($xml->asXML(), 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => 'attachment; filename="vat-form002-' . $vatReturn->period . '.xml"',
        ]);
    }

    /**
     * Resolve business from request
     */
    private function resolveBusiness(Request $request): Business
    {
        $business = $request->user()->defaultBusiness();

        if (!$business) {
            abort(403, 'No active business selected');
        }

        return $business;
    }
}
