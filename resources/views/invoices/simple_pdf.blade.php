<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number ?? $invoice->id }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #222; margin: 0; padding: 24px; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:18px }
        .brand { text-align:right }
        .brand .name { font-size:20px; font-weight:700; color:#0b5cff }
        .meta { font-size:12px; color:#666 }

        .addresses { display:flex; justify-content:space-between; gap:20px; margin-bottom:18px }
        .box { padding:12px; border:1px solid #e9e9ef; border-radius:6px; background:#fbfbfd }
        .box h4 { margin:0 0 6px 0; font-size:12px; color:#333 }

        table.items { width:100%; border-collapse:collapse; margin-top:12px; }
        table.items thead th { background:#f3f4f8; padding:10px 12px; font-size:12px; text-align:left; border-bottom:1px solid #e6e9f0 }
        table.items tbody td { padding:10px 12px; border-bottom:1px solid #f0f2f7; vertical-align:top; font-size:12px }
        .text-right { text-align:right }

        .totals { margin-top:12px; width:320px; margin-left:auto }
        .totals .row { display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px dashed #eee }
        .totals .row.total { font-weight:700; font-size:14px; border-top:2px solid #e6e9f0; padding-top:10px }

        .footer { margin-top:26px; font-size:11px; color:#666; text-align:center }

        .muted { color:#777; font-size:12px }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <div style="font-size:14px;font-weight:700">Invoice</div>
                <div style="font-size:18px;font-weight:700">{{ $invoice->invoice_number ?? $invoice->id }}</div>
                <div class="meta">Date: {{ $invoice->invoice_date?->toDateString() ?? $invoice->created_at?->toDateString() }}</div>
            </div>
            <div class="brand">
                <div class="name">{{ $invoice->business->name ?? '' }}</div>
                <div class="muted">{{ $invoice->business->address ?? '' }}</div>
            </div>
        </div>

        <div class="addresses">
            <div class="box" style="flex:1">
                <h4>Bill To</h4>
                <div style="font-weight:600">{{ $invoice->data['buyer_name'] ?? $invoice->business->name }}</div>
                <div class="muted">TIN: {{ $invoice->data['buyer_tin'] ?? 'N/A' }}</div>
                @if(!empty($invoice->data['buyer_address']))
                    <div class="muted" style="margin-top:6px">{{ $invoice->data['buyer_address'] }}</div>
                @endif
            </div>

            <div class="box" style="width:220px">
                <h4>Invoice Details</h4>
                <div class="muted">Issue Date: {{ $invoice->invoice_date?->toDateString() ?? '-' }}</div>
                <div class="muted">Due Date: {{ $invoice->due_date?->toDateString() ?? '-' }}</div>
                <div class="muted">Status: {{ ucfirst($invoice->status ?? 'draft') }}</div>
            </div>
        </div>

        <table class="items">
            <thead>
                <tr>
                    <th style="width:55%">Description</th>
                    <th style="width:10%" class="text-right">Qty</th>
                    <th style="width:17%" class="text-right">Unit</th>
                    <th style="width:18%" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @if(is_array($invoice->data['items'] ?? null) && count($invoice->data['items']))
                    @foreach($invoice->data['items'] as $item)
                        <tr>
                            <td>{{ $item['description'] ?? '' }}</td>
                            <td class="text-right">{{ $item['quantity'] ?? '' }}</td>
                            <td class="text-right">{{ number_format($item['unit_price'] ?? 0, 2) }}</td>
                            <td class="text-right">{{ number_format($item['line_total'] ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr><td colspan="4" class="muted">No items</td></tr>
                @endif
            </tbody>
        </table>

        <div class="totals">
            <div class="row"><div>Subtotal</div><div class="text-right">{{ number_format($invoice->subtotal ?? ($invoice->total ?? 0), 2) }}</div></div>
            <div class="row"><div>Tax (VAT)</div><div class="text-right">{{ number_format($invoice->tax ?? 0, 2) }}</div></div>
            <div class="row total"><div>Total</div><div class="text-right">{{ number_format($invoice->total ?? 0, 2) }}</div></div>
        </div>

        <div class="footer">Thank you for your business. Please contact us at {{ $invoice->business->email ?? 'support@example.com' }} for questions.</div>
    </div>
</body>
</html>
