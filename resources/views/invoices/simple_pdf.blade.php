<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number ?? $invoice->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1a1a1a;
            line-height: 1.6;
            background: #ffffff;
        }
        .page {
            width: 210mm;
            padding: 20mm 15mm;
            margin: 0 auto;
            background: white;
        }

        /* Header Section */
        .invoice-header {
            display: table;
            width: 100%;
            margin-bottom: 30px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 20px;
        }
        .header-left {
            display: table-cell;
            width: 60%;
            vertical-align: top;
        }
        .header-right {
            display: table-cell;
            width: 40%;
            text-align: right;
            vertical-align: top;
        }
        .invoice-title {
            font-size: 36px;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 5px;
            letter-spacing: 1px;
        }
        .invoice-number {
            font-size: 18px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 8px;
        }
        .company-name {
            font-size: 22px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 6px;
        }
        .company-details {
            font-size: 11px;
            color: #64748b;
            line-height: 1.7;
        }

        /* Info Boxes */
        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 25px;
        }
        .info-box {
            display: table-cell;
            width: 48%;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 16px;
            vertical-align: top;
        }
        .info-box:first-child {
            margin-right: 4%;
        }
        .info-box-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #475569;
            margin-bottom: 12px;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #cbd5e1;
            padding-bottom: 6px;
        }
        .info-box-content {
            font-size: 12px;
            color: #334155;
        }
        .info-label {
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
            margin-top: 8px;
            margin-bottom: 2px;
            font-weight: 600;
        }
        .info-value {
            font-size: 13px;
            color: #1e293b;
            font-weight: 500;
        }
        .buyer-name {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            font-size: 12px;
        }
        .items-table thead {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: white;
        }
        .items-table thead th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .items-table thead th:last-child,
        .items-table tbody td:last-child {
            text-align: right;
        }
        .items-table tbody tr {
            border-bottom: 1px solid #e2e8f0;
        }
        .items-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }
        .items-table tbody td {
            padding: 12px;
            color: #334155;
            vertical-align: top;
        }
        .item-description {
            font-weight: 500;
            color: #1e293b;
            margin-bottom: 3px;
        }
        .item-details {
            font-size: 10px;
            color: #64748b;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }

        /* Totals Section */
        .totals-section {
            display: table;
            width: 100%;
            margin-top: 20px;
        }
        .totals-left {
            display: table-cell;
            width: 55%;
            vertical-align: top;
            padding-right: 20px;
        }
        .totals-right {
            display: table-cell;
            width: 45%;
            vertical-align: top;
        }
        .totals-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 16px;
        }
        .totals-row {
            display: table;
            width: 100%;
            padding: 8px 0;
            font-size: 13px;
        }
        .totals-row-label {
            display: table-cell;
            color: #475569;
            font-weight: 500;
        }
        .totals-row-value {
            display: table-cell;
            text-align: right;
            color: #1e293b;
            font-weight: 600;
        }
        .totals-divider {
            border-top: 1px dashed #cbd5e1;
            margin: 6px 0;
        }
        .totals-final {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: white;
            padding: 14px 16px;
            border-radius: 6px;
            margin-top: 8px;
            font-size: 16px;
            font-weight: 700;
        }

        /* Payment Info */
        .payment-info {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 14px;
            border-radius: 4px;
            font-size: 11px;
            color: #78350f;
            line-height: 1.6;
        }
        .payment-info-title {
            font-weight: 700;
            margin-bottom: 6px;
            font-size: 12px;
            color: #92400e;
        }

        /* Notes Section */
        .notes-section {
            margin-top: 25px;
            padding: 14px;
            background: #f1f5f9;
            border-radius: 6px;
            border-left: 4px solid #64748b;
        }
        .notes-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #475569;
            margin-bottom: 8px;
        }
        .notes-content {
            font-size: 11px;
            color: #334155;
            line-height: 1.6;
        }

        /* Footer */
        .invoice-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e2e8f0;
            text-align: center;
        }
        .footer-message {
            font-size: 13px;
            color: #2563eb;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .footer-contact {
            font-size: 10px;
            color: #64748b;
            line-height: 1.6;
        }
        .footer-legal {
            font-size: 9px;
            color: #94a3b8;
            margin-top: 12px;
            font-style: italic;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-paid {
            background: #d1fae5;
            color: #065f46;
        }
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        .status-draft {
            background: #e2e8f0;
            color: #475569;
        }
        .status-overdue {
            background: #fee2e2;
            color: #991b1b;
        }

        /* FIRS Compliance Badge */
        .firs-badge {
            display: inline-block;
            margin-top: 12px;
            padding: 6px 10px;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 4px;
            font-size: 9px;
            color: #047857;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- Header -->
        <div class="invoice-header">
            <div class="header-left">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-number">#{{ $invoice->invoice_number ?? str_pad($invoice->id, 6, '0', STR_PAD_LEFT) }}</div>
                <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                    <strong>Issue Date:</strong> {{ $invoice->invoice_date?->format('F d, Y') ?? $invoice->created_at?->format('F d, Y') }}
                </div>
                @if($invoice->due_date)
                <div style="font-size: 11px; color: #64748b;">
                    <strong>Due Date:</strong> {{ $invoice->due_date->format('F d, Y') }}
                </div>
                @endif
            </div>
            <div class="header-right">
                <div class="company-name">{{ $invoice->business->name ?? 'Your Business' }}</div>
                <div class="company-details">
                    @if($invoice->business->address)
                        {{ $invoice->business->address }}<br>
                    @endif
                    @if($invoice->business->phone)
                        Tel: {{ $invoice->business->phone }}<br>
                    @endif
                    @if($invoice->business->email)
                        Email: {{ $invoice->business->email }}<br>
                    @endif
                    @if($invoice->business->tin ?? $invoice->business->tax_id)
                        <strong>TIN:</strong> {{ $invoice->business->tin ?? $invoice->business->tax_id }}
                    @endif
                </div>
                @if($invoice->firs_status === 'approved')
                <div class="firs-badge">
                    ✓ FIRS E-INVOICE COMPLIANT
                </div>
                @endif
            </div>
        </div>

        <!-- Bill To & Invoice Details -->
        <div class="info-section">
            <div class="info-box">
                <div class="info-box-title">Bill To</div>
                <div class="buyer-name">{{ $invoice->data['buyer_name'] ?? 'Customer' }}</div>
                @if(!empty($invoice->data['buyer_tin']))
                <div class="info-label">Tax ID (TIN)</div>
                <div class="info-value">{{ $invoice->data['buyer_tin'] }}</div>
                @endif
                @if(!empty($invoice->data['buyer_address']))
                <div class="info-label">Address</div>
                <div class="info-value">{{ $invoice->data['buyer_address'] }}</div>
                @endif
                @if(!empty($invoice->data['buyer_email']))
                <div class="info-label">Email</div>
                <div class="info-value">{{ $invoice->data['buyer_email'] }}</div>
                @endif
                @if(!empty($invoice->data['buyer_phone']))
                <div class="info-label">Phone</div>
                <div class="info-value">{{ $invoice->data['buyer_phone'] }}</div>
                @endif
            </div>

            <div class="info-box">
                <div class="info-box-title">Invoice Information</div>
                <div class="info-label">Status</div>
                <div class="info-value">
                    <span class="status-badge status-{{ strtolower($invoice->status ?? 'draft') }}">
                        {{ ucfirst($invoice->status ?? 'draft') }}
                    </span>
                </div>
                @if($invoice->firs_reference)
                <div class="info-label">FIRS Reference</div>
                <div class="info-value" style="font-family: monospace; font-size: 11px;">{{ $invoice->firs_reference }}</div>
                @endif
                @if(!empty($invoice->data['payment_terms']))
                <div class="info-label">Payment Terms</div>
                <div class="info-value">{{ $invoice->data['payment_terms'] }}</div>
                @endif
                @if($invoice->currency)
                <div class="info-label">Currency</div>
                <div class="info-value">{{ strtoupper($invoice->currency) }}</div>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 8%">#</th>
                    <th style="width: 42%">Description</th>
                    <th style="width: 12%" class="text-center">Quantity</th>
                    <th style="width: 18%" class="text-right">Unit Price</th>
                    <th style="width: 20%" class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @if(is_array($invoice->data['items'] ?? null) && count($invoice->data['items']))
                    @foreach($invoice->data['items'] as $index => $item)
                        <tr>
                            <td class="text-center" style="color: #94a3b8;">{{ $index + 1 }}</td>
                            <td>
                                <div class="item-description">{{ $item['description'] ?? 'Item' }}</div>
                                @if(!empty($item['item_code']))
                                <div class="item-details">Code: {{ $item['item_code'] }}</div>
                                @endif
                            </td>
                            <td class="text-center">{{ $item['quantity'] ?? 1 }}</td>
                            <td class="text-right">{{ number_format($item['unit_price'] ?? 0, 2) }}</td>
                            <td class="text-right" style="font-weight: 600;">{{ number_format($item['line_total'] ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="5" class="text-center" style="color: #94a3b8; padding: 30px;">No items found</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <!-- Totals & Payment Info -->
        <div class="totals-section">
            <div class="totals-left">
                @if(!empty($invoice->data['payment_means_code']) || $invoice->business->bank_name)
                <div class="payment-info">
                    <div class="payment-info-title">💳 Payment Information</div>
                    @if($invoice->business->bank_name)
                        <strong>Bank:</strong> {{ $invoice->business->bank_name }}<br>
                    @endif
                    @if($invoice->business->bank_account_number)
                        <strong>Account:</strong> {{ $invoice->business->bank_account_number }}<br>
                    @endif
                    @if($invoice->business->bank_account_name)
                        <strong>Account Name:</strong> {{ $invoice->business->bank_account_name }}<br>
                    @endif
                    @if(!empty($invoice->data['payment_means_code']))
                        <strong>Payment Method:</strong> {{ $invoice->data['payment_means_code'] }}
                    @endif
                </div>
                @endif
            </div>

            <div class="totals-right">
                <div class="totals-box">
                    <div class="totals-row">
                        <div class="totals-row-label">Subtotal</div>
                        <div class="totals-row-value">{{ number_format($invoice->subtotal ?? ($invoice->total ?? 0) / 1.075, 2) }}</div>
                    </div>
                    @if($invoice->discount ?? 0 > 0)
                    <div class="totals-row">
                        <div class="totals-row-label">Discount</div>
                        <div class="totals-row-value" style="color: #dc2626;">-{{ number_format($invoice->discount, 2) }}</div>
                    </div>
                    @endif
                    <div class="totals-row">
                        <div class="totals-row-label">
                            VAT ({{ number_format(($invoice->data['vat_rate'] ?? 7.5), 2) }}%)
                        </div>
                        <div class="totals-row-value">{{ number_format($invoice->tax ?? 0, 2) }}</div>
                    </div>
                    <div class="totals-divider"></div>
                </div>

                <div class="totals-final">
                    <div class="totals-row">
                        <div class="totals-row-label" style="color: white;">TOTAL AMOUNT DUE</div>
                        <div class="totals-row-value" style="color: white; font-size: 18px;">
                            {{ $invoice->currency ?? 'NGN' }} {{ number_format($invoice->total ?? 0, 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes -->
        @if(!empty($invoice->notes) || !empty($invoice->data['notes']))
        <div class="notes-section">
            <div class="notes-title">Additional Notes</div>
            <div class="notes-content">
                {{ $invoice->notes ?? $invoice->data['notes'] ?? '' }}
            </div>
        </div>
        @endif

        <!-- Footer -->
        <div class="invoice-footer">
            <div class="footer-message">Thank you for your business! 🎉</div>
            <div class="footer-contact">
                For any questions regarding this invoice, please contact us at
                {{ $invoice->business->email ?? 'support@taxmaster.ng' }} or
                {{ $invoice->business->phone ?? '+234 XXX XXX XXXX' }}
            </div>
            <div class="footer-legal">
                This is a computer-generated invoice and does not require a physical signature.<br>
                @if($invoice->firs_status === 'approved')
                This invoice has been electronically submitted and approved by FIRS (Federal Inland Revenue Service).
                @endif
            </div>
        </div>
    </div>
</body>
</html>
