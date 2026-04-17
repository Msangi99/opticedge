<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Distribution Invoice {{ $invoiceNo }}</title>
    <style>
        @page { size: A4; margin: 8mm; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 10px;
            background: #f3f4f6;
            font-family: Arial, Helvetica, sans-serif;
            color: #111827;
        }
        .sheet {
            width: 100%;
            max-width: 780px;
            margin: 0 auto;
            border: 1px solid #9ca3af;
            background: #ffffff;
            padding: 16px 16px 18px;
            min-height: 281mm;
            display: flex;
            flex-direction: column;
        }
        .orange { color: #f08a00; }
        .top-table,
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }
        .title {
            font-size: 48px;
            font-weight: 800;
            line-height: 1;
            margin: 0;
            letter-spacing: 1px;
        }
        .invoice-number {
            margin-top: 4px;
            font-size: 22px;
            font-weight: 700;
        }
        .logo-box {
            width: 84px;
            height: 84px;
            background: transparent;
            text-align: center;
            vertical-align: middle;
            border-radius: 2px;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .logo-image {
            width: 64px;
            height: 64px;
            object-fit: contain;
            display: block;
        }
        .bar {
            margin-top: 12px;
            background: #f08a00;
            color: #ffffff;
            font-size: 18px;
            font-weight: 700;
            padding: 5px 12px;
            line-height: 1.2;
        }
        .meta-lines {
            margin-top: 10px;
            font-size: 15px;
            line-height: 1.35;
        }
        .meta-lines strong { display: inline-block; min-width: 90px; }
        .items-wrap {
            margin-top: 12px;
            border: 1px solid #6b7280;
            min-height: 96px;
            flex: 1 1 auto;
        }
        .items-table thead th {
            background: #f08a00;
            color: #ffffff;
            font-size: 17px;
            font-weight: 700;
            text-align: left;
            padding: 8px 10px;
        }
        .items-table tbody td {
            font-size: 14px;
            padding: 8px 10px;
            border-top: 1px solid #d1d5db;
            vertical-align: top;
            word-break: break-word;
            overflow-wrap: anywhere;
        }
        .items-table { table-layout: fixed; }
        .item-col { width: 28%; }
        .qty-col { width: 14%; }
        .unit-col { width: 29%; }
        .total-col { width: 29%; }
        .right { text-align: right; }
        .total-row {
            margin-top: 12px;
            font-size: 18px;
            font-weight: 700;
            text-align: right;
            flex-shrink: 0;
        }
        .total-row .label { margin-right: 18px; }
        .from-block {
            margin-top: 10px;
            font-size: 14px;
            line-height: 1.45;
            flex-shrink: 0;
        }
        .from-block strong { display: inline-block; min-width: 95px; }
        .muted-colon {
            display: inline-block;
            min-width: 14px;
            text-align: center;
        }
    </style>
</head>
@php
    $companyName = 'OPTIC EDGE AFRICA';
    $formattedDate = $sale->date ? \Carbon\Carbon::parse($sale->date)->format('d M Y') : now()->format('d M Y');
    $dealerName = $sale->dealer_name ?? $sale->dealer?->name ?? 'N/A';
    $productName = $sale->product
        ? (($sale->product->category?->name ?? 'N/A') . ' - ' . $sale->product->name)
        : 'N/A';
    $qty = (int) ($sale->quantity_sold ?? 0);
    $unitPrice = (float) ($sale->selling_price ?? 0);
    $total = (float) ($sale->total_selling_value ?? ($qty * $unitPrice));
    $appIconUrl = url('/assets/app_icon.png');
    $appIconPath = public_path('assets/app_icon.png');
    $appIconExists = is_file($appIconPath) && is_readable($appIconPath);
    $appIconDataUri = null;
    if ($appIconExists) {
        $bytes = @file_get_contents($appIconPath);
        if ($bytes !== false) {
            $appIconDataUri = 'data:image/png;base64,' . base64_encode($bytes);
        }
    }
    $iconSrc = $appIconExists ? $appIconUrl : $appIconDataUri;
@endphp
<body>
    <div class="sheet">
        <table class="top-table">
            <tr>
                <td>
                    <h1 class="title orange">INVOICE</h1>
                    <div class="invoice-number">Invoice Number: {{ $invoiceNo }}</div>
                </td>
                <td class="right" style="width: 120px;">
                    <div class="logo-box">
                        @if($iconSrc)
                            <img src="{{ $iconSrc }}" alt="App Icon" class="logo-image">
                        @else
                            <div style="width:84px;height:84px;background:#f08a00;"></div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <div class="bar">To:</div>

        <div class="meta-lines">
            <div><strong>Name</strong> <span class="muted-colon">:</span> {{ $dealerName }}</div>
            <div><strong>Date</strong> <span class="muted-colon">:</span> {{ $formattedDate }}</div>
        </div>

        <div class="items-wrap">
            <table class="items-table">
                <thead>
                <tr>
                    <th class="item-col">Item Description</th>
                    <th class="qty-col">Quantity</th>
                    <th class="unit-col">Unit Price (TZS)</th>
                    <th class="total-col">Total (TZS)</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>{{ $productName }}</td>
                    <td>{{ number_format($qty) }}</td>
                    <td>{{ number_format($unitPrice, 2) }}</td>
                    <td>{{ number_format($total, 2) }}</td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="total-row">
            <span class="label">Total Amount</span>
            <span>: {{ number_format($total, 2) }}</span>
        </div>

        <div class="bar">From:</div>
        <div class="from-block">
            <div>{{ $companyName }}</div>
            <div><strong>Address</strong> <span class="muted-colon">:</span> Dar es Salaam, Sinza Makaburini</div>
            <div><strong>Email</strong> <span class="muted-colon">:</span> info@opticedgeafrica.net</div>
            <div><strong>Phone</strong> <span class="muted-colon">:</span> 0677 - 609929</div>
        </div>
    </div>
</body>
</html>
