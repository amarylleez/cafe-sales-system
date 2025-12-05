@extends('layouts.branch-manager')

@section('page-title', 'Branch Manager Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="mb-1">Welcome, {{ auth()->user()->name }}!</h4>
                    <p class="text-muted mb-0">
                        <i class="bi bi-building"></i> Managing: {{ $branch->name }}
                        <br>
                        <small>{{ $branch->address }}</small>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales This Week -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm" style="background: linear-gradient(135deg, #423A8E 0%, #00CCCD 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="opacity-75">This Week Sales</small>
                            <h3 class="mb-0 mt-2">RM {{ number_format($weekSales, 2) }}</h3>
                        </div>
                        <div>
                            <i class="bi bi-currency-dollar" style="font-size: 2.5rem; opacity: 0.5;"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small>
                            <i class="bi bi-{{ $weekGrowth >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                            {{ abs($weekGrowth) }}% from last week
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm" style="background: linear-gradient(135deg, #423A8E 0%, #00CCCD 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="opacity-75">Monthly Sales</small>
                            <h3 class="mb-0 mt-2">RM {{ number_format($monthSales, 2) }}</h3>
                        </div>
                        <div>
                            <i class="bi bi-graph-up-arrow" style="font-size: 2.5rem; opacity: 0.5;"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small>Target: RM {{ number_format($monthlyTarget, 2) }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm" style="background: linear-gradient(135deg, #423A8E 0%, #00CCCD 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="opacity-75">Total Transactions</small>
                            <h3 class="mb-0 mt-2">{{ number_format($totalTransactions) }}</h3>
                        </div>
                        <div>
                            <i class="bi bi-receipt" style="font-size: 2.5rem; opacity: 0.5;"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small>This month</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm" style="background: linear-gradient(135deg, #423A8E 0%, #00CCCD 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="opacity-75">Active Staff</small>
                            <h3 class="mb-0 mt-2">{{ $activeStaff }}</h3>
                        </div>
                        <div>
                            <i class="bi bi-people" style="font-size: 2.5rem; opacity: 0.5;"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small>Team members</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Trend Chart -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-graph-up"></i> Sales Trend (Last 7 Days)</h5>
                </div>
                <div class="card-body">
                    <canvas id="salesTrendChart" height="80"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Sales by Category</h5>
                </div>
                <div class="card-body">
                    <canvas id="salesCategoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Branch Performance Comparison -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-bar-chart-line"></i> Branch Performance Comparison (Monthly Sales)</h5>
                </div>
                <div class="card-body">
                    <canvas id="branchComparisonChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('branch-manager.sales-report') }}" class="btn btn-outline-primary">
                            <i class="bi bi-file-earmark-text"></i> View Sales Reports
                        </a>
                        <a href="{{ route('branch-manager.performance') }}" class="btn btn-outline-success">
                            <i class="bi bi-graph-up"></i> Check Performance
                        </a>
                        <a href="{{ route('branch-manager.team-overview') }}" class="btn btn-outline-info">
                            <i class="bi bi-people"></i> Manage Team
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-exclamation-circle"></i> Alerts & Reminders</h5>
                </div>
                <div class="card-body">
                    @if($pendingReports > 0)
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> You have <strong>{{ $pendingReports }}</strong> pending sales report(s) to review.
                    </div>
                    @endif
                    @if($lowStockItems > 0)
                    <div class="alert alert-danger">
                        <i class="bi bi-box-seam"></i> <strong>{{ $lowStockItems }}</strong> item(s) are running low on stock!
                    </div>
                    @endif
                    @if($pendingReports == 0 && $lowStockItems == 0)
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> All caught up! No pending tasks.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sales Trend Chart
    const salesTrendData = @json($salesTrendData);
    const trendCtx = document.getElementById('salesTrendChart');
    
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: salesTrendData.labels,
            datasets: [{
                label: 'Daily Sales (RM)',
                data: salesTrendData.values,
                borderColor: '#423A8E',
                backgroundColor: 'rgba(66, 58, 142, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
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

    // Sales by Category Chart
    const categoryData = @json($categoryData);
    const categoryCtx = document.getElementById('salesCategoryChart');
    
    new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: categoryData.labels,
            datasets: [{
                data: categoryData.values,
                backgroundColor: [
                    '#423A8E',
                    '#00CCCD',
                    '#F8F9FA',
                    '#3d3581',
                    '#FFC107'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Branch Comparison Chart
    const branchData = @json($branchComparisonData);
    const comparisonCtx = document.getElementById('branchComparisonChart');
    
    new Chart(comparisonCtx, {
        type: 'bar',
        data: {
            labels: branchData.labels,
            datasets: [{
                label: 'Monthly Sales (RM)',
                data: branchData.values,
                backgroundColor: branchData.colors,
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
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
</script>
@endpush
@endsection


