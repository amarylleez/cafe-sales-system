@extends('layouts.hq-admin')

@section('page-title', 'Unified Sales Reports')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="mb-0 text-white">
                        <i class="bi bi-file-earmark-text"></i> Unified Sales Reports
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">View and download consolidated sales reports from all branches.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Date Range</label>
                            <select class="form-select" id="dateRange">
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month" selected>This Month</option>
                                <option value="custom">Custom Range</option>
                            </select>
                        </div>
                        <div class="col-md-3" id="customDateStart" style="display:none;">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="startDate">
                        </div>
                        <div class="col-md-3" id="customDateEnd" style="display:none;">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" id="endDate">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Branch</label>
                            <select class="form-select" id="filterBranch">
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="filterStatus">
                                <option value="">All Status</option>
                                <option value="pending">Pending Verification</option>
                                <option value="verified">Verified</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <button class="btn btn-primary" onclick="applyFilter()">
                                <i class="bi bi-funnel"></i> Apply Filter
                            </button>
                            <button class="btn btn-outline-secondary" onclick="resetFilter()">
                                <i class="bi bi-arrow-clockwise"></i> Reset
                            </button>
                            <button class="btn btn-success ms-2" onclick="exportCSV()">
                                <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
                            </button>
                            <button class="btn btn-danger ms-2" onclick="exportPDF()">
                                <i class="bi bi-file-earmark-pdf"></i> Export PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white text-center">
                    <small class="opacity-75">Total Sales</small>
                    <h3 class="mb-0 mt-2">RM {{ number_format($reportSummary['totalSales'], 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white text-center">
                    <small class="opacity-75">Total Reports</small>
                    <h3 class="mb-0 mt-2">{{ $reportSummary['totalReports'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="card-body text-white text-center">
                    <small class="opacity-75">Verified Reports</small>
                    <h3 class="mb-0 mt-2">{{ $reportSummary['verifiedReports'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                <div class="card-body text-white text-center">
                    <small class="opacity-75">Pending Verification</small>
                    <h3 class="mb-0 mt-2">{{ $reportSummary['pendingReports'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Reports List -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-list-ul"></i> Sales Reports</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Report ID</th>
                                    <th>Date</th>
                                    <th>Branch</th>
                                    <th>Submitted By</th>
                                    <th>Total Sales</th>
                                    <th>Transactions</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="reportsTableBody">
                                @foreach($salesReports as $report)
                                <tr>
                                    <td><strong>#{{ $report->id }}</strong></td>
                                    <td>{{ \Carbon\Carbon::parse($report->sale_date)->format('d M Y') }}</td>
                                    <td>{{ $report->branch->name }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar me-2" style="width: 30px; height: 30px; font-size: 0.9rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                                                {{ substr($report->staff->name, 0, 1) }}
                                            </div>
                                            {{ $report->staff->name }}
                                        </div>
                                    </td>
                                    <td><strong class="text-primary">RM {{ number_format($report->total_amount, 2) }}</strong></td>
                                    <td>{{ $report->items_count ?? 0 }}</td>
                                    <td>
                                        @if($report->status === 'completed')
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Completed</span>
                                        @elseif($report->status === 'verified')
                                        <span class="badge bg-info"><i class="bi bi-shield-check"></i> Verified</span>
                                        @else
                                        <span class="badge bg-warning"><i class="bi bi-clock"></i> Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" onclick="viewReport({{ $report->id }})" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="downloadReportPDF({{ $report->id }})" title="Download PDF">
                                                <i class="bi bi-file-pdf"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-3">
                        {{ $salesReports->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Report Detail Modal (Receipt View) -->
<div class="modal fade" id="reportDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sales Report Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="receiptContent" class="p-4" style="background: white; max-width: 600px; margin: 0 auto; font-family: 'Courier New', monospace;">
                    <!-- Receipt content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" onclick="printReceipt()">
                    <i class="bi bi-printer"></i> Print
                </button>
                <button type="button" class="btn btn-primary" onclick="downloadCurrentReport()">
                    <i class="bi bi-download"></i> Download PDF
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const reportModal = new bootstrap.Modal(document.getElementById('reportDetailModal'));
let currentReportId = null;

// Date range toggle
document.getElementById('dateRange').addEventListener('change', function() {
    const customStart = document.getElementById('customDateStart');
    const customEnd = document.getElementById('customDateEnd');
    
    if (this.value === 'custom') {
        customStart.style.display = 'block';
        customEnd.style.display = 'block';
    } else {
        customStart.style.display = 'none';
        customEnd.style.display = 'none';
    }
});

// View report details
function viewReport(reportId) {
    currentReportId = reportId;
    
    fetch(`/hq-admin/reports/${reportId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const report = data.report;
                const receiptHTML = `
                    <div class="text-center border-bottom pb-3 mb-3">
                        <h4 class="mb-0">${report.branch.name}</h4>
                        <p class="mb-0 small">${report.branch.address}</p>
                        <p class="mb-0 small">Tel: ${report.branch.phone || 'N/A'}</p>
                    </div>
                    
                    <div class="border-bottom pb-2 mb-2">
                        <div class="row">
                            <div class="col-6"><strong>Report ID:</strong></div>
                            <div class="col-6 text-end">#${report.id}</div>
                        </div>
                        <div class="row">
                            <div class="col-6"><strong>Date:</strong></div>
                            <div class="col-6 text-end">${new Date(report.sale_date).toLocaleDateString()}</div>
                        </div>
                        <div class="row">
                            <div class="col-6"><strong>Submitted By:</strong></div>
                            <div class="col-6 text-end">${report.staff.name}</div>
                        </div>
                        <div class="row">
                            <div class="col-6"><strong>Status:</strong></div>
                            <div class="col-6 text-end">
                                <span class="badge bg-${report.status === 'completed' ? 'success' : (report.status === 'verified' ? 'info' : 'warning')}">
                                    ${report.status.toUpperCase()}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="border-bottom pb-2 mb-2">
                        <h6 class="text-center mb-2">SALES SUMMARY</h6>
                        <div class="row">
                            <div class="col-6">Total Items:</div>
                            <div class="col-6 text-end"><strong>${report.items_count || 0}</strong></div>
                        </div>
                        <div class="row">
                            <div class="col-6">Total Sales:</div>
                            <div class="col-6 text-end"><strong>RM ${parseFloat(report.total_amount).toFixed(2)}</strong></div>
                        </div>
                        <div class="row">
                            <div class="col-6">Average per Item:</div>
                            <div class="col-6 text-end"><strong>RM ${report.items_count > 0 ? (report.total_amount / report.items_count).toFixed(2) : '0.00'}</strong></div>
                        </div>
                    </div>
                    
                    ${report.notes ? `
                    <div class="border-bottom pb-2 mb-2">
                        <h6>Notes:</h6>
                        <p class="small">${report.notes}</p>
                    </div>
                    ` : ''}
                    
                    <div class="text-center mt-3">
                        <p class="small mb-0">Generated on ${new Date().toLocaleString()}</p>
                        <p class="small mb-0">Thank you for your business!</p>
                    </div>
                `;
                
                document.getElementById('receiptContent').innerHTML = receiptHTML;
                reportModal.show();
            }
        });
}

// Download single report as PDF
function downloadReportPDF(reportId) {
    window.location.href = `/hq-admin/reports/${reportId}/pdf`;
}

// Download current viewed report
function downloadCurrentReport() {
    if (currentReportId) {
        downloadReportPDF(currentReportId);
    }
}

// Print receipt
function printReceipt() {
    const content = document.getElementById('receiptContent').innerHTML;
    const printWindow = window.open('', '', 'height=600,width=800');
    printWindow.document.write('<html><head><title>Sales Report</title>');
    printWindow.document.write('<style>body{font-family: "Courier New", monospace; padding: 20px;}</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write(content);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}

// Export all reports as CSV
function exportCSV() {
    const dateRange = document.getElementById('dateRange').value;
    const branch = document.getElementById('filterBranch').value;
    const status = document.getElementById('filterStatus').value;
    
    const params = new URLSearchParams({
        date_range: dateRange,
        branch: branch,
        status: status,
        start_date: document.getElementById('startDate').value,
        end_date: document.getElementById('endDate').value
    });
    
    window.location.href = `/hq-admin/reports/export/csv?${params.toString()}`;
}

// Export all reports as PDF
function exportPDF() {
    const dateRange = document.getElementById('dateRange').value;
    const branch = document.getElementById('filterBranch').value;
    const status = document.getElementById('filterStatus').value;
    
    const params = new URLSearchParams({
        date_range: dateRange,
        branch: branch,
        status: status,
        start_date: document.getElementById('startDate').value,
        end_date: document.getElementById('endDate').value
    });
    
    window.location.href = `/hq-admin/reports/export/pdf?${params.toString()}`;
}

// Apply filters
function applyFilter() {
    const dateRange = document.getElementById('dateRange').value;
    const branch = document.getElementById('filterBranch').value;
    const status = document.getElementById('filterStatus').value;
    
    const params = new URLSearchParams({
        date_range: dateRange,
        branch: branch,
        status: status,
        start_date: document.getElementById('startDate').value,
        end_date: document.getElementById('endDate').value
    });
    
    window.location.href = `/hq-admin/reports?${params.toString()}`;
}

// Reset filters
function resetFilter() {
    window.location.href = '/hq-admin/reports';
}
</script>
@endpush
@endsection