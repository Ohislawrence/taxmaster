<?php

namespace App\Services;

use App\Models\PayeReturn;
use App\Models\VatReturn;
use App\Models\WhtReturn;
use App\Models\CitReturn;
use App\Models\Business;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class ReturnPdfGenerator
{
    /**
     * Common mPDF configuration
     */
    private function createMpdf(): Mpdf
    {
        return new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
        ]);
    }

    /**
     * Format currency value
     */
    private function formatCurrency(mixed $value): string
    {
        if (is_null($value)) {
            return '0.00';
        }
        return number_format((float) $value, 2, '.', ',');
    }

    /**
     * Common CSS styles for all PDF returns
     */
    private function getStyles(): string
    {
        return <<<CSS
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            line-height: 1.6;
            font-size: 11px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px solid #1f2937;
            padding-bottom: 15px;
        }
        .header h1 { font-size: 18px; margin-bottom: 5px; font-weight: bold; }
        .header h2 { font-size: 14px; color: #666; margin-bottom: 3px; }
        .header h3 { font-size: 12px; color: #4b5563; margin: 5px 0; }
        .section { margin-bottom: 18px; page-break-inside: avoid; }
        .section h3 {
            font-size: 12px;
            font-weight: bold;
            background-color: #f3f4f6;
            padding: 8px 10px;
            margin-bottom: 10px;
            border-left: 4px solid #2563eb;
        }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table td, table th { padding: 6px 10px; border-bottom: 1px solid #e5e7eb; }
        table th {
            background-color: #f3f4f6;
            font-weight: 600;
            text-align: left;
            font-size: 10px;
        }
        table tr:nth-child(even) { background-color: #f9fafb; }
        .label { font-weight: 600; color: #1f2937; width: 45%; }
        .value { text-align: right; color: #374151; }
        .total-row { background-color: #dbeafe !important; font-weight: bold; }
        .total-row .value { font-size: 12px; color: #1e40af; }
        .negative { color: #dc2626; }
        .positive { color: #16a34a; }
        .footer {
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px solid #d1d5db;
            font-size: 10px;
            color: #6b7280;
        }
        .info-box {
            background-color: #f0fdf4;
            border-left: 4px solid #16a34a;
            padding: 10px;
            margin-bottom: 10px;
            font-size: 10px;
            color: #166534;
        }
        .signature-section { margin-top: 30px; }
        .signature-block {
            display: inline-block;
            width: 45%;
            padding-top: 30px;
            border-top: 1px solid #000;
            text-align: center;
            font-size: 10px;
        }
CSS;
    }

    /**
     * Generate business info HTML block
     */
    private function businessInfoHtml(Business $business): string
    {
        return <<<HTML
    <div class="section">
        <h3>Business Information</h3>
        <table>
            <tr><td class="label">Business Name:</td><td class="value">{$business->name}</td></tr>
            <tr><td class="label">Tax Identification Number:</td><td class="value">{$business->tax_identification_number}</td></tr>
            <tr><td class="label">Business Address:</td><td class="value">{$business->address}, {$business->city}</td></tr>
            <tr><td class="label">State:</td><td class="value">{$business->state}</td></tr>
        </table>
    </div>
HTML;
    }

    /**
     * Generate declaration & signature block
     */
    private function declarationHtml(): string
    {
        $date = now()->format('F j, Y');
        return <<<HTML
    <div class="section">
        <div class="info-box">
            I declare that the information provided in this return is true and correct to the best of my knowledge and belief. I am aware that giving false information on this return is an offense under the laws of the Federal Republic of Nigeria.
        </div>
    </div>

    <div class="signature-section">
        <table>
            <tr>
                <td style="width:45%;padding-top:30px;border-top:1px solid #000;text-align:center;font-size:10px;">
                    <strong>Taxpayer/Agent Signature</strong><br>Date: _______________________
                </td>
                <td style="width:10%;">&nbsp;</td>
                <td style="width:45%;padding-top:30px;border-top:1px solid #000;text-align:center;font-size:10px;">
                    <strong>Revenue Officer</strong><br>Date: _______________________
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p><strong>Important Notice:</strong> This tax return should be submitted to the appropriate tax authority before the due date. Failure to file may result in penalties and interest charges as prescribed by the Tax Administration Law.</p>
        <p style="margin-top: 10px; text-align: center;">Generated by TaxMaster on {$date}</p>
    </div>
HTML;
    }

    // =============================================
    // PAYE RETURN PDF
    // =============================================

    /**
     * Generate PDF for PAYE Return
     */
    public function generatePayeReturnPdf(PayeReturn $payeReturn): string
    {
        $payeReturn->load(['business', 'schedules.staff']);
        $html = $this->generatePayeHtml($payeReturn);

        $mpdf = $this->createMpdf();
        $mpdf->SetTitle("PAYE Return - {$payeReturn->period_label}");
        $mpdf->WriteHTML($html);

        return $mpdf->Output('paye-return-' . $payeReturn->id . '.pdf', Destination::STRING_RETURN);
    }

    private function generatePayeHtml(PayeReturn $payeReturn): string
    {
        $business = $payeReturn->business;
        $styles = $this->getStyles();
        $businessInfo = $this->businessInfoHtml($business);
        $declaration = $this->declarationHtml();

        $totalGross = $this->formatCurrency($payeReturn->total_gross_pay);
        $totalTax = $this->formatCurrency($payeReturn->total_tax_deducted);
        $staffCount = $payeReturn->staff_count ?: ($payeReturn->schedules ? $payeReturn->schedules->count() : 0);
        $status = ucfirst($payeReturn->status);
        $firsRef = $payeReturn->firs_reference ?: 'N/A';
        $filedDate = $payeReturn->filed_date ? $payeReturn->filed_date->format('F j, Y') : 'Not filed';

        // Build schedule rows
        $scheduleRows = '';
        if ($payeReturn->schedules && $payeReturn->schedules->count() > 0) {
            $sn = 1;
            foreach ($payeReturn->schedules as $schedule) {
                $staffName = $schedule->staff
                    ? $schedule->staff->first_name . ' ' . $schedule->staff->last_name
                    : 'Staff #' . $schedule->business_staff_id;
                $staffTin = $schedule->staff->tax_identification_number ?? 'N/A';
                $grossPay = $this->formatCurrency($schedule->gross_pay);
                $taxableIncome = $this->formatCurrency($schedule->taxable_income);
                $payeDue = $this->formatCurrency($schedule->paye_due);
                $scheduleRows .= "<tr>
                    <td>{$sn}</td>
                    <td>{$staffName}</td>
                    <td>{$staffTin}</td>
                    <td style='text-align:right'>₦{$grossPay}</td>
                    <td style='text-align:right'>₦{$taxableIncome}</td>
                    <td style='text-align:right'>₦{$payeDue}</td>
                </tr>";
                $sn++;
            }
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>PAYE Return - {$payeReturn->period_label}</title>
    <style>{$styles}</style>
</head>
<body>
    <div class="header">
        <h1>FEDERAL REPUBLIC OF NIGERIA</h1>
        <h2>PAY-AS-YOU-EARN (PAYE) MONTHLY REMITTANCE SCHEDULE</h2>
        <h3>Period: {$payeReturn->period_label}</h3>
    </div>

    {$businessInfo}

    <div class="section">
        <h3>Return Summary</h3>
        <table>
            <tr><td class="label">Period:</td><td class="value">{$payeReturn->period_label}</td></tr>
            <tr><td class="label">Number of Employees:</td><td class="value">{$staffCount}</td></tr>
            <tr><td class="label">Total Gross Pay:</td><td class="value">₦{$totalGross}</td></tr>
            <tr class="total-row"><td class="label">Total PAYE Tax Deducted:</td><td class="value">₦{$totalTax}</td></tr>
            <tr><td class="label">Filing Status:</td><td class="value">{$status}</td></tr>
            <tr><td class="label">Filed Date:</td><td class="value">{$filedDate}</td></tr>
            <tr><td class="label">FIRS Reference:</td><td class="value">{$firsRef}</td></tr>
        </table>
    </div>

    <div class="section">
        <h3>Employee Schedule</h3>
        <table>
            <tr>
                <th>S/N</th>
                <th>Employee Name</th>
                <th>TIN</th>
                <th style="text-align:right">Gross Pay</th>
                <th style="text-align:right">Taxable Income</th>
                <th style="text-align:right">PAYE Due</th>
            </tr>
            {$scheduleRows}
            <tr class="total-row">
                <td colspan="3"><strong>TOTAL</strong></td>
                <td style="text-align:right">₦{$totalGross}</td>
                <td></td>
                <td style="text-align:right">₦{$totalTax}</td>
            </tr>
        </table>
    </div>

    {$declaration}
</body>
</html>
HTML;
    }

    // =============================================
    // VAT RETURN PDF
    // =============================================

    /**
     * Generate PDF for VAT Return
     */
    public function generateVatReturnPdf(VatReturn $vatReturn): string
    {
        $vatReturn->load('business');
        $html = $this->generateVatHtml($vatReturn);

        $mpdf = $this->createMpdf();
        $mpdf->SetTitle("VAT Return - {$vatReturn->period_label}");
        $mpdf->WriteHTML($html);

        return $mpdf->Output('vat-return-' . $vatReturn->id . '.pdf', Destination::STRING_RETURN);
    }

    private function generateVatHtml(VatReturn $vatReturn): string
    {
        $business = $vatReturn->business;
        $styles = $this->getStyles();
        $businessInfo = $this->businessInfoHtml($business);
        $declaration = $this->declarationHtml();

        $salesTurnover = $this->formatCurrency($vatReturn->sales_turnover);
        $exemptSales = $this->formatCurrency($vatReturn->exempt_sales);
        $zeroRatedSales = $this->formatCurrency($vatReturn->zero_rated_sales);
        $exportSales = $this->formatCurrency($vatReturn->export_sales);
        $vatOnSales = $this->formatCurrency($vatReturn->vat_on_sales);
        $purchasesTurnover = $this->formatCurrency($vatReturn->purchases_turnover);
        $inputVat = $this->formatCurrency($vatReturn->input_vat);
        $vatDue = $this->formatCurrency($vatReturn->vat_due);
        $settlementAmount = $this->formatCurrency($vatReturn->settlement_amount);
        $status = ucfirst($vatReturn->status);
        $firsRef = $vatReturn->firs_reference ?: 'N/A';
        $dueDate = $vatReturn->due_date ? $vatReturn->due_date->format('F j, Y') : 'N/A';
        $filedAt = $vatReturn->filed_at ? $vatReturn->filed_at->format('F j, Y') : 'Not filed';
        $priorCredit = $this->formatCurrency($vatReturn->prior_month_credit);
        $withholdingVat = $this->formatCurrency($vatReturn->withholding_vat);

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>VAT Return - {$vatReturn->period_label}</title>
    <style>{$styles}</style>
</head>
<body>
    <div class="header">
        <h1>FEDERAL REPUBLIC OF NIGERIA</h1>
        <h2>VALUE ADDED TAX (VAT) RETURN</h2>
        <h3>Period: {$vatReturn->period_label}</h3>
    </div>

    {$businessInfo}

    <div class="section">
        <h3>Return Details</h3>
        <table>
            <tr><td class="label">Period:</td><td class="value">{$vatReturn->period_label}</td></tr>
            <tr><td class="label">Due Date:</td><td class="value">{$dueDate}</td></tr>
            <tr><td class="label">Filed Date:</td><td class="value">{$filedAt}</td></tr>
            <tr><td class="label">Filing Status:</td><td class="value">{$status}</td></tr>
            <tr><td class="label">FIRS Reference:</td><td class="value">{$firsRef}</td></tr>
        </table>
    </div>

    <div class="section">
        <h3>Output VAT (Sales)</h3>
        <table>
            <tr><td class="label">Sales Turnover:</td><td class="value">₦{$salesTurnover}</td></tr>
            <tr><td class="label">Exempt Sales:</td><td class="value">₦{$exemptSales}</td></tr>
            <tr><td class="label">Zero-Rated Sales:</td><td class="value">₦{$zeroRatedSales}</td></tr>
            <tr><td class="label">Export Sales:</td><td class="value">₦{$exportSales}</td></tr>
            <tr class="total-row"><td class="label">VAT on Sales (Output VAT):</td><td class="value">₦{$vatOnSales}</td></tr>
        </table>
    </div>

    <div class="section">
        <h3>Input VAT (Purchases)</h3>
        <table>
            <tr><td class="label">Purchases Turnover:</td><td class="value">₦{$purchasesTurnover}</td></tr>
            <tr class="total-row"><td class="label">Input VAT:</td><td class="value">₦{$inputVat}</td></tr>
        </table>
    </div>

    <div class="section">
        <h3>VAT Computation</h3>
        <table>
            <tr><td class="label">Output VAT:</td><td class="value">₦{$vatOnSales}</td></tr>
            <tr><td class="label">Less: Input VAT:</td><td class="value negative">₦{$inputVat}</td></tr>
            <tr><td class="label">Less: Prior Month Credit:</td><td class="value negative">₦{$priorCredit}</td></tr>
            <tr><td class="label">Less: Withholding VAT:</td><td class="value negative">₦{$withholdingVat}</td></tr>
            <tr class="total-row"><td class="label">NET VAT DUE:</td><td class="value">₦{$vatDue}</td></tr>
            <tr class="total-row"><td class="label">SETTLEMENT AMOUNT:</td><td class="value">₦{$settlementAmount}</td></tr>
        </table>
    </div>

    {$declaration}
</body>
</html>
HTML;
    }

    // =============================================
    // WHT RETURN PDF
    // =============================================

    /**
     * Generate PDF for WHT Return
     */
    public function generateWhtReturnPdf(WhtReturn $whtReturn): string
    {
        $whtReturn->load('business');
        $html = $this->generateWhtHtml($whtReturn);

        $mpdf = $this->createMpdf();
        $mpdf->SetTitle("WHT Return - {$whtReturn->period_label}");
        $mpdf->WriteHTML($html);

        return $mpdf->Output('wht-return-' . $whtReturn->id . '.pdf', Destination::STRING_RETURN);
    }

    private function generateWhtHtml(WhtReturn $whtReturn): string
    {
        $business = $whtReturn->business;
        $styles = $this->getStyles();
        $businessInfo = $this->businessInfoHtml($business);
        $declaration = $this->declarationHtml();

        $totalWht = $this->formatCurrency($whtReturn->total_wht_deducted);
        $transactionCount = $whtReturn->transaction_count ?? 0;
        $status = ucfirst($whtReturn->status);
        $firsRef = $whtReturn->firs_reference ?: 'N/A';
        $filedDate = $whtReturn->filed_date ? \Carbon\Carbon::parse($whtReturn->filed_date)->format('F j, Y') : 'Not filed';

        // Build transaction schedule from schedule_data
        $scheduleRows = '';
        if (!empty($whtReturn->schedule_data) && is_array($whtReturn->schedule_data)) {
            $sn = 1;
            foreach ($whtReturn->schedule_data as $item) {
                $beneficiary = $item['beneficiary'] ?? $item['beneficiary_name'] ?? 'N/A';
                $type = ucwords(str_replace('_', ' ', $item['transaction_type'] ?? $item['type'] ?? 'N/A'));
                $gross = $this->formatCurrency($item['gross_amount'] ?? $item['amount'] ?? 0);
                $rate = ($item['wht_rate'] ?? $item['rate'] ?? 0) . '%';
                $whtAmt = $this->formatCurrency($item['wht_amount'] ?? $item['wht'] ?? 0);
                $scheduleRows .= "<tr>
                    <td>{$sn}</td>
                    <td>{$beneficiary}</td>
                    <td>{$type}</td>
                    <td style='text-align:right'>₦{$gross}</td>
                    <td style='text-align:center'>{$rate}</td>
                    <td style='text-align:right'>₦{$whtAmt}</td>
                </tr>";
                $sn++;
            }
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>WHT Return - {$whtReturn->period_label}</title>
    <style>{$styles}</style>
</head>
<body>
    <div class="header">
        <h1>FEDERAL REPUBLIC OF NIGERIA</h1>
        <h2>WITHHOLDING TAX (WHT) RETURN</h2>
        <h3>Period: {$whtReturn->period_label}</h3>
    </div>

    {$businessInfo}

    <div class="section">
        <h3>Return Summary</h3>
        <table>
            <tr><td class="label">Period:</td><td class="value">{$whtReturn->period_label}</td></tr>
            <tr><td class="label">Number of Transactions:</td><td class="value">{$transactionCount}</td></tr>
            <tr class="total-row"><td class="label">Total WHT Deducted:</td><td class="value">₦{$totalWht}</td></tr>
            <tr><td class="label">Filing Status:</td><td class="value">{$status}</td></tr>
            <tr><td class="label">Filed Date:</td><td class="value">{$filedDate}</td></tr>
            <tr><td class="label">FIRS Reference:</td><td class="value">{$firsRef}</td></tr>
        </table>
    </div>

    <div class="section">
        <h3>WHT Deduction Schedule</h3>
        <table>
            <tr>
                <th>S/N</th>
                <th>Beneficiary</th>
                <th>Transaction Type</th>
                <th style="text-align:right">Gross Amount</th>
                <th style="text-align:center">Rate</th>
                <th style="text-align:right">WHT Amount</th>
            </tr>
            {$scheduleRows}
            <tr class="total-row">
                <td colspan="5"><strong>TOTAL WHT DEDUCTED</strong></td>
                <td style="text-align:right">₦{$totalWht}</td>
            </tr>
        </table>
    </div>

    {$declaration}
</body>
</html>
HTML;
    }

    // =============================================
    // CIT RETURN PDF
    // =============================================

    /**
     * Generate PDF for CIT Return
     */
    public function generateCitReturnPdf(CitReturn $citReturn): string
    {
        $citReturn->load('business');
        $html = $this->generateCitHtml($citReturn);

        $mpdf = $this->createMpdf();
        $mpdf->SetTitle("CIT Return - " . ($citReturn->period ?? 'N/A'));
        $mpdf->WriteHTML($html);

        return $mpdf->Output('cit-return-' . $citReturn->id . '.pdf', Destination::STRING_RETURN);
    }

    private function generateCitHtml(CitReturn $citReturn): string
    {
        $business = $citReturn->business;
        $styles = $this->getStyles();
        $businessInfo = $this->businessInfoHtml($business);
        $declaration = $this->declarationHtml();

        $revenue = $this->formatCurrency($citReturn->revenue);
        $cogs = $this->formatCurrency($citReturn->cost_of_goods_sold);
        $grossProfit = $this->formatCurrency($citReturn->gross_profit);
        $depreciation = $this->formatCurrency($citReturn->depreciation);
        $amortization = $this->formatCurrency($citReturn->amortization);
        $otherAddBacks = $this->formatCurrency($citReturn->other_add_backs);
        $capitalAllowances = $this->formatCurrency($citReturn->capital_allowances);
        $allowableExpenses = $this->formatCurrency($citReturn->allowable_expenses);
        $otherDeductions = $this->formatCurrency($citReturn->other_deductions);
        $taxableIncome = $this->formatCurrency($citReturn->taxable_income);
        $citRate = $citReturn->cit_rate ? number_format((float) $citReturn->cit_rate, 2) . '%' : 'N/A';
        $citPayable = $this->formatCurrency($citReturn->cit_payable);
        $turnover = $this->formatCurrency($citReturn->turnover);
        $minimumTax = $this->formatCurrency($citReturn->minimum_tax_amount);
        $taxDue = $this->formatCurrency($citReturn->tax_due);
        $advanceTax = $this->formatCurrency($citReturn->advance_tax);
        $whtCredit = $this->formatCurrency($citReturn->withholding_tax);
        $totalCredits = $this->formatCurrency($citReturn->total_credits);
        $balanceDue = $this->formatCurrency($citReturn->balance_due);
        $balanceRefund = $this->formatCurrency($citReturn->balance_refund);
        $penalty = $this->formatCurrency($citReturn->late_filing_penalty);
        $interest = $this->formatCurrency($citReturn->payment_interest);
        $status = ucfirst($citReturn->status);
        $firsRef = $citReturn->firs_reference ?: 'N/A';
        $dueDate = $citReturn->due_date ? $citReturn->due_date->format('F j, Y') : 'N/A';
        $filedAt = $citReturn->filed_at ? $citReturn->filed_at->format('F j, Y') : 'Not filed';
        $period = $citReturn->period ?? 'N/A';

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>CIT Return - {$period}</title>
    <style>{$styles}</style>
</head>
<body>
    <div class="header">
        <h1>FEDERAL REPUBLIC OF NIGERIA</h1>
        <h2>COMPANY INCOME TAX (CIT) RETURN</h2>
        <h3>Assessment Year: {$period}</h3>
    </div>

    {$businessInfo}

    <div class="section">
        <h3>Return Details</h3>
        <table>
            <tr><td class="label">Assessment Period:</td><td class="value">{$period}</td></tr>
            <tr><td class="label">Return Type:</td><td class="value">{$citReturn->return_type}</td></tr>
            <tr><td class="label">Due Date:</td><td class="value">{$dueDate}</td></tr>
            <tr><td class="label">Filed Date:</td><td class="value">{$filedAt}</td></tr>
            <tr><td class="label">Filing Status:</td><td class="value">{$status}</td></tr>
            <tr><td class="label">FIRS Reference:</td><td class="value">{$firsRef}</td></tr>
        </table>
    </div>

    <div class="section">
        <h3>Income Computation</h3>
        <table>
            <tr><td class="label">Revenue / Turnover:</td><td class="value">₦{$revenue}</td></tr>
            <tr><td class="label">Less: Cost of Goods Sold:</td><td class="value negative">₦{$cogs}</td></tr>
            <tr class="total-row"><td class="label">Gross Profit:</td><td class="value">₦{$grossProfit}</td></tr>
        </table>
    </div>

    <div class="section">
        <h3>Adjustments & Deductions</h3>
        <table>
            <tr><td class="label">Add: Depreciation (disallowed):</td><td class="value">₦{$depreciation}</td></tr>
            <tr><td class="label">Add: Amortization (disallowed):</td><td class="value">₦{$amortization}</td></tr>
            <tr><td class="label">Add: Other Add-backs:</td><td class="value">₦{$otherAddBacks}</td></tr>
            <tr><td class="label">Less: Capital Allowances:</td><td class="value negative">₦{$capitalAllowances}</td></tr>
            <tr><td class="label">Less: Allowable Expenses:</td><td class="value negative">₦{$allowableExpenses}</td></tr>
            <tr><td class="label">Less: Other Deductions:</td><td class="value negative">₦{$otherDeductions}</td></tr>
            <tr class="total-row"><td class="label">Taxable Income:</td><td class="value">₦{$taxableIncome}</td></tr>
        </table>
    </div>

    <div class="section">
        <h3>Tax Computation</h3>
        <table>
            <tr><td class="label">Turnover:</td><td class="value">₦{$turnover}</td></tr>
            <tr><td class="label">CIT Rate Applied:</td><td class="value">{$citRate}</td></tr>
            <tr><td class="label">CIT on Taxable Income:</td><td class="value">₦{$citPayable}</td></tr>
            <tr><td class="label">Minimum Tax (0.5% of Turnover):</td><td class="value">₦{$minimumTax}</td></tr>
            <tr class="total-row"><td class="label">TAX DUE (Higher of CIT / Minimum Tax):</td><td class="value">₦{$taxDue}</td></tr>
        </table>
    </div>

    <div class="section">
        <h3>Tax Credits & Balance</h3>
        <table>
            <tr><td class="label">Less: Advance Tax Paid:</td><td class="value negative">₦{$advanceTax}</td></tr>
            <tr><td class="label">Less: Withholding Tax Credits:</td><td class="value negative">₦{$whtCredit}</td></tr>
            <tr><td class="label">Total Credits:</td><td class="value negative">₦{$totalCredits}</td></tr>
            <tr><td class="label">Late Filing Penalty:</td><td class="value negative">₦{$penalty}</td></tr>
            <tr><td class="label">Interest on Late Payment:</td><td class="value negative">₦{$interest}</td></tr>
            <tr class="total-row"><td class="label">BALANCE DUE:</td><td class="value">₦{$balanceDue}</td></tr>
            <tr><td class="label">Refund Due (if overpaid):</td><td class="value positive">₦{$balanceRefund}</td></tr>
        </table>
    </div>

    {$declaration}
</body>
</html>
HTML;
    }
}
