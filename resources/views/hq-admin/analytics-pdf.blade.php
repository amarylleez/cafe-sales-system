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
        .summary-card .value.info { color: #0dcaf0; }
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
        .text-warning { color: #b8860b; }
        .text-primary { color: #0d6efd; }
        .loss-breakdown {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .loss-item {
            display: table-cell;
            width: 50%;
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }
        .loss-item.expired {
            background: #fff3cd;
        }
        .loss-item.rejected {
            background: #f8d7da;
        }
        .loss-item .label {
            font-size: 10px;
            color: #666;
        }
        .loss-item .value {
            font-size: 14px;
            font-weight: bold;
            margin-top: 3px;
        }
        .footer {
            text-align: center;
            font-size: 9px;
            color: #999;
            padding: 15px 0;
            border-top: 1px solid #ddd;
            margin-top: 20px;
        }
        .net-profit-box {
            background: #d4edda;
            border: 2px solid #198754;
            padding: 15px;
            text-align: center;
            margin-bottom: 20px;
        }
        .net-profit-box .label {
            font-size: 11px;
            color: #155724;
        }
        .net-profit-box .value {
            font-size: 20px;
            font-weight: bold;
            color: #198754;
        }
        .net-profit-box.negative {
            background: #f8d7da;
            border-color: #dc3545;
        }
        .net-profit-box.negative .label {
            color: #721c24;
        }
        .net-profit-box.negative .value {
            color: #dc3545;
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
            <div class="label">Total Cost</div>
            <div class="value">RM {{ number_format($totalCost, 2) }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Gross Profit</div>
            <div class="value success">RM {{ number_format($grossProfit, 2) }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Profit Margin</div>
            <div class="value info">{{ number_format($profitMargin, 1) }}%</div>
        </div>
    </div>

    <!-- Stock Loss Breakdown -->
    <div class="section">
        <div class="section-title">Stock Loss Breakdown</div>
        <div class="loss-breakdown">
            <div class="loss-item expired">
                <div class="label">Expired Stock Loss</div>
                <div class="value text-warning">RM {{ number_format($expiredLoss, 2) }}</div>
            </div>
            <div class="loss-item rejected">
                <div class="label">Rejected Sales Loss</div>
                <div class="value text-danger">RM {{ number_format($rejectedSalesLoss, 2) }}</div>
            </div>
        </div>
        <table>
            <tr>
                <th style="width: 70%;">Loss Type</th>
                <th class="text-right" style="width: 30%;">Amount</th>
            </tr>
            <tr>
                <td>Expired Stock</td>
                <td class="text-right text-warning">RM {{ number_format($expiredLoss, 2) }}</td>
            </tr>
            <tr>
                <td>Rejected Sales</td>
                <td class="text-right text-danger">RM {{ number_format($rejectedSalesLoss, 2) }}</td>
            </tr>
            <tr style="background: #f0f0f0; font-weight: bold;">
                <td><strong>Total Stock Loss</strong></td>
                <td class="text-right text-danger"><strong>RM {{ number_format($stockLoss, 2) }}</strong></td>
            </tr>
        </table>
    </div>

    <!-- Net Profit -->
    @php $netProfit = $grossProfit - $stockLoss; @endphp
    <div class="net-profit-box {{ $netProfit < 0 ? 'negative' : '' }}">
        <div class="label">Net Profit (After Stock Loss)</div>
        <div class="value">RM {{ number_format($netProfit, 2) }}</div>
    </div>

    <!-- Top Profitable Products -->
    <div class="section">
        <div class="section-title">Top 5 Profitable Products</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 35%;">Product</th>
                    <th class="text-right" style="width: 12%;">Qty Sold</th>
                    <th class="text-right" style="width: 16%;">Revenue</th>
                    <th class="text-right" style="width: 16%;">Cost</th>
                    <th class="text-right" style="width: 16%;">Profit</th>
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

    <div class="page-break"></div>

    <!-- Page 2 Header -->
    <div class="header">
        <h1>Detailed Loss Analysis</h1>
        <p>Period: {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</p>
    </div>

    <!-- Stock Loss by Category -->
    @if($stockLossByCategory->count() > 0)
    <div class="section">
        <div class="section-title">Stock Loss by Category</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 40%;">Category</th>
                    <th class="text-right" style="width: 20%;">Expired</th>
                    <th class="text-right" style="width: 20%;">Rejected</th>
                    <th class="text-right" style="width: 20%;">Total Loss</th>
                </tr>
            </thead>
            <tbody>
                @php $categoryTotalExpired = 0; $categoryTotalRejected = 0; $categoryTotal = 0; @endphp
                @foreach($stockLossByCategory as $category)
                @php 
                    $categoryTotalExpired += $category['expired'];
                    $categoryTotalRejected += $category['rejected'];
                    $categoryTotal += $category['total'];
                @endphp
                <tr>
                    <td>{{ $category['category'] }}</td>
                    <td class="text-right text-warning">RM {{ number_format($category['expired'], 2) }}</td>
                    <td class="text-right text-danger">RM {{ number_format($category['rejected'], 2) }}</td>
                    <td class="text-right"><strong>RM {{ number_format($category['total'], 2) }}</strong></td>
                </tr>
                @endforeach
                <tr style="background: #f0f0f0; font-weight: bold;">
                    <td><strong>Total</strong></td>
                    <td class="text-right text-warning"><strong>RM {{ number_format($categoryTotalExpired, 2) }}</strong></td>
                    <td class="text-right text-danger"><strong>RM {{ number_format($categoryTotalRejected, 2) }}</strong></td>
                    <td class="text-right"><strong>RM {{ number_format($categoryTotal, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    <!-- Stock Loss by Branch -->
    @if($stockLossByBranch->count() > 0)
    <div class="section">
        <div class="section-title">Stock Loss by Branch</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 40%;">Branch</th>
                    <th class="text-right" style="width: 20%;">Expired</th>
                    <th class="text-right" style="width: 20%;">Rejected</th>
                    <th class="text-right" style="width: 20%;">Total Loss</th>
                </tr>
            </thead>
            <tbody>
                @php $branchTotalExpired = 0; $branchTotalRejected = 0; $branchTotal = 0; @endphp
                @foreach($stockLossByBranch as $branch)
                @php 
                    $branchTotalExpired += $branch['expired'];
                    $branchTotalRejected += $branch['rejected'];
                    $branchTotal += $branch['total'];
                @endphp
                <tr>
                    <td>{{ $branch['branch'] }}</td>
                    <td class="text-right text-warning">RM {{ number_format($branch['expired'], 2) }}</td>
                    <td class="text-right text-danger">RM {{ number_format($branch['rejected'], 2) }}</td>
                    <td class="text-right"><strong>RM {{ number_format($branch['total'], 2) }}</strong></td>
                </tr>
                @endforeach
                <tr style="background: #f0f0f0; font-weight: bold;">
                    <td><strong>Total</strong></td>
                    <td class="text-right text-warning"><strong>RM {{ number_format($branchTotalExpired, 2) }}</strong></td>
                    <td class="text-right text-danger"><strong>RM {{ number_format($branchTotalRejected, 2) }}</strong></td>
                    <td class="text-right"><strong>RM {{ number_format($branchTotal, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    <!-- Rejected Transactions -->
    @if($rejectedSales->count() > 0)
    <div class="section">
        <div class="section-title">Rejected Transactions ({{ $rejectedSales->count() }} total)</div>
        <table>
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Branch</th>
                    <th>Staff</th>
                    <th>Date</th>
                    <th class="text-right">Items</th>
                    <th class="text-right">Loss Amount</th>
                    <th>Reason</th>
                </tr>
            </thead>
            <tbody>
                @php $totalRejectedLoss = 0; @endphp
                @foreach($rejectedSales->take(20) as $sale)
                @php 
                    $saleLoss = $sale->items->sum(function($item) { 
                        return $item->quantity * ($item->product->cost_price ?? 0); 
                    });
                    $totalRejectedLoss += $saleLoss;
                @endphp
                <tr>
                    <td style="font-size: 9px;">{{ $sale->transaction_id }}</td>
                    <td>{{ $sale->branch->name ?? 'N/A' }}</td>
                    <td>{{ $sale->staff->name ?? 'N/A' }}</td>
                    <td>{{ $sale->sale_date->format('M d') }}</td>
                    <td class="text-right">{{ $sale->items->sum('quantity') }}</td>
                    <td class="text-right text-danger">RM {{ number_format($saleLoss, 2) }}</td>
                    <td style="font-size: 9px;">{{ Str::limit($sale->rejection_reason ?? '-', 20) }}</td>
                </tr>
                @endforeach
                <tr style="background: #f0f0f0; font-weight: bold;">
                    <td colspan="5"><strong>Total</strong></td>
                    <td class="text-right text-danger"><strong>RM {{ number_format($totalRejectedLoss, 2) }}</strong></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        @if($rejectedSales->count() > 20)
        <p style="font-size: 9px; color: #999; text-align: center; margin-top: 5px;">
            Showing 20 of {{ $rejectedSales->count() }} rejected transactions
        </p>
        @endif
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        Cafe Sales System - Analytics Report | Generated on {{ now()->format('M d, Y H:i:s') }}
    </div>
</body>
</html>
