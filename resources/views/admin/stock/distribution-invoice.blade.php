<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Distribution Invoice {{ $invoiceNo }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 18px;
            background: #f3f4f6;
            font-family: Arial, Helvetica, sans-serif;
            color: #111827;
        }
        .sheet {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #6b7280;
            background: #ffffff;
            padding: 22px 24px 26px;
        }
        .orange { color: #f08a00; }
        .top-table,
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }
        .title {
            font-size: 62px;
            font-weight: 800;
            line-height: 1;
            margin: 0;
            letter-spacing: 1px;
        }
        .invoice-number {
            margin-top: 4px;
            font-size: 34px;
            font-weight: 700;
        }
        .logo-box {
            width: 96px;
            height: 82px;
            background: #f08a00;
            text-align: center;
            vertical-align: middle;
            border-radius: 2px;
        }
        .logo-mark {
            display: inline-block;
            margin-top: 24px;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: 7px solid #111827;
            position: relative;
        }
        .logo-mark::after {
            content: "";
            position: absolute;
            right: -18px;
            top: 8px;
            width: 18px;
            height: 5px;
            background: #ffffff;
            box-shadow: 0 8px 0 #ffffff;
        }
        .bar {
            margin-top: 20px;
            background: #f08a00;
            color: #ffffff;
            font-size: 26px;
            font-weight: 700;
            padding: 6px 14px;
        }
        .meta-lines {
            margin-top: 18px;
            font-size: 23px;
            line-height: 1.8;
        }
        .meta-lines strong { display: inline-block; min-width: 90px; }
        .items-wrap {
            margin-top: 24px;
            border: 2px solid #6b7280;
            min-height: 255px;
        }
        .items-table thead th {
            background: #f08a00;
            color: #ffffff;
            font-size: 22px;
            font-weight: 700;
            text-align: left;
            padding: 10px 12px;
        }
        .items-table tbody td {
            font-size: 21px;
            padding: 12px;
            border-top: 1px solid #d1d5db;
            vertical-align: top;
        }
        .right { text-align: right; }
        .total-row {
            margin-top: 30px;
            font-size: 25px;
            font-weight: 700;
            text-align: right;
        }
        .total-row .label { margin-right: 26px; }
        .from-block {
            margin-top: 26px;
            font-size: 21px;
            line-height: 1.7;
        }
        .from-block strong { display: inline-block; min-width: 95px; }
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
                        <span class="logo-mark"></span>
                    </div>
                </td>
            </tr>
        </table>

        <div class="bar">To:</div>

        <div class="meta-lines">
            <div><strong>Name</strong>: {{ $dealerName }}</div>
            <div><strong>Date</strong>: {{ $formattedDate }}</div>
        </div>

        <div class="items-wrap">
            <table class="items-table">
                <thead>
                <tr>
                    <th>Item Description</th>
                    <th style="width: 130px;">Quantity</th>
                    <th style="width: 200px;">Unit Price (TZS)</th>
                    <th style="width: 180px;">Total (TZS)</th>
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
            <div><strong>Address</strong>: Dar es Salaam, Sinza Makaburini</div>
            <div><strong>Email</strong>: info@opticedgeafrica.net</div>
            <div><strong>Phone</strong>: 0677 - 609929</div>
        </div>
    </div>
</body>
</html>
