<?php

namespace App\Http\Controllers\Business;

use App\Models\BusinessStaff;
use App\Models\TaxReturn;
use App\Services\BusinessService;
use App\Services\TaxCalculationService;
use Inertia\Inertia;
use Illuminate\Http\Request;

class StaffController
{
    protected $businessService;
    protected $taxCalculationService;

    public function __construct(BusinessService $businessService, TaxCalculationService $taxCalculationService)
    {
        $this->businessService = $businessService;
        $this->taxCalculationService = $taxCalculationService;
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
        ]);
    }

    /**
     * Store new staff member
     */
    public function store(Request $request)
    {
        $business = auth()->user()->ownedBusiness;

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:business_staff',
            'phone' => 'nullable|string',
            'tax_identification_number' => 'nullable|string|unique:business_staff',
            'monthly_salary' => 'required|numeric|min:0',
            'employment_type' => 'required|in:full_time,part_time,contract',
            'designation' => 'required|string|max:255',
            'date_employed' => 'required|date',
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
