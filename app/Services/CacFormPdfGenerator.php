<?php

namespace App\Services;

use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class CacFormPdfGenerator
{
    /**
     * Generate PDF for CAC forms (Form AR + Notice of Situation)
     */
    public function generate(array $data): string
    {
        $html = $this->generateHtml($data);

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
        ]);

        $mpdf->SetTitle('CAC Annual Return Forms');
        $mpdf->WriteHTML($html);

        return $mpdf->Output('cac-forms.pdf', Destination::STRING_RETURN);
    }

    protected function generateHtml(array $data): string
    {
        $directors = $data['directors'] ?? [];
        $shareholders = $data['shareholders'] ?? [];

        $directorsRows = $this->renderRows($directors, ['name', 'address']);
        $shareholdersRows = $this->renderRows($shareholders, ['name', 'shares']);

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>CAC Annual Return Forms</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111; font-size: 12px; }
        h1, h2 { margin: 0; text-align: center; }
        h1 { font-size: 16px; }
        h2 { font-size: 13px; margin: 12px 0; }
        .section { margin-top: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; }
        th { text-align: left; background: #f3f4f6; }
        .label { width: 40%; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Corporate Affairs Commission</h1>
    <h2>Form AR - Annual Return</h2>

    <div class="section">
        <table>
            <tr><td class="label">Company Name</td><td>{$data['company_name']}</td></tr>
            <tr><td class="label">RC Number</td><td>{$data['rc_number']}</td></tr>
            <tr><td class="label">Incorporation Date</td><td>{$data['incorporation_date']}</td></tr>
            <tr><td class="label">Registered Address</td><td>{$data['registered_address']}</td></tr>
            <tr><td class="label">Business Address</td><td>{$data['business_address']}</td></tr>
            <tr><td class="label">Email</td><td>{$data['email']}</td></tr>
            <tr><td class="label">Phone</td><td>{$data['phone']}</td></tr>
            <tr><td class="label">Nature of Business</td><td>{$data['nature_of_business']}</td></tr>
            <tr><td class="label">Share Capital</td><td>{$data['share_capital']}</td></tr>
            <tr><td class="label">Company Secretary</td><td>{$data['secretary_name']}</td></tr>
            <tr><td class="label">Secretary Address</td><td>{$data['secretary_address']}</td></tr>
        </table>
    </div>

    <div class="section">
        <h2>Directors</h2>
        <table>
            <tr><th>Name</th><th>Address</th></tr>
            {$directorsRows}
        </table>
    </div>

    <div class="section">
        <h2>Shareholders</h2>
        <table>
            <tr><th>Name</th><th>Shareholding</th></tr>
            {$shareholdersRows}
        </table>
    </div>

    <div class="section">
        <h2>Notice of Situation of Registered Office</h2>
        <table>
            <tr><td class="label">Registered Office Address</td><td>{$data['notice_registered_address']}</td></tr>
            <tr><td class="label">Effective Date</td><td>{$data['notice_effective_date']}</td></tr>
        </table>
    </div>
</body>
</html>
HTML;
    }

    protected function renderRows(array $rows, array $columns): string
    {
        if (count($rows) === 0) {
            return '<tr><td colspan="' . count($columns) . '">None</td></tr>';
        }

        $html = '';
        foreach ($rows as $row) {
            $cells = [];
            foreach ($columns as $column) {
                $cells[] = '<td>' . ($row[$column] ?? '') . '</td>';
            }
            $html .= '<tr>' . implode('', $cells) . '</tr>';
        }

        return $html;
    }
}
