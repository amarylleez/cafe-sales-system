<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Reports</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #667eea;
        }
        
        .header h1 {
            font-size: 24px;
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .header p {
            color: #666;
            font-size: 12px;
        }
        
        .summary {
            margin-bottom: 30px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .summary h3 {
            margin-bottom: 10px;
            color: #667eea;
        }
        
        .summary-grid {
            display: table;
            width: 100%;
        }
        
        .summary-item {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 10px;
        }
        
        .summary-item .label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
        }
        
        .summary-item .value {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            padding: 10px 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #667eea;
            color: white;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
        
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        tr:hover {
            background-color: #e9ecef;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .status-completed {
            color: #28a745;
            font-weight: bold;
        }
        
        .status-verified {
            color: #17a2b8;
            font-weight: bold;
        }
        
        .status-pending {
            color: #ffc107;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        
        .total-row {
            font-weight: bold;
            background-color: #e9ecef !important;
        }
        
        .total-row td {
            border-top: 2px solid #667eea;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sales Reports</h1>
        <p>Generated on {{ \Carbon\Carbon::now()->format('d M Y, h:i A') }}</p>
    </div>
    
    @php
        $totalAmount = $reports->sum('total_amount');
        $totalTransactions = $reports->sum('items_count');
        $completedCount = $reports->where('status', 'completed')->count();
        $pendingCount = $reports->where('status', 'pending')->count();
    @endphp
    
    <div class="summary">
        <h3>Summary</h3>
        <table style="border: none;">
            <tr>
                <td style="border: none; text-align: center; width: 25%;">
                    <div class="label">Total Reports</div>
                    <div class="value">{{ $reports->count() }}</div>
                </td>
                <td style="border: none; text-align: center; width: 25%;">
                    <div class="label">Total Sales</div>
                    <div class="value">RM {{ number_format($totalAmount, 2) }}</div>
                </td>
                <td style="border: none; text-align: center; width: 25%;">
                    <div class="label">Total Items</div>
                    <div class="value">{{ $totalTransactions }}</div>
                </td>
                <td style="border: none; text-align: center; width: 25%;">
                    <div class="label">Completed</div>
                    <div class="value">{{ $completedCount }}</div>
                </td>
            </tr>
        </table>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Branch</th>
                <th>Staff</th>
                <th class="text-right">Amount</th>
                <th class="text-center">Items</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $report)
            <tr>
                <td>#{{ $report->id }}</td>
                <td>{{ \Carbon\Carbon::parse($report->sale_date)->format('d M Y') }}</td>
                <td>{{ $report->branch->name ?? 'N/A' }}</td>
                <td>{{ $report->staff->name ?? 'N/A' }}</td>
                <td class="text-right">RM {{ number_format($report->total_amount, 2) }}</td>
                <td class="text-center">{{ $report->items_count ?? 0 }}</td>
                <td class="text-center">
                    <span class="status-{{ $report->status }}">
                        {{ ucfirst($report->status) }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">No reports found</td>
            </tr>
            @endforelse
            
            @if($reports->count() > 0)
            <tr class="total-row">
                <td colspan="4"><strong>TOTAL</strong></td>
                <td class="text-right"><strong>RM {{ number_format($totalAmount, 2) }}</strong></td>
                <td class="text-center"><strong>{{ $totalTransactions }}</strong></td>
                <td></td>
            </tr>
            @endif
        </tbody>
    </table>
    
    <div class="footer">
        <p>This report was automatically generated by the Cafe Sales System.</p>
        <p>© {{ date('Y') }} Cafe Sales System. All rights reserved.</p>
    </div>
</body>
</html>
