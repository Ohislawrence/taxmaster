<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\FinancialStatementPdfGenerator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FinancialStatementController extends Controller
{
    public function __construct(
        protected FinancialStatementPdfGenerator $pdfGenerator
    ) {}

    /**
     * Show financial statements builder
     */
    public function index(Request $request)
    {
        $business = $request->user()?->ownedBusiness;
        if (!$business) {
            return redirect()->route('business.setup')
                ->with('error', 'Please complete your business setup first.');
        }

        $year = $request->input('year', now()->format('Y'));
        $defaults = $this->buildDefaults($business->id, $year);

        return Inertia::render('Business/Reports/FinancialStatements', [
            'business' => [
                'id' => $business->id,
                'name' => $business->name,
                'tax_identification_number' => $business->tax_identification_number,
                'address' => $business->address,
                'state' => $business->state,
            ],
            'year' => $year,
            'defaults' => $defaults,
        ]);
    }

    /**
     * Download financial statements as PDF
     */
    public function downloadPdf(Request $request)
    {
        $business = $request->user()?->ownedBusiness;
        if (!$business) {
            return redirect()->route('business.setup')
                ->with('error', 'Please complete your business setup first.');
        }

        $validated = $request->validate([
            'year' => 'required|digits:4',
            'balance_sheet' => 'required|array',
            'balance_sheet.current_assets' => 'required|numeric',
            'balance_sheet.non_current_assets' => 'required|numeric',
            'balance_sheet.current_liabilities' => 'required|numeric',
            'balance_sheet.non_current_liabilities' => 'required|numeric',
            'balance_sheet.share_capital' => 'required|numeric',
            'balance_sheet.retained_earnings' => 'required|numeric',
            'balance_sheet.other_reserves' => 'required|numeric',
            'profit_loss' => 'required|array',
            'profit_loss.revenue' => 'required|numeric',
            'profit_loss.cost_of_sales' => 'required|numeric',
            'profit_loss.operating_expenses' => 'required|numeric',
            'profit_loss.other_income' => 'required|numeric',
            'profit_loss.finance_costs' => 'required|numeric',
            'profit_loss.tax_expense' => 'required|numeric',
        ]);

        $pdf = $this->pdfGenerator->generate($business, $validated['year'], $validated);
        $filename = 'financial-statements-' . $validated['year'] . '.pdf';

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'public, must-revalidate, max-age=0');
    }

    /**
     * Build default statement values from transactions
     */
    protected function buildDefaults(int $businessId, string $year): array
    {
        $start = Carbon::createFromFormat('Y', $year)->startOfYear();
        $end = $start->copy()->endOfYear();

        $transactions = Transaction::where('business_id', $businessId)
            ->whereBetween('transaction_date', [$start, $end])
            ->get();

        $revenue = (float) $transactions->where('type', 'credit')->sum('amount');
        $expenses = (float) $transactions->where('type', 'debit')->sum('amount');

        $profitBeforeTax = $revenue - $expenses;

        return [
            'balance_sheet' => [
                'current_assets' => max($profitBeforeTax, 0),
                'non_current_assets' => 0,
                'current_liabilities' => 0,
                'non_current_liabilities' => 0,
                'share_capital' => 0,
                'retained_earnings' => $profitBeforeTax,
                'other_reserves' => 0,
            ],
            'profit_loss' => [
                'revenue' => $revenue,
                'cost_of_sales' => 0,
                'operating_expenses' => $expenses,
                'other_income' => 0,
                'finance_costs' => 0,
                'tax_expense' => 0,
            ],
        ];
    }
}
