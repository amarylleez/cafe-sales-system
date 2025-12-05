<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Profit & Loss Report</title>
    <style>
        @page {
            margin: 20mm 15mm;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.6;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 35px;
            padding-bottom: 20px;
            border-bottom: 3px solid #423A8E;
        }
        
        .header h1 {
            font-size: 24px;
            color: #423A8E;
            margin-bottom: 8px;
        }
        
        .header .subtitle {
            font-size: 14px;
            color: #00CCCD;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .header p {
            color: #666;
            font-size: 10px;
        }
        
        .filter-info {
            background-color: #f8f9fa;
            padding: 12px 18px;
            margin-bottom: 30px;
            border-left: 4px solid #423A8E;
        }
        
        .filter-info span {
            margin-right: 30px;
            font-size: 10px;
        }
        
        .filter-info strong {
            color: #423A8E;
        }
        
        .summary-box {
            margin-bottom: 35px;
            padding: 20px;
            background-color: #423A8E;
            color: white;
        }
        
        .summary-box h3 {
            margin-bottom: 20px;
            font-size: 14px;
            border-bottom: 1px solid rgba(255,255,255,0.3);
            padding-bottom: 10px;
            color: white;
        }
        
        .summary-table {
            width: 100%;
            border: none;
        }
        
        .summary-table td {
            text-align: center;
            padding: 12px 8px;
            vertical-align: top;
            border: none;
            color: white;
        }
        
        .summary-label {
            font-size: 8px;
            color: #ccc;
            text-transform: uppercase;
            display: block;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }
        
        .summary-value {
            font-size: 13px;
            font-weight: bold;
            color: white;
        }
        
        .summary-value.positive {
            color: #7dffb3;
        }
        
        .summary-value.negative {
            color: #ff7d7d;
        }
        
        .section {
            margin-bottom: 35px;
        }
        
        .section-title {
            font-size: 13px;
            color: #423A8E;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #00CCCD;
        }
        
        table.data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table.data-table th, 
        table.data-table td {
            padding: 12px 10px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
            font-size: 9px;
        }
        
        table.data-table th {
            background-color: #423A8E;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8px;
            letter-spacing: 0.3px;
        }
        
        table.data-table tbody tr {
            background-color: #fff;
        }
        
        table.data-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-success {
            color: #28a745;
        }
        
        .text-danger {
            color: #dc3545;
        }
        
        .total-row {
            font-weight: bold;
            background-color: #e9ecef !important;
        }
        
        .total-row td {
            border-top: 2px solid #423A8E;
            font-size: 10px;
            padding: 14px 10px;
        }
        
        /* Simple bar chart using table */
        .bar-chart-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        
        .bar-chart-table td {
            padding: 10px 8px;
            border: none;
            vertical-align: middle;
        }
        
        .bar-chart-table .branch-name {
            width: 35%;
            font-size: 9px;
            font-weight: bold;
            padding-right: 15px;
        }
        
        .bar-chart-table .bar-cell {
            width: 45%;
            padding: 10px 0;
        }
        
        .bar-chart-table .value-cell {
            width: 20%;
            text-align: right;
            font-size: 9px;
            font-weight: bold;
            padding-left: 15px;
        }
        
        .bar-outer {
            background-color: #e9ecef;
            height: 22px;
            width: 100%;
        }
        
        .bar-inner {
            height: 22px;
            background-color: #423A8E;
        }
        
        .bar-inner.profit {
            background-color: #28a745;
        }
        
        .bar-inner.loss {
            background-color: #dc3545;
        }
        
        .branch-card {
            border: 1px solid #ddd;
            padding: 18px;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        
        .branch-card.profitable {
            border-left: 5px solid #28a745;
        }
        
        .branch-card.loss {
            border-left: 5px solid #dc3545;
        }
        
        .branch-header-table {
            width: 100%;
            margin-bottom: 15px;
            border: none;
        }
        
        .branch-header-table td {
            border: none;
            padding: 0;
        }
        
        .branch-name-header {
            font-size: 12px;
            font-weight: bold;
            color: #333;
        }
        
        .branch-status {
            font-size: 9px;
            font-weight: bold;
            padding: 4px 10px;
        }
        
        .branch-status.profitable {
            background-color: #d4edda;
            color: #155724;
        }
        
        .branch-status.loss {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .metrics-table {
            width: 100%;
            border: none;
        }
        
        .metrics-table td {
            border: none;
            padding: 6px 8px;
            font-size: 9px;
        }
        
        .metric-label {
            color: #666;
            width: 25%;
        }
        
        .metric-value {
            font-weight: bold;
            width: 25%;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 8px;
            color: #666;
            line-height: 1.8;
        }
        
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Profit & Loss Report</h1>
        <div class="subtitle">{{ $dateRangeLabel }}</div>
        <p>Generated on {{ \Carbon\Carbon::now()->format('d M Y, h:i A') }}</p>
    </div>
    
    <div class="filter-info">
        <span><strong>Report Type:</strong> Profit & Loss Analysis</span>
        <span><strong>Period:</strong> {{ $dateRangeLabel }}</span>
        <span><strong>Scope:</strong> {{ $selectedBranch ? $selectedBranch->name : 'All Branches' }}</span>
    </div>
    
    <!-- Overall Summary -->
    <div class="summary-box">
        <h3>Overall Financial Summary</h3>
        <table class="summary-table">
            <tr>
                <td>
                    <span class="summary-label">Total Revenue</span>
                    <span class="summary-value">RM {{ number_format($overallTotals['revenue'], 2) }}</span>
                </td>
                <td>
                    <span class="summary-label">Total Cost</span>
                    <span class="summary-value">RM {{ number_format($overallTotals['cost'], 2) }}</span>
                </td>
                <td>
                    <span class="summary-label">Gross Profit</span>
                    <span class="summary-value positive">RM {{ number_format($overallTotals['grossProfit'], 2) }}</span>
                </td>
                <td>
                    <span class="summary-label">Rejected Loss</span>
                    <span class="summary-value negative">-RM {{ number_format($overallTotals['rejectedLoss'], 2) }}</span>
                </td>
                <td>
                    <span class="summary-label">Net Profit</span>
                    <span class="summary-value {{ $overallTotals['netProfit'] >= 0 ? 'positive' : 'negative' }}">
                        RM {{ number_format($overallTotals['netProfit'], 2) }}
                    </span>
                </td>
                <td>
                    <span class="summary-label">Profit Margin</span>
                    <span class="summary-value">{{ number_format($overallTotals['profitMargin'], 1) }}%</span>
                </td>
            </tr>
        </table>
    </div>
    
    <!-- Branch Performance Comparison (Visual Bar Chart) -->
    @if(count($branchProfitData) > 1)
    <div class="section">
        <h3 class="section-title">Branch Revenue Comparison</h3>
        @php
            $maxRevenue = collect($branchProfitData)->max('revenue');
            $maxRevenue = $maxRevenue > 0 ? $maxRevenue : 1;
        @endphp
        <table class="bar-chart-table">
            @foreach($branchProfitData as $data)
            <tr>
                <td class="branch-name">{{ $data['branch']->name }}</td>
                <td class="bar-cell">
                    <div class="bar-outer">
                        <div class="bar-inner" style="width: {{ ($data['revenue'] / $maxRevenue) * 100 }}%;"></div>
                    </div>
                </td>
                <td class="value-cell">RM {{ number_format($data['revenue'], 2) }}</td>
            </tr>
            @endforeach
        </table>
    </div>
    
    <div class="section">
        <h3 class="section-title">Branch Net Profit Comparison</h3>
        @php
            $maxProfit = collect($branchProfitData)->max('netProfit');
            $minProfit = collect($branchProfitData)->min('netProfit');
            $absMax = max(abs($maxProfit), abs($minProfit));
            $absMax = $absMax > 0 ? $absMax : 1;
        @endphp
        <table class="bar-chart-table">
            @foreach($branchProfitData as $data)
            <tr>
                <td class="branch-name">{{ $data['branch']->name }}</td>
                <td class="bar-cell">
                    <div class="bar-outer">
                        <div class="bar-inner {{ $data['netProfit'] >= 0 ? 'profit' : 'loss' }}" 
                             style="width: {{ (abs($data['netProfit']) / $absMax) * 100 }}%;"></div>
                    </div>
                </td>
                <td class="value-cell" style="color: {{ $data['netProfit'] >= 0 ? '#28a745' : '#dc3545' }};">
                    {{ $data['netProfit'] >= 0 ? '' : '-' }}RM {{ number_format(abs($data['netProfit']), 2) }}
                </td>
            </tr>
            @endforeach
        </table>
    </div>
    @endif
    
    <!-- Branch Breakdown Table -->
    <div class="section">
        <h3 class="section-title">
            @if($selectedBranch)
                Branch Performance: {{ $selectedBranch->name }}
            @else
                Performance by Branch
            @endif
        </h3>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>Branch</th>
                    <th class="text-right">Revenue</th>
                    <th class="text-right">Cost</th>
                    <th class="text-right">Gross Profit</th>
                    <th class="text-right">Rejected Loss</th>
                    <th class="text-right">Net Profit</th>
                    <th class="text-center">Margin</th>
                    <th class="text-center">Trans.</th>
                </tr>
            </thead>
            <tbody>
                @forelse($branchProfitData as $data)
                <tr>
                    <td><strong>{{ $data['branch']->name }}</strong></td>
                    <td class="text-right">RM {{ number_format($data['revenue'], 2) }}</td>
                    <td class="text-right">RM {{ number_format($data['cost'], 2) }}</td>
                    <td class="text-right text-success">RM {{ number_format($data['grossProfit'], 2) }}</td>
                    <td class="text-right text-danger">-RM {{ number_format($data['rejectedLoss'], 2) }}</td>
                    <td class="text-right {{ $data['netProfit'] >= 0 ? 'text-success' : 'text-danger' }}">
                        <strong>RM {{ number_format($data['netProfit'], 2) }}</strong>
                    </td>
                    <td class="text-center">{{ number_format($data['profitMargin'], 1) }}%</td>
                    <td class="text-center">{{ $data['transactions'] }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center">No data available</td>
                </tr>
                @endforelse
                
                @if(count($branchProfitData) > 1)
                <tr class="total-row">
                    <td><strong>TOTAL</strong></td>
                    <td class="text-right"><strong>RM {{ number_format($overallTotals['revenue'], 2) }}</strong></td>
                    <td class="text-right"><strong>RM {{ number_format($overallTotals['cost'], 2) }}</strong></td>
                    <td class="text-right text-success"><strong>RM {{ number_format($overallTotals['grossProfit'], 2) }}</strong></td>
                    <td class="text-right text-danger"><strong>-RM {{ number_format($overallTotals['rejectedLoss'], 2) }}</strong></td>
                    <td class="text-right {{ $overallTotals['netProfit'] >= 0 ? 'text-success' : 'text-danger' }}">
                        <strong>RM {{ number_format($overallTotals['netProfit'], 2) }}</strong>
                    </td>
                    <td class="text-center"><strong>{{ number_format($overallTotals['profitMargin'], 1) }}%</strong></td>
                    <td class="text-center"><strong>{{ $overallTotals['transactions'] }}</strong></td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    
    <!-- Top Products -->
    @if($topProducts->count() > 0)
    <div class="section">
        <h3 class="section-title">Top 10 Products by Revenue</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th>Product Name</th>
                    <th class="text-center">Qty Sold</th>
                    <th class="text-right">Revenue</th>
                    <th class="text-right">Cost</th>
                    <th class="text-right">Profit</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topProducts as $index => $product)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $product['name'] }}</strong></td>
                    <td class="text-center">{{ $product['quantity'] }}</td>
                    <td class="text-right">RM {{ number_format($product['revenue'], 2) }}</td>
                    <td class="text-right">RM {{ number_format($product['cost'], 2) }}</td>
                    <td class="text-right {{ $product['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                        RM {{ number_format($product['profit'], 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
    
    <!-- Detailed Branch Cards (for multi-branch reports) -->
    @if(count($branchProfitData) > 1)
    <div class="page-break"></div>
    <div class="section">
        <h3 class="section-title">Detailed Branch Analysis</h3>
        
        @foreach($branchProfitData as $data)
        <div class="branch-card {{ $data['netProfit'] >= 0 ? 'profitable' : 'loss' }}">
            <table class="branch-header-table">
                <tr>
                    <td style="width: 70%;">
                        <span class="branch-name-header">{{ $data['branch']->name }}</span>
                    </td>
                    <td style="width: 30%; text-align: right;">
                        <span class="branch-status {{ $data['netProfit'] >= 0 ? 'profitable' : 'loss' }}">
                            {{ $data['netProfit'] >= 0 ? 'PROFITABLE' : 'LOSS' }}
                        </span>
                    </td>
                </tr>
            </table>
            
            <table class="metrics-table">
                <tr>
                    <td class="metric-label">Revenue:</td>
                    <td class="metric-value">RM {{ number_format($data['revenue'], 2) }}</td>
                    <td class="metric-label">Cost of Goods:</td>
                    <td class="metric-value">RM {{ number_format($data['cost'], 2) }}</td>
                </tr>
                <tr>
                    <td class="metric-label">Gross Profit:</td>
                    <td class="metric-value" style="color: #28a745;">RM {{ number_format($data['grossProfit'], 2) }}</td>
                    <td class="metric-label">Rejected Loss:</td>
                    <td class="metric-value" style="color: #dc3545;">-RM {{ number_format($data['rejectedLoss'], 2) }}</td>
                </tr>
                <tr>
                    <td class="metric-label">Net Profit:</td>
                    <td class="metric-value" style="color: {{ $data['netProfit'] >= 0 ? '#28a745' : '#dc3545' }};">
                        <strong>RM {{ number_format($data['netProfit'], 2) }}</strong>
                    </td>
                    <td class="metric-label">Profit Margin:</td>
                    <td class="metric-value">{{ number_format($data['profitMargin'], 1) }}%</td>
                </tr>
                <tr>
                    <td class="metric-label">Transactions:</td>
                    <td class="metric-value">{{ $data['transactions'] }}</td>
                    <td class="metric-label">Avg per Transaction:</td>
                    <td class="metric-value">RM {{ $data['transactions'] > 0 ? number_format($data['revenue'] / $data['transactions'], 2) : '0.00' }}</td>
                </tr>
            </table>
        </div>
        @endforeach
    </div>
    @endif
    
    <div class="footer">
        <p>This report was automatically generated by the Cafe Sales System.</p>
        <p>Report Period: {{ $dateRangeLabel }} | Scope: {{ $selectedBranch ? $selectedBranch->name : 'All Branches' }}</p>
        <p>&copy; {{ date('Y') }} Cafe Sales System. All rights reserved.</p>
    </div>
</body>
</html>



