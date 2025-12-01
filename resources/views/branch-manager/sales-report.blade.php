@extends('layouts.branch-manager')

@section('page-title', 'Sales Report')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header" style="background: linear-gradient(135deg, #D35400 0%, #E67E22 100%);">
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
            <div class="card shadow-sm" style="background: linear-gradient(135deg, #D35400 0%, #E67E22 100%);">
                <div class="card-body text-white">
                    <small class="opacity-75">Total Sales</small>
                    <h3 class="mb-0 mt-2">RM {{ number_format($totalSales, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm" style="background: linear-gradient(135deg, #D35400 0%, #E67E22 100%);">
                <div class="card-body text-white">
                    <small class="opacity-75">Total Transactions</small>
                    <h3 class="mb-0 mt-2">{{ number_format($totalTransactions) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm" style="background: linear-gradient(135deg, #D35400 0%, #E67E22 100%);">
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
                                <option value="" selected>All Time</option>
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                                <option value="custom">Custom Range</option>
                            </select>
                        </div>
                        <div class="col-md-2" id="customDateStart" style="display:none;">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="startDate">
                        </div>
                        <div class="col-md-2" id="customDateEnd" style="display:none;">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" id="endDate">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="filterStatus">
                                <option value="">All Status</option>
                                <option value="approved">Approved</option>
                                <option value="pending">Pending Approval</option>
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
                        <button class="btn btn-success btn-sm" onclick="exportReport()">
                            <i class="bi bi-file-earmark-excel"></i> Export to Excel
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
                                            <div class="user-avatar me-2" style="width: 30px; height: 30px; font-size: 0.9rem; background: linear-gradient(135deg, #D35400 0%, #E67E22 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
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
                                            <button class="btn btn-outline-primary" onclick="viewReport({{ $report->id }})">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-warning" onclick="editReport({{ $report->id }})">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            @if(!$report->verified_by)
                                            <button class="btn btn-outline-success" onclick="verifyReport({{ $report->id }})" title="Approve Transaction">
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
                <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Edit Sale Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="editReportContent">
                <div class="text-center">
                    <div class="spinner-border" role="status"></div>
                </div>
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

let currentEditReport = null;

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

// Edit report - load data first, then show modal with editable fields
function editReport(reportId) {
    document.getElementById('editReportContent').innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status"></div>
        </div>
    `;
    
    editReportModal.show();
    
    fetch(`/branch-manager/sales-report/${reportId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentEditReport = data.report;
                const report = data.report;
                const saleDate = new Date(report.sale_date).toISOString().split('T')[0];
                
                document.getElementById('editReportContent').innerHTML = `
                    <form id="editReportForm">
                        <input type="hidden" id="editReportId" value="${report.id}">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary fw-bold mb-3">Transaction Information</h6>
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th class="text-muted" style="width: 40%;">Transaction ID:</th>
                                        <td><code>${report.transaction_id}</code></td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">Date:</th>
                                        <td>
                                            <input type="date" class="form-control form-control-sm" id="editSaleDate" value="${saleDate}">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">Staff:</th>
                                        <td>${report.staff.name}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">Payment Method:</th>
                                        <td>
                                            <select class="form-select form-select-sm" id="editPaymentMethod">
                                                <option value="cash" ${report.payment_method === 'cash' ? 'selected' : ''}>Cash</option>
                                                <option value="card" ${report.payment_method === 'card' ? 'selected' : ''}>Card</option>
                                                <option value="e-wallet" ${report.payment_method === 'e-wallet' ? 'selected' : ''}>E-Wallet</option>
                                                <option value="bank_transfer" ${report.payment_method === 'bank_transfer' ? 'selected' : ''}>Bank Transfer</option>
                                                <option value="other" ${report.payment_method === 'other' ? 'selected' : ''}>Other</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">Status:</th>
                                        <td><span class="badge bg-${report.status === 'completed' ? 'success' : 'warning'}">${report.status}</span></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary fw-bold mb-3">Items Purchased</h6>
                                <table class="table table-sm" id="editItemsTable">
                                    <thead class="table-light">
                                        <tr><th>Item</th><th class="text-center">Qty</th><th class="text-end">Price</th></tr>
                                    </thead>
                                    <tbody>
                                        ${report.items.map((item, index) => `
                                            <tr>
                                                <td>${item.product.name}</td>
                                                <td class="text-center">
                                                    <input type="number" class="form-control form-control-sm text-center item-qty" 
                                                           data-index="${index}" 
                                                           data-unit-price="${item.unit_price}"
                                                           value="${item.quantity}" 
                                                           min="1" 
                                                           style="width: 70px; display: inline-block;"
                                                           onchange="recalculateTotal()">
                                                </td>
                                                <td class="text-end item-total" data-index="${index}">RM ${parseFloat(item.total).toFixed(2)}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr><th colspan="2">Total:</th><th class="text-end" id="editGrandTotal">RM ${parseFloat(report.total_amount).toFixed(2)}</th></tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <label class="form-label fw-bold"><i class="bi bi-sticky"></i> Notes</label>
                            <textarea class="form-control" id="editNotes" rows="2" placeholder="Add notes about this transaction...">${report.notes || ''}</textarea>
                        </div>
                    </form>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('editReportContent').innerHTML = `
                <div class="alert alert-danger">Failed to load report details</div>
            `;
        });
}

// Recalculate total when quantity changes
function recalculateTotal() {
    let grandTotal = 0;
    document.querySelectorAll('.item-qty').forEach(input => {
        const index = input.dataset.index;
        const qty = parseInt(input.value) || 0;
        const unitPrice = parseFloat(input.dataset.unitPrice) || 0;
        const itemTotal = qty * unitPrice;
        grandTotal += itemTotal;
        
        document.querySelector(`.item-total[data-index="${index}"]`).textContent = `RM ${itemTotal.toFixed(2)}`;
    });
    
    document.getElementById('editGrandTotal').textContent = `RM ${grandTotal.toFixed(2)}`;
}

// Save edited report
function saveEdit() {
    const reportId = document.getElementById('editReportId').value;
    const saleDate = document.getElementById('editSaleDate').value;
    const paymentMethod = document.getElementById('editPaymentMethod').value;
    const notes = document.getElementById('editNotes').value;
    
    // Collect item quantities
    const items = [];
    document.querySelectorAll('.item-qty').forEach((input, index) => {
        items.push({
            id: currentEditReport.items[index].id,
            quantity: parseInt(input.value) || 1
        });
    });
    
    fetch(`/branch-manager/sales-report/${reportId}/edit`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ 
            sale_date: saleDate,
            payment_method: paymentMethod,
            notes: notes,
            items: items
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Report updated successfully!');
            editReportModal.hide();
            location.reload();
        } else {
            alert('Failed to update report: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update report');
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
    window.location.href = '/branch-manager/sales-report/export';
}

// Finalize and submit to HQ
function finalizeAndSubmit() {
    if (confirm('Are you sure you want to finalize and submit all approved reports to HQ? This action cannot be undone.')) {
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
                alert('Reports finalized and submitted to HQ!');
                location.reload();
            }
        });
    }
}
</script>
@endpush
@endsection
