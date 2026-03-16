<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\TaxReturn;
use App\Models\TaxType;
use App\Services\TaxCalculationService;
use App\Services\AiAgentService;
use App\Services\ComplianceService;
use App\Services\TaxReturnPdfGenerator;
use App\Services\TaxCalculators\TaxCalculatorFactory;
use Inertia\Inertia;
use Illuminate\Http\Request;

class TaxReturnController extends Controller
{
    protected $taxCalculationService;
    protected $aiAgentService;
    protected $complianceService;
    protected $pdfGenerator;

    public function __construct(
        TaxCalculationService $taxCalculationService,
        AiAgentService $aiAgentService,
        ComplianceService $complianceService,
        TaxReturnPdfGenerator $pdfGenerator
    ) {
        $this->taxCalculationService = $taxCalculationService;
        $this->aiAgentService = $aiAgentService;
        $this->complianceService = $complianceService;
        $this->pdfGenerator = $pdfGenerator;
    }

    /**
     * Display all tax returns
     */
    public function index()
    {
        $business = auth()->user()->defaultBusiness();

        $taxReturns = $business->taxReturns()
            ->with('taxType')
            ->orderBy('tax_period', 'desc')
            ->paginate(20);

        $complianceStatus = $this->complianceService->getComplianceStatus($business);

        return Inertia::render('Business/TaxReturns/Index', [
            'taxReturns' => $taxReturns,
            'complianceStatus' => $complianceStatus,
        ]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        $business = auth()->user()->defaultBusiness();
        $taxTypes = TaxType::active()->get();

        return Inertia::render('Business/TaxReturns/Create', [
            'business' => $business,
            'taxTypes' => $taxTypes,
            'nigerianStates' => $this->getNigerianStates(),
        ]);
    }

    /**
     * Store new tax return
     */
    public function store(Request $request)
    {
        $business = auth()->user()->defaultBusiness();

        $validated = $request->validate([
            'tax_type_id' => 'required|exists:tax_types,id',
            'tax_period' => 'required',
            'return_type' => 'required|in:monthly,quarterly,annual',
            'due_date' => 'required|date',
            'date_filed' => 'nullable|date|before_or_equal:today',
            'date_paid' => 'nullable|date|after_or_equal:date_filed',
            'state_code' => 'nullable|string|max:2',
            'filing_status' => 'nullable|string',
            'gross_income' => 'required|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'additional_data' => 'nullable|array',
        ]);

        $taxType = TaxType::findOrFail($validated['tax_type_id']);
        $calculator = TaxCalculatorFactory::make($taxType);

        $deductions = $validated['deductions'] ?? 0;
        $taxableIncome = $validated['gross_income'] - $deductions;

        // Prepare calculation data
        $calculationData = array_merge([
            'gross_income' => $validated['gross_income'],
            'taxable_income' => $taxableIncome,
            'business' => $business,
            'tax_period' => $validated['tax_period'],
        ], $validated['additional_data'] ?? []);

        // Calculate tax using appropriate calculator
        $taxResult = $calculator->calculate($calculationData);

        $taxReturn = $business->taxReturns()->create([
            'tax_type_id' => $validated['tax_type_id'],
            'return_type' => $validated['return_type'],
            'tax_period' => $validated['tax_period'],
            'due_date' => $validated['due_date'],
            'date_filed' => $validated['date_filed'] ?? null,
            'date_paid' => $validated['date_paid'] ?? null,
            'state_code' => $validated['state_code'] ?? $business->state,
            'filing_status' => $validated['filing_status'],
            'gross_income' => $validated['gross_income'],
            'deductions' => $deductions,
            'taxable_income' => $taxableIncome,
            'total_tax_due' => $taxResult['tax_due'],
            'total_amount_due' => $taxResult['total_due'] ?? $taxResult['tax_due'],
            'reliefs_claimed' => $taxResult['reliefs'] ?? [],
            'calculation_details' => $taxResult,
            'status' => 'draft',
        ]);

        return redirect()->route('business.tax-returns.show', $taxReturn)
            ->with('message', 'Tax return created successfully');
    }

    /**
     * Show tax return details
     */
    public function show(TaxReturn $taxReturn)
    {
        $this->authorize('view', $taxReturn);

        $upcomingDeadlines = $this->complianceService->getUpcomingDeadlines($taxReturn->business, 30);

        return Inertia::render('Business/TaxReturns/Show', [
            'taxReturn' => $taxReturn->load(['payments', 'taxType', 'reminders']),
            'upcomingDeadlines' => $upcomingDeadlines,
        ]);
    }

    /**
     * Show edit form
     */
    public function edit(TaxReturn $taxReturn)
    {
        $this->authorize('update', $taxReturn);

        return Inertia::render('Business/TaxReturns/Edit', [
            'taxReturn' => $taxReturn,
        ]);
    }

    /**
     * Update tax return
     */
    public function update(TaxReturn $taxReturn, Request $request)
    {
        $this->authorize('update', $taxReturn);

        $validated = $request->validate([
            'gross_income' => 'required|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'date_filed' => 'nullable|date|before_or_equal:today',
            'date_paid' => 'nullable|date|after_or_equal:date_filed',
            'additional_data' => 'nullable|array',
        ]);

        $calculator = TaxCalculatorFactory::make($taxReturn->taxType);

        $deductions = $validated['deductions'] ?? 0;
        $taxableIncome = $validated['gross_income'] - $deductions;

        // Recalculate tax
        $calculationData = array_merge([
            'gross_income' => $validated['gross_income'],
            'taxable_income' => $taxableIncome,
            'business' => $taxReturn->business,
            'tax_period' => $taxReturn->tax_period,
        ], $validated['additional_data'] ?? []);

        $taxResult = $calculator->calculate($calculationData);

        $taxReturn->update([
            'gross_income' => $validated['gross_income'],
            'deductions' => $deductions,
            'taxable_income' => $taxableIncome,
            'total_tax_due' => $taxResult['tax_due'],
            'total_amount_due' => $taxResult['total_due'] ?? $taxResult['tax_due'],
            'reliefs_claimed' => $taxResult['reliefs'] ?? [],
            'calculation_details' => $taxResult,
            'date_filed' => $validated['date_filed'] ?? $taxReturn->date_filed,
            'date_paid' => $validated['date_paid'] ?? $taxReturn->date_paid,
        ]);

        return redirect()->route('business.tax-returns.show', $taxReturn)
            ->with('message', 'Tax return updated successfully');
    }

    /**
     * Submit tax return
     */
    public function submit(TaxReturn $taxReturn, Request $request)
    {
        $this->authorize('update', $taxReturn);

        if ($taxReturn->status !== 'draft') {
            return back()->withErrors(['error' => 'Only draft returns can be submitted']);
        }

        $taxReturn->update([
            'status' => 'submitted',
            'submission_date' => now(),
        ]);

        return back()->with('message', 'Tax return submitted successfully');
    }

    /**
     * Get AI analysis
     */
    public function getAnalysis(TaxReturn $taxReturn)
    {
        $this->authorize('view', $taxReturn);

        if (!$taxReturn->ai_analysis) {
            return response()->json(['analysis' => null]);
        }

        return response()->json([
            'analysis' => $taxReturn->ai_analysis,
        ]);
    }

    /**
     * Analyze tax return with AI
     */
    public function analyze(TaxReturn $taxReturn, Request $request)
    {
        $this->authorize('update', $taxReturn);

        $business = $taxReturn->business;
        $aiService = new AiAgentService($business);

        $result = $aiService->analyzeTaxReturn($taxReturn);

        if ($result['success']) {
            return back()->with('message', 'AI analysis completed successfully');
        }

        return back()->withErrors(['error' => $result['message'] ?? 'Analysis failed']);
    }

    /**
     * Delete tax return
     */
    public function destroy(TaxReturn $taxReturn)
    {
        $this->authorize('delete', $taxReturn);

        if ($taxReturn->status !== 'draft') {
            return back()->withErrors(['error' => 'Only draft returns can be deleted']);
        }

        $taxReturn->delete();

        return redirect()->route('business.tax-returns.index')
            ->with('message', 'Tax return deleted successfully');
    }

    /**
     * Export tax return as PDF
     */
    public function exportPdf(TaxReturn $taxReturn)
    {
        $this->authorize('view', $taxReturn);

        $pdf = $this->pdfGenerator->generate($taxReturn);
        $filename = 'tax-return-' . $taxReturn->tax_period . '-' . $taxReturn->id . '.pdf';

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'public, must-revalidate, max-age=0');
    }

    /**
     * Get Nigerian states
     */
    protected function getNigerianStates(): array
    {
        return [
            'AB' => 'Abia', 'AD' => 'Adamawa', 'AK' => 'Akwa Ibom', 'AN' => 'Anambra',
            'BA' => 'Bauchi', 'BY' => 'Bayelsa', 'BE' => 'Benue', 'BO' => 'Borno',
            'CR' => 'Cross River', 'DE' => 'Delta', 'EB' => 'Ebonyi', 'ED' => 'Edo',
            'EK' => 'Ekiti', 'EN' => 'Enugu', 'GO' => 'Gombe', 'IM' => 'Imo',
            'JI' => 'Jigawa', 'KD' => 'Kaduna', 'KN' => 'Kano', 'KT' => 'Katsina',
            'KE' => 'Kebbi', 'KO' => 'Kogi', 'KW' => 'Kwara', 'LA' => 'Lagos',
            'NA' => 'Nasarawa', 'NI' => 'Niger', 'OG' => 'Ogun', 'ON' => 'Ondo',
            'OS' => 'Osun', 'OY' => 'Oyo', 'PL' => 'Plateau', 'RI' => 'Rivers',
            'SO' => 'Sokoto', 'TA' => 'Taraba', 'YO' => 'Yobe', 'ZA' => 'Zamfara',
            'FC' => 'FCT',
        ];
    }
}
