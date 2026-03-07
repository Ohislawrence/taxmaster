<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * DataExportController
 *
 * Provides NDPA 2023 (Nigeria Data Protection Act) compliant data portability.
 * Article 25 requires data controllers to provide data subjects with their
 * personal data in a structured, commonly used, machine-readable format.
 */
class DataExportController extends Controller
{
    /**
     * Export all business data as JSON for NDPA data portability compliance.
     */
    public function export(Request $request): StreamedResponse
    {
        $business = $request->user()->currentBusiness;

        if (!$business) {
            abort(403, 'No business context found.');
        }

        $data = $this->collectBusinessData($business);

        $filename = sprintf(
            'taxmaster_data_export_%s_%s.json',
            str_replace(' ', '_', strtolower($business->name ?? 'business')),
            now()->format('Y-m-d_His')
        );

        Log::info('NDPA data export requested', [
            'business_id' => $business->id,
            'user_id' => $request->user()->id,
            'ip' => $request->ip(),
        ]);

        return response()->streamDownload(function () use ($data) {
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $filename, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Collect all business data for export.
     */
    private function collectBusinessData(Business $business): array
    {
        return [
            'export_metadata' => [
                'exported_at' => now()->toIso8601String(),
                'format_version' => '1.0',
                'platform' => 'TaxMaster Nigeria',
                'compliance' => 'NDPA 2023 Article 25 — Data Portability',
            ],

            'business_profile' => $this->exportBusinessProfile($business),
            'staff' => $this->exportStaff($business),
            'paye_returns' => $this->exportPayeReturns($business),
            'vat_returns' => $this->exportVatReturns($business),
            'wht_returns' => $this->exportWhtReturns($business),
            'cit_returns' => $this->exportCitReturns($business),
            'government_payments' => $this->exportGovernmentPayments($business),
            'activity_log' => $this->exportActivityLog($business),
        ];
    }

    private function exportBusinessProfile(Business $business): array
    {
        return $business->only([
            'name', 'email', 'phone', 'address', 'city', 'state',
            'tax_identification_number', 'registration_number',
            'business_type', 'industry', 'date_of_incorporation',
            'financial_year_start', 'financial_year_end',
            'created_at', 'updated_at',
        ]);
    }

    private function exportStaff(Business $business): array
    {
        return $business->staff()
            ->get()
            ->map(fn ($staff) => $staff->only([
                'first_name', 'last_name', 'email', 'phone',
                'tax_identification_number', 'employment_date',
                'department', 'position', 'monthly_salary',
                'pension_number', 'nhf_number',
                'status', 'created_at', 'updated_at',
            ]))
            ->toArray();
    }

    private function exportPayeReturns(Business $business): array
    {
        return $business->payeReturns()
            ->get()
            ->map(fn ($r) => $r->only([
                'period', 'return_type', 'total_gross_pay', 'total_tax_deducted',
                'staff_count', 'schedule_data', 'status', 'tax_authority',
                'firs_reference', 'notes', 'filed_date',
                'created_at', 'updated_at',
            ]))
            ->toArray();
    }

    private function exportVatReturns(Business $business): array
    {
        return $business->vatReturns()
            ->get()
            ->map(fn ($r) => $r->only([
                'period', 'form_type', 'reporting_period',
                'sales_turnover', 'exempt_sales', 'zero_rated_sales',
                'vat_on_sales', 'input_vat', 'vat_due',
                'status', 'tax_authority', 'firs_reference', 'notes',
                'submitted_at', 'filed_at', 'paid_at',
                'created_at', 'updated_at',
            ]))
            ->toArray();
    }

    private function exportWhtReturns(Business $business): array
    {
        return $business->whtReturns()
            ->get()
            ->map(fn ($r) => $r->only([
                'period', 'total_amount', 'wht_rate', 'wht_amount',
                'beneficiary_name', 'beneficiary_tin', 'transaction_type',
                'status', 'tax_authority', 'firs_reference', 'notes',
                'created_at', 'updated_at',
            ]))
            ->toArray();
    }

    private function exportCitReturns(Business $business): array
    {
        return $business->citReturns()
            ->get()
            ->map(fn ($r) => $r->only([
                'period', 'return_type', 'turnover', 'gross_profit',
                'assessable_profit', 'total_profit', 'tax_payable',
                'minimum_tax', 'education_tax', 'total_due',
                'status', 'tax_authority', 'firs_reference', 'notes',
                'submitted_at', 'filed_at', 'paid_at',
                'created_at', 'updated_at',
            ]))
            ->toArray();
    }

    private function exportGovernmentPayments(Business $business): array
    {
        return $business->governmentPayments()
            ->get()
            ->map(fn ($p) => $p->only([
                'tax_type', 'amount', 'rrr', 'status',
                'payment_date', 'payment_reference',
                'created_at', 'updated_at',
            ]))
            ->toArray();
    }

    private function exportActivityLog(Business $business): array
    {
        return $business->activityLogs()
            ->latest()
            ->limit(500)
            ->get()
            ->map(fn ($log) => $log->only([
                'action', 'description', 'subject_type', 'subject_id',
                'changes', 'ip_address', 'created_at',
            ]))
            ->toArray();
    }
}
