@extends('layouts.hq-admin')

@section('page-title', 'Branch Analytics - ' . $branch->name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header" style="background: linear-gradient(135deg, #D35400 0%, #E67E22 100%);">
                    <h5 class="mb-0 text-white">
                        <i class="bi bi-graph-up-arrow"></i> Branch Analytics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-1">{{ $branch->name }}</h4>
                            <p class="text-muted mb-0">
                                <i class="bi bi-geo-alt"></i> {{ $branch->address }}
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            @if($branchManager)
                            <div class="d-flex align-items-center justify-content-end">
                                <div class="text-end me-3">
                                    <small class="text-muted d-block">Branch Manager</small>
                                    <strong>{{ $branchManager->name }}</strong>
                                </div>
                                <div class="user-avatar" style="width: 50px; height: 50px; font-size: 1.2rem; background: linear-gradient(135deg, #D35400 0%, #E67E22 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                                    {{ substr($branchManager->name, 0, 1) }}
                                </div>
                            </div>
                            @else
                            <span class="badge bg-secondary">No Manager Assigned</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm" style="background: linear-gradient(135deg, #D35400 0%, #E67E22 100%);">
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
                            {{ number_format(abs($weekGrowth), 1) }}% from last week
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm" style="background: linear-gradient(135deg, #D35400 0%, #E67E22 100%);">
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
            <div class="card shadow-sm" style="background: linear-gradient(135deg, #D35400 0%, #E67E22 100%);">
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
            <div class="card shadow-sm" style="background: linear-gradient(135deg, #D35400 0%, #E67E22 100%);">
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

    <!-- Sales Trend & Sales by Category -->
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

    <!-- Monthly Sales Performance -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-bar-chart-line"></i> Monthly Sales Performance (Last 6 Months)</h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlySalesChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Summary -->
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-speedometer2"></i> Performance Summary</h5>
                </div>
                <div class="card-body">
                    @php
                        $targetProgress = $monthlyTarget > 0 ? min(($monthSales / $monthlyTarget) * 100, 100) : 0;
                    @endphp
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Monthly Target Progress</span>
                            <strong>{{ number_format($targetProgress, 1) }}%</strong>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-{{ $targetProgress >= 100 ? 'success' : ($targetProgress >= 50 ? 'info' : 'warning') }}" 
                                 role="progressbar" 
                                 style="width: {{ $targetProgress }}%">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <small class="text-muted">RM {{ number_format($monthSales, 2) }}</small>
                            <small class="text-muted">Target: RM {{ number_format($monthlyTarget, 2) }}</small>
                        </div>
                    </div>

                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h4 class="text-primary mb-0">RM {{ number_format($totalTransactions > 0 ? $monthSales / $totalTransactions : 0, 2) }}</h4>
                                <small class="text-muted">Avg. Transaction</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h4 class="text-success mb-0">RM {{ number_format($activeStaff > 0 ? $monthSales / $activeStaff : 0, 2) }}</h4>
                                <small class="text-muted">Sales per Staff</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Branch Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><i class="bi bi-building text-muted"></i> Branch Name</td>
                            <td><strong>{{ $branch->name }}</strong></td>
                        </tr>
                        <tr>
                            <td><i class="bi bi-geo-alt text-muted"></i> Address</td>
                            <td>{{ $branch->address }}</td>
                        </tr>
                        <tr>
                            <td><i class="bi bi-person text-muted"></i> Manager</td>
                            <td>{{ $branchManager->name ?? 'Not Assigned' }}</td>
                        </tr>
                        <tr>
                            <td><i class="bi bi-people text-muted"></i> Staff Count</td>
                            <td>{{ $activeStaff }} members</td>
                        </tr>
                        <tr>
                            <td><i class="bi bi-check-circle text-muted"></i> Status</td>
                            <td>
                                <span class="badge bg-{{ $branch->is_active ? 'success' : 'secondary' }}">
                                    {{ $branch->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sales Trend Chart (Last 7 Days)
    const salesTrendData = @json($salesTrendData);
    const trendCtx = document.getElementById('salesTrendChart');
    
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: salesTrendData.labels,
            datasets: [{
                label: 'Daily Sales (RM)',
                data: salesTrendData.values,
                borderColor: '#D35400',
                backgroundColor: 'rgba(145, 118, 110, 0.1)',
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
                    '#D35400',
                    '#E67E22',
                    '#FDF6E3',
                    '#C0392B',
                    '#F39C12',
                    '#E74C3C',
                    '#FEF5E7'
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

    // Monthly Sales Performance Chart
    const monthlySalesData = @json($monthlySalesData);
    const monthlyCtx = document.getElementById('monthlySalesChart');
    
    @if(isset($benchmark) && $benchmark)
    const targetLine = {{ $benchmark->monthly_sales_target }};
    @else
    const targetLine = 0;
    @endif
    
    new Chart(monthlyCtx, {
        type: 'bar',
        data: {
            labels: monthlySalesData.labels,
            datasets: [
                {
                    label: 'Monthly Sales (RM)',
                    data: monthlySalesData.values,
                    backgroundColor: 'rgba(145, 118, 110, 0.8)',
                    borderColor: '#D35400',
                    borderWidth: 2,
                    borderRadius: 5
                },
                @if(isset($benchmark) && $benchmark)
                {
                    label: 'HQ Target (RM)',
                    data: Array(monthlySalesData.labels.length).fill(targetLine),
                    type: 'line',
                    borderColor: '#E67E22',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    pointRadius: 0,
                    fill: false
                }
                @endif
            ]
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
                            return context.dataset.label + ': RM ' + context.parsed.y.toLocaleString();
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
</script>
@endpush
@endsection

