<?php

namespace App\Services;

use App\Models\TaxReturn;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class TaxReturnPdfGenerator
{
    /**
     * Generate PDF for tax return using mPDF
     * @return string PDF content as binary string
     */
    public function generate(TaxReturn $taxReturn): string
    {
        $html = $this->generateHtml($taxReturn);
        
        try {
            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => 15,
                'margin_bottom' => 15,
            ]);
            
            $mpdf->SetTitle('Tax Return - ' . $taxReturn->tax_period);
            $mpdf->WriteHTML($html);
            
            return $mpdf->Output('tax-return-' . $taxReturn->id . '.pdf', Destination::STRING_RETURN);
        } catch (\Exception $e) {
            throw new \RuntimeException('PDF generation failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate HTML for tax return
     */
    protected function generateHtml(TaxReturn $taxReturn): string
    {
        $business = $taxReturn->business;
        $taxType = $taxReturn->taxType;
        
        $submissionDate = $taxReturn->submission_date 
            ? $taxReturn->submission_date->format('F j, Y') 
            : 'Not submitted';
        $taxTypeName = $taxType->name ?? 'Tax Return';

        $grossIncome = $this->formatCurrency($taxReturn->gross_income);
        $deductions = $this->formatCurrency($taxReturn->deductions);
        $taxableIncome = $this->formatCurrency($taxReturn->taxable_income);
        $totalTax = $this->formatCurrency($taxReturn->total_tax_due);
        $penalties = $this->formatCurrency($taxReturn->penalties);
        $interest = $this->formatCurrency($taxReturn->interest);
        $totalDue = $this->formatCurrency($taxReturn->total_amount_due ?: $taxReturn->total_tax_due);
        $balance = $this->formatCurrency($taxReturn->balance);

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tax Return - {$taxReturn->tax_period}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            color: #333;
            line-height: 1.6;
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
        .section { 
            margin-bottom: 18px; 
            page-break-inside: avoid;
        }
        .section h3 { 
            font-size: 12px; 
            font-weight: bold; 
            background-color: #f3f4f6; 
            padding: 8px 10px; 
            margin-bottom: 10px;
            border-left: 4px solid #2563eb;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 10px;
        }
        table td { 
            padding: 8px 10px; 
            border-bottom: 1px solid #e5e7eb;
        }
        table tr:nth-child(even) { 
            background-color: #f9fafb; 
        }
        .label { 
            font-weight: 600; 
            color: #1f2937; 
            width: 45%;
        }
        .value { 
            text-align: right; 
            color: #374151;
        }
        .total-row { 
            background-color: #dbeafe !important; 
            font-weight: bold;
        }
        .total-row .value {
            font-size: 13px;
            color: #1e40af;
        }
        .positive { color: #16a34a; }
        .negative { color: #dc2626; }
        .footer {
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px solid #d1d5db;
            font-size: 11px;
            color: #6b7280;
        }
        .signature-section {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        .signature-block {
            width: 45%;
            padding-top: 30px;
            border-top: 1px solid #000;
            text-align: center;
            font-size: 11px;
        }
        .info-box {
            background-color: #f0fdf4;
            border-left: 4px solid #16a34a;
            padding: 10px;
            margin-bottom: 10px;
            font-size: 11px;
            color: #166534;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>FEDERAL REPUBLIC OF NIGERIA</h1>
        <h2>TAX RETURN FORM</h2>
        <h3>{$taxTypeName}</h3>
    </div>

    <div class="section">
        <h3>Business Information</h3>
        <table>
            <tr>
                <td class="label">Business Name:</td>
                <td class="value">{$business->name}</td>
            </tr>
            <tr>
                <td class="label">Tax Identification Number:</td>
                <td class="value">{$business->tax_identification_number}</td>
            </tr>
            <tr>
                <td class="label">Business Address:</td>
                <td class="value">{$business->address}</td>
            </tr>
            <tr>
                <td class="label">State:</td>
                <td class="value">{$business->state}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Return Details</h3>
        <table>
            <tr>
                <td class="label">Tax Period:</td>
                <td class="value">{$taxReturn->tax_period}</td>
            </tr>
            <tr>
                <td class="label">Return Type:</td>
                <td class="value">{$taxReturn->return_type}</td>
            </tr>
            <tr>
                <td class="label">Due Date:</td>
                <td class="value">{$taxReturn->due_date->format('F j, Y')}</td>
            </tr>
            <tr>
                <td class="label">Submission Date:</td>
                <td class="value">{$submissionDate}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Tax Computation</h3>
        <table>
            <tr>
                <td class="label">Gross Income:</td>
                <td class="value">₦{$grossIncome}</td>
            </tr>
            <tr>
                <td class="label">Less: Deductions:</td>
                <td class="value">₦{$deductions}</td>
            </tr>
            <tr class="total-row">
                <td class="label">Taxable Income:</td>
                <td class="value">₦{$taxableIncome}</td>
            </tr>
            <tr class="total-row">
                <td class="label">Total Tax Due:</td>
                <td class="value">₦{$totalTax}</td>
            </tr>
            <tr>
                <td class="label">Late Filing Penalty (10%):</td>
                <td class="value negative">₦{$penalties}</td>
            </tr>
            <tr>
                <td class="label">Interest (21% p.a.):</td>
                <td class="value negative">₦{$interest}</td>
            </tr>
            <tr class="total-row">
                <td class="label">TOTAL AMOUNT DUE:</td>
                <td class="value">₦{$totalDue}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Payment Information</h3>
        <table>
            <tr>
                <td class="label">Total Tax Paid:</td>
                <td class="value">₦{$this->formatCurrency($taxReturn->total_tax_paid)}</td>
            </tr>
            <tr class="total-row">
                <td class="label">Balance Outstanding:</td>
                <td class="value">₦{$balance}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Compliance Information</h3>
        <table>
            <tr>
                <td class="label">Filing Status:</td>
                <td class="value">{$this->formatStatus($taxReturn->status)}</td>
            </tr>
            <tr>
                <td class="label">Document ID:</td>
                <td class="value">TXR-{$taxReturn->id}</td>
            </tr>
            <tr>
                <td class="label">Generated On:</td>
                <td class="value">February 25, 2026</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="info-box">
            I declare that the information provided in this return is true and correct to the best of my knowledge and belief. I am aware that giving false information on this return is an offense under the laws of the Federal Republic of Nigeria.
        </div>
    </div>

    <div class="signature-section">
        <div class="signature-block">
            <strong>Taxpayer/Agent Signature</strong><br>
            _______________________<br>
            Date: _______________________
        </div>
        <div class="signature-block">
            <strong>Revenue Officer</strong><br>
            _______________________<br>
            Date: _______________________
        </div>
    </div>

    <div class="footer">
        <p><strong>Important Notice:</strong> This tax return should be submitted to the appropriate tax authority before the due date. Failure to file may result in penalties and interest charges as prescribed by the Tax Administration Law.</p>
        <p style="margin-top: 10px; text-align: center;">Generated by TaxMaster - Tax Management System</p>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Format currency value
     */
    private function formatCurrency(mixed $value): string
    {
        if (is_null($value)) {
            return '0.00';
        }
        return number_format((float)$value, 2, '.', ',');
    }

    /**
     * Format status for display
     */
    private function formatStatus(string $status): string
    {
        return match($status) {
            'draft' => 'Draft',
            'submitted' => 'Submitted',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'paid' => 'Paid',
            default => ucfirst($status),
        };
    }
}
