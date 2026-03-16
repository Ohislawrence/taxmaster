<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Services\CacFormPdfGenerator;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CacFormController extends Controller
{
    public function __construct(
        protected CacFormPdfGenerator $pdfGenerator
    ) {}

    /**
     * Show CAC forms builder
     */
    public function index(Request $request)
    {
        $business = $request->user()?->defaultBusiness();
        if (!$business) {
            return redirect()->route('business.setup')
                ->with('error', 'Please complete your business setup first.');
        }

        $owner = $business->owner;

        $defaults = [
            'company_name' => $business->name,
            'rc_number' => $business->registration_number,
            'incorporation_date' => optional($business->incorporation_date)->format('Y-m-d'),
            'registered_address' => $business->address,
            'business_address' => $business->address,
            'email' => $business->email,
            'phone' => $business->phone,
            'nature_of_business' => $business->industry,
            'share_capital' => $business->annual_revenue ? (string) $business->annual_revenue : '',
            'secretary_name' => '',
            'secretary_address' => '',
            'directors' => $owner ? [[
                'name' => $owner->name,
                'address' => $business->address,
            ]] : [],
            'shareholders' => $owner ? [[
                'name' => $owner->name,
                'shares' => '100%',
            ]] : [],
            'notice_registered_address' => $business->address,
            'notice_effective_date' => now()->format('Y-m-d'),
        ];

        return Inertia::render('Business/Reports/CacForms', [
            'business' => [
                'id' => $business->id,
                'name' => $business->name,
            ],
            'defaults' => $defaults,
        ]);
    }

    /**
     * Download CAC forms as PDF
     */
    public function downloadPdf(Request $request)
    {
        $business = $request->user()?->defaultBusiness();
        if (!$business) {
            return redirect()->route('business.setup')
                ->with('error', 'Please complete your business setup first.');
        }

        $validated = $request->validate([
            'company_name' => 'required|string',
            'rc_number' => 'required|string',
            'incorporation_date' => 'nullable|date',
            'registered_address' => 'required|string',
            'business_address' => 'required|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'nature_of_business' => 'nullable|string',
            'share_capital' => 'nullable|string',
            'secretary_name' => 'nullable|string',
            'secretary_address' => 'nullable|string',
            'directors' => 'array',
            'directors.*.name' => 'required|string',
            'directors.*.address' => 'nullable|string',
            'shareholders' => 'array',
            'shareholders.*.name' => 'required|string',
            'shareholders.*.shares' => 'nullable|string',
            'notice_registered_address' => 'required|string',
            'notice_effective_date' => 'required|date',
        ]);

        $pdf = $this->pdfGenerator->generate($validated);
        $filename = 'cac-forms-' . now()->format('Y-m-d') . '.pdf';

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'public, must-revalidate, max-age=0');
    }
}
