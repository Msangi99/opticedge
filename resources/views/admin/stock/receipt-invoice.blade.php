<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'RECEIPT' }} {{ $invoiceNo ?? '' }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 22px;
            font-family: Arial, Helvetica, sans-serif;
            background: #f3f4f6;
            color: #1f2937;
        }
        .paper {
            max-width: 760px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 24px;
            box-shadow: 0 6px 24px rgba(15, 23, 42, 0.08);
        }
        .center { text-align: center; }
        .brand { font-size: 42px; font-weight: 800; color: #1d4f9d; margin: 0; }
        .meta { color: #6b7280; font-size: 18px; margin-top: 4px; }
        .title { margin: 14px 0 6px; font-size: 52px; line-height: 1; color: #1d4f9d; font-weight: 800; }
        .order { font-size: 26px; color: #4b5563; }
        .sep { margin: 14px auto; width: 48%; border-top: 2px dashed #d1d5db; }
        .section-title { font-size: 28px; color: #1d4f9d; margin: 18px 0 10px; font-weight: 800; text-transform: uppercase; }
        .box {
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 12px 14px;
            background: #f9fafb;
        }
        .box p { margin: 4px 0; font-size: 22px; }
        table { width: 100%; border-collapse: collapse; border: 1px solid #d1d5db; border-radius: 10px; overflow: hidden; }
        th, td { padding: 10px 12px; border-bottom: 1px solid #e5e7eb; font-size: 22px; vertical-align: top; }
        th { text-align: left; font-weight: 700; background: #f3f4f6; }
        .right { text-align: right; }
        .mono { font-family: "Courier New", monospace; font-weight: 700; color: #1d4f9d; letter-spacing: 1px; }
        .summary {
            margin-top: 14px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            background: #f9fafb;
            padding: 14px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            font-size: 28px;
            line-height: 1.5;
        }
        .summary-row.total { font-weight: 800; color: #1d4f9d; border-top: 2px dashed #d1d5db; margin-top: 8px; padding-top: 8px; }
        .status {
            margin-top: 14px;
            border: 1px solid #a7d6af;
            background: #eef9f0;
            border-radius: 10px;
            padding: 12px;
        }
        .status .label { text-align: center; color: #246a35; font-size: 24px; font-weight: 800; margin-bottom: 8px; text-transform: uppercase; }
        .foot {
            margin-top: 16px;
            display: inline-block;
            padding: 12px 16px;
            background: #eaf2fb;
            border-radius: 10px;
            color: #1d4f9d;
        }
        .foot .big { font-size: 30px; font-weight: 800; margin: 0 0 4px; }
        .foot .small { margin: 0; color: #6b7280; font-size: 16px; }
    </style>
</head>
@php
    $isCredit = isset($credit);
    $row = $isCredit ? $credit : $sale;
    $customer = $isCredit
        ? ($credit->customer_name ?? 'N/A')
        : ($sale->customer_name ?? 'N/A');
    $phone = $isCredit
        ? ($credit->customer_phone ?? null)
        : null;
    $productName = $isCredit
        ? ($credit->product ? (($credit->product->category?->name ?? '—') . ' – ' . $credit->product->name) : 'N/A')
        : ($sale->product ? (($sale->product->category?->name ?? '—') . ' – ' . $sale->product->name) : 'N/A');
    $qty = (int) ($row->quantity_sold ?? 1);
    $amount = $isCredit
        ? (float) ($credit->total_amount ?? 0)
        : (float) ($sale->total_selling_value ?? 0);
    $paid = $isCredit
        ? (float) ($credit->paid_amount ?? 0)
        : max(0, $amount - (float) ($sale->balance ?? 0));
    $remaining = max(0, $amount - $paid);
    $serial = $isCredit
        ? ($credit->productListItem?->imei_number ?? null)
        : null;
@endphp
<body>
<div class="paper">
    <div class="center">
        <h1 class="brand">OPTIC EDGE AFRICA</h1>
        <div class="meta">TIN: 1810810231</div>
        <div class="title">{{ $title ?? 'RECEIPT' }}</div>
        <div class="order">Order #{{ $invoiceNo ?? '—' }}</div>
        <div class="sep"></div>
    </div>

    <div class="section-title">Customer details</div>
    <div class="box">
        <p><strong>Name:</strong> {{ $customer }}</p>
        @if(!empty($phone))
            <p><strong>Phone:</strong> {{ $phone }}</p>
        @endif
    </div>

    <div class="section-title">Product details</div>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th style="width:80px;">Qty</th>
                <th class="right" style="width:180px;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $productName }}</td>
                <td>{{ $qty }}</td>
                <td class="right">{{ number_format($amount, 2) }} TZS</td>
            </tr>
            @if(!empty($serial))
            <tr>
                <td colspan="3" class="mono">SERIAL NUMBER: {{ $serial }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="summary">
        <div class="summary-row">
            <span>Product Amount:</span>
            <span>{{ number_format($amount, 2) }} TZS</span>
        </div>
        <div class="summary-row">
            <span>+VAT:</span>
            <span>0 TZS</span>
        </div>
        <div class="summary-row">
            <span>Sub Total:</span>
            <span>{{ number_format($amount, 2) }} TZS</span>
        </div>
        <div class="summary-row total">
            <span>AMOUNT DUE:</span>
            <span>{{ number_format($remaining, 2) }} TZS</span>
        </div>
    </div>

    <div class="status">
        <div class="label">Payment Status</div>
        <div class="summary-row">
            <span>Paid:</span>
            <span>{{ number_format($paid, 2) }} TZS</span>
        </div>
        <div class="summary-row">
            <span>Remaining:</span>
            <span>{{ number_format($remaining, 2) }} TZS</span>
        </div>
    </div>

    <div class="foot">
        <p class="big">Thank you for your business!</p>
        <p class="small">Generated on {{ ($invoiceDate ?? now())->format('Y-m-d') }}</p>
    </div>
</div>
</body>
</html>
