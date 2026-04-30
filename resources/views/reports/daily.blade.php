<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>DOM_REPORT_{{ $generated_at }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .header {
            border-bottom: 4px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .brand {
            font-size: 24px;
            font-weight: 900;
            font-style: italic;
            letter-spacing: -1px;
            text-transform: uppercase;
        }

        .subtitle {
            font-size: 10px;
            color: #dc2626;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .kpi-container {
            width: 100%;
            margin-bottom: 30px;
            border-collapse: separate;
            border-spacing: 10px 0;
        }

        .kpi-box {
            border: 2px solid #000;
            padding: 15px;
            background: #f4f4f4;
            text-align: center;
        }

        .kpi-box.highlight {
            border-color: #dc2626;
            color: #dc2626;
        }

        .kpi-title {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
            color: #000;
        }

        .kpi-value {
            font-size: 18px;
            font-weight: 900;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table.data-table th {
            background: #000;
            color: #fff;
            text-align: left;
            padding: 8px;
            text-transform: uppercase;
            font-size: 10px;
        }

        table.data-table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            font-size: 11px;
        }

        .footer {
            margin-top: 50px;
            font-size: 9px;
            color: #999;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }

        .status-tag {
            background: #dc2626;
            color: #fff;
            padding: 2px 6px;
            font-size: 9px;
            font-weight: bold;
            display: inline-block;
        }
    </style>
</head>

<body>

    <div class="header">
        <table width="100%">
            <tr>
                <td>
                    <div class="brand">DOM_SOCIAL_HUB</div>
                    <div class="subtitle">Central Intelligence Report</div>
                </td>
                <td align="right">
                    <div class="status-tag">S_OS_V.1.0 // ACTIVE</div>
                    <div style="font-size: 10px; margin-top: 5px;">Gen: {{ $generated_at }}</div>
                </td>
            </tr>
        </table>
    </div>

    <h3>// EXECUTIVE_SUMMARY (Last {{ $days }} Days)</h3>
    <table class="kpi-container" style="margin-left: -10px; width: 105%;">
        <tr>
            <td width="33%">
                <div class="kpi-box">
                    <div class="kpi-title">Gross_Revenue</div>
                    <div class="kpi-value">Rp {{ number_format($revenue, 0, ',', '.') }}</div>
                </div>
            </td>
            <td width="34%">
                <div class="kpi-box highlight">
                    <div class="kpi-title" style="color: #dc2626;">Net_Profit</div>
                    <div class="kpi-value">Rp {{ number_format($net_profit, 0, ',', '.') }}</div>
                </div>
            </td>
            <td width="33%">
                <div class="kpi-box">
                    <div class="kpi-title">Margin_//_Trx</div>
                    <div class="kpi-value">{{ $profit_margin }}% // {{ $trx_count }}</div>
                </div>
            </td>
        </tr>
    </table>

    <h3 style="margin-top: 20px;">// TOP_PERFORMING_ITEMS</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th width="10%">RANK</th>
                <th width="60%">PRODUCT_NAME</th>
                <th width="30%" align="right">QTY_SOLD</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($top_items as $index => $item)
                <tr>
                    <td>#{{ $index + 1 }}</td>
                    <td style="font-weight: bold; text-transform: uppercase;">{{ $item->name }}</td>
                    <td align="right">{{ $item->total_qty }} PCS</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3 style="margin-top: 30px; color: #dc2626;">// AI_BUNDLING_SUGGESTION (MARKET BASKET)</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th width="60%">ASSOCIATED_PRODUCTS (BOUGHT TOGETHER)</th>
                <th width="40%" align="right">FREQUENCY</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($market_basket as $pair)
                <tr>
                    <td style="font-weight: bold; text-transform: uppercase;">{{ $pair->product_a }} <span
                            style="color: #dc2626;">+</span> {{ $pair->product_b }}</td>
                    <td align="right">Found in {{ $pair->times_bought_together }} Receipts</td>
                </tr>
            @endforeach
            @if (count($market_basket) == 0)
                <tr>
                    <td colspan="2" style="text-align: center; font-style: italic;">Membutuhkan lebih banyak data
                        transaksi untuk analisa korelasi.</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        <p>This is a system-generated report for DOM Social Hub analytics.</p>
        <p>Confidential // Unauthorized reproduction is prohibited.</p>
    </div>

</body>

</html>
