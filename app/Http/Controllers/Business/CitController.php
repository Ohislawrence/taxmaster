<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\CitReturn;
use App\Models\Business;
use App\Models\User;
use App\Services\GovernmentPaymentService;
use App\Services\ReturnPdfGenerator;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CitController extends Controller
{
    public function __construct(
        private GovernmentPaymentService $paymentService,
        private SubscriptionService $subscriptionService
    ) {}

    /**
     * Display CIT returns dashboard
     */
    public function index(Request $request)
    {
        $business = $this->resolveBusiness($request);

        $returns = CitReturn::where('business_id', $business->id)
            ->with('reviewer')
            ->orderBy('period', 'desc')
            ->paginate(12);

        $stats = [
            'total_returns' => CitReturn::where('business_id', $business->id)->count(),
            'total_cit_paid' => CitReturn::where('business_id', $business->id)
                ->where('status', 'paid')
                ->sum('tax_due') ?? 0,
            'pending_returns' => CitReturn::where('business_id', $business->id)
                ->whereIn('status', ['draft', 'submitted'])
                ->count(),
            'overdue_returns' => CitReturn::where('business_id', $business->id)
                ->where('due_date', '<', now())
                ->whereIn('status', ['draft', 'submitted'])
                ->count(),
            'this_year_tax' => CitReturn::where('business_id', $business->id)
                ->whereYear('created_at', now()->year)
                ->sum('tax_due') ?? 0,
        ];

        $latestReturn = CitReturn::where('business_id', $business->id)
            ->latest('period')
            ->first();

        return Inertia::render('Business/CIT/Index', [
            'returns' => $returns,
            'stats' => $stats,
            'latestReturn' => $latestReturn,
        ]);
    }

    /**
     * Show form to create new CIT return
     */
    public function create(Request $request)
    {
        $business = $this->resolveBusiness($request);

        // Check subscription feature
        if (!$this->subscriptionService->canPerformAction($business, 'file_cit')) {
            return redirect()->route('business.plans.index')
                ->with('error', 'Your current plan does not include CIT filing. Please upgrade to Basic or higher.');
        }

        // Get accountants (users with accountant role)
        $accountants = User::role('accountant')
            ->select('id', 'name', 'email')
            ->get();

        return Inertia::render('Business/CIT/Create', [
            'accountants' => $accountants,
        ]);
    }

    /**
     * Store new CIT return
     */
    public function store(Request $request)
    {
        $business = $this->resolveBusiness($request);

        // Check subscription feature
        if (!$this->subscriptionService->canPerformAction($business, 'file_cit')) {
            return redirect()->route('business.plans.index')
                ->with('error', 'Your current plan does not include CIT filing. Please upgrade to Basic or higher.');
        }

        $validated = $request->validate([
            'period' => 'required|string|max:7',
            'turnover' => 'required|numeric|min:0',
            'gross_assets' => 'nullable|numeric|min:0',
            'paid_up_capital' => 'nullable|numeric|min:0',
            'revenue' => 'required|numeric|min:0',
            'cost_of_goods_sold' => 'nullable|numeric|min:0',
            'depreciation' => 'nullable|numeric|min:0',
            'amortization' => 'nullable|numeric|min:0',
            'other_add_backs' => 'nullable|numeric|min:0',
            'capital_allowances' => 'nullable|numeric|min:0',
            'allowable_expenses' => 'nullable|numeric|min:0',
            'other_deductions' => 'nullable|numeric|min:0',
            'withholding_tax' => 'nullable|numeric|min:0',
            'advance_tax' => 'nullable|numeric|min:0',
            'reviewer_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:draft,submitted',
        ]);

        // Determine CIT rate based on turnover (Finance Act 2019)
        $turnover = $validated['turnover'];
        if ($turnover < 25000000) {
            $citRate = 0;
        } elseif ($turnover <= 100000000) {
            $citRate = 0.20;
        } else {
            $citRate = 0.30;
        }

        $citReturn = CitReturn::create([
            'business_id' => $business->id,
            'period' => $validated['period'],
            'turnover' => $validated['turnover'],
            'gross_assets' => $validated['gross_assets'] ?? 0,
            'paid_up_capital' => $validated['paid_up_capital'] ?? 0,
            'revenue' => $validated['revenue'],
            'cost_of_goods_sold' => $validated['cost_of_goods_sold'] ?? 0,
            'depreciation' => $validated['depreciation'] ?? 0,
            'amortization' => $validated['amortization'] ?? 0,
            'other_add_backs' => $validated['other_add_backs'] ?? 0,
            'capital_allowances' => $validated['capital_allowances'] ?? 0,
            'allowable_expenses' => $validated['allowable_expenses'] ?? 0,
            'other_deductions' => $validated['other_deductions'] ?? 0,
            'withholding_tax' => $validated['withholding_tax'] ?? 0,
            'advance_tax' => $validated['advance_tax'] ?? 0,
            'cit_rate' => $citRate,
            'reviewed_by' => $validated['reviewer_id'],
            'notes' => $validated['notes'],
            'status' => $validated['status'] ?? 'draft',
            'due_date' => $this->calculateDueDate($validated['period']),
            'form_data' => [],
        ]);

        // Perform tax calculations
        $citReturn->performCalculations();
        $citReturn->save();

        return redirect()->route('business.cit.show', $citReturn->id)
            ->with('success', 'CIT return created successfully');
    }

    /**
     * Calculate due date based on period
     * CIT returns are due within 6 months after the end of the financial year
     * (18 months for companies in their first year of assessment)
     */
    private function calculateDueDate(string $period, bool $isFirstYear = false): string
    {
        $parts = explode('-', $period);
        $year = (int) $parts[0];

        // Financial year assumed to end Dec 31
        $yearEnd = now()
            ->setYear($year)
            ->setMonth(12)
            ->setDay(31);

        // 6 months for normal companies, 18 months for first-year companies
        $monthsAllowed = $isFirstYear ? 18 : 6;

        return $yearEnd->addMonths($monthsAllowed)->toDateString();
    }

    /**
     * Show CIT return details
     */
    public function show(Request $request, CitReturn $citReturn)
    {
        $business = $this->resolveBusiness($request);

        // Ensure the return belongs to this business
        if ($citReturn->business_id !== $business->id) {
            abort(403, 'Unauthorized');
        }

        $citReturn->load(['reviewer', 'governmentPayments']);

        return Inertia::render('Business/CIT/Show', [
            'citReturn' => $citReturn,
            'calculations' => $citReturn->getCalculationSummary(),
            'governmentPayments' => $citReturn->governmentPayments,
        ]);
    }

    /**
     * Show form to edit CIT return
     */
    public function edit(Request $request, CitReturn $citReturn)
    {
        $business = $this->resolveBusiness($request);

        if ($citReturn->business_id !== $business->id) {
            abort(403, 'Unauthorized');
        }

        // Can only edit if in draft status
        if ($citReturn->status !== 'draft') {
            abort(403, 'Cannot edit submitted returns');
        }

        // Get accountants (users with accountant role)
        $accountants = User::role('accountant')
            ->select('id', 'name', 'email')
            ->get();

        return Inertia::render('Business/CIT/Edit', [
            'citReturn' => $citReturn,
            'accountants' => $accountants,
        ]);
    }

    /**
     * Update CIT return
     */
    public function update(Request $request, CitReturn $citReturn)
    {
        $business = $this->resolveBusiness($request);

        if ($citReturn->business_id !== $business->id) {
            abort(403, 'Unauthorized');
        }

        if ($citReturn->status !== 'draft') {
            abort(403, 'Cannot edit submitted returns');
        }

        $validated = $request->validate([
            'period' => 'required|string|max:7',
            'turnover' => 'required|numeric|min:0',
            'gross_assets' => 'nullable|numeric|min:0',
            'paid_up_capital' => 'nullable|numeric|min:0',
            'revenue' => 'required|numeric|min:0',
            'cost_of_goods_sold' => 'nullable|numeric|min:0',
            'depreciation' => 'nullable|numeric|min:0',
            'amortization' => 'nullable|numeric|min:0',
            'other_add_backs' => 'nullable|numeric|min:0',
            'capital_allowances' => 'nullable|numeric|min:0',
            'allowable_expenses' => 'nullable|numeric|min:0',
            'other_deductions' => 'nullable|numeric|min:0',
            'withholding_tax' => 'nullable|numeric|min:0',
            'advance_tax' => 'nullable|numeric|min:0',
            'reviewer_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:draft,submitted',
        ]);

        // Determine CIT rate based on turnover (Finance Act 2019)
        $turnover = $validated['turnover'];
        if ($turnover < 25000000) {
            $citRate = 0;
        } elseif ($turnover <= 100000000) {
            $citRate = 0.20;
        } else {
            $citRate = 0.30;
        }

        $citReturn->update([
            'period' => $validated['period'],
            'turnover' => $validated['turnover'],
            'gross_assets' => $validated['gross_assets'] ?? 0,
            'paid_up_capital' => $validated['paid_up_capital'] ?? 0,
            'revenue' => $validated['revenue'],
            'cost_of_goods_sold' => $validated['cost_of_goods_sold'] ?? 0,
            'depreciation' => $validated['depreciation'] ?? 0,
            'amortization' => $validated['amortization'] ?? 0,
            'other_add_backs' => $validated['other_add_backs'] ?? 0,
            'capital_allowances' => $validated['capital_allowances'] ?? 0,
            'allowable_expenses' => $validated['allowable_expenses'] ?? 0,
            'other_deductions' => $validated['other_deductions'] ?? 0,
            'withholding_tax' => $validated['withholding_tax'] ?? 0,
            'advance_tax' => $validated['advance_tax'] ?? 0,
            'cit_rate' => $citRate,
            'reviewed_by' => $validated['reviewer_id'],
            'notes' => $validated['notes'],
            'status' => $validated['status'] ?? $citReturn->status,
            'due_date' => $this->calculateDueDate($validated['period']),
        ]);

        // Recalculate
        $citReturn->performCalculations();
        $citReturn->save();

        return redirect()->route('business.cit.show', $citReturn->id)
            ->with('success', 'CIT return updated successfully');
    }

    /**
     * Calculate preview without saving
     */
    public function calculatePreview(Request $request)
    {
        $business = $this->resolveBusiness($request);

        $validated = $request->validate([
            'revenue' => 'required|numeric|min:0',
            'cost_of_goods_sold' => 'nullable|numeric|min:0',
            'depreciation' => 'nullable|numeric|min:0',
            'amortization' => 'nullable|numeric|min:0',
            'other_add_backs' => 'nullable|numeric|min:0',
            'capital_allowances' => 'nullable|numeric|min:0',
            'allowable_expenses' => 'nullable|numeric|min:0',
            'other_deductions' => 'nullable|numeric|min:0',
            'turnover' => 'nullable|numeric|min:0',
            'gross_assets' => 'nullable|numeric|min:0',
            'paid_up_capital' => 'nullable|numeric|min:0',
        ]);

        // Create temporary instance for calculation (don't save)
        $citReturn = new CitReturn($validated);
        $citReturn->performCalculations();

        return response()->json([
            'success' => true,
            'calculations' => $citReturn->getCalculationSummary(),
        ]);
    }

    /**
     * Generate government payment RRR
     */
    public function generatePaymentRRR(Request $request, CitReturn $citReturn)
    {
        $business = $this->resolveBusiness($request);

        if ($citReturn->business_id !== $business->id) {
            abort(403, 'Unauthorized');
        }

        try {
            $rrr = $this->paymentService->generateRemitaRRR(
                reference: "CIT-{$citReturn->period}-{$business->id}",
                amount: $citReturn->balance_due ?? 0,
                description: "CIT Payment for {$citReturn->period}",
                paymentType: 'federal_inland_revenue',
                externalReference: "CIT-{$citReturn->id}"
            );

            // Record the payment
            $citReturn->governmentPayments()->create([
                'reference' => $rrr['reference'],
                'amount' => $citReturn->balance_due,
                'payment_type' => 'federal_inland_revenue',
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
    public function updateStatus(Request $request, CitReturn $citReturn)
    {
        $business = $this->resolveBusiness($request);

        if ($citReturn->business_id !== $business->id) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'status' => 'required|in:draft,submitted,accepted,paid,rejected,overdue',
            'firs_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        if ($validated['status'] === 'submitted') {
            $citReturn->markAsSubmitted();
        } elseif ($validated['status'] === 'accepted') {
            $citReturn->markAsAccepted($validated['firs_reference'] ?? '');
        } elseif ($validated['status'] === 'paid') {
            $citReturn->markAsPaid();
        } else {
            $citReturn->update([
                'status' => $validated['status'],
                'notes' => $validated['notes'],
            ]);
        }

        return redirect()->route('business.cit.show', $citReturn->id)
            ->with('success', 'CIT return status updated successfully');
    }

    /**
     * Export CIT return as PDF
     */
    public function exportPdf(CitReturn $citReturn)
    {
        $business = auth()->user()->ownedBusiness;

        if ($citReturn->business_id !== $business->id) {
            abort(403, 'Unauthorized');
        }

        $generator = new ReturnPdfGenerator();
        $pdf = $generator->generateCitReturnPdf($citReturn);

        $filename = 'cit-return-' . $citReturn->period . '.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Resolve business from request
     */
    private function resolveBusiness(Request $request): Business
    {
        $business = $request->user()->ownedBusiness;

        if (!$business) {
            abort(403, 'No active business selected');
        }

        return $business;
    }
}
