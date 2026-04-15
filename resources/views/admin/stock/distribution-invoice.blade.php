<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Delivery Note {{ $invoiceNo }}</title>
    <style>
        :root {
            --ink: #0b4ea2;
            --ink-dark: #083a78;
            --line: #2a66b2;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 20px;
            background: #f5f7fb;
            font-family: "Arial", "Helvetica", sans-serif;
            color: #17345c;
        }

        .sheet {
            max-width: 760px;
            margin: 0 auto;
            background: #fff;
            border: 3px solid var(--line);
            border-radius: 6px;
            box-shadow: 0 8px 24px rgba(15, 53, 107, 0.08);
        }

        .head {
            border-bottom: 3px solid var(--line);
            text-align: center;
            padding: 14px 18px 8px;
        }

        .head h1 {
            margin: 0;
            font-size: 40px;
            letter-spacing: 1.2px;
            line-height: 1;
            color: var(--ink);
            font-weight: 800;
            text-transform: uppercase;
        }

        .head .meta {
            margin-top: 4px;
            color: var(--ink);
            font-size: 28px;
            font-weight: 700;
            line-height: 1.15;
            text-transform: uppercase;
        }

        .title {
            text-align: center;
            border-top: 2px solid var(--line);
            border-bottom: 2px solid var(--line);
            color: var(--ink);
            font-size: 38px;
            font-weight: 800;
            letter-spacing: 1px;
            margin: 8px 14px 0;
            padding: 8px 0 6px;
            text-transform: uppercase;
        }

        .content {
            padding: 12px 14px 16px;
        }

        .top-grid {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
            margin-bottom: 10px;
        }

        .to-box {
            border: 2px solid var(--line);
            border-radius: 10px;
            padding: 10px 12px;
            min-height: 95px;
        }

        .to-box .label,
        .num-box .label {
            color: var(--ink);
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .to-box .value {
            color: #13355f;
            font-size: 28px;
            line-height: 1.2;
            font-weight: 700;
            text-transform: uppercase;
        }

        .num-box {
            border: 2px solid var(--line);
            border-radius: 10px;
            padding: 10px 12px;
            min-width: 220px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .num-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            color: #12345e;
            font-size: 26px;
            font-weight: 700;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        th, td {
            border: 2px solid var(--line);
            padding: 8px 10px;
            vertical-align: top;
        }

        th {
            text-align: left;
            color: var(--ink);
            font-size: 24px;
            font-weight: 800;
        }

        td {
            color: #12345e;
            font-size: 24px;
            line-height: 1.25;
            height: 44px;
        }

        .right { text-align: right; }

        .summary {
            margin-top: 10px;
            border: 2px solid var(--line);
            border-radius: 10px;
            padding: 8px 10px;
            color: #143760;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            font-size: 24px;
            font-weight: 700;
            line-height: 1.4;
        }

        .summary-row + .summary-row {
            border-top: 1px dashed #8caed9;
            margin-top: 6px;
            padding-top: 6px;
        }

        .foot {
            margin-top: 16px;
            border-top: 2px solid var(--line);
            padding-top: 12px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            color: #1a3f6f;
            font-size: 22px;
            font-weight: 700;
        }

        .line {
            border-bottom: 2px solid var(--line);
            display: inline-block;
            min-width: 180px;
            height: 30px;
            vertical-align: bottom;
        }

        @media print {
            body { padding: 0; background: #fff; }
            .sheet { box-shadow: none; border-width: 2px; }
        }
    </style>
</head>
@php
    $companyName = 'OPTIC EDGE AFRICA';
    $companyAddr = 'P.O. Box 41245, Dar es Salaam';
    $companyPhone = 'Mob: 0677 - 609929';
    $companyLocation = 'Location: Sinza Makaburini';
    $formattedDate = $sale->date ? \Carbon\Carbon::parse($sale->date)->format('d M Y') : now()->format('d M Y');
    $dealerName = $sale->dealer_name ?? $sale->dealer?->name ?? 'N/A';
    $productName = $sale->product?->name ?? 'N/A';
    $qty = (int) ($sale->quantity_sold ?? 0);
    $unitPrice = (float) ($sale->selling_price ?? 0);
    $total = (float) ($sale->total_selling_value ?? ($qty * $unitPrice));
    $alreadyPaid = (float) ($sale->paid_amount ?? 0);
    $remaining = max(0, $total - $alreadyPaid);
@endphp
<body>
    <div class="sheet">
        <div class="head">
            <h1>{{ $companyName }}</h1>
            <div class="meta">{{ $companyAddr }}</div>
            <div class="meta">{{ $companyPhone }}</div>
            <div class="meta">{{ $companyLocation }}</div>
        </div>

        <div class="title">Delivery Note</div>

        <div class="content">
            <div class="top-grid">
                <div class="to-box">
                    <div class="label">M/s</div>
                    <div class="value">{{ $dealerName }}</div>
                </div>
                <div class="num-box">
                    <div class="num-row">
                        <span>No.</span>
                        <span>{{ $invoiceNo }}</span>
                    </div>
                    <div class="num-row">
                        <span>Date:</span>
                        <span>{{ $formattedDate }}</span>
                    </div>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 90px;">Qty</th>
                        <th>Particulars</th>
                        <th style="width: 170px;" class="right">Amount (TZS)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ number_format($qty) }}</td>
                        <td>{{ $productName }} @ {{ number_format($unitPrice, 2) }}</td>
                        <td class="right">{{ number_format($total, 2) }}</td>
                    </tr>
                    <tr><td>&nbsp;</td><td></td><td></td></tr>
                    <tr><td>&nbsp;</td><td></td><td></td></tr>
                    <tr><td>&nbsp;</td><td></td><td></td></tr>
                </tbody>
            </table>

            <div class="summary">
                <div class="summary-row">
                    <span>Total value</span>
                    <span>{{ number_format($total, 2) }} TZS</span>
                </div>
                <div class="summary-row">
                    <span>Already paid</span>
                    <span>{{ number_format($alreadyPaid, 2) }} TZS</span>
                </div>
                <div class="summary-row">
                    <span>Balance due</span>
                    <span>{{ number_format($remaining, 2) }} TZS</span>
                </div>
            </div>

            <div class="foot">
                <div>Name: <span class="line"></span></div>
                <div>Signature: <span class="line"></span></div>
            </div>
        </div>
    </div>
</body>
</html>
