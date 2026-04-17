<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Distribution Invoice {{ $invoiceNo }}</title>
    <style>
        @page { size: A4; margin: 10mm; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 14px;
            background: #d1d5db;
            font-family: Arial, Helvetica, sans-serif;
            color: #111827;
            font-size: 14px;
        }
        .sheet {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #9ca3af;
            background: #f9fafb;
            padding: 22px 24px 28px;
        }
        .doc-title {
            margin: 0 0 6px;
            text-align: center;
            font-size: 36px;
            font-weight: 700;
            letter-spacing: 0.06em;
            color: #1d4e9e;
            line-height: 1.1;
        }
        .invoice-number {
            text-align: center;
            font-size: 13px;
            font-weight: 400;
            color: #111827;
            margin-bottom: 18px;
        }
        .section-bar {
            background: #1d4e9e;
            color: #ffffff;
            font-size: 14px;
            font-weight: 700;
            padding: 7px 12px;
            line-height: 1.2;
            margin-top: 14px;
        }
        .section-bar:first-of-type {
            margin-top: 0;
        }
        .meta-lines {
            margin-top: 10px;
            font-size: 14px;
            line-height: 1.55;
        }
        .meta-lines strong {
            font-weight: 700;
        }
        .items-wrap {
            margin-top: 14px;
            border: 1px solid #d1d5db;
            background: #ffffff;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .items-table thead th {
            background: #1d4e9e;
            color: #ffffff;
            font-size: 13px;
            font-weight: 700;
            text-align: left;
            padding: 8px 10px;
            border: 1px solid #1d4e9e;
        }
        .items-table thead th.num {
            text-align: right;
        }
        .items-table tbody td {
            font-size: 13px;
            padding: 10px 10px;
            border: 1px solid #e5e7eb;
            vertical-align: top;
            word-break: break-word;
        }
        .items-table tbody td.num {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }
        .items-table tbody tr.spacer td {
            border-top: 1px solid #e5e7eb;
            height: 200px;
            vertical-align: top;
            background: #ffffff;
        }
        .item-col { width: 40%; }
        .qty-col { width: 12%; }
        .unit-col { width: 24%; }
        .total-col { width: 24%; }
        .total-row {
            margin-top: 14px;
            font-size: 14px;
            text-align: right;
            padding-right: 2px;
        }
        .total-row .label {
            font-weight: 700;
        }
        .total-row .value {
            font-weight: 400;
        }
        .from-block {
            margin-top: 10px;
            font-size: 14px;
            line-height: 1.55;
        }
        .from-name {
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 6px;
            letter-spacing: 0.02em;
        }
        .from-block strong {
            font-weight: 700;
        }
    </style>
</head>
@php
    $companyName = 'OPTIC EDGE AFRICA';
    $formattedDate = $sale->date ? \Carbon\Carbon::parse($sale->date)->format('d M Y') : '';
    $dealerName = $sale->dealer_name ?? $sale->dealer?->name ?? 'N/A';
    $productName = $sale->product
        ? (($sale->product->category?->name ?? 'N/A') . ' - ' . $sale->product->name)
        : 'N/A';
    $qty = (int) ($sale->quantity_sold ?? 0);
    $unitPrice = (float) ($sale->selling_price ?? 0);
    $total = (float) ($sale->total_selling_value ?? ($qty * $unitPrice));
@endphp
<body>
    <div class="sheet">
        <h1 class="doc-title">INVOICE</h1>
        <div class="invoice-number">Invoice Number: {{ $invoiceNo }}</div>

        <div class="section-bar">To:</div>
        <div class="meta-lines">
            <div><strong>Name</strong> : {{ $dealerName }}</div>
            <div><strong>Date</strong> : {{ $formattedDate }}</div>
        </div>

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
        </div>

        <div class="total-row">
            <span class="label">Total Amount</span><span class="value"> : {{ number_format($total, 2) }}</span>
        </div>

        <div class="section-bar">From:</div>
        <div class="from-block">
            <div class="from-name">{{ $companyName }}</div>
            <div><strong>Address</strong> : Dar es Salaam, Sinza Makaburini</div>
            <div><strong>Email</strong> : info@opticedgeafrica.net</div>
            <div><strong>Phone</strong> : 0677 - 609929</div>
            <div><strong>TIN</strong> : 148-908-613</div>
        </div>
    </div>
</body>
</html>
