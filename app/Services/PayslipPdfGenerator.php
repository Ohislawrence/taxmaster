<?php

namespace App\Services;

use App\Models\BusinessStaff;
use App\Models\Business;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class PayslipPdfGenerator
{
    /**
     * Generate payslip PDF for a staff member
     *
     * @param BusinessStaff $staff
     * @param float $monthlyTax
     * @param int|null $year
     * @param int|null $month
     * @return string PDF content as binary string
     */
    public function generate(BusinessStaff $staff, float $monthlyTax, ?int $year = null, ?int $month = null): string
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;
        $periodLabel = now()->setYear($year)->setMonth($month)->format('F Y');

        $business = $staff->business;
        $html = $this->generateHtml($staff, $business, $monthlyTax, $periodLabel);

        try {
            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => 15,
                'margin_bottom' => 15,
            ]);

            $mpdf->SetTitle("Payslip - {$staff->full_name} - {$periodLabel}");
            $mpdf->WriteHTML($html);

            return $mpdf->Output("payslip-{$staff->id}-{$year}-{$month}.pdf", Destination::STRING_RETURN);
        } catch (\Exception $e) {
            throw new \RuntimeException('Payslip generation failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate payslip HTML
     */
    protected function generateHtml(BusinessStaff $staff, Business $business, float $monthlyTax, string $periodLabel): string
    {
        $grossSalary = (float) $staff->monthly_salary;
        $personalRelief = config('taxmaster.tax.personal_reliefs.personal', 500000) / 12;
        $taxableIncome = max(0, $grossSalary - $personalRelief);
        $netPay = $grossSalary - $monthlyTax;

        // Format values
        $fGross = $this->formatCurrency($grossSalary);
        $fRelief = $this->formatCurrency($personalRelief);
        $fTaxable = $this->formatCurrency($taxableIncome);
        $fTax = $this->formatCurrency($monthlyTax);
        $fNet = $this->formatCurrency($netPay);
        $fAnnualGross = $this->formatCurrency($grossSalary * 12);
        $fAnnualTax = $this->formatCurrency($monthlyTax * 12);
        $fAnnualNet = $this->formatCurrency($netPay * 12);

        $businessName = htmlspecialchars($business->name ?? 'Company');
        $businessAddress = htmlspecialchars($business->address ?? '');
        $staffName = htmlspecialchars($staff->full_name);
        $staffEmail = htmlspecialchars($staff->email ?? '');
        $staffDesignation = htmlspecialchars($staff->designation ?? 'N/A');
        $employmentType = ucfirst(str_replace('_', ' ', $staff->employment_type ?? 'N/A'));
        $tin = htmlspecialchars($staff->tax_identification_number ?? 'N/A');
        $dateEmployed = $staff->date_employed ? $staff->date_employed->format('M j, Y') : 'N/A';
        $generatedDate = now()->format('F j, Y');
        $payDate = now()->endOfMonth()->format('F j, Y');

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip - {$staffName} - {$periodLabel}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            line-height: 1.5;
            font-size: 12px;
        }

        /* Header */
        .header {
            text-align: center;
            padding-bottom: 15px;
            border-bottom: 3px solid #1e40af;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 20px;
            color: #1e3a5f;
            margin-bottom: 3px;
            letter-spacing: 1px;
        }
        .header .company-name {
            font-size: 16px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 2px;
        }
        .header .company-address {
            font-size: 11px;
            color: #6b7280;
        }
        .header .period {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-top: 8px;
            background-color: #eff6ff;
            display: inline-block;
            padding: 4px 16px;
            border-radius: 4px;
        }

        /* Two-column layout */
        .info-grid {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-grid td {
            vertical-align: top;
            padding: 0;
        }
        .info-box {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 12px 15px;
        }
        .info-box h3 {
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
        }
        .info-row {
            margin-bottom: 4px;
        }
        .info-label {
            color: #6b7280;
            font-size: 11px;
        }
        .info-value {
            font-weight: 600;
            color: #1f2937;
        }

        /* Tables */
        .section {
            margin-bottom: 18px;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            background-color: #eff6ff;
            padding: 8px 12px;
            margin-bottom: 0;
            border-left: 4px solid #1e40af;
            color: #1e3a5f;
        }
        table.pay-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.pay-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        table.pay-table .label {
            font-weight: 500;
            color: #374151;
            width: 55%;
        }
        table.pay-table .amount {
            text-align: right;
            font-weight: 600;
            color: #1f2937;
            width: 45%;
        }
        table.pay-table .deduction .amount {
            color: #dc2626;
        }
        table.pay-table .total-row {
            background-color: #dbeafe;
            font-weight: bold;
        }
        table.pay-table .total-row .amount {
            font-size: 14px;
            color: #1e40af;
        }
        table.pay-table .net-row {
            background-color: #dcfce7;
        }
        table.pay-table .net-row .label {
            font-weight: bold;
            color: #166534;
        }
        table.pay-table .net-row .amount {
            font-size: 15px;
            color: #166534;
            font-weight: bold;
        }
        table.pay-table .sub-label {
            color: #9ca3af;
            font-size: 10px;
            font-weight: normal;
        }

        /* Annual box */
        .annual-box {
            background-color: #f0f9ff;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            padding: 12px 15px;
            margin-bottom: 18px;
        }
        .annual-box h3 {
            font-size: 11px;
            color: #1e40af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        .annual-grid {
            width: 100%;
        }
        .annual-grid td {
            padding: 4px 0;
        }
        .annual-grid .a-label {
            color: #374151;
            width: 40%;
        }
        .annual-grid .a-value {
            text-align: right;
            font-weight: 600;
            color: #1e40af;
        }

        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #e5e7eb;
        }
        .footer-note {
            font-size: 10px;
            color: #9ca3af;
            text-align: center;
            line-height: 1.6;
        }
        .confidential {
            text-align: center;
            font-size: 10px;
            color: #ef4444;
            font-weight: bold;
            margin-top: 10px;
            letter-spacing: 2px;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <div class="company-name">{$businessName}</div>
        <div class="company-address">{$businessAddress}</div>
        <h1>PAYSLIP</h1>
        <div class="period">Pay Period: {$periodLabel}</div>
    </div>

    <!-- Employee & Pay Info -->
    <table class="info-grid" cellspacing="0" cellpadding="0">
        <tr>
            <td style="width: 48%; padding-right: 8px;">
                <div class="info-box">
                    <h3>Employee Information</h3>
                    <div class="info-row">
                        <span class="info-label">Name:</span>
                        <span class="info-value">{$staffName}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value">{$staffEmail}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Designation:</span>
                        <span class="info-value">{$staffDesignation}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">TIN:</span>
                        <span class="info-value">{$tin}</span>
                    </div>
                </div>
            </td>
            <td style="width: 48%; padding-left: 8px;">
                <div class="info-box">
                    <h3>Pay Details</h3>
                    <div class="info-row">
                        <span class="info-label">Employment Type:</span>
                        <span class="info-value">{$employmentType}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Date Employed:</span>
                        <span class="info-value">{$dateEmployed}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Pay Date:</span>
                        <span class="info-value">{$payDate}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Status:</span>
                        <span class="info-value" style="color: #16a34a;">Active</span>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Earnings -->
    <div class="section">
        <div class="section-title">EARNINGS</div>
        <table class="pay-table">
            <tr>
                <td class="label">Basic Salary</td>
                <td class="amount">₦{$fGross}</td>
            </tr>
            <tr class="total-row">
                <td class="label">Total Earnings</td>
                <td class="amount">₦{$fGross}</td>
            </tr>
        </table>
    </div>

    <!-- Deductions -->
    <div class="section">
        <div class="section-title">DEDUCTIONS</div>
        <table class="pay-table">
            <tr class="deduction">
                <td class="label">
                    PAYE Tax
                    <div class="sub-label">Personal relief: ₦{$fRelief}/month | Taxable: ₦{$fTaxable}</div>
                </td>
                <td class="amount">₦{$fTax}</td>
            </tr>
            <tr class="total-row deduction">
                <td class="label">Total Deductions</td>
                <td class="amount" style="color: #dc2626;">₦{$fTax}</td>
            </tr>
        </table>
    </div>

    <!-- Net Pay -->
    <div class="section">
        <table class="pay-table">
            <tr class="net-row">
                <td class="label">NET PAY</td>
                <td class="amount">₦{$fNet}</td>
            </tr>
        </table>
    </div>

    <!-- Annual Summary -->
    <div class="annual-box">
        <h3>Annual Projection</h3>
        <table class="annual-grid" cellspacing="0">
            <tr>
                <td class="a-label">Annual Gross Salary</td>
                <td class="a-value">₦{$fAnnualGross}</td>
            </tr>
            <tr>
                <td class="a-label">Annual PAYE Tax</td>
                <td class="a-value" style="color: #dc2626;">₦{$fAnnualTax}</td>
            </tr>
            <tr>
                <td class="a-label" style="font-weight: bold;">Annual Net Pay</td>
                <td class="a-value" style="color: #166534; font-size: 13px;">₦{$fAnnualNet}</td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p class="footer-note">
            This payslip was generated by TaxMaster on {$generatedDate}.<br>
            This is a computer-generated document. For questions, please contact your HR department.
        </p>
        <p class="confidential">— CONFIDENTIAL —</p>
    </div>

</body>
</html>
HTML;
    }

    /**
     * Format currency
     */
    protected function formatCurrency(float $amount): string
    {
        return number_format($amount, 2);
    }
}
