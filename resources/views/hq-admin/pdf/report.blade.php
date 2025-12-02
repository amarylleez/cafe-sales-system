<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Report #{{ $report->id }}</title>
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
            padding: 20px;
        }
        
        .receipt {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px dashed #423A8E;
        }
        
        .header h1 {
            font-size: 20px;
            color: #423A8E;
            margin-bottom: 5px;
        }
        
        .header .branch-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .header .branch-info {
            font-size: 11px;
            color: #666;
        }
        
        .details {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px dashed #ddd;
        }
        
        .details-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }
        
        .details-row .label {
            display: table-cell;
            width: 40%;
            font-weight: bold;
            color: #666;
        }
        
        .details-row .value {
            display: table-cell;
            width: 60%;
            text-align: right;
        }
        
        .summary-section {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .summary-section h3 {
            text-align: center;
            margin-bottom: 15px;
            color: #423A8E;
            font-size: 14px;
            text-transform: uppercase;
        }
        
        .summary-row {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        
        .summary-row .label {
            display: table-cell;
            width: 50%;
        }
        
        .summary-row .value {
            display: table-cell;
            width: 50%;
            text-align: right;
            font-weight: bold;
        }
        
        .total-row {
            border-top: 2px solid #423A8E;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        .total-row .value {
            font-size: 18px;
            color: #423A8E;
        }
        
        .status {
            text-align: center;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-verified {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .notes {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-left: 3px solid #423A8E;
        }
        
        .notes h4 {
            margin-bottom: 5px;
            color: #423A8E;
        }
        
        .footer {
            text-align: center;
            padding-top: 20px;
            border-top: 2px dashed #ddd;
            font-size: 10px;
            color: #666;
        }
        
        .footer p {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <h1>SALES REPORT</h1>
            <div class="branch-name">{{ $report->branch->name ?? 'N/A' }}</div>
            <div class="branch-info">
                {{ $report->branch->address ?? '' }}<br>
                Tel: {{ $report->branch->phone ?? 'N/A' }}
            </div>
        </div>
        
        <div class="details">
            <div class="details-row">
                <span class="label">Report ID:</span>
                <span class="value">#{{ $report->id }}</span>
            </div>
            <div class="details-row">
                <span class="label">Transaction ID:</span>
                <span class="value">{{ $report->transaction_id }}</span>
            </div>
            <div class="details-row">
                <span class="label">Date:</span>
                <span class="value">{{ \Carbon\Carbon::parse($report->sale_date)->format('d M Y') }}</span>
            </div>
            <div class="details-row">
                <span class="label">Submitted By:</span>
                <span class="value">{{ $report->staff->name ?? 'N/A' }}</span>
            </div>
            <div class="details-row">
                <span class="label">Payment Method:</span>
                <span class="value">{{ ucfirst(str_replace('_', ' ', $report->payment_method)) }}</span>
            </div>
        </div>
        
        <div class="status status-{{ $report->status }}">
            <strong>Status: {{ strtoupper($report->status) }}</strong>
        </div>
        
        <div class="summary-section">
            <h3>Sales Summary</h3>
            <div class="summary-row">
                <span class="label">Total Items:</span>
                <span class="value">{{ $report->items_count ?? 0 }}</span>
            </div>
            <div class="summary-row total-row">
                <span class="label">Total Amount:</span>
                <span class="value">RM {{ number_format($report->total_amount, 2) }}</span>
            </div>
        </div>
        
        @if($report->notes)
        <div class="notes">
            <h4>Notes:</h4>
            <p>{{ $report->notes }}</p>
        </div>
        @endif
        
        @if($report->verified_by)
        <div class="details">
            <div class="details-row">
                <span class="label">Verified By:</span>
                <span class="value">{{ $report->verifier->name ?? 'N/A' }}</span>
            </div>
            <div class="details-row">
                <span class="label">Verified At:</span>
                <span class="value">{{ $report->verified_at ? \Carbon\Carbon::parse($report->verified_at)->format('d M Y, h:i A') : 'N/A' }}</span>
            </div>
        </div>
        @endif
        
        <div class="footer">
            <p>Generated on {{ \Carbon\Carbon::now()->format('d M Y, h:i A') }}</p>
            <p>Thank you for your business!</p>
            <p>© {{ date('Y') }} Cafe Sales System</p>
        </div>
    </div>
</body>
</html>



