<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\BusinessStaff;
use App\Models\TaxReturn;
use App\Services\AiAgentService;
use App\Services\BusinessService;
use App\Services\PayslipPdfGenerator;
use App\Services\SubscriptionService;
use App\Services\TaxCalculationService;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StaffController extends Controller
{
    protected $businessService;
    protected $taxCalculationService;
    protected $subscriptionService;

    public function __construct(BusinessService $businessService, TaxCalculationService $taxCalculationService, SubscriptionService $subscriptionService)
    {
        $this->businessService = $businessService;
        $this->taxCalculationService = $taxCalculationService;
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Get the staff limit for the business based on subscription
     */
    protected function getStaffLimit($business): int
    {
        $subscription = $this->subscriptionService->getActiveSubscription($business);
        return $subscription->max_staff_members ?? 1;
    }

    /**
     * Display all staff
     */
    public function index()
    {
        $business = auth()->user()->ownedBusiness;

        $staff = $business->staff()
            ->orderBy('first_name')
            ->paginate(20);

        $stats = [
            'total_staff' => $business->staff()->count(),
            'active_staff' => $business->staff()->where('status', 'active')->count(),
            'total_monthly_payroll' => $business->staff()->where('status', 'active')->sum('monthly_salary'),
            'total_monthly_tax' => $business->staff()->where('status', 'active')->get()->sum(function ($s) {
                return $this->taxCalculationService->calculateMonthlyStaffTax($s);
            }),
        ];

        return Inertia::render('Business/Staff/Index', [
            'staff' => $staff,
            'stats' => $stats,
        ]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        $business = auth()->user()->ownedBusiness;

        return Inertia::render('Business/Staff/Create', [
            'business' => $business,
            'nigerianStates' => config('nigerian_states.state_options'),
        ]);
    }

    /**
     * Store new staff member
     */
    public function store(Request $request)
    {
        $business = auth()->user()->ownedBusiness;

        // Check staff limit
        $staffLimit = $this->getStaffLimit($business);
        $currentCount = $business->staff()->count();

        if ($currentCount >= $staffLimit) {
            return back()
                ->with('error', "You've reached your staff limit ({$staffLimit}). Please upgrade your plan to add more staff members.")
                ->withInput();
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:business_staff',
            'phone' => 'nullable|string',
            'tax_identification_number' => ['nullable', 'string', 'regex:/^\d{8,14}(-\d{1,4})?$/', 'unique:business_staff'],
            'monthly_salary' => 'required|numeric|min:0',
            'employment_type' => 'required|in:full_time,part_time,contract',
            'designation' => 'required|string|max:255',
            'date_employed' => 'required|date',
            'tax_state' => 'nullable|string|in:' . implode(',', array_keys(config('nigerian_states.state_options', []))),
        ]);

        $staff = $this->businessService->addStaff($business, $validated);

        return redirect()->route('business.staff.show', $staff)
            ->with('message', 'Staff member added successfully');
    }

    /**
     * Show staff details
     */
    public function show(BusinessStaff $staff)
    {
        $this->authorize('view', $staff);

        $monthlyTax = $this->taxCalculationService->calculateMonthlyStaffTax($staff);

        return Inertia::render('Business/Staff/Show', [
            'staff' => $staff,
            'monthlyTax' => $monthlyTax,
            'annualTax' => $monthlyTax * 12,
        ]);
    }

    /**
     * Show edit form
     */
    public function edit(BusinessStaff $staff)
    {
        $this->authorize('update', $staff);

        return Inertia::render('Business/Staff/Edit', [
            'staff' => $staff,
            'nigerianStates' => config('nigerian_states.state_options'),
        ]);
    }

    /**
     * Update staff member
     */
    public function update(BusinessStaff $staff, Request $request)
    {
        $this->authorize('update', $staff);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:business_staff,email,' . $staff->id,
            'phone' => 'nullable|string',
            'monthly_salary' => 'required|numeric|min:0',
            'designation' => 'required|string|max:255',
            'status' => 'required|in:active,on_leave,terminated',
            'tax_state' => 'nullable|string|in:' . implode(',', array_keys(config('nigerian_states.state_options', []))),
        ]);

        $staff = $this->businessService->updateStaff($staff, $validated);

        return redirect()->route('business.staff.show', $staff)
            ->with('message', 'Staff member updated successfully');
    }

    /**
     * Get tax analysis for staff
     */
    public function taxAnalysis(BusinessStaff $staff)
    {
        $this->authorize('view', $staff);

        $monthlyTax = $this->taxCalculationService->calculateMonthlyStaffTax($staff);
        $annualTax = $monthlyTax * 12;

        return Inertia::render('Business/Staff/TaxAnalysis', [
            'staff' => $staff,
            'monthlyTax' => $monthlyTax,
            'annualTax' => $annualTax,
            'breakdown' => [
                'monthly_salary' => $staff->monthly_salary,
                'personal_relief' => config('taxmaster.tax.personal_reliefs.personal') / 12,
                'taxable_amount' => $staff->monthly_salary - (config('taxmaster.tax.personal_reliefs.personal') / 12),
                'tax_rate' => '10%', // Simplified
            ],
        ]);
    }

    /**
     * Generate payslip PDF for a staff member
     */
    public function generatePayslip(BusinessStaff $staff, ?int $year = null, ?int $month = null)
    {
        $this->authorize('view', $staff);

        $monthlyTax = $this->taxCalculationService->calculateMonthlyStaffTax($staff);

        $generator = new PayslipPdfGenerator();
        $pdf = $generator->generate($staff, $monthlyTax, $year, $month);

        $period = now()->setYear($year ?? now()->year)->setMonth($month ?? now()->month);
        $filename = 'payslip-' . str_replace(' ', '-', strtolower($staff->full_name)) . '-' . $period->format('F-Y') . '.pdf';

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'public, must-revalidate, max-age=0');
    }

    /**
     * Show bulk upload form
     */
    public function bulkUploadForm()
    {
        return Inertia::render('Business/Staff/BulkUpload');
    }

    /**
     * Use AI to map uploaded column headers to staff fields
     */
    public function mapColumns(Request $request)
    {
        $request->validate([
            'headers' => 'required|array|min:1',
            'headers.*' => 'string',
            'sample_rows' => 'nullable|array',
        ]);

        $headers = $request->input('headers');
        $sampleRows = $request->input('sample_rows', []);

        // Define target fields
        $targetFields = [
            'first_name' => 'First name of the staff member',
            'last_name' => 'Last name / surname of the staff member',
            'email' => 'Email address',
            'phone' => 'Phone number',
            'tax_identification_number' => 'Tax Identification Number (TIN)',
            'monthly_salary' => 'Monthly salary amount in Naira',
            'employment_type' => 'Employment type: full_time, part_time, or contract',
            'designation' => 'Job title / designation / role',
            'date_employed' => 'Date of employment (hire date)',
        ];

        // Try AI mapping first
        $mapping = $this->tryAiColumnMapping($headers, $sampleRows, $targetFields);

        // Fallback to fuzzy matching if AI is unavailable
        if (!$mapping) {
            $mapping = $this->fuzzyMapColumns($headers, $targetFields);
        }

        return response()->json([
            'mapping' => $mapping,
            'target_fields' => $targetFields,
        ]);
    }

    /**
     * Try AI-powered column mapping
     */
    protected function tryAiColumnMapping(array $headers, array $sampleRows, array $targetFields): ?array
    {
        try {
            $business = auth()->user()->ownedBusiness;
            if (!$business) return null;

            $aiService = new AiAgentService($business);

            $sampleDataStr = '';
            if (!empty($sampleRows)) {
                $sampleDataStr = "\n\nHere are some sample data rows to help you understand the data:\n";
                foreach (array_slice($sampleRows, 0, 3) as $i => $row) {
                    $sampleDataStr .= "Row " . ($i + 1) . ": " . json_encode($row) . "\n";
                }
            }

            $prompt = <<<PROMPT
You are helping map CSV/Excel column headers to a staff database schema.

The uploaded file has these column headers:
{$this->formatList($headers)}
{$sampleDataStr}
The target database fields are:
{$this->formatFieldDescriptions($targetFields)}

For each uploaded column header, determine which target field it maps to. If a column doesn't match any target field, map it to "skip".

IMPORTANT: If a column seems to contain a full name (like "Name", "Staff Name", "Full Name", "Employee Name"), map it to "full_name" (special value - the system will split it into first_name and last_name).

Respond ONLY with a valid JSON object mapping each uploaded header to either a target field key or "skip".
Example format: {"Column A": "first_name", "Column B": "email", "Column C": "skip"}

JSON response:
PROMPT;

            $result = $aiService->callAiForCategorization($prompt);

            if ($result) {
                // Extract JSON from AI response
                $result = trim($result);
                // Handle markdown code blocks
                if (str_contains($result, '```')) {
                    preg_match('/```(?:json)?\s*\n?(.*?)\n?```/s', $result, $matches);
                    $result = $matches[1] ?? $result;
                }

                $mapping = json_decode($result, true);
                if (is_array($mapping)) {
                    Log::info('AI column mapping successful', ['mapping' => $mapping]);
                    return $mapping;
                }
            }
        } catch (\Exception $e) {
            Log::warning('AI column mapping failed, falling back to fuzzy matching', [
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }

    /**
     * Fuzzy match column headers to target fields
     */
    protected function fuzzyMapColumns(array $headers, array $targetFields): array
    {
        $mapping = [];

        $aliases = [
            'first_name' => ['first name', 'firstname', 'first', 'given name', 'forename'],
            'last_name' => ['last name', 'lastname', 'last', 'surname', 'family name'],
            'email' => ['email', 'email address', 'e-mail', 'mail'],
            'phone' => ['phone', 'phone number', 'telephone', 'tel', 'mobile', 'cell', 'contact'],
            'tax_identification_number' => ['tin', 'tax id', 'tax identification', 'tax number', 'tax_id', 'tax identification number'],
            'monthly_salary' => ['salary', 'monthly salary', 'pay', 'wage', 'monthly pay', 'monthly wage', 'compensation', 'amount'],
            'employment_type' => ['employment type', 'type', 'employment', 'contract type', 'work type', 'job type', 'emp type'],
            'designation' => ['designation', 'title', 'job title', 'position', 'role', 'job role', 'dept', 'department'],
            'date_employed' => ['date employed', 'hire date', 'start date', 'employment date', 'date hired', 'joined', 'joining date', 'date of employment', 'date_employed'],
            'full_name' => ['name', 'full name', 'fullname', 'staff name', 'employee name', 'employee', 'staff'],
        ];

        foreach ($headers as $header) {
            $normalizedHeader = strtolower(trim($header));
            $matched = false;

            foreach ($aliases as $field => $fieldAliases) {
                foreach ($fieldAliases as $alias) {
                    if ($normalizedHeader === $alias || str_contains($normalizedHeader, $alias) || str_contains($alias, $normalizedHeader)) {
                        $mapping[$header] = $field;
                        $matched = true;
                        break 2;
                    }
                }
            }

            if (!$matched) {
                $mapping[$header] = 'skip';
            }
        }

        return $mapping;
    }

    /**
     * Process the bulk upload data
     */
    public function processBulkUpload(Request $request)
    {
        $request->validate([
            'staff_data' => 'required|array|min:1',
            'staff_data.*.first_name' => 'required|string|max:255',
            'staff_data.*.last_name' => 'required|string|max:255',
            'staff_data.*.email' => 'required|email',
            'staff_data.*.phone' => 'nullable|string',
            'staff_data.*.tax_identification_number' => ['nullable', 'string', 'regex:/^\d{8,14}(-\d{1,4})?$/'],
            'staff_data.*.monthly_salary' => 'required|numeric|min:0',
            'staff_data.*.employment_type' => 'required|in:full_time,part_time,contract',
            'staff_data.*.designation' => 'required|string|max:255',
            'staff_data.*.date_employed' => 'required|date',
        ]);

        $business = auth()->user()->ownedBusiness;
        $staffData = $request->input('staff_data');
        $results = ['success' => 0, 'failed' => 0, 'errors' => []];

        // Check staff limit before processing
        $staffLimit = $this->getStaffLimit($business);
        $currentCount = $business->staff()->count();
        $availableSlots = max(0, $staffLimit - $currentCount);

        if ($availableSlots === 0) {
            return response()->json([
                'message' => "You've reached your staff limit ({$staffLimit}). Please upgrade your plan to add more staff members.",
                'results' => ['success' => 0, 'failed' => count($staffData), 'errors' => ["Staff limit ({$staffLimit}) reached. Upgrade your plan to add more."]],
            ], 422);
        }

        if (count($staffData) > $availableSlots) {
            return response()->json([
                'message' => "You can only add {$availableSlots} more staff member(s) (limit: {$staffLimit}, current: {$currentCount}). Please reduce the number of rows or upgrade your plan.",
                'results' => ['success' => 0, 'failed' => count($staffData), 'errors' => ["Only {$availableSlots} slot(s) available. You're trying to add " . count($staffData) . "."]],
            ], 422);
        }

        DB::beginTransaction();

        try {
            foreach ($staffData as $index => $data) {
                $rowNum = $index + 1;

                // Double-check limit during loop (safety net)
                if ($results['success'] >= $availableSlots) {
                    $results['failed'] += count($staffData) - $index;
                    $results['errors'][] = "Row {$rowNum} onwards: Staff limit reached.";
                    break;
                }

                // Check for duplicate email within this business
                $existingEmail = BusinessStaff::where('business_id', $business->id)
                    ->where('email', $data['email'])
                    ->exists();

                if ($existingEmail) {
                    $results['failed']++;
                    $results['errors'][] = "Row {$rowNum}: Email '{$data['email']}' already exists for a staff member.";
                    continue;
                }

                // Check for duplicate TIN if provided
                if (!empty($data['tax_identification_number'])) {
                    $existingTin = BusinessStaff::where('tax_identification_number', $data['tax_identification_number'])->exists();
                    if ($existingTin) {
                        $results['failed']++;
                        $results['errors'][] = "Row {$rowNum}: TIN '{$data['tax_identification_number']}' already exists.";
                        continue;
                    }
                }

                // Validate individual row
                $validator = Validator::make($data, [
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string|max:255',
                    'email' => 'required|email',
                    'monthly_salary' => 'required|numeric|min:0',
                    'employment_type' => 'required|in:full_time,part_time,contract',
                    'designation' => 'required|string|max:255',
                    'date_employed' => 'required|date',
                ]);

                if ($validator->fails()) {
                    $results['failed']++;
                    $errorMessages = implode(', ', $validator->errors()->all());
                    $results['errors'][] = "Row {$rowNum}: {$errorMessages}";
                    continue;
                }

                try {
                    $this->businessService->addStaff($business, $data);
                    $results['success']++;
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = "Row {$rowNum}: " . $e->getMessage();
                }
            }

            DB::commit();

            $message = "{$results['success']} staff member(s) added successfully.";
            if ($results['failed'] > 0) {
                $message .= " {$results['failed']} row(s) had errors.";
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => $message,
                    'results' => $results,
                ]);
            }

            return redirect()->route('business.staff.index')
                ->with('message', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk staff upload failed', ['error' => $e->getMessage()]);

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Bulk upload failed: ' . $e->getMessage(),
                    'results' => $results,
                ], 500);
            }

            return back()->with('error', 'Bulk upload failed. Please try again.');
        }
    }

    /**
     * Download CSV template
     */
    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="staff_upload_template.csv"',
        ];

        return response()->stream(function () {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fwrite($file, "\xEF\xBB\xBF");

            // Header row
            fputcsv($file, [
                'First Name',
                'Last Name',
                'Email',
                'Phone',
                'Tax Identification Number (TIN)',
                'Monthly Salary',
                'Employment Type',
                'Designation',
                'Date Employed',
            ]);

            // Example row
            fputcsv($file, [
                'John',
                'Doe',
                'john.doe@example.com',
                '08012345678',
                'TIN-12345678',
                '250000',
                'full_time',
                'Software Engineer',
                '2024-01-15',
            ]);

            // Second example
            fputcsv($file, [
                'Jane',
                'Smith',
                'jane.smith@example.com',
                '08098765432',
                '',
                '180000',
                'contract',
                'Accountant',
                '2024-03-01',
            ]);

            fclose($file);
        }, 200, $headers);
    }

    /**
     * Helper: Format list for AI prompt
     */
    protected function formatList(array $items): string
    {
        return implode("\n", array_map(fn($item) => "- {$item}", $items));
    }

    /**
     * Helper: Format field descriptions for AI prompt
     */
    protected function formatFieldDescriptions(array $fields): string
    {
        return implode("\n", array_map(
            fn($key, $desc) => "- {$key}: {$desc}",
            array_keys($fields),
            array_values($fields)
        ));
    }

    /**
     * Delete staff member
     */
    public function destroy(BusinessStaff $staff)
    {
        $this->authorize('delete', $staff);

        $this->businessService->removeStaff($staff);

        return redirect()->route('business.staff.index')
            ->with('message', 'Staff member removed successfully');
    }
}
