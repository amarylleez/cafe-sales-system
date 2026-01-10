<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Branch Performance Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }
        .page-break {
            page-break-after: always;
        }
        .header {
            background: #423A8E;
            color: white;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 20px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 11px;
            opacity: 0.9;
        }
        .branch-badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 5px 15px;
            border-radius: 15px;
            margin-top: 10px;
            font-size: 12px;
        }
        .summary-cards {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .summary-card {
            display: table-cell;
            width: 20%;
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
            background: #f8f9fa;
        }
        .summary-card .label {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
        }
        .summary-card .value {
            font-size: 14px;
            font-weight: bold;
            margin-top: 5px;
        }
        .summary-card .value.primary { color: #0d6efd; }
        .summary-card .value.success { color: #198754; }
        .summary-card .value.danger { color: #dc3545; }
        .summary-card .value.warning { color: #ffc107; }
        .summary-card .value.info { color: #423A8E; }
        .net-profit-box {
            background: #f8f9fa;
            border: 2px solid {{ $netProfit >= 0 ? '#198754' : '#dc3545' }};
            padding: 15px;
            text-align: center;
            margin-bottom: 20px;
        }
        .net-profit-box h3 {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        .net-profit-box .amount {
            font-size: 24px;
            font-weight: bold;
            color: {{ $netProfit >= 0 ? '#198754' : '#dc3545' }};
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            background: #423A8E;
            color: white;
            padding: 8px 15px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .section-title.warning {
            background: #ffc107;
            color: #333;
        }
        .section-title.danger {
            background: #dc3545;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
            font-size: 10px;
        }
        th {
            background: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        tr:nth-child(even) {
            background: #fafafa;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-success { color: #198754; }
        .text-danger { color: #dc3545; }
        .text-warning { color: #ffc107; }
        .text-primary { color: #0d6efd; }
        .benchmark-box {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .benchmark-card {
            display: table-cell;
            width: 33.33%;
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
            background: linear-gradient(135deg, #423A8E 0%, #00CCCD 100%);
            color: white;
        }
        .benchmark-card .label {
            font-size: 9px;
            opacity: 0.8;
            text-transform: uppercase;
        }
        .benchmark-card .value {
            font-size: 16px;
            font-weight: bold;
            margin: 5px 0;
        }
        .benchmark-card .progress-text {
            font-size: 10px;
            opacity: 0.9;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #999;
            padding: 10px;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Branch Performance Report</h1>
        <p>{{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</p>
        <div class="branch-badge">{{ $branch->name }}</div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card">
            <div class="label">Total Revenue</div>
            <div class="value primary">RM {{ number_format($totalRevenue, 2) }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Gross Profit</div>
            <div class="value success">RM {{ number_format($grossProfit, 2) }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Stock Loss</div>
            <div class="value danger">RM {{ number_format($stockLoss, 2) }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Profit Margin</div>
            <div class="value info">{{ number_format($profitMargin, 1) }}%</div>
        </div>
    </div>

    <!-- Stock Loss Breakdown -->
    @if($expiredLoss > 0 || $rejectedSalesLoss > 0)
    <div class="section">
        <table>
            <tr>
                <td style="width: 50%; text-align: center; padding: 10px;">
                    <strong>Expired Loss:</strong> 
                    <span class="text-danger">RM {{ number_format($expiredLoss, 2) }}</span>
                </td>
                <td style="width: 50%; text-align: center; padding: 10px;">
                    <strong>Rejected Sales Loss:</strong> 
                    <span class="text-danger">RM {{ number_format($rejectedSalesLoss, 2) }}</span>
                </td>
            </tr>
        </table>
    </div>
    @endif

    <!-- Net Profit Box -->
    <div class="net-profit-box">
        <h3>Net Profit (After Stock Loss)</h3>
        <div class="amount">RM {{ number_format($netProfit, 2) }}</div>
    </div>

    <!-- Staff Performance -->
    @if(isset($staffKpis) && count($staffKpis) > 0)
    <div class="section">
        <div class="section-title">Staff Performance (This Month)</div>
        <table>
            <thead>
                <tr>
                    <th>Staff Name</th>
                    <th class="text-right">Sales</th>
                    <th class="text-right">Transactions</th>
                    <th class="text-right">Target</th>
                    <th class="text-right">Progress</th>
                </tr>
            </thead>
            <tbody>
                @foreach($staffKpis as $staffData)
                <tr>
                    <td>{{ $staffData['staff']->name }}</td>
                    <td class="text-right">RM {{ number_format($staffData['sales'], 2) }}</td>
                    <td class="text-right">{{ $staffData['transactions'] }}</td>
                    <td class="text-right">RM {{ number_format($staffData['target'], 2) }}</td>
                    <td class="text-right {{ $staffData['progress'] >= 100 ? 'text-success' : ($staffData['progress'] >= 50 ? 'text-warning' : 'text-danger') }}">
                        {{ number_format($staffData['progress'], 1) }}%
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Top Profitable Products -->
    @if(isset($topProfitableProducts) && count($topProfitableProducts) > 0)
    <div class="section">
        <div class="section-title">Top Profitable Products</div>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th class="text-right">Qty Sold</th>
                    <th class="text-right">Revenue</th>
                    <th class="text-right">Cost</th>
                    <th class="text-right">Profit</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topProfitableProducts as $productData)
                <tr>
                    <td>{{ $productData['product']->name ?? 'Unknown' }}</td>
                    <td class="text-right">{{ $productData['quantity_sold'] }}</td>
                    <td class="text-right">RM {{ number_format($productData['revenue'], 2) }}</td>
                    <td class="text-right">RM {{ number_format($productData['cost'], 2) }}</td>
                    <td class="text-right text-success">RM {{ number_format($productData['profit'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Expired Products -->
    @if(isset($expiredProducts) && $expiredProducts->count() > 0)
    <div class="section">
        <div class="section-title warning">Expired Products ({{ $expiredProducts->count() }} items)</div>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th class="text-right">Quantity</th>
                    <th>Expired Date</th>
                    <th class="text-right">Loss Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expiredProducts->take(15) as $expired)
                <tr>
                    <td>{{ $expired->product->name ?? 'Unknown' }}</td>
                    <td class="text-right">{{ $expired->quantity }} units</td>
                    <td>{{ $expired->expired_at ? \Carbon\Carbon::parse($expired->expired_at)->format('M d, Y') : 'N/A' }}</td>
                    <td class="text-right text-danger">RM {{ number_format($expired->total_loss, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if($expiredProducts->count() > 15)
        <p style="font-size: 9px; color: #999; text-align: center;">
            Showing 15 of {{ $expiredProducts->count() }} expired items
        </p>
        @endif
    </div>
    @endif

    <!-- Rejected Transactions -->
    @if(isset($rejectedSales) && $rejectedSales->count() > 0)
    <div class="section">
        <div class="section-title danger">Rejected Transactions ({{ $rejectedSales->count() }} total)</div>
        <table>
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Staff</th>
                    <th>Date</th>
                    <th class="text-right">Items</th>
                    <th class="text-right">Loss Amount</th>
                    <th>Reason</th>
                </tr>
            </thead>
            <tbody>
                @php $totalRejectedLoss = 0; @endphp
                @foreach($rejectedSales->take(15) as $sale)
                @php 
                    $saleLoss = $sale->items->sum(function($item) { 
                        return $item->quantity * ($item->product->cost_price ?? 0); 
                    });
                    $totalRejectedLoss += $saleLoss;
                    // Extract reason from notes field
                    $reason = '-';
                    if ($sale->notes && preg_match('/Reason:\s*(.+?)(?:\n|$)/i', $sale->notes, $matches)) {
                        $reason = trim($matches[1]);
                    }
                @endphp
                <tr>
                    <td style="font-size: 9px;">{{ $sale->transaction_id }}</td>
                    <td>{{ $sale->staff->name ?? 'N/A' }}</td>
                    <td>{{ $sale->sale_date->format('M d') }}</td>
                    <td class="text-right">{{ $sale->items->sum('quantity') }}</td>
                    <td class="text-right text-danger">RM {{ number_format($saleLoss, 2) }}</td>
                    <td style="font-size: 9px;">{{ Str::limit($reason, 20) }}</td>
                </tr>
                @endforeach
                <tr style="background: #f0f0f0; font-weight: bold;">
                    <td colspan="4"><strong>Total</strong></td>
                    <td class="text-right text-danger"><strong>RM {{ number_format($totalRejectedLoss, 2) }}</strong></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        @if($rejectedSales->count() > 15)
        <p style="font-size: 9px; color: #999; text-align: center;">
            Showing 15 of {{ $rejectedSales->count() }} rejected transactions
        </p>
        @endif
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        Cafe Sales System - Branch Performance Report | Generated on {{ now()->format('M d, Y H:i:s') }}
    </div>
</body>
</html>
