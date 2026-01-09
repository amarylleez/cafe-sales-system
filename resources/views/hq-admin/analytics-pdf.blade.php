<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Analytics Report</title>
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
            background: linear-gradient(135deg, #423A8E 0%, #00CCCD 100%);
            color: white;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 22px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 12px;
            opacity: 0.9;
        }
        .summary-cards {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .summary-card {
            display: table-cell;
            width: 25%;
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
            background: #f8f9fa;
        }
        .summary-card .label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
        }
        .summary-card .value {
            font-size: 16px;
            font-weight: bold;
            margin-top: 5px;
        }
        .summary-card .value.primary { color: #0d6efd; }
        .summary-card .value.success { color: #198754; }
        .summary-card .value.danger { color: #dc3545; }
        .summary-card .value.info { color: #0dcaf0; }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            background: #423A8E;
            color: white;
            padding: 8px 15px;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
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
        .chart-container {
            text-align: center;
            margin: 15px 0;
        }
        .chart-container img {
            max-width: 100%;
            height: auto;
        }
        .loss-breakdown {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .loss-item {
            display: table-cell;
            width: 50%;
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }
        .loss-item.expired {
            background: #fff3cd;
        }
        .loss-item.rejected {
            background: #f8d7da;
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
            border-top: 1px solid #ddd;
        }
        .two-column {
            display: table;
            width: 100%;
        }
        .two-column .column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 0 10px;
        }
        .two-column .column:first-child {
            padding-left: 0;
        }
        .two-column .column:last-child {
            padding-right: 0;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Branch Performance Analytics Report</h1>
        <p>Period: {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</p>
        <p>Generated: {{ now()->format('M d, Y H:i') }}</p>
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

    <!-- Loss Breakdown -->
    <div class="section">
        <div class="section-title">Stock Loss Breakdown</div>
        <div class="loss-breakdown">
            <div class="loss-item expired">
                <div class="label">Expired Stock</div>
                <div class="value text-warning" style="font-size: 14px; font-weight: bold;">RM {{ number_format($expiredLoss, 2) }}</div>
            </div>
            <div class="loss-item rejected">
                <div class="label">Rejected Sales</div>
                <div class="value text-danger" style="font-size: 14px; font-weight: bold;">RM {{ number_format($rejectedSalesLoss, 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Monthly Sales Chart -->
    @if($monthlySalesChartUrl)
    <div class="section">
        <div class="section-title">Monthly Sales Comparison (Last 6 Months)</div>
        <div class="chart-container">
            <img src="{{ $monthlySalesChartUrl }}" alt="Monthly Sales Chart" style="max-height: 200px;">
        </div>
    </div>
    @endif

    <!-- Loss Breakdown Pie Chart -->
    @if($lossBreakdownChartUrl)
    <div class="section">
        <div class="section-title">Loss Distribution</div>
        <div class="chart-container">
            <img src="{{ $lossBreakdownChartUrl }}" alt="Loss Breakdown Chart" style="max-height: 180px;">
        </div>
    </div>
    @endif

    <div class="page-break"></div>

    <!-- Page 2 Header -->
    <div class="header" style="margin-top: 0;">
        <h1>Detailed Analytics Data</h1>
        <p>Period: {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</p>
    </div>

    <!-- Top Profitable Products -->
    <div class="section">
        <div class="section-title">Top 5 Profitable Products</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 35%;">Product</th>
                    <th class="text-right" style="width: 15%;">Qty Sold</th>
                    <th class="text-right" style="width: 15%;">Revenue</th>
                    <th class="text-right" style="width: 15%;">Cost</th>
                    <th class="text-right" style="width: 15%;">Profit</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topProfitableProducts as $index => $product)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $product['name'] }}</td>
                    <td class="text-right">{{ $product['quantity'] }}</td>
                    <td class="text-right">RM {{ number_format($product['revenue'], 2) }}</td>
                    <td class="text-right">RM {{ number_format($product['cost'], 2) }}</td>
                    <td class="text-right text-success">RM {{ number_format($product['profit'], 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">No data available</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Stock Loss by Category -->
    @if($stockLossByCategory->count() > 0)
    <div class="section">
        <div class="section-title">Stock Loss by Category</div>
        @if($stockLossByCategoryChartUrl)
        <div class="chart-container">
            <img src="{{ $stockLossByCategoryChartUrl }}" alt="Stock Loss by Category" style="max-height: 180px;">
        </div>
        @endif
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th class="text-right">Expired</th>
                    <th class="text-right">Rejected</th>
                    <th class="text-right">Total Loss</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stockLossByCategory as $category)
                <tr>
                    <td>{{ $category['category'] }}</td>
                    <td class="text-right text-warning">RM {{ number_format($category['expired'], 2) }}</td>
                    <td class="text-right text-danger">RM {{ number_format($category['rejected'], 2) }}</td>
                    <td class="text-right"><strong>RM {{ number_format($category['total'], 2) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Stock Loss by Branch -->
    @if($stockLossByBranch->count() > 0)
    <div class="section">
        <div class="section-title">Stock Loss by Branch</div>
        @if($stockLossByBranchChartUrl)
        <div class="chart-container">
            <img src="{{ $stockLossByBranchChartUrl }}" alt="Stock Loss by Branch" style="max-height: 180px;">
        </div>
        @endif
        <table>
            <thead>
                <tr>
                    <th>Branch</th>
                    <th class="text-right">Expired</th>
                    <th class="text-right">Rejected</th>
                    <th class="text-right">Total Loss</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stockLossByBranch as $branch)
                <tr>
                    <td>{{ $branch['branch'] }}</td>
                    <td class="text-right text-warning">RM {{ number_format($branch['expired'], 2) }}</td>
                    <td class="text-right text-danger">RM {{ number_format($branch['rejected'], 2) }}</td>
                    <td class="text-right"><strong>RM {{ number_format($branch['total'], 2) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Rejected Transactions -->
    @if($rejectedSales->count() > 0)
    <div class="section">
        <div class="section-title">Rejected Transactions</div>
        <table>
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Branch</th>
                    <th>Staff</th>
                    <th>Date</th>
                    <th class="text-right">Items</th>
                    <th class="text-right">Loss Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rejectedSales->take(15) as $sale)
                <tr>
                    <td>{{ $sale->transaction_id }}</td>
                    <td>{{ $sale->branch->name ?? 'N/A' }}</td>
                    <td>{{ $sale->staff->name ?? 'N/A' }}</td>
                    <td>{{ $sale->sale_date->format('M d, Y') }}</td>
                    <td class="text-right">{{ $sale->items->sum('quantity') }}</td>
                    <td class="text-right text-danger">RM {{ number_format($sale->items->sum(function($item) { return $item->quantity * ($item->product->cost_price ?? 0); }), 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if($rejectedSales->count() > 15)
        <p style="font-size: 9px; color: #999; text-align: center;">Showing 15 of {{ $rejectedSales->count() }} rejected transactions</p>
        @endif
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        Cafe Sales System - Analytics Report | Generated on {{ now()->format('M d, Y H:i:s') }}
    </div>
</body>
</html>
