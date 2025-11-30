@extends('layouts.branch-manager')

@section('page-title', 'Sales Report')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="mb-0 text-white">
                        <i class="bi bi-file-earmark-text"></i> Sales Report Management
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">Review, edit, and finalize sales reports before submitting to HQ Admin.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <small class="opacity-75">Total Sales This Month</small>
                    <h3 class="mb-0 mt-2">RM {{ number_format($totalSales, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white">
                    <small class="opacity-75">Total Transactions</small>
                    <h3 class="mb-0 mt-2">{{ number_format($totalTransactions) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="card-body text-white">
                    <small class="opacity-75">Average Transaction</small>
                    <h3 class="mb-0 mt-2">RM {{ number_format($totalTransactions > 0 ? $totalSales / $totalTransactions : 0, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Export -->
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control" id="searchReport" placeholder="Search by staff name or transaction ID...">
            </div>
        </div>
        <div class="col-md-6 text-end">
            <button class="btn btn-success" onclick="exportReport()">
                <i class="bi bi-file-earmark-excel"></i> Export to Excel
            </button>
            <button class="btn btn-primary" onclick="finalizeAndSubmit()">
                <i class="bi bi-send"></i> Finalize & Submit to HQ
            </button>
        </div>
    </div>

    <!-- Sales Reports Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-table"></i> Sales Transactions</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Transaction ID</th>
                                    <th>Staff</th>
                                    <th>Items</th>
                                    <th>Amount</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="reportsTableBody">
                                @foreach($reports as $report)
                                <tr data-report-id="{{ $report->id }}">
                                    <td>{{ $report->sale_date->format('d M Y') }}</td>
                                    <td><code>{{ $report->transaction_id }}</code></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar me-2" style="width: 30px; height: 30px; font-size: 0.9rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                                                {{ substr($report->staff->name, 0, 1) }}
                                            </div>
                                            {{ $report->staff->name }}
                                        </div>
                                    </td>
                                    <td>{{ $report->items_count }} items</td>
                                    <td><strong>RM {{ number_format($report->total_amount, 2) }}</strong></td>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $report->payment_method)) }}</span>
                                    </td>
                                    <td>
                                        @if($report->verified_by)
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Verified
                                        </span>
                                        @else
                                        <span class="badge bg-warning">
                                            <i class="bi bi-clock"></i> Pending
                                        </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" onclick="viewReport({{ $report->id }})">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-warning" onclick="editReport({{ $report->id }})">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            @if(!$report->verified_by)
                                            <button class="btn btn-outline-success" onclick="verifyReport({{ $report->id }})">
                                                <i class="bi bi-check"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-3">
                        {{ $reports->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Report Modal -->
<div class="modal fade" id="viewReportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sale Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="reportDetailsContent">
                <div class="text-center">
                    <div class="spinner-border" role="status"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Report Modal -->
<div class="modal fade" id="editReportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Sale Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editReportForm">
                    <input type="hidden" id="editReportId">
                    <div class="mb-3">
                        <label class="form-label">Total Amount</label>
                        <input type="number" class="form-control" id="editAmount" step="0.01">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" id="editNotes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveEdit()">
                    <i class="bi bi-save"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const viewReportModal = new bootstrap.Modal(document.getElementById('viewReportModal'));
const editReportModal = new bootstrap.Modal(document.getElementById('editReportModal'));

// Search functionality
document.getElementById('searchReport').addEventListener('input', function() {
    const query = this.value.toLowerCase();
    document.querySelectorAll('#reportsTableBody tr').forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });
});

// View report details
function viewReport(reportId) {
    document.getElementById('reportDetailsContent').innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status"></div>
        </div>
    `;
    
    viewReportModal.show();
    
    fetch(`/branch-manager/sales-report/${reportId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const report = data.report;
                document.getElementById('reportDetailsContent').innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Transaction Information</h6>
                            <table class="table table-sm">
                                <tr><th>Transaction ID:</th><td>${report.transaction_id}</td></tr>
                                <tr><th>Date:</th><td>${report.sale_date}</td></tr>
                                <tr><th>Staff:</th><td>${report.staff.name}</td></tr>
                                <tr><th>Payment Method:</th><td>${report.payment_method}</td></tr>
                                <tr><th>Status:</th><td><span class="badge bg-${report.status === 'completed' ? 'success' : 'warning'}">${report.status}</span></td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Items Purchased</h6>
                            <table class="table table-sm">
                                <thead>
                                    <tr><th>Item</th><th>Qty</th><th>Price</th></tr>
                                </thead>
                                <tbody>
                                    ${report.items.map(item => `
                                        <tr>
                                            <td>${item.product.name}</td>
                                            <td>${item.quantity}</td>
                                            <td>RM ${parseFloat(item.total).toFixed(2)}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                                <tfoot>
                                    <tr><th colspan="2">Total:</th><th>RM ${parseFloat(report.total_amount).toFixed(2)}</th></tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    ${report.notes ? `<div class="alert alert-info mt-3"><strong>Notes:</strong> ${report.notes}</div>` : ''}
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('reportDetailsContent').innerHTML = `
                <div class="alert alert-danger">Failed to load report details</div>
            `;
        });
}

// Edit report
function editReport(reportId) {
    // Fetch report data and populate form
    document.getElementById('editReportId').value = reportId;
    editReportModal.show();
}

// Save edited report
function saveEdit() {
    const reportId = document.getElementById('editReportId').value;
    const amount = document.getElementById('editAmount').value;
    const notes = document.getElementById('editNotes').value;
    
    fetch(`/branch-manager/sales-report/${reportId}/edit`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ amount, notes })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Report updated successfully!');
            editReportModal.hide();
            location.reload();
        }
    });
}

// Verify report
function verifyReport(reportId) {
    if (confirm('Are you sure you want to verify this report?')) {
        fetch(`/branch-manager/sales-report/${reportId}/verify`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Report verified!');
                location.reload();
            }
        });
    }
}

// Export report
function exportReport() {
    window.location.href = '/branch-manager/sales-report/export';
}

// Finalize and submit to HQ
function finalizeAndSubmit() {
    if (confirm('Are you sure you want to finalize and submit this report to HQ? This action cannot be undone.')) {
        fetch('/branch-manager/sales-report/finalize', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Report finalized and submitted to HQ!');
                location.reload();
            }
        });
    }
}
</script>
@endpush
@endsection