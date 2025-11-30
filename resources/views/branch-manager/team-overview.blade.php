@extends('layouts.branch-manager')

@section('page-title', 'Team Overview')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="mb-0 text-white">
                        <i class="bi bi-people"></i> Team Overview
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">View and manage your team members at {{ $branchManager->branch->name }}.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Branch Manager Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-person-badge"></i> Branch Manager</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <div class="user-avatar mx-auto" style="width: 100px; height: 100px; font-size: 3rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                                {{ substr($branchManager->name, 0, 1) }}
                            </div>
                        </div>
                        <div class="col-md-5">
                            <h4>{{ $branchManager->name }}</h4>
                            <p class="mb-1"><i class="bi bi-envelope"></i> {{ $branchManager->email }}</p>
                            <p class="mb-1"><i class="bi bi-building"></i> {{ $branchManager->branch->name }}</p>
                            <p class="mb-0"><i class="bi bi-geo-alt"></i> {{ $branchManager->branch->address }}</p>
                        </div>
                        <div class="col-md-5">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h3 class="text-primary mb-0">{{ $staffMembers->count() }}</h3>
                                            <small class="text-muted">Team Members</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h3 class="text-success mb-0">{{ $staffMembers->count() }}</h3>
                                            <small class="text-muted">Active Staff</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Staff Members -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-people-fill"></i> Staff Members</h5>
                    <div class="input-group" style="max-width: 300px;">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="searchStaff" placeholder="Search staff...">
                    </div>
                </div>
                <div class="card-body">
                    @if($staffMembers->count() > 0)
                    <div class="row g-4" id="staffContainer">
                        @foreach($staffMembers as $staff)
                        <div class="col-md-6 col-lg-4 staff-card">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <div class="user-avatar mx-auto" style="width: 70px; height: 70px; font-size: 2rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                                            {{ substr($staff->name, 0, 1) }}
                                        </div>
                                    </div>
                                    <h6 class="text-center mb-2">{{ $staff->name }}</h6>
                                    <p class="text-center text-muted small mb-3">
                                        <i class="bi bi-envelope"></i> {{ $staff->email }}
                                    </p>
                                    
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-sm btn-outline-primary" onclick="viewStaffPerformance({{ $staff->id }})">
                                            <i class="bi bi-graph-up"></i> View Performance
                                        </button>
                                        <button class="btn btn-sm btn-outline-info" onclick="viewStaffSchedule({{ $staff->id }})">
                                            <i class="bi bi-calendar"></i> View Schedule
                                        </button>
                                    </div>
                                </div>
                                <div class="card-footer bg-light text-center">
                                    <small class="text-muted">
                                        <i class="bi bi-clock"></i> Member since {{ $staff->created_at->format('M Y') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No staff members registered yet.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Staff Performance Modal -->
<div class="modal fade" id="staffPerformanceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Staff Performance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="performanceContent">
                <div class="text-center">
                    <div class="spinner-border" role="status"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Staff Schedule Modal -->
<div class="modal fade" id="staffScheduleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Staff Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="scheduleContent">
                <div class="text-center">
                    <div class="spinner-border" role="status"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const performanceModal = new bootstrap.Modal(document.getElementById('staffPerformanceModal'));
const scheduleModal = new bootstrap.Modal(document.getElementById('staffScheduleModal'));

// Search staff
document.getElementById('searchStaff').addEventListener('input', function() {
    const query = this.value.toLowerCase();
    document.querySelectorAll('.staff-card').forEach(card => {
        const text = card.textContent.toLowerCase();
        card.style.display = text.includes(query) ? '' : 'none';
    });
});

// View staff performance
function viewStaffPerformance(staffId) {
    document.getElementById('performanceContent').innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status"></div>
        </div>
    `;
    
    performanceModal.show();
    
    fetch(`/branch-manager/staff/${staffId}/performance`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const staff = data.staff;
                document.getElementById('performanceContent').innerHTML = `
                    <div class="row mb-4">
                        <div class="col-md-4 text-center">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h3 class="text-primary">RM ${parseFloat(data.totalSales).toFixed(2)}</h3>
                                    <small>Total Sales</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h3 class="text-success">${data.totalTransactions}</h3>
                                    <small>Transactions</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h3 class="text-info">RM ${parseFloat(data.avgTransaction).toFixed(2)}</h3>
                                    <small>Avg. Transaction</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <h6>Sales Trend (Last 7 Days)</h6>
                            <canvas id="staffSalesChart"></canvas>
                        </div>
                    </div>
                `;
                
                // Create chart
                const ctx = document.getElementById('staffSalesChart');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.chartData.labels,
                        datasets: [{
                            label: 'Daily Sales',
                            data: data.chartData.values,
                            borderColor: '#667eea',
                            backgroundColor: 'rgba(102, 126, 234, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'RM ' + value;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('performanceContent').innerHTML = `
                <div class="alert alert-danger">Failed to load performance data</div>
            `;
        });
}

// View staff schedule
function viewStaffSchedule(staffId) {
    document.getElementById('scheduleContent').innerHTML = `
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Staff scheduling feature coming soon!
        </div>
    `;
    
    scheduleModal.show();
}
</script>
@endpush
@endsection