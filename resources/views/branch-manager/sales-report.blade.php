@extends('layouts.branch-manager')

@section('page-title', 'Sales Report')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header" style="background: linear-gradient(135deg, #423A8E 0%, #00CCCD 100%);">
                    <h5 class="mb-0 text-white">
                        <i class="bi bi-file-earmark-text"></i> Sales Report Management
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">Review and verify sales transactions before submitting to HQ Admin.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm" style="background: linear-gradient(135deg, #423A8E 0%, #00CCCD 100%);">
                <div class="card-body text-white">
                    <small class="opacity-75">Total Sales</small>
                    <h3 class="mb-0 mt-2">RM {{ number_format($totalSales, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm" style="background: linear-gradient(135deg, #423A8E 0%, #00CCCD 100%);">
                <div class="card-body text-white">
                    <small class="opacity-75">Total Transactions</small>
                    <h3 class="mb-0 mt-2">{{ number_format($totalTransactions) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm" style="background: linear-gradient(135deg, #423A8E 0%, #00CCCD 100%);">
                <div class="card-body text-white">
                    <small class="opacity-75">Average Transaction</small>
                    <h3 class="mb-0 mt-2">RM {{ number_format($totalTransactions > 0 ? $totalSales / $totalTransactions : 0, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label">Date Range</label>
                            <select class="form-select" id="dateRange">
                                <option value="" {{ request('date_range') == '' ? 'selected' : '' }}>All Time</option>
                                <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>Today</option>
                                <option value="week" {{ request('date_range') == 'week' ? 'selected' : '' }}>This Week</option>
                                <option value="month" {{ request('date_range') == 'month' ? 'selected' : '' }}>This Month</option>
                                <option value="custom" {{ request('date_range') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                            </select>
                        </div>
                        <div class="col-md-2" id="customDateStart" style="display:{{ request('date_range') == 'custom' ? 'block' : 'none' }};">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="startDate" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-2" id="customDateEnd" style="display:{{ request('date_range') == 'custom' ? 'block' : 'none' }};">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" id="endDate" value="{{ request('end_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="filterStatus">
                                <option value="" {{ request('status') == '' ? 'selected' : '' }}>All Status</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Approval</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="searchReport" placeholder="Search staff or transaction ID...">
                            </div>
                        </div>
                        <div class="col-md-auto">
                            <button class="btn btn-primary" onclick="applyFilter()">
                                <i class="bi bi-funnel"></i> Apply Filter
                            </button>
                            <button class="btn btn-outline-secondary" onclick="resetFilter()">
                                <i class="bi bi-arrow-clockwise"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Reports Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-table"></i> Sales Transactions</h5>
                    <div>
                        <button class="btn btn-danger btn-sm" onclick="exportReport()">
                            <i class="bi bi-file-earmark-pdf"></i> Export to PDF
                        </button>
                        <button class="btn btn-primary btn-sm" onclick="finalizeAndSubmit()">
                            <i class="bi bi-send"></i> Finalize & Submit to HQ
                        </button>
                    </div>
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
                                            <div class="user-avatar me-2" style="width: 30px; height: 30px; font-size: 0.9rem; background: linear-gradient(135deg, #423A8E 0%, #00CCCD 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
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
                                        @if($report->status === 'rejected')
                                        <span class="badge bg-danger">
                                            <i class="bi bi-x-circle"></i> Rejected
                                        </span>
                                        @elseif($report->verified_by)
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Approved
                                        </span>
                                        @else
                                        <span class="badge bg-warning">
                                            <i class="bi bi-clock"></i> Pending Approval
                                        </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" onclick="viewReport({{ $report->id }})" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            @if(!$report->verified_by && $report->status !== 'rejected')
                                            <button class="btn btn-outline-success" onclick="verifyReport({{ $report->id }})" title="Approve Transaction">
                                                <i class="bi bi-check"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="rejectReport({{ $report->id }})" title="Reject Transaction">
                                                <i class="bi bi-x"></i>
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
                        {{ $reports->links('vendor.pagination.bootstrap-5') }}
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

<!-- Reject Report Modal -->
<div class="modal fade" id="rejectReportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-x-circle"></i> Reject Transaction</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="rejectReportId">
                <div class="mb-3">
                    <label for="rejectReason" class="form-label fw-bold">Reason for Rejection <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="rejectReason" rows="3" placeholder="Please provide a reason for rejecting this transaction..." required></textarea>
                    <div class="form-text">This reason will be visible to the staff member who submitted the sale.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmReject()">
                    <i class="bi bi-x-circle"></i> Reject Transaction
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Finalize Confirmation Modal -->
<div class="modal fade" id="finalizeConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body text-center p-5">
                <div class="mb-4">
                    <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 4rem;"></i>
                    <h4 class="mt-3 mb-3">Finalize and Submit to HQ?</h4>
                    <p class="text-muted mb-0">Are you sure you want to finalize and submit all pending reports to HQ? This will approve all pending transactions.</p>
                </div>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-secondary btn-lg px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-primary btn-lg px-4" id="confirmFinalizeBtn">
                        <i class="bi bi-check-circle"></i> Yes, Submit
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Finalize Success Modal -->
<div class="modal fade" id="finalizeSuccessModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body text-center p-5">
                <div class="mb-4">
                    <div class="success-checkmark mx-auto mb-3">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                    </div>
                    <h3 class="text-success mb-3">Reports Submitted Successfully!</h3>
                    <div class="alert alert-light border">
                        <strong>Reports Finalized:</strong>
                        <div class="mt-2">
                            <span id="finalizedCount" class="fs-4 text-primary fw-bold"></span>
                        </div>
                    </div>
                    <p class="text-muted mb-0">All pending transactions have been approved and submitted to HQ.</p>
                </div>
                <button type="button" class="btn btn-primary btn-lg px-5" id="finalizeSuccessOk">
                    <i class="bi bi-check-circle"></i> OK
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const viewReportModal = new bootstrap.Modal(document.getElementById('viewReportModal'));
const rejectReportModal = new bootstrap.Modal(document.getElementById('rejectReportModal'));
const finalizeConfirmModal = new bootstrap.Modal(document.getElementById('finalizeConfirmModal'));
const finalizeSuccessModal = new bootstrap.Modal(document.getElementById('finalizeSuccessModal'));

// Date range toggle for custom dates
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

// Apply filters
function applyFilter() {
    const dateRange = document.getElementById('dateRange').value;
    const status = document.getElementById('filterStatus').value;
    
    const params = new URLSearchParams();
    if (dateRange) params.append('date_range', dateRange);
    if (status) params.append('status', status);
    if (dateRange === 'custom') {
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);
    }
    
    window.location.href = `/branch-manager/sales-report?${params.toString()}`;
}

// Reset filters
function resetFilter() {
    window.location.href = '/branch-manager/sales-report';
}

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
                const saleDate = new Date(report.sale_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
                document.getElementById('reportDetailsContent').innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary fw-bold mb-3">Transaction Information</h6>
                            <table class="table table-sm table-borderless">
                                <tr><th class="text-muted" style="width: 40%;">Transaction ID:</th><td><code>${report.transaction_id}</code></td></tr>
                                <tr><th class="text-muted">Date:</th><td>${saleDate}</td></tr>
                                <tr><th class="text-muted">Staff:</th><td>${report.staff.name}</td></tr>
                                <tr><th class="text-muted">Payment Method:</th><td><span class="badge bg-info">${report.payment_method.replace('_', ' ')}</span></td></tr>
                                <tr><th class="text-muted">Payment Details:</th><td>${report.payment_details ? report.payment_details : '<span class="text-muted fst-italic">Not provided</span>'}</td></tr>
                                <tr><th class="text-muted">Status:</th><td><span class="badge bg-${report.status === 'completed' ? 'success' : 'warning'}">${report.status}</span></td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary fw-bold mb-3">Items Purchased</h6>
                            <table class="table table-sm">
                                <thead class="table-light">
                                    <tr><th>Item</th><th class="text-center">Qty</th><th class="text-end">Price</th></tr>
                                </thead>
                                <tbody>
                                    ${report.items.map(item => `
                                        <tr>
                                            <td>${item.product.name}</td>
                                            <td class="text-center">${item.quantity}</td>
                                            <td class="text-end">RM ${parseFloat(item.total).toFixed(2)}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                                <tfoot class="table-light">
                                    <tr><th colspan="2">Total:</th><th class="text-end">RM ${parseFloat(report.total_amount).toFixed(2)}</th></tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    ${report.notes ? `<div class="alert alert-info mt-3"><i class="bi bi-sticky"></i> <strong>Notes:</strong> ${report.notes}</div>` : ''}
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

// Reject report - show modal with reason input
function rejectReport(reportId) {
    document.getElementById('rejectReportId').value = reportId;
    document.getElementById('rejectReason').value = '';
    rejectReportModal.show();
}

// Confirm rejection
function confirmReject() {
    const reportId = document.getElementById('rejectReportId').value;
    const reason = document.getElementById('rejectReason').value.trim();
    
    if (!reason) {
        alert('Please provide a reason for rejection.');
        return;
    }
    
    fetch(`/branch-manager/sales-report/${reportId}/reject`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ reason: reason })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Transaction rejected successfully!');
            rejectReportModal.hide();
            location.reload();
        } else {
            alert('Failed to reject transaction: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to reject transaction');
    });
}

// Verify report
function verifyReport(reportId) {
    if (confirm('Are you sure you want to approve this transaction?')) {
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
                alert('Transaction approved!');
                location.reload();
            }
        });
    }
}

// Export report
function exportReport() {
    const dateRange = document.getElementById('dateRange').value;
    const status = document.getElementById('filterStatus').value;
    
    const params = new URLSearchParams();
    if (dateRange) params.append('date_range', dateRange);
    if (status) params.append('status', status);
    if (dateRange === 'custom') {
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);
    }
    
    window.location.href = `/branch-manager/sales-report/export?${params.toString()}`;
}

// Finalize and submit to HQ
function finalizeAndSubmit() {
    finalizeConfirmModal.show();
}

// Confirm finalize button click
document.getElementById('confirmFinalizeBtn').addEventListener('click', function() {
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
    
    fetch('/branch-manager/sales-report/finalize', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        finalizeConfirmModal.hide();
        
        if (data.success) {
            // Show success modal
            document.getElementById('finalizedCount').textContent = data.count || '0';
            finalizeSuccessModal.show();
            
            // Reload when OK is clicked
            document.getElementById('finalizeSuccessOk').addEventListener('click', function() {
                location.reload();
            });
        } else {
            alert(data.message || 'No pending reports to finalize.');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle"></i> Yes, Submit';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        finalizeConfirmModal.hide();
        alert('An error occurred while processing your request.');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle"></i> Yes, Submit';
    });
});
</script>
@endpush
@endsection


