<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Report - {{ $branch->name }}</title>
    <style>
        @page {
            margin: 18mm 15mm;
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
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #423A8E;
        }
        
        .header h1 {
            font-size: 24px;
            color: #423A8E;
            margin-bottom: 8px;
        }
        
        .header .branch-name {
            font-size: 16px;
            color: #00CCCD;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .header .branch-address {
            font-size: 9px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .header .report-date {
            color: #888;
            font-size: 9px;
            margin-top: 10px;
        }
        
        .summary-box {
            margin-bottom: 30px;
            padding: 20px;
            background-color: #423A8E;
            color: white;
        }
        
        .summary-box h3 {
            margin-bottom: 18px;
            font-size: 13px;
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
            font-size: 16px;
            font-weight: bold;
            color: white;
        }
        
        .summary-value.success {
            color: #7dffb3;
        }
        
        .summary-value.warning {
            color: #ffc107;
        }
        
        .summary-value.danger {
            color: #ff7d7d;
        }
        
        .stats-row {
            margin-bottom: 30px;
        }
        
        .stats-table {
            width: 100%;
            border: none;
        }
        
        .stats-table td {
            width: 33.33%;
            padding: 15px;
            vertical-align: top;
            border: none;
        }
        
        .stat-card {
            border: 1px solid #e0e0e0;
            padding: 15px;
            text-align: center;
            background-color: #f8f9fa;
        }
        
        .stat-card .stat-label {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 8px;
            display: block;
        }
        
        .stat-card .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #423A8E;
        }
        
        .section {
            margin-bottom: 30px;
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
            padding: 12px 8px;
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
        
        table.data-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .status-badge {
            padding: 3px 8px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .total-row {
            font-weight: bold;
            background-color: #e9ecef !important;
        }
        
        .total-row td {
            border-top: 2px solid #423A8E;
            font-size: 10px;
            padding: 14px 8px;
        }
        
        .transaction-card {
            border: 1px solid #ddd;
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        
        .transaction-header {
            background-color: #f8f9fa;
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
        }
        
        .transaction-header table {
            width: 100%;
            border: none;
        }
        
        .transaction-header td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }
        
        .transaction-id {
            font-weight: bold;
            font-size: 11px;
            color: #423A8E;
        }
        
        .transaction-date {
            font-size: 9px;
            color: #666;
        }
        
        .transaction-body {
            padding: 15px;
        }
        
        .transaction-body table {
            width: 100%;
            border: none;
            margin-bottom: 10px;
        }
        
        .transaction-body td {
            border: none;
            padding: 4px 0;
            font-size: 9px;
        }
        
        .transaction-body .label {
            color: #666;
            width: 30%;
        }
        
        .transaction-body .value {
            font-weight: bold;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .items-table th,
        .items-table td {
            padding: 8px;
            border: 1px solid #e0e0e0;
            font-size: 8px;
        }
        
        .items-table th {
            background-color: #f0f0f0;
            color: #333;
            font-weight: bold;
        }
        
        .transaction-footer {
            background-color: #423A8E;
            color: white;
            padding: 10px 15px;
            text-align: right;
        }
        
        .transaction-footer .total-label {
            font-size: 9px;
            margin-right: 15px;
        }
        
        .transaction-footer .total-amount {
            font-size: 14px;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 8px;
            color: #666;
            line-height: 2;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .payment-badge {
            padding: 2px 6px;
            font-size: 8px;
            background-color: #e9ecef;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sales Report</h1>
        <div class="branch-name">{{ $branch->name }}</div>
        @if($branch->address)
        <div class="branch-address">{{ $branch->address }}</div>
        @endif
        @if(isset($summary['dateRangeLabel']))
        <div style="font-size: 12px; color: #00CCCD; font-weight: bold; margin-top: 8px;">{{ $summary['dateRangeLabel'] }}</div>
        @endif
        <div class="report-date">Generated on {{ \Carbon\Carbon::now()->format('l, d F Y \a\t h:i A') }}</div>
    </div>
    
    <!-- Summary -->
    <div class="summary-box">
        <h3>Report Summary</h3>
        <table class="summary-table">
            <tr>
                <td>
                    <span class="summary-label">Total Revenue</span>
                    <span class="summary-value">RM {{ number_format($summary['totalSales'], 2) }}</span>
                </td>
                <td>
                    <span class="summary-label">Transactions</span>
                    <span class="summary-value">{{ $summary['totalTransactions'] }}</span>
                </td>
                <td>
                    <span class="summary-label">Completed</span>
                    <span class="summary-value success">{{ $summary['completedCount'] }}</span>
                </td>
                <td>
                    <span class="summary-label">Pending</span>
                    <span class="summary-value warning">{{ $summary['pendingCount'] }}</span>
                </td>
                <td>
                    <span class="summary-label">Rejected</span>
                    <span class="summary-value danger">{{ $summary['rejectedCount'] }}</span>
                </td>
            </tr>
        </table>
    </div>
    
    <!-- Additional Stats -->
    @php
        $avgTransaction = $summary['totalTransactions'] > 0 ? $summary['totalSales'] / $summary['totalTransactions'] : 0;
        $completedSales = $sales->where('status', 'completed')->sum('total_amount');
        $successRate = $summary['totalTransactions'] > 0 ? ($summary['completedCount'] / $summary['totalTransactions']) * 100 : 0;
    @endphp
    <div class="stats-row">
        <table class="stats-table">
            <tr>
                <td>
                    <div class="stat-card">
                        <span class="stat-label">Average Transaction</span>
                        <span class="stat-value">RM {{ number_format($avgTransaction, 2) }}</span>
                    </div>
                </td>
                <td>
                    <div class="stat-card">
                        <span class="stat-label">Confirmed Revenue</span>
                        <span class="stat-value" style="color: #28a745;">RM {{ number_format($completedSales, 2) }}</span>
                    </div>
                </td>
                <td>
                    <div class="stat-card">
                        <span class="stat-label">Success Rate</span>
                        <span class="stat-value">{{ number_format($successRate, 1) }}%</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    
    <!-- Quick Summary Table -->
    <div class="section">
        <h3 class="section-title">Transaction Overview</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Transaction ID</th>
                    <th style="width: 12%;">Date</th>
                    <th style="width: 15%;">Staff</th>
                    <th style="width: 8%;">Items</th>
                    <th style="width: 15%;" class="text-right">Amount</th>
                    <th style="width: 15%;" class="text-center">Payment</th>
                    <th style="width: 10%;" class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                <tr>
                    <td><strong>{{ $sale->transaction_id }}</strong></td>
                    <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d M Y') }}</td>
                    <td>{{ $sale->staff->name ?? 'N/A' }}</td>
                    <td class="text-center">{{ $sale->items->count() }}</td>
                    <td class="text-right"><strong>RM {{ number_format($sale->total_amount, 2) }}</strong></td>
                    <td class="text-center">
                        <span class="payment-badge">{{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }}</span>
                    </td>
                    <td class="text-center">
                        <span class="status-badge status-{{ $sale->status }}">{{ ucfirst($sale->status) }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 30px;">No transactions found</td>
                </tr>
                @endforelse
                
                @if($sales->count() > 0)
                <tr class="total-row">
                    <td colspan="3"><strong>GRAND TOTAL</strong></td>
                    <td class="text-center"><strong>{{ $sales->sum(function($s) { return $s->items->count(); }) }}</strong></td>
                    <td class="text-right"><strong>RM {{ number_format($summary['totalSales'], 2) }}</strong></td>
                    <td colspan="2"></td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    
    <!-- Detailed Transaction Cards (limit to first 10 for PDF size) -->
    @if($sales->count() > 0)
    <div class="page-break"></div>
    <div class="section">
        <h3 class="section-title">Detailed Transaction Breakdown</h3>
        
        @foreach($sales->take(15) as $sale)
        <div class="transaction-card">
            <div class="transaction-header">
                <table>
                    <tr>
                        <td style="width: 50%;">
                            <span class="transaction-id">{{ $sale->transaction_id }}</span>
                        </td>
                        <td style="width: 30%; text-align: center;">
                            <span class="transaction-date">{{ \Carbon\Carbon::parse($sale->sale_date)->format('d M Y, h:i A') }}</span>
                        </td>
                        <td style="width: 20%; text-align: right;">
                            <span class="status-badge status-{{ $sale->status }}">{{ ucfirst($sale->status) }}</span>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="transaction-body">
                <table>
                    <tr>
                        <td class="label">Staff:</td>
                        <td class="value">{{ $sale->staff->name ?? 'N/A' }}</td>
                        <td class="label">Payment:</td>
                        <td class="value">{{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }}</td>
                    </tr>
                    @if($sale->payment_details)
                    <tr>
                        <td class="label">Payment Ref:</td>
                        <td class="value" colspan="3">{{ $sale->payment_details }}</td>
                    </tr>
                    @endif
                </table>
                
                <!-- Items -->
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 50%;">Product</th>
                            <th style="width: 15%;" class="text-center">Qty</th>
                            <th style="width: 17%;" class="text-right">Unit Price</th>
                            <th style="width: 18%;" class="text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->items as $item)
                        <tr>
                            <td>{{ $item->product->name ?? 'Unknown Product' }}</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-right">RM {{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-right">RM {{ number_format($item->total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                
                @if($sale->notes)
                <div style="margin-top: 10px; padding: 8px; background-color: #fff3cd; font-size: 8px;">
                    <strong>Notes:</strong> {{ $sale->notes }}
                </div>
                @endif
            </div>
            <div class="transaction-footer">
                <span class="total-label">Total Amount:</span>
                <span class="total-amount">RM {{ number_format($sale->total_amount, 2) }}</span>
            </div>
        </div>
        @endforeach
        
        @if($sales->count() > 15)
        <div style="text-align: center; padding: 20px; color: #666; font-style: italic;">
            Showing 15 of {{ $sales->count() }} transactions. Additional transactions omitted for brevity.
        </div>
        @endif
    </div>
    @endif
    
    <div class="footer">
        <p>This report was automatically generated by the Cafe Sales System.</p>
        <p>Branch: {{ $branch->name }} | Total Transactions: {{ $summary['totalTransactions'] }} | Report Date: {{ \Carbon\Carbon::now()->format('d M Y') }}</p>
        <p>&copy; {{ date('Y') }} Cafe Sales System. All rights reserved.</p>
    </div>
</body>
</html>
