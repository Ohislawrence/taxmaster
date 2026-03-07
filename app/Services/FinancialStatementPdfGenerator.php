<?php

namespace App\Services;

use App\Models\Business;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class FinancialStatementPdfGenerator
{
    /**
     * Generate PDF for financial statements
     */
    public function generate(Business $business, string $year, array $data): string
    {
        $html = $this->generateHtml($business, $year, $data);

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
        ]);

        $mpdf->SetTitle('Financial Statements - ' . $business->name . ' - ' . $year);
        $mpdf->WriteHTML($html);

        return $mpdf->Output('financial-statements-' . $year . '.pdf', Destination::STRING_RETURN);
    }

    protected function generateHtml(Business $business, string $year, array $data): string
    {
        $bs = $data['balance_sheet'];
        $pl = $data['profit_loss'];
        $cf = $data['cash_flow'] ?? [];
        $prior = $data['prior_year'] ?? null;
        $priorBs = $prior['balance_sheet'] ?? null;
        $priorPl = $prior['profit_loss'] ?? null;
        $priorYear = (int) $year - 1;

        // Balance Sheet calculations
        $currentAssets = ($bs['cash_and_bank'] ?? 0) + ($bs['trade_receivables'] ?? 0) + ($bs['inventory'] ?? 0) + ($bs['other_current_assets'] ?? 0);
        $nonCurrentAssets = ($bs['property_plant_equipment'] ?? 0) + ($bs['intangible_assets'] ?? 0) + ($bs['other_non_current_assets'] ?? 0);
        $totalAssets = $currentAssets + $nonCurrentAssets;
        $currentLiabilities = ($bs['trade_payables'] ?? 0) + ($bs['tax_payable'] ?? 0) + ($bs['other_current_liabilities'] ?? 0);
        $nonCurrentLiabilities = ($bs['long_term_borrowings'] ?? 0) + ($bs['other_non_current_liabilities'] ?? 0);
        $totalLiabilities = $currentLiabilities + $nonCurrentLiabilities;
        $totalEquity = ($bs['share_capital'] ?? 0) + ($bs['retained_earnings'] ?? 0) + ($bs['other_reserves'] ?? 0);

        // Prior Balance Sheet
        $pCurrentAssets = $pNonCurrentAssets = $pTotalAssets = $pCurrentLiabilities = $pNonCurrentLiabilities = $pTotalLiabilities = $pTotalEquity = 0;
        if ($priorBs) {
            $pCurrentAssets = ($priorBs['cash_and_bank'] ?? 0) + ($priorBs['trade_receivables'] ?? 0) + ($priorBs['inventory'] ?? 0) + ($priorBs['other_current_assets'] ?? 0);
            $pNonCurrentAssets = ($priorBs['property_plant_equipment'] ?? 0) + ($priorBs['intangible_assets'] ?? 0) + ($priorBs['other_non_current_assets'] ?? 0);
            $pTotalAssets = $pCurrentAssets + $pNonCurrentAssets;
            $pCurrentLiabilities = ($priorBs['trade_payables'] ?? 0) + ($priorBs['tax_payable'] ?? 0) + ($priorBs['other_current_liabilities'] ?? 0);
            $pNonCurrentLiabilities = ($priorBs['long_term_borrowings'] ?? 0) + ($priorBs['other_non_current_liabilities'] ?? 0);
            $pTotalLiabilities = $pCurrentLiabilities + $pNonCurrentLiabilities;
            $pTotalEquity = ($priorBs['share_capital'] ?? 0) + ($priorBs['retained_earnings'] ?? 0) + ($priorBs['other_reserves'] ?? 0);
        }

        // P&L calculations
        $grossProfit = ($pl['revenue'] ?? 0) - ($pl['cost_of_sales'] ?? 0);
        $totalOpex = ($pl['salaries_wages'] ?? 0) + ($pl['rent_facilities'] ?? 0) + ($pl['utilities'] ?? 0) + ($pl['professional_fees'] ?? 0) + ($pl['marketing'] ?? 0) + ($pl['depreciation'] ?? 0) + ($pl['other_operating_expenses'] ?? 0);
        $operatingProfit = $grossProfit - $totalOpex;
        $profitBeforeTax = $operatingProfit + ($pl['other_income'] ?? 0) - ($pl['finance_costs'] ?? 0);
        $profitAfterTax = $profitBeforeTax - ($pl['tax_expense'] ?? 0);

        // Prior P&L
        $pGrossProfit = $pTotalOpex = $pOperatingProfit = $pProfitBeforeTax = $pProfitAfterTax = 0;
        if ($priorPl) {
            $pGrossProfit = ($priorPl['revenue'] ?? 0) - ($priorPl['cost_of_sales'] ?? 0);
            $pTotalOpex = ($priorPl['salaries_wages'] ?? 0) + ($priorPl['rent_facilities'] ?? 0) + ($priorPl['utilities'] ?? 0) + ($priorPl['professional_fees'] ?? 0) + ($priorPl['marketing'] ?? 0) + ($priorPl['depreciation'] ?? 0) + ($priorPl['other_operating_expenses'] ?? 0);
            $pOperatingProfit = $pGrossProfit - $pTotalOpex;
            $pProfitBeforeTax = $pOperatingProfit + ($priorPl['other_income'] ?? 0) - ($priorPl['finance_costs'] ?? 0);
            $pProfitAfterTax = $pProfitBeforeTax - ($priorPl['tax_expense'] ?? 0);
        }

        // Cash Flow calculations
        $netCashOperating = $profitBeforeTax + ($cf['depreciation_add_back'] ?? 0) + ($cf['change_in_receivables'] ?? 0) + ($cf['change_in_inventory'] ?? 0) + ($cf['change_in_payables'] ?? 0);
        $netCashInvesting = ($cf['purchase_of_assets'] ?? 0) + ($cf['sale_of_assets'] ?? 0);
        $netCashFinancing = ($cf['loan_proceeds'] ?? 0) + ($cf['loan_repayments'] ?? 0) + ($cf['equity_contributions'] ?? 0) + ($cf['dividends_paid'] ?? 0);
        $netChangeCash = $netCashOperating + $netCashInvesting + $netCashFinancing;
        $closingCash = ($cf['opening_cash'] ?? 0) + $netChangeCash;

        $hasPrior = $priorBs || $priorPl;
        $colHeader = $hasPrior ? "<th class='right'>{$year}<br><small>NGN</small></th><th class='right'>{$priorYear}<br><small>NGN</small></th>" : "<th class='right'>Amount<br><small>NGN</small></th>";

        $tin = $business->tax_identification_number ? "TIN: {$business->tax_identification_number}" : '';
        $address = $business->address ? "{$business->address}" : '';
        $dateGenerated = date('d F Y');

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Financial Statements</title>
    <style>
        body { font-family: Arial, sans-serif; color: #1a1a1a; font-size: 11px; line-height: 1.4; }
        h1 { font-size: 16px; margin: 0; text-align: center; }
        h2 { font-size: 12px; margin: 0 0 4px 0; text-align: center; color: #555; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #1a1a1a; padding-bottom: 10px; }
        .header .subtitle { font-size: 10px; color: #666; }
        .section { margin-top: 20px; page-break-inside: avoid; }
        .section-title { font-size: 13px; font-weight: bold; text-align: center; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .section-sub { font-size: 10px; text-align: center; color: #666; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        th, td { padding: 4px 8px; }
        th { text-align: left; font-size: 10px; color: #555; border-bottom: 2px solid #333; }
        .right { text-align: right; }
        .row td { border-bottom: 1px solid #eee; }
        .subtotal td { border-top: 1px solid #999; font-weight: bold; background: #f8f8f8; }
        .grandtotal td { border-top: 2px solid #333; border-bottom: 2px solid #333; font-weight: bold; background: #f0f0f0; }
        .group-label td { font-weight: bold; font-size: 10px; color: #555; text-transform: uppercase; padding-top: 10px; border-bottom: none; }
        .indent td:first-child { padding-left: 20px; }
        .negative { color: #c00; }
        .spacer td { height: 8px; border: none; }
        .notes { margin-top: 30px; border-top: 1px solid #ccc; padding-top: 10px; font-size: 10px; color: #666; }
        .page-break { page-break-before: always; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 9px; color: #999; padding: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{$business->name}</h1>
        <h2>{$address}</h2>
        <div class="subtitle">{$tin}</div>
    </div>

    <!-- STATEMENT OF FINANCIAL POSITION -->
    <div class="section">
        <div class="section-title">Statement of Financial Position</div>
        <div class="section-sub">As at 31 December {$year}</div>
        <table>
            <tr><th>Description</th>{$colHeader}</tr>
            <tr class="group-label"><td colspan="3">Assets</td></tr>
            <tr class="group-label"><td>Current Assets</td><td></td>{$this->priorCol($hasPrior)}</tr>
            {$this->row('Cash &amp; Bank Balances', $bs['cash_and_bank'] ?? 0, $priorBs['cash_and_bank'] ?? 0, $hasPrior, true)}
            {$this->row('Trade Receivables', $bs['trade_receivables'] ?? 0, $priorBs['trade_receivables'] ?? 0, $hasPrior, true)}
            {$this->row('Inventory', $bs['inventory'] ?? 0, $priorBs['inventory'] ?? 0, $hasPrior, true)}
            {$this->row('Other Current Assets', $bs['other_current_assets'] ?? 0, $priorBs['other_current_assets'] ?? 0, $hasPrior, true)}
            {$this->subtotalRow('Total Current Assets', $currentAssets, $pCurrentAssets, $hasPrior)}
            <tr class="spacer"><td colspan="3"></td></tr>
            <tr class="group-label"><td>Non-Current Assets</td><td></td>{$this->priorCol($hasPrior)}</tr>
            {$this->row('Property, Plant &amp; Equipment', $bs['property_plant_equipment'] ?? 0, $priorBs['property_plant_equipment'] ?? 0, $hasPrior, true)}
            {$this->row('Intangible Assets', $bs['intangible_assets'] ?? 0, $priorBs['intangible_assets'] ?? 0, $hasPrior, true)}
            {$this->row('Other Non-Current Assets', $bs['other_non_current_assets'] ?? 0, $priorBs['other_non_current_assets'] ?? 0, $hasPrior, true)}
            {$this->subtotalRow('Total Non-Current Assets', $nonCurrentAssets, $pNonCurrentAssets, $hasPrior)}
            {$this->grandTotalRow('TOTAL ASSETS', $totalAssets, $pTotalAssets, $hasPrior)}

            <tr class="spacer"><td colspan="3"></td></tr>
            <tr class="group-label"><td colspan="3">Liabilities</td></tr>
            <tr class="group-label"><td>Current Liabilities</td><td></td>{$this->priorCol($hasPrior)}</tr>
            {$this->row('Trade Payables', $bs['trade_payables'] ?? 0, $priorBs['trade_payables'] ?? 0, $hasPrior, true)}
            {$this->row('Tax Payable', $bs['tax_payable'] ?? 0, $priorBs['tax_payable'] ?? 0, $hasPrior, true)}
            {$this->row('Other Current Liabilities', $bs['other_current_liabilities'] ?? 0, $priorBs['other_current_liabilities'] ?? 0, $hasPrior, true)}
            {$this->subtotalRow('Total Current Liabilities', $currentLiabilities, $pCurrentLiabilities, $hasPrior)}
            <tr class="spacer"><td colspan="3"></td></tr>
            <tr class="group-label"><td>Non-Current Liabilities</td><td></td>{$this->priorCol($hasPrior)}</tr>
            {$this->row('Long-Term Borrowings', $bs['long_term_borrowings'] ?? 0, $priorBs['long_term_borrowings'] ?? 0, $hasPrior, true)}
            {$this->row('Other Non-Current Liabilities', $bs['other_non_current_liabilities'] ?? 0, $priorBs['other_non_current_liabilities'] ?? 0, $hasPrior, true)}
            {$this->subtotalRow('Total Non-Current Liabilities', $nonCurrentLiabilities, $pNonCurrentLiabilities, $hasPrior)}
            {$this->subtotalRow('TOTAL LIABILITIES', $totalLiabilities, $pTotalLiabilities, $hasPrior)}

            <tr class="spacer"><td colspan="3"></td></tr>
            <tr class="group-label"><td colspan="3">Equity</td></tr>
            {$this->row('Share Capital', $bs['share_capital'] ?? 0, $priorBs['share_capital'] ?? 0, $hasPrior, true)}
            {$this->row('Retained Earnings', $bs['retained_earnings'] ?? 0, $priorBs['retained_earnings'] ?? 0, $hasPrior, true)}
            {$this->row('Other Reserves', $bs['other_reserves'] ?? 0, $priorBs['other_reserves'] ?? 0, $hasPrior, true)}
            {$this->subtotalRow('Total Equity', $totalEquity, $pTotalEquity, $hasPrior)}
            {$this->grandTotalRow('TOTAL LIABILITIES &amp; EQUITY', $totalLiabilities + $totalEquity, $pTotalLiabilities + $pTotalEquity, $hasPrior)}
        </table>
    </div>

    <!-- STATEMENT OF PROFIT OR LOSS -->
    <div class="section page-break">
        <div class="section-title">Statement of Profit or Loss</div>
        <div class="section-sub">For the year ended 31 December {$year}</div>
        <table>
            <tr><th>Description</th>{$colHeader}</tr>
            {$this->row('Revenue', $pl['revenue'] ?? 0, $priorPl['revenue'] ?? 0, $hasPrior)}
            {$this->row('Cost of Sales', -($pl['cost_of_sales'] ?? 0), -($priorPl['cost_of_sales'] ?? 0), $hasPrior)}
            {$this->subtotalRow('Gross Profit', $grossProfit, $pGrossProfit, $hasPrior)}
            <tr class="spacer"><td colspan="3"></td></tr>
            <tr class="group-label"><td>Operating Expenses</td><td></td>{$this->priorCol($hasPrior)}</tr>
            {$this->row('Salaries &amp; Wages', $pl['salaries_wages'] ?? 0, $priorPl['salaries_wages'] ?? 0, $hasPrior, true)}
            {$this->row('Rent &amp; Facilities', $pl['rent_facilities'] ?? 0, $priorPl['rent_facilities'] ?? 0, $hasPrior, true)}
            {$this->row('Utilities', $pl['utilities'] ?? 0, $priorPl['utilities'] ?? 0, $hasPrior, true)}
            {$this->row('Professional Fees', $pl['professional_fees'] ?? 0, $priorPl['professional_fees'] ?? 0, $hasPrior, true)}
            {$this->row('Marketing &amp; Advertising', $pl['marketing'] ?? 0, $priorPl['marketing'] ?? 0, $hasPrior, true)}
            {$this->row('Depreciation &amp; Amortisation', $pl['depreciation'] ?? 0, $priorPl['depreciation'] ?? 0, $hasPrior, true)}
            {$this->row('Other Operating Expenses', $pl['other_operating_expenses'] ?? 0, $priorPl['other_operating_expenses'] ?? 0, $hasPrior, true)}
            {$this->subtotalRow('Total Operating Expenses', $totalOpex, $pTotalOpex, $hasPrior)}
            {$this->subtotalRow('Operating Profit', $operatingProfit, $pOperatingProfit, $hasPrior)}
            <tr class="spacer"><td colspan="3"></td></tr>
            {$this->row('Other Income', $pl['other_income'] ?? 0, $priorPl['other_income'] ?? 0, $hasPrior)}
            {$this->row('Finance Costs', -($pl['finance_costs'] ?? 0), -($priorPl['finance_costs'] ?? 0), $hasPrior)}
            {$this->subtotalRow('Profit Before Tax', $profitBeforeTax, $pProfitBeforeTax, $hasPrior)}
            {$this->row('Tax Expense', -($pl['tax_expense'] ?? 0), -($priorPl['tax_expense'] ?? 0), $hasPrior)}
            {$this->grandTotalRow('Profit After Tax', $profitAfterTax, $pProfitAfterTax, $hasPrior)}
        </table>
    </div>

    <!-- STATEMENT OF CASH FLOWS -->
    <div class="section page-break">
        <div class="section-title">Statement of Cash Flows</div>
        <div class="section-sub">For the year ended 31 December {$year} (Indirect Method)</div>
        <table>
            <tr><th>Description</th><th class="right">Amount<br><small>NGN</small></th></tr>
            {$this->cfRow('Profit Before Tax', $profitBeforeTax)}
            <tr class="group-label"><td>Adjustments for Operating Activities</td><td></td></tr>
            {$this->cfRow('Depreciation Add-back', $cf['depreciation_add_back'] ?? 0, true)}
            {$this->cfRow('(Increase)/Decrease in Receivables', $cf['change_in_receivables'] ?? 0, true)}
            {$this->cfRow('(Increase)/Decrease in Inventory', $cf['change_in_inventory'] ?? 0, true)}
            {$this->cfRow('Increase/(Decrease) in Payables', $cf['change_in_payables'] ?? 0, true)}
            {$this->cfSubtotal('Net Cash from Operating Activities', $netCashOperating)}
            <tr class="spacer"><td colspan="2"></td></tr>
            <tr class="group-label"><td>Investing Activities</td><td></td></tr>
            {$this->cfRow('Purchase of Assets', $cf['purchase_of_assets'] ?? 0, true)}
            {$this->cfRow('Sale of Assets', $cf['sale_of_assets'] ?? 0, true)}
            {$this->cfSubtotal('Net Cash from Investing Activities', $netCashInvesting)}
            <tr class="spacer"><td colspan="2"></td></tr>
            <tr class="group-label"><td>Financing Activities</td><td></td></tr>
            {$this->cfRow('Loan Proceeds', $cf['loan_proceeds'] ?? 0, true)}
            {$this->cfRow('Loan Repayments', $cf['loan_repayments'] ?? 0, true)}
            {$this->cfRow('Equity Contributions', $cf['equity_contributions'] ?? 0, true)}
            {$this->cfRow('Dividends Paid', $cf['dividends_paid'] ?? 0, true)}
            {$this->cfSubtotal('Net Cash from Financing Activities', $netCashFinancing)}
            <tr class="spacer"><td colspan="2"></td></tr>
            {$this->cfSubtotal('Net Change in Cash', $netChangeCash)}
            {$this->cfRow('Opening Cash Balance', $cf['opening_cash'] ?? 0)}
            {$this->cfGrandTotal('Closing Cash Balance', $closingCash)}
        </table>
    </div>

    <!-- NOTES -->
    <div class="notes">
        <p><strong>Notes to the Financial Statements</strong></p>
        <p><strong>1. Basis of Preparation:</strong> These financial statements have been prepared in accordance with the requirements of the Companies and Allied Matters Act (CAMA) 2020 and applicable Nigerian financial reporting standards.</p>
        <p><strong>2. Accounting Policies:</strong> Revenue is recognised when earned. Expenses are recognised on an accrual basis. Property, plant and equipment are stated at cost less accumulated depreciation.</p>
        <p><strong>3. Taxation:</strong> Tax expense is computed in accordance with the Companies Income Tax Act (CITA), as amended by the Finance Act 2019. The applicable rate depends on company turnover: Small (&lt; ₦25M) = 0%, Medium (₦25M–₦100M) = 20%, Large (&gt; ₦100M) = 30%.</p>
        <p><strong>4. Currency:</strong> All amounts are stated in Nigerian Naira (₦/NGN).</p>
        <p style="margin-top: 12px; font-style: italic;">Generated by TaxMaster on {$dateGenerated}. These statements should be reviewed by a qualified chartered accountant before submission to regulatory authorities.</p>
    </div>
</body>
</html>
HTML;
    }

    // ---- Helper methods for HTML rows ----

    protected function formatCurrency($value): string
    {
        $v = (float) $value;
        if ($v < 0) {
            return '(' . number_format(abs($v), 2) . ')';
        }
        return number_format($v, 2);
    }

    protected function priorCol(bool $hasPrior): string
    {
        return $hasPrior ? '<td></td>' : '';
    }

    protected function row(string $label, $value, $priorValue = 0, bool $hasPrior = false, bool $indent = false): string
    {
        $cls = $indent ? 'row indent' : 'row';
        $negCls = (float) $value < 0 ? ' negative' : '';
        $pNegCls = (float) $priorValue < 0 ? ' negative' : '';
        $priorCol = $hasPrior ? "<td class='right{$pNegCls}'>{$this->formatCurrency($priorValue)}</td>" : '';
        return "<tr class='{$cls}'><td>{$label}</td><td class='right{$negCls}'>{$this->formatCurrency($value)}</td>{$priorCol}</tr>";
    }

    protected function subtotalRow(string $label, $value, $priorValue = 0, bool $hasPrior = false): string
    {
        $negCls = (float) $value < 0 ? ' negative' : '';
        $pNegCls = (float) $priorValue < 0 ? ' negative' : '';
        $priorCol = $hasPrior ? "<td class='right{$pNegCls}'>{$this->formatCurrency($priorValue)}</td>" : '';
        return "<tr class='subtotal'><td>{$label}</td><td class='right{$negCls}'>{$this->formatCurrency($value)}</td>{$priorCol}</tr>";
    }

    protected function grandTotalRow(string $label, $value, $priorValue = 0, bool $hasPrior = false): string
    {
        $negCls = (float) $value < 0 ? ' negative' : '';
        $pNegCls = (float) $priorValue < 0 ? ' negative' : '';
        $priorCol = $hasPrior ? "<td class='right{$pNegCls}'>{$this->formatCurrency($priorValue)}</td>" : '';
        return "<tr class='grandtotal'><td>{$label}</td><td class='right{$negCls}'>{$this->formatCurrency($value)}</td>{$priorCol}</tr>";
    }

    protected function cfRow(string $label, $value, bool $indent = false): string
    {
        $cls = $indent ? 'row indent' : 'row';
        $negCls = (float) $value < 0 ? ' negative' : '';
        return "<tr class='{$cls}'><td>{$label}</td><td class='right{$negCls}'>{$this->formatCurrency($value)}</td></tr>";
    }

    protected function cfSubtotal(string $label, $value): string
    {
        $negCls = (float) $value < 0 ? ' negative' : '';
        return "<tr class='subtotal'><td>{$label}</td><td class='right{$negCls}'>{$this->formatCurrency($value)}</td></tr>";
    }

    protected function cfGrandTotal(string $label, $value): string
    {
        $negCls = (float) $value < 0 ? ' negative' : '';
        return "<tr class='grandtotal'><td>{$label}</td><td class='right{$negCls}'>{$this->formatCurrency($value)}</td></tr>";
    }
}
