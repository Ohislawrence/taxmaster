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

        $mpdf->SetTitle('Financial Statements - ' . $year);
        $mpdf->WriteHTML($html);

        return $mpdf->Output('financial-statements-' . $year . '.pdf', Destination::STRING_RETURN);
    }

    protected function generateHtml(Business $business, string $year, array $data): string
    {
        $bs = $data['balance_sheet'];
        $pl = $data['profit_loss'];

        $totalAssets = $bs['current_assets'] + $bs['non_current_assets'];
        $totalLiabilities = $bs['current_liabilities'] + $bs['non_current_liabilities'];
        $totalEquity = $bs['share_capital'] + $bs['retained_earnings'] + $bs['other_reserves'];

        $grossProfit = $pl['revenue'] - $pl['cost_of_sales'];
        $operatingProfit = $grossProfit - $pl['operating_expenses'];
        $profitBeforeTax = $operatingProfit + $pl['other_income'] - $pl['finance_costs'];
        $profitAfterTax = $profitBeforeTax - $pl['tax_expense'];

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Financial Statements</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111; font-size: 12px; }
        h1, h2 { margin: 0; text-align: center; }
        h1 { font-size: 16px; }
        h2 { font-size: 13px; margin-bottom: 12px; }
        .section { margin-top: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; }
        th { text-align: left; background: #f3f4f6; }
        .right { text-align: right; }
        .total { font-weight: bold; background: #e5e7eb; }
    </style>
</head>
<body>
    <h1>{$business->name}</h1>
    <h2>Financial Statements for the year ended {$year}</h2>

    <div class="section">
        <h3>Statement of Financial Position</h3>
        <table>
            <tr><th>Assets</th><th class="right">Amount (NGN)</th></tr>
            <tr><td>Current Assets</td><td class="right">{$this->formatCurrency($bs['current_assets'])}</td></tr>
            <tr><td>Non-current Assets</td><td class="right">{$this->formatCurrency($bs['non_current_assets'])}</td></tr>
            <tr class="total"><td>Total Assets</td><td class="right">{$this->formatCurrency($totalAssets)}</td></tr>
        </table>
        <table>
            <tr><th>Liabilities</th><th class="right">Amount (NGN)</th></tr>
            <tr><td>Current Liabilities</td><td class="right">{$this->formatCurrency($bs['current_liabilities'])}</td></tr>
            <tr><td>Non-current Liabilities</td><td class="right">{$this->formatCurrency($bs['non_current_liabilities'])}</td></tr>
            <tr class="total"><td>Total Liabilities</td><td class="right">{$this->formatCurrency($totalLiabilities)}</td></tr>
        </table>
        <table>
            <tr><th>Equity</th><th class="right">Amount (NGN)</th></tr>
            <tr><td>Share Capital</td><td class="right">{$this->formatCurrency($bs['share_capital'])}</td></tr>
            <tr><td>Retained Earnings</td><td class="right">{$this->formatCurrency($bs['retained_earnings'])}</td></tr>
            <tr><td>Other Reserves</td><td class="right">{$this->formatCurrency($bs['other_reserves'])}</td></tr>
            <tr class="total"><td>Total Equity</td><td class="right">{$this->formatCurrency($totalEquity)}</td></tr>
        </table>
    </div>

    <div class="section">
        <h3>Statement of Profit or Loss</h3>
        <table>
            <tr><th>Description</th><th class="right">Amount (NGN)</th></tr>
            <tr><td>Revenue</td><td class="right">{$this->formatCurrency($pl['revenue'])}</td></tr>
            <tr><td>Cost of Sales</td><td class="right">{$this->formatCurrency($pl['cost_of_sales'])}</td></tr>
            <tr class="total"><td>Gross Profit</td><td class="right">{$this->formatCurrency($grossProfit)}</td></tr>
            <tr><td>Operating Expenses</td><td class="right">{$this->formatCurrency($pl['operating_expenses'])}</td></tr>
            <tr class="total"><td>Operating Profit</td><td class="right">{$this->formatCurrency($operatingProfit)}</td></tr>
            <tr><td>Other Income</td><td class="right">{$this->formatCurrency($pl['other_income'])}</td></tr>
            <tr><td>Finance Costs</td><td class="right">{$this->formatCurrency($pl['finance_costs'])}</td></tr>
            <tr class="total"><td>Profit Before Tax</td><td class="right">{$this->formatCurrency($profitBeforeTax)}</td></tr>
            <tr><td>Tax Expense</td><td class="right">{$this->formatCurrency($pl['tax_expense'])}</td></tr>
            <tr class="total"><td>Profit After Tax</td><td class="right">{$this->formatCurrency($profitAfterTax)}</td></tr>
        </table>
    </div>
</body>
</html>
HTML;
    }

    protected function formatCurrency($value): string
    {
        return number_format((float) $value, 2);
    }
}
