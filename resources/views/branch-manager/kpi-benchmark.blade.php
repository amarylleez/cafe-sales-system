@extends('layouts.branch-manager')

@section('page-title', 'KPI & Benchmark')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="mb-0 text-white">
                        <i class="bi bi-graph-up"></i> KPI & Benchmark Tracking
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">Monitor branch KPIs and track staff performance against targets.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Sales Comparison Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-bar-chart-line"></i> Monthly Sales Comparison (Last 6 Months)</h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlySalesChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Branch KPIs -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-bullseye"></i> Branch KPI Overview</h5>
                </div>
                <div class="card-body">
                    @if($kpis->count() > 0)
                        @foreach($kpis as $kpi)
                        @php
                            $currentProgress = 0;
                            foreach($kpi->progress as $p) {
                                $currentProgress += $p->daily_value;
                            }
                            $percentage = $kpi->target_value > 0 ? min(($currentProgress / $kpi->target_value) * 100, 100) : 0;
                            $isAchieved = $currentProgress >= $kpi->target_value;
                        @endphp
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <h6 class="mb-0">{{ $kpi->kpi_name }}</h6>
                                    <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $kpi->kpi_type)) }}</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-{{ $kpi->priority === 'critical' ? 'danger' : ($kpi->priority === 'high' ? 'warning' : 'info') }}">
                                        {{ ucfirst($kpi->priority) }}
                                    </span>
                                    @if($isAchieved)
                                    <span class="badge bg-success ms-2">
                                        <i class="bi bi-check-circle"></i> Achieved
                                    </span>
                                    @endif
                                    <div class="mt-1">
                                        <strong>{{ number_format($currentProgress, 2) }}</strong> / {{ number_format($kpi->target_value, 2) }}
                                    </div>
                                </div>
                            </div>
                            <div class="progress" style="height: 30px;">
                                <div class="progress-bar bg-{{ $percentage >= 100 ? 'success' : ($percentage >= 75 ? 'info' : ($percentage >= 50 ? 'warning' : 'danger')) }}" 
                                     style="width: {{ $percentage }}%;">
                                    <strong>{{ number_format($percentage, 1) }}%</strong>
                                </div>
                            </div>
                            @if($kpi->reward_amount || $kpi->penalty_amount)
                            <div class="row mt-2">
                                @if($kpi->reward_amount)
                                <div class="col-md-6">
                                    <small class="text-success">
                                        <i class="bi bi-gift"></i> Reward: RM {{ number_format($kpi->reward_amount, 2) }}
                                    </small>
                                </div>
                                @endif
                                @if($kpi->penalty_amount)
                                <div class="col-md-6">
                                    <small class="text-danger">
                                        <i class="bi bi-exclamation-triangle"></i> Penalty: RM {{ number_format($kpi->penalty_amount, 2) }}
                                    </small>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>
                        @endforeach
                    @else
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No KPIs set for this month.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Staff KPI Tracking -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-people"></i> Staff Performance Tracking</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Staff</th>
                                    <th>Monthly Sales</th>
                                    <th>Transactions</th>
                                    <th>Avg. Transaction</th>
                                    <th>Performance</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($staffKpis as $staffKpi)
                                @php
                                    $avgTransaction = $staffKpi['transactions'] > 0 ? $staffKpi['sales'] / $staffKpi['transactions'] : 0;
                                    // Calculate performance based on branch average
                                    $branchAvg = $staffKpis->avg('sales');
                                    $performance = $branchAvg > 0 ? ($staffKpi['sales'] / $branchAvg) * 100 : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar me-2" style="width: 35px; height: 35px; font-size: 1rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                                                {{ substr($staffKpi['staff']->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $staffKpi['staff']->name }}</div>
                                                <small class="text-muted">{{ $staffKpi['staff']->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><strong class="text-primary">RM {{ number_format($staffKpi['sales'], 2) }}</strong></td>
                                    <td>{{ number_format($staffKpi['transactions']) }}</td>
                                    <td>RM {{ number_format($avgTransaction, 2) }}</td>
                                    <td>
                                        <div class="progress" style="height: 20px; min-width: 100px;">
                                            <div class="progress-bar bg-{{ $performance >= 100 ? 'success' : ($performance >= 75 ? 'info' : 'warning') }}" 
                                                 style="width: {{ min($performance, 100) }}%;">
                                                {{ number_format($performance, 0) }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-outline-primary" onclick="viewStaffDetails({{ $staffKpi['staff']->id }})">
                                            <i class="bi bi-eye"></i> View Details
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Staff Details Modal -->
<div class="modal fade" id="staffDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Staff Performance Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="staffDetailsContent">
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
const staffDetailsModal = new bootstrap.Modal(document.getElementById('staffDetailsModal'));

document.addEventListener('DOMContentLoaded', function() {
    // Monthly Sales Comparison Chart
    const monthlySalesData = @json($monthlySalesData);
    const ctx = document.getElementById('monthlySalesChart');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: monthlySalesData.labels,
            datasets: [{
                label: 'Monthly Sales (RM)',
                data: monthlySalesData.values,
                backgroundColor: 'rgba(102, 126, 234, 0.8)',
                borderColor: '#667eea',
                borderWidth: 2,
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'RM ' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'RM ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
});

function viewStaffDetails(staffId) {
    document.getElementById('staffDetailsContent').innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status"></div>
        </div>
    `;
    
    staffDetailsModal.show();
    
    fetch(`/branch-manager/staff/${staffId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Display staff details
                document.getElementById('staffDetailsContent').innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Staff Information</h6>
                            <p><strong>Name:</strong> ${data.staff.name}</p>
                            <p><strong>Email:</strong> ${data.staff.email}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Performance Summary</h6>
                            <p><strong>Total Sales:</strong> RM ${data.totalSales.toFixed(2)}</p>
                            <p><strong>Transactions:</strong> ${data.transactions}</p>
                        </div>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('staffDetailsContent').innerHTML = `
                <div class="alert alert-danger">Failed to load staff details</div>
            `;
        });
}
</script>
@endpush
@endsection