<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Distribution Invoice {{ $invoiceNo }}</title>
    <style>
        @page { size: A4; margin: 12mm; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 16px;
            background: #e8e8e8;
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            color: #000000;
            font-size: 13px;
        }
        .sheet {
            width: 100%;
            max-width: 820px;
            margin: 0 auto;
            background: #ffffff;
            padding: 32px 36px 28px;
        }
        .top-header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 28px;
        }
        .top-header td {
            vertical-align: top;
            padding: 0;
        }
        .logo-box {
            width: 88px;
            height: 88px;
            background: #E68A19;
            text-align: center;
            vertical-align: middle;
        }
        .logo-box img {
            width: 72px;
            height: 72px;
            object-fit: contain;
            display: block;
            margin: 8px auto 0;
        }
        .title-block {
            text-align: right;
            padding-top: 4px;
        }
        .title-block h1 {
            margin: 0;
            font-size: 34px;
            font-weight: 700;
            color: #214F91;
            letter-spacing: 0.02em;
            line-height: 1;
        }
        .title-block .invoice-number {
            margin: 10px 0 0 0;
            font-size: 13px;
            color: #000000;
            font-weight: 400;
        }
        .info-row {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 28px;
        }
        .info-row td {
            width: 50%;
            vertical-align: top;
            padding: 0 12px 0 0;
            font-size: 13px;
            line-height: 1.65;
        }
        .info-row td:last-child {
            padding-right: 0;
            padding-left: 12px;
        }
        .company-name {
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 6px;
        }
        .info-row strong {
            font-weight: 700;
        }
        .bill-heading {
            font-weight: 700;
            margin-bottom: 8px;
        }
        .items-wrap {
            border: 1px solid #c8c8c8;
            margin: 0 0 20px 0;
            background: #ffffff;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .items-table thead th {
            background: #214F91;
            color: #ffffff;
            font-size: 12px;
            font-weight: 700;
            text-align: left;
            padding: 11px 14px;
            border: none;
        }
        .items-table thead th.num {
            text-align: right;
        }
        .items-table tbody td {
            font-size: 13px;
            padding: 14px 14px;
            border: 1px solid #c8c8c8;
            border-top: none;
            vertical-align: top;
            word-break: break-word;
        }
        .items-table tbody td.num {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }
        .items-table tbody tr.spacer td {
            height: 220px;
            padding: 0 14px;
            border: 1px solid #c8c8c8;
            border-top: none;
            vertical-align: top;
        }
        .item-col { width: 42%; }
        .qty-col { width: 14%; }
        .unit-col { width: 22%; }
        .total-col { width: 22%; }
        .table-footer-bar {
            height: 14px;
            background: #214F91;
            width: 100%;
        }
        .total-section {
            text-align: right;
            margin: 8px 0 0 0;
            padding: 0 4px 0 0;
            font-size: 14px;
            color: #000000;
        }
        .total-section strong {
            font-weight: 700;
        }
        .thank-you {
            background: #214F91;
            color: #ffffff;
            text-align: center;
            padding: 14px 12px;
            margin-top: 28px;
            font-weight: 700;
            font-size: 14px;
        }
    </style>
</head>
@php
    $companyName = 'OPTIC EDGE AFRICA';
    $formattedDate = $sale->date ? \Carbon\Carbon::parse($sale->date)->format('d M Y') : '';
    $dealerName = $sale->dealer_name
        ?? $sale->dealer?->name
        ?? $sale->dealer?->business_name
        ?? 'N/A';
    $productName = $sale->product
        ? (($sale->product->category?->name ?? 'N/A') . ' - ' . $sale->product->name)
        : 'N/A';
    $qty = (int) ($sale->quantity_sold ?? 0);
    $unitPrice = (float) ($sale->selling_price ?? 0);
    $total = (float) ($sale->total_selling_value ?? ($qty * $unitPrice));
    $iconPath = public_path('assets/app_icon.png');
    $iconDataUri = '';
    if (is_readable($iconPath)) {
        $iconDataUri = 'data:image/png;base64,' . base64_encode((string) file_get_contents($iconPath));
    }
@endphp
<body>
    <div class="sheet">
        <table class="top-header" role="presentation">
            <tr>
                <td class="logo-box">
                    @if ($iconDataUri !== '')
                        <img src="{{ $iconDataUri }}" alt="">
                    @endif
                </td>
                <td class="title-block">
                    <h1>INVOICE</h1>
                    <p class="invoice-number">Invoice Number: {{ $invoiceNo }}</p>
                </td>
            </tr>
        </table>

        <table class="info-row" role="presentation">
            <tr>
                <td>
                    <div class="company-name">{{ $companyName }}</div>
                    <div><strong>Address:</strong> Dar es Salaam, Sinza Makaburini</div>
                    <div><strong>Email:</strong> info@opticedgeafrica.net</div>
                    <div><strong>Phone:</strong> 0677 - 609929</div>
                    <div><strong>TIN:</strong> 148-908-613</div>
                </td>
                <td>
                    <div class="bill-heading">Bill to</div>
                    <div><strong>Name:</strong> {{ $dealerName }}</div>
                    <div><strong>Date:</strong> {{ $formattedDate }}</div>
                </td>
            </tr>
        </table>

        <div class="items-wrap">
            <table class="items-table">
                <thead>
                    <tr>
                        <th class="item-col">Item Description</th>
                        <th class="qty-col num">Quantity</th>
                        <th class="unit-col num">Unit Price (TZS)</th>
                        <th class="total-col num">Total (TZS)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $productName }}</td>
                        <td class="num">{{ number_format($qty) }}</td>
                        <td class="num">{{ number_format($unitPrice, 2) }}</td>
                        <td class="num">{{ number_format($total, 2) }}</td>
                    </tr>
                    <tr class="spacer">
                        <td colspan="4">&nbsp;</td>
                    </tr>
                </tbody>
            </table>
            <div class="table-footer-bar"></div>
        </div>

        <div class="total-section">
            <strong>Total Amount</strong> : {{ number_format($total, 2) }}
        </div>

        <div class="thank-you">Thank you!</div>
    </div>
</body>
</html>
