<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\PayeReturn;
use App\Models\PayeSchedule;
use App\Models\BusinessStaff;
use App\Services\PAYECalculationService;
use App\Services\GovernmentPaymentService;
use App\Services\ReturnPdfGenerator;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Inertia\Inertia;

class PayeController extends Controller
{
    public function __construct(
        private PAYECalculationService $payeService,
        private GovernmentPaymentService $paymentService
    ) {}

    /**
     * Display PAYE returns dashboard
     */
    public function index(Request $request)
    {
        $business = $this->resolveBusiness($request);

        $returns = PayeReturn::where('business_id', $business->id)
            ->with(['payments', 'schedules'])
            ->orderBy('period', 'desc')
            ->paginate(12);

        // Ensure staff_count is set for each return
        $returns->getCollection()->transform(function ($return) {
            if ($return->staff_count === null || $return->staff_count === 0) {
                $return->staff_count = $return->schedules->count();
            }
            return $return;
        });

        $stats = [
            'total_returns' => PayeReturn::where('business_id', $business->id)->count(),
            'total_tax_collected' => PayeReturn::where('business_id', $business->id)
                ->where('status', 'paid')
                ->sum('total_tax_deducted') ?? 0,
            'pending_returns' => PayeReturn::where('business_id', $business->id)
                ->whereIn('status', ['draft', 'filed'])
                ->count(),
            'this_month_tax' => PayeReturn::where('business_id', $business->id)
                ->whereYear('period', date('Y'))
                ->whereMonth('period', date('m'))
                ->sum('total_tax_deducted') ?? 0,
        ];

        return Inertia::render('Business/PAYE/Index', [
            'returns' => $returns,
            'stats' => $stats,
        ]);
    }

    /**
     * Show form to create a new PAYE return
     */
    public function create(Request $request)
    {
        $business = $this->resolveBusiness($request);

        // Get active staff members with tax_state for multi-state grouping
        $staff = BusinessStaff::where('business_id', $business->id)
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'designation', 'monthly_salary', 'tax_state']);

        // Append effective_tax_state (falls back to business state)
        $staff->each(function ($member) use ($business) {
            $member->effective_tax_state = $member->tax_state ?: $business->state;
            $stateName = config("nigerian_states.state_options.{$member->effective_tax_state}");
            $member->tax_state_name = $stateName ?: $member->effective_tax_state;
        });

        // Suggest next period based on last return
        $lastReturn = PayeReturn::where('business_id', $business->id)
            ->orderBy('period', 'desc')
            ->first();

        $suggestedPeriod = $lastReturn
            ? date('Y-m', strtotime($lastReturn->period . ' +1 month'))
            : date('Y-m');

        return Inertia::render('Business/PAYE/Create', [
            'staff' => $staff,
            'suggestedPeriod' => $suggestedPeriod,
            'businessState' => $business->state,
            'nigerianStates' => config('nigerian_states.state_options'),
        ]);
    }

    /**
     * Store a new PAYE return
     */
    public function store(Request $request)
    {
        $business = $this->resolveBusiness($request);

        $validated = $request->validate([
            'period' => 'required|date_format:Y-m',
            'schedules' => 'required|array|min:1',
            'schedules.*.staff_id' => 'required|exists:business_staff,id',
            'schedules.*.gross_pay' => 'required|numeric|min:0',
            'schedules.*.allowances' => 'nullable|array',
            'schedules.*.reliefs' => 'nullable|array',
        ]);

        // Check if return already exists for this period
        $existingReturn = PayeReturn::where('business_id', $business->id)
            ->where('period', $validated['period'])
            ->first();

        if ($existingReturn) {
            return back()->withErrors(['period' => 'A PAYE return already exists for this period']);
        }

        // Calculate totals
        $totalGrossPay = 0;
        $totalTaxDeducted = 0;
        $scheduleData = [];

        foreach ($validated['schedules'] as $schedule) {
            $calculation = $this->payeService->calculateMonthlyPAYE(
                $schedule['gross_pay'],
                $schedule['allowances'] ?? [],
                $schedule['reliefs'] ?? []
            );

            $totalGrossPay += $calculation['total_gross'];
            $totalTaxDeducted += $calculation['paye_due'];

            $scheduleData[] = [
                'staff_id' => $schedule['staff_id'],
                'calculation' => $calculation,
            ];
        }

        // Determine tax_state from staff members
        $staffIds = collect($validated['schedules'])->pluck('staff_id');
        $staffMembers = BusinessStaff::whereIn('id', $staffIds)->get();
        $taxStates = $staffMembers->map(fn($s) => $s->effective_tax_state ?? $business->state)->unique();

        // If all staff share one state, use it; otherwise default to business state
        $taxState = $taxStates->count() === 1 ? $taxStates->first() : $business->state;

        // Create PAYE return
        $payeReturn = PayeReturn::create([
            'business_id' => $business->id,
            'period' => $validated['period'],
            'total_gross_pay' => $totalGrossPay,
            'total_tax_deducted' => $totalTaxDeducted,
            'staff_count' => count($validated['schedules']),
            'schedule_data' => $scheduleData,
            'tax_state' => $taxState,
            'status' => 'draft',
        ]);

        // Create individual schedules
        foreach ($validated['schedules'] as $index => $schedule) {
            $calculation = $scheduleData[$index]['calculation'];

            PayeSchedule::create([
                'paye_return_id' => $payeReturn->id,
                'business_staff_id' => $schedule['staff_id'],
                'gross_pay' => $calculation['gross_pay'],
                'allowances' => $calculation['allowances'],
                'tax_reliefs' => $calculation['reliefs'],
                'taxable_income' => $calculation['taxable_income'],
                'paye_due' => $calculation['paye_due'],
                'cumulative_gross' => $calculation['total_gross'],
                'cumulative_tax' => $calculation['paye_due'],
            ]);
        }

        return redirect()->route('business.paye.show', $payeReturn)
            ->with('success', 'PAYE return created successfully');
    }

    /**
     * Display a specific PAYE return
     */
    public function show(Request $request, PayeReturn $payeReturn)
    {
        $this->authorize('view', $payeReturn);

        $payeReturn->load(['schedules.staff', 'payments']);

        // Ensure staff_count is set
        if ($payeReturn->staff_count === null || $payeReturn->staff_count === 0) {
            $payeReturn->staff_count = $payeReturn->schedules->count();
        }

        return Inertia::render('Business/PAYE/Show', [
            'payeReturn' => $payeReturn,
        ]);
    }

    /**
     * Update PAYE return status (file, mark as paid, etc.)
     */
    public function updateStatus(Request $request, PayeReturn $payeReturn)
    {
        $this->authorize('update', $payeReturn);

        $validated = $request->validate([
            'status' => 'required|in:filed,paid',
            'filed_date' => 'required_if:status,filed|date',
            'firs_reference' => 'nullable|string|max:100',
        ]);

        $payeReturn->update($validated);

        return back()->with('success', 'PAYE return status updated successfully');
    }

    /**
     * Generate Remita RRR for payment
     */
    public function generatePaymentRRR(Request $request, PayeReturn $payeReturn)
    {
        $this->authorize('update', $payeReturn);

        $business = $this->resolveBusiness($request);

        // Generate RRR via Remita
        $rrrResult = $this->paymentService->generateRRR('PAYE', $payeReturn, $business);

        if (!$rrrResult['success']) {
            return back()->withErrors(['payment' => $rrrResult['message']]);
        }

        // Create payment record
        $payment = $this->paymentService->createPayment(
            $business,
            'PAYE',
            $payeReturn,
            $payeReturn->total_tax_deducted,
            'remita',
            $rrrResult['rrr']
        );

        return back()->with('success', 'Payment RRR generated successfully')
            ->with('payment', $payment);
    }

    /**
     * Calculate PAYE for preview (AJAX)
     */
    public function calculatePreview(Request $request)
    {
        $business = $this->resolveBusiness($request);

        $validated = $request->validate([
            'period' => 'required|date_format:Y-m',
            'staff_ids' => 'required|array|min:1',
            'staff_ids.*' => 'required|exists:business_staff,id',
        ]);

        // Get selected staff members belonging to this business
        $staffMembers = BusinessStaff::where('business_id', $business->id)
            ->whereIn('id', $validated['staff_ids'])
            ->get();

        if ($staffMembers->isEmpty()) {
            return response()->json(['error' => 'No valid staff members found.'], 422);
        }

        $schedules = [];
        $totalGrossPay = 0;
        $totalTaxDeducted = 0;

        foreach ($staffMembers as $staff) {
            $grossPay = (float) $staff->monthly_salary;

            $calculation = $this->payeService->calculateMonthlyPAYE($grossPay);

            $schedules[] = [
                'staff_id' => $staff->id,
                'staff_name' => $staff->full_name,
                'designation' => $staff->designation,
                'gross_pay' => $grossPay,
                'allowances' => $calculation['allowances'],
                'reliefs' => $calculation['reliefs'],
                'total_reliefs' => $calculation['total_reliefs'],
                'taxable_income' => $calculation['taxable_income'],
                'paye_due' => $calculation['paye_due'],
                'net_pay' => $calculation['net_pay'],
                'effective_rate' => $calculation['effective_rate'],
            ];

            $totalGrossPay += $grossPay;
            $totalTaxDeducted += $calculation['paye_due'];
        }

        return response()->json([
            'period' => $validated['period'],
            'schedules' => $schedules,
            'total_gross_pay' => round($totalGrossPay, 2),
            'total_tax_deducted' => round($totalTaxDeducted, 2),
            'total_net_pay' => round($totalGrossPay - $totalTaxDeducted, 2),
            'total_reliefs' => round(collect($schedules)->sum('total_reliefs'), 2),
            'total_taxable' => round(collect($schedules)->sum('taxable_income'), 2),
            'staff_count' => count($schedules),
        ]);
    }

    private function resolveBusiness(Request $request)
    {
        $business = $request->user()?->defaultBusiness();

        if (!$business) {
            throw new HttpResponseException(
                redirect()->route('business.setup')
                    ->with('error', 'Please complete business setup first.')
            );
        }

        return $business;
    }

    /**
     * Export PAYE return as PDF
     */
    public function exportPdf(PayeReturn $payeReturn)
    {
        $this->authorize('view', $payeReturn);

        $generator = new ReturnPdfGenerator();
        $pdf = $generator->generatePayeReturnPdf($payeReturn);

        $filename = 'paye-return-' . $payeReturn->period . '.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Export PAYE schedules for a single PAYE return (CSV or XML)
     */
    public function exportSchedules(Request $request, PayeReturn $payeReturn)
    {
        $this->authorize('view', $payeReturn);

        $business = $this->resolveBusiness($request);

        if ($payeReturn->business_id !== $business->id) {
            abort(403, 'Unauthorized');
        }

        $payeReturn->load('schedules.staff');

        $format = strtolower($request->get('format', 'csv'));

        if ($format === 'csv') {
            $csv = "Staff ID,Staff Name,TIN,Gross Pay,Allowances,Reliefs,Taxable Income,PAYE Due,Cumulative Gross,Cumulative Tax\n";

            foreach ($payeReturn->schedules as $s) {
                $staff = $s->staff;
                $csv .= sprintf(
                    '"%s","%s","%s",%s,%s,%s,%s,%s,%s,%s\n',
                    $staff->id ?? 'N/A',
                    ($staff->full_name ?? 'N/A'),
                    ($staff->tax_identification_number ?? 'N/A'),
                    $s->gross_pay,
                    number_format($s->getTotalAllowancesAttribute() ?? 0, 2, '.', ''),
                    number_format($s->getTotalReliefsAttribute() ?? 0, 2, '.', ''),
                    $s->taxable_income,
                    $s->paye_due,
                    $s->cumulative_gross,
                    $s->cumulative_tax
                );
            }

            $filename = 'paye-schedules-' . $payeReturn->period . '-' . $business->id . '.csv';

            return response($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        $xml = new \SimpleXMLElement('<PayeSchedules/>');
        $xml->addChild('Period', $payeReturn->period);
        $xml->addChild('Business', $business->name ?? '');
        $schedulesNode = $xml->addChild('Schedules');

        foreach ($payeReturn->schedules as $s) {
            $item = $schedulesNode->addChild('Schedule');
            $staff = $s->staff;
            $item->addChild('StaffId', $staff->id ?? '');
            $item->addChild('FullName', $staff->full_name ?? '');
            $item->addChild('TIN', $staff->tax_identification_number ?? '');
            $item->addChild('GrossPay', (string)$s->gross_pay);
            $item->addChild('Allowances', (string)($s->getTotalAllowancesAttribute() ?? 0));
            $item->addChild('Reliefs', (string)($s->getTotalReliefsAttribute() ?? 0));
            $item->addChild('TaxableIncome', (string)$s->taxable_income);
            $item->addChild('PayeDue', (string)$s->paye_due);
            $item->addChild('CumulativeGross', (string)$s->cumulative_gross);
            $item->addChild('CumulativeTax', (string)$s->cumulative_tax);
        }

        return response($xml->asXML(), 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => 'attachment; filename="paye-schedules-' . $payeReturn->period . '.xml"',
        ]);
    }

    /**
     * Export PAYE schedules across multiple returns for this business (bulk)
     */
    public function exportSchedulesBulk(Request $request)
    {
        $business = $this->resolveBusiness($request);

        $query = PayeReturn::with(['schedules.staff'])
            ->where('business_id', $business->id);

        if ($request->filled('period')) {
            $query->where('period', $request->period);
        }

        $returns = $query->get();

        $format = strtolower($request->get('format', 'csv'));

        if ($format === 'csv') {
            $csv = "ReturnPeriod,Business,Staff ID,Staff Name,TIN,Gross Pay,PAYE Due\n";

            foreach ($returns as $r) {
                foreach ($r->schedules as $s) {
                    $staff = $s->staff;
                    $csv .= sprintf(
                        '"%s","%s","%s","%s","%s",%s,%s\n',
                        $r->period,
                        $business->name ?? 'N/A',
                        $staff->id ?? '',
                        $staff->full_name ?? '',
                        $staff->tax_identification_number ?? '',
                        $s->gross_pay,
                        $s->paye_due
                    );
                }
            }

            return response($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="paye-schedules-bulk-' . date('Y-m-d') . '.csv"',
            ]);
        }

        return response('Only CSV supported for bulk export at this time', 400);
    }

    /**
     * Generate annual PAYE return (Form H1)
     * Aggregates all monthly returns for a given tax year.
     */
    public function generateFormH1(Request $request)
    {
        $business = $this->resolveBusiness($request);

        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:' . (date('Y') + 1),
        ]);

        $year = $validated['year'];

        // Check if annual return already exists
        $existingAnnual = PayeReturn::where('business_id', $business->id)
            ->where('period', $year)
            ->where('return_type', 'annual')
            ->first();

        if ($existingAnnual) {
            return back()->withErrors(['year' => "An annual Form H1 return already exists for {$year}."]);
        }

        // Get all monthly returns for the year
        $monthlyReturns = PayeReturn::where('business_id', $business->id)
            ->where('period', 'like', $year . '-%')
            ->where('return_type', 'monthly')
            ->orderBy('period')
            ->get();

        if ($monthlyReturns->isEmpty()) {
            return back()->withErrors(['year' => "No monthly PAYE returns found for {$year}. File monthly returns first."]);
        }

        // Aggregate data from all monthly returns
        $totalGrossPay = $monthlyReturns->sum('total_gross_pay');
        $totalTaxDeducted = $monthlyReturns->sum('total_tax_deducted');

        // Build annual schedule from all monthly schedules
        $annualSchedule = [];
        $staffAggregation = [];

        foreach ($monthlyReturns as $return) {
            if (!empty($return->schedule_data)) {
                foreach ($return->schedule_data as $entry) {
                    $staffId = $entry['staff_id'] ?? null;
                    if ($staffId) {
                        if (!isset($staffAggregation[$staffId])) {
                            $staffAggregation[$staffId] = [
                                'staff_id' => $staffId,
                                'months_count' => 0,
                                'total_gross' => 0,
                                'total_tax' => 0,
                            ];
                        }
                        $staffAggregation[$staffId]['months_count']++;
                        $staffAggregation[$staffId]['total_gross'] += $entry['calculation']['total_gross'] ?? $entry['calculation']['gross_pay'] ?? 0;
                        $staffAggregation[$staffId]['total_tax'] += $entry['calculation']['paye_due'] ?? 0;
                    }
                }
            }
        }

        $annualSchedule = array_values($staffAggregation);

        // Get unique staff count
        $uniqueStaffCount = count($staffAggregation);

        // Create annual Form H1 return
        $formH1 = PayeReturn::create([
            'business_id' => $business->id,
            'period' => (string) $year,
            'return_type' => 'annual',
            'total_gross_pay' => $totalGrossPay,
            'total_tax_deducted' => $totalTaxDeducted,
            'staff_count' => $uniqueStaffCount,
            'schedule_data' => [
                'monthly_returns' => $monthlyReturns->map(fn($r) => [
                    'period' => $r->period,
                    'total_gross_pay' => $r->total_gross_pay,
                    'total_tax_deducted' => $r->total_tax_deducted,
                    'staff_count' => $r->staff_count,
                    'status' => $r->status,
                ])->toArray(),
                'staff_annual_summary' => $annualSchedule,
            ],
            'status' => 'draft',
            'notes' => "Annual Form H1 for {$year}. Generated from {$monthlyReturns->count()} monthly returns.",
        ]);

        return redirect()->route('business.paye.show', $formH1)
            ->with('success', "Annual Form H1 for {$year} generated successfully from {$monthlyReturns->count()} monthly returns.");
    }
}
