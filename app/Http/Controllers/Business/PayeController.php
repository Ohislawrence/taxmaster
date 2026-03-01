<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\PayeReturn;
use App\Models\PayeSchedule;
use App\Models\BusinessStaff;
use App\Services\PAYECalculationService;
use App\Services\GovernmentPaymentService;
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

        // Get active staff members
        $staff = BusinessStaff::where('business_id', $business->id)
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'designation', 'monthly_salary']);

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

        // Create PAYE return
        $payeReturn = PayeReturn::create([
            'business_id' => $business->id,
            'period' => $validated['period'],
            'total_gross_pay' => $totalGrossPay,
            'total_tax_deducted' => $totalTaxDeducted,
            'staff_count' => count($validated['schedules']),
            'schedule_data' => $scheduleData,
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
        $validated = $request->validate([
            'gross_pay' => 'required|numeric|min:0',
            'allowances' => 'nullable|array',
            'reliefs' => 'nullable|array',
        ]);

        $calculation = $this->payeService->calculateMonthlyPAYE(
            $validated['gross_pay'],
            $validated['allowances'] ?? [],
            $validated['reliefs'] ?? []
        );

        return response()->json($calculation);
    }

    private function resolveBusiness(Request $request)
    {
        $business = $request->user()?->ownedBusiness;

        if (!$business) {
            throw new HttpResponseException(
                redirect()->route('business.setup')
                    ->with('error', 'Please complete business setup first.')
            );
        }

        return $business;
    }
}
