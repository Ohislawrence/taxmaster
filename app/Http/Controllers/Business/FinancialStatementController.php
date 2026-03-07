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
        $priorDefaults = $this->buildDefaults($business->id, (string) ((int) $year - 1));

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
            'priorDefaults' => $priorDefaults,
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
            // Balance Sheet
            'balance_sheet' => 'required|array',
            'balance_sheet.cash_and_bank' => 'required|numeric',
            'balance_sheet.trade_receivables' => 'required|numeric',
            'balance_sheet.inventory' => 'required|numeric',
            'balance_sheet.other_current_assets' => 'required|numeric',
            'balance_sheet.property_plant_equipment' => 'required|numeric',
            'balance_sheet.intangible_assets' => 'required|numeric',
            'balance_sheet.other_non_current_assets' => 'required|numeric',
            'balance_sheet.trade_payables' => 'required|numeric',
            'balance_sheet.tax_payable' => 'required|numeric',
            'balance_sheet.other_current_liabilities' => 'required|numeric',
            'balance_sheet.long_term_borrowings' => 'required|numeric',
            'balance_sheet.other_non_current_liabilities' => 'required|numeric',
            'balance_sheet.share_capital' => 'required|numeric',
            'balance_sheet.retained_earnings' => 'required|numeric',
            'balance_sheet.other_reserves' => 'required|numeric',
            // Profit & Loss
            'profit_loss' => 'required|array',
            'profit_loss.revenue' => 'required|numeric',
            'profit_loss.cost_of_sales' => 'required|numeric',
            'profit_loss.salaries_wages' => 'required|numeric',
            'profit_loss.rent_facilities' => 'required|numeric',
            'profit_loss.utilities' => 'required|numeric',
            'profit_loss.professional_fees' => 'required|numeric',
            'profit_loss.marketing' => 'required|numeric',
            'profit_loss.depreciation' => 'required|numeric',
            'profit_loss.other_operating_expenses' => 'required|numeric',
            'profit_loss.other_income' => 'required|numeric',
            'profit_loss.finance_costs' => 'required|numeric',
            'profit_loss.tax_expense' => 'required|numeric',
            // Cash Flow
            'cash_flow' => 'required|array',
            'cash_flow.depreciation_add_back' => 'required|numeric',
            'cash_flow.change_in_receivables' => 'required|numeric',
            'cash_flow.change_in_inventory' => 'required|numeric',
            'cash_flow.change_in_payables' => 'required|numeric',
            'cash_flow.purchase_of_assets' => 'required|numeric',
            'cash_flow.sale_of_assets' => 'required|numeric',
            'cash_flow.loan_proceeds' => 'required|numeric',
            'cash_flow.loan_repayments' => 'required|numeric',
            'cash_flow.equity_contributions' => 'required|numeric',
            'cash_flow.dividends_paid' => 'required|numeric',
            'cash_flow.opening_cash' => 'required|numeric',
            // Prior Year
            'prior_year' => 'nullable|array',
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

        // Revenue categories
        $salesRevenue = (float) $transactions->where('type', 'credit')
            ->whereIn('sub_category', ['VAT_OUTPUT', 'EXEMPT_SALES'])->sum('amount');
        $otherCredits = (float) $transactions->where('type', 'credit')
            ->whereNotIn('sub_category', ['VAT_OUTPUT', 'EXEMPT_SALES'])->sum('amount');
        $totalRevenue = $salesRevenue ?: (float) $transactions->where('type', 'credit')->sum('amount');

        // Expense categories mapped from sub_category
        $salaries = (float) $transactions->where('type', 'debit')
            ->where('sub_category', 'SALARY_PAYE')->sum('amount');
        $rent = (float) $transactions->where('type', 'debit')
            ->where('sub_category', 'RENT')->sum('amount');
        $utilities = (float) $transactions->where('type', 'debit')
            ->where('sub_category', 'UTILITIES')->sum('amount');
        $professional = (float) $transactions->where('type', 'debit')
            ->where('sub_category', 'PROFESSIONAL')->sum('amount');
        $marketing = (float) $transactions->where('type', 'debit')
            ->where('sub_category', 'MARKETING')->sum('amount');
        $rawMaterials = (float) $transactions->where('type', 'debit')
            ->where('sub_category', 'RAW_MATERIALS')->sum('amount');
        $itSoftware = (float) $transactions->where('type', 'debit')
            ->where('sub_category', 'IT_SOFTWARE')->sum('amount');
        $transport = (float) $transactions->where('type', 'debit')
            ->where('sub_category', 'TRANSPORT')->sum('amount');
        $taxPayments = (float) $transactions->where('type', 'debit')
            ->whereIn('sub_category', ['VAT_PAYMENT', 'PAYE_PAYMENT', 'WHT_PAYMENT', 'CIT_PAYMENT'])->sum('amount');

        $categorizedExpenses = $salaries + $rent + $utilities + $professional + $marketing
            + $rawMaterials + $itSoftware + $transport + $taxPayments;
        $totalDebits = (float) $transactions->where('type', 'debit')->sum('amount');
        $otherExpenses = max(0, $totalDebits - $categorizedExpenses);

        // Cash position from latest balance
        $latestTransaction = $transactions->sortByDesc('transaction_date')->first();
        $cashBalance = $latestTransaction ? (float) $latestTransaction->balance : 0;

        $profitBeforeTax = $totalRevenue - $rawMaterials - $salaries - $rent - $utilities
            - $professional - $marketing - $itSoftware - $transport - $otherExpenses;

        return [
            'balance_sheet' => [
                'cash_and_bank' => max($cashBalance, 0),
                'trade_receivables' => 0,
                'inventory' => 0,
                'other_current_assets' => 0,
                'property_plant_equipment' => 0,
                'intangible_assets' => 0,
                'other_non_current_assets' => 0,
                'trade_payables' => 0,
                'tax_payable' => $taxPayments,
                'other_current_liabilities' => 0,
                'long_term_borrowings' => 0,
                'other_non_current_liabilities' => 0,
                'share_capital' => 0,
                'retained_earnings' => round($profitBeforeTax - $taxPayments, 2),
                'other_reserves' => 0,
            ],
            'profit_loss' => [
                'revenue' => $totalRevenue,
                'cost_of_sales' => $rawMaterials,
                'salaries_wages' => $salaries,
                'rent_facilities' => $rent,
                'utilities' => $utilities,
                'professional_fees' => $professional,
                'marketing' => $marketing,
                'depreciation' => 0,
                'other_operating_expenses' => $itSoftware + $transport + $otherExpenses,
                'other_income' => $salesRevenue > 0 ? $otherCredits : 0,
                'finance_costs' => 0,
                'tax_expense' => $taxPayments,
            ],
            'cash_flow' => [
                'depreciation_add_back' => 0,
                'change_in_receivables' => 0,
                'change_in_inventory' => 0,
                'change_in_payables' => 0,
                'purchase_of_assets' => 0,
                'sale_of_assets' => 0,
                'loan_proceeds' => 0,
                'loan_repayments' => 0,
                'equity_contributions' => 0,
                'dividends_paid' => 0,
                'opening_cash' => 0,
            ],
        ];
    }
}
