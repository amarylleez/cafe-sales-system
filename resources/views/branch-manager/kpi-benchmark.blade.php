@extends('layouts.branch-manager')

@section('page-title', 'Benchmark')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header" style="background: linear-gradient(135deg, #423A8E 0%, #00CCCD 100%);">
                    <h5 class="mb-0 text-white">
                        <i class="bi bi-graph-up"></i> Benchmark Tracking
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">Monitor branch benchmarks and track staff performance against targets.</p>
                </div>
            </div>
        </div>
    </div>

    @if(isset($benchmark) && $benchmark)
    <!-- HQ Benchmark Targets -->
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="mb-3"><i class="bi bi-bullseye"></i> HQ Benchmark Targets</h5>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Branch Sales Target Card -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100" style="background: linear-gradient(135deg, #423A8E 0%, #00CCCD 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="opacity-75 mb-2">Branch Sales Target</h6>
                            <h2 class="mb-1">RM {{ number_format($benchmark->monthly_sales_target, 2) }}</h2>
                            <p class="mb-0 opacity-75">Monthly Target</p>
                        </div>
                        <div class="opacity-50">
                            <i class="bi bi-bullseye" style="font-size: 2.5rem;"></i>
                        </div>
                    </div>
                    
                    @php
                        $branchSalesPercentage = $benchmark->monthly_sales_target > 0 
                            ? min(($branchMonthlySales / $benchmark->monthly_sales_target) * 100, 100) 
                            : 0;
                    @endphp
                    
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Progress</span>
                            <span>{{ number_format($branchSalesPercentage, 1) }}%</span>
                        </div>
                        <div class="progress" style="height: 8px; background: rgba(255,255,255,0.3);">
                            <div class="progress-bar bg-white" style="width: {{ $branchSalesPercentage }}%;"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <small>RM {{ number_format($branchMonthlySales, 2) }}</small>
                            <small>RM {{ number_format($benchmark->monthly_sales_target, 2) }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Average Transaction Value Card -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100" style="background: linear-gradient(135deg, #423A8E 0%, #00CCCD 100%);">
                <div class="card-body text-white">
                    @php
                        $avgTransactionValue = $branchTransactionCount > 0 
                            ? $branchMonthlySales / $branchTransactionCount 
                            : 0;
                    @endphp
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="opacity-75 mb-2">Average Transaction</h6>
                            <h2 class="mb-1">RM {{ number_format($avgTransactionValue, 2) }}</h2>
                            <p class="mb-0 opacity-75">Per Transaction</p>
                        </div>
                        <div class="opacity-50">
                            <i class="bi bi-cash-stack" style="font-size: 2.5rem;"></i>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>This Month</span>
                            <span>{{ number_format($branchTransactionCount) }} sales</span>
                        </div>
                        <div class="progress" style="height: 8px; background: rgba(255,255,255,0.3);">
                            <div class="progress-bar bg-white" style="width: 100%;"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <small>Total: RM {{ number_format($branchMonthlySales, 2) }}</small>
                            <small>{{ number_format($branchTransactionCount) }} transactions</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Month-over-Month Growth Card -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100" style="background: linear-gradient(135deg, #423A8E 0%, #00CCCD 100%);">
                <div class="card-body text-white">
                    @php
                        $lastMonthSales = $monthlySalesData['values'][count($monthlySalesData['values']) - 2] ?? 0;
                        $currentMonthSales = $branchMonthlySales;
                        $growthPercentage = $lastMonthSales > 0 
                            ? (($currentMonthSales - $lastMonthSales) / $lastMonthSales) * 100 
                            : 0;
                        $isPositiveGrowth = $growthPercentage >= 0;
                    @endphp
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="opacity-75 mb-2">Month-over-Month</h6>
                            <h2 class="mb-1">
                                <i class="bi bi-{{ $isPositiveGrowth ? 'arrow-up' : 'arrow-down' }}"></i>
                                {{ $isPositiveGrowth ? '+' : '' }}{{ number_format($growthPercentage, 1) }}%
                            </h2>
                            <p class="mb-0 opacity-75">Sales Growth</p>
                        </div>
                        <div class="opacity-50">
                            <i class="bi bi-graph-up-arrow" style="font-size: 2.5rem;"></i>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>vs Last Month</span>
                            <span>RM {{ number_format(abs($currentMonthSales - $lastMonthSales), 2) }}</span>
                        </div>
                        <div class="progress" style="height: 8px; background: rgba(255,255,255,0.3);">
                            <div class="progress-bar {{ $isPositiveGrowth ? 'bg-white' : 'bg-warning' }}" style="width: {{ min(abs($growthPercentage), 100) }}%;"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <small>Last: RM {{ number_format($lastMonthSales, 2) }}</small>
                            <small>Now: RM {{ number_format($currentMonthSales, 2) }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> No benchmarks have been set by HQ Admin yet. Please contact your administrator.
            </div>
        </div>
    </div>
    @endif

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

    <!-- Staff Performance Tracking -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-people"></i> Staff Performance Tracking</h5>
                    @if(isset($benchmark) && $benchmark)
                    <span class="badge bg-info">Target: RM {{ number_format($benchmark->staff_sales_target, 2) }} per staff</span>
                    @endif
                </div>
                <div class="card-body">
                    @if($staffKpis->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Staff</th>
                                    <th>Monthly Sales</th>
                                    <th>Target</th>
                                    <th>Transactions</th>
                                    <th>Progress</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($staffKpis->sortByDesc('sales') as $staffKpi)
                                @php
                                    $avgTransaction = $staffKpi['transactions'] > 0 ? $staffKpi['sales'] / $staffKpi['transactions'] : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar me-2" style="width: 35px; height: 35px; font-size: 1rem; background: linear-gradient(135deg, #423A8E 0%, #00CCCD 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                                                {{ substr($staffKpi['staff']->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $staffKpi['staff']->name }}</div>
                                                <small class="text-muted">{{ $staffKpi['staff']->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><strong class="text-primary">RM {{ number_format($staffKpi['sales'], 2) }}</strong></td>
                                    <td>
                                        @if($staffKpi['target'] > 0)
                                        <span class="text-muted">RM {{ number_format($staffKpi['target'], 2) }}</span>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ number_format($staffKpi['transactions']) }}
                                        <br><small class="text-muted">Avg: RM {{ number_format($avgTransaction, 2) }}</small>
                                    </td>
                                    <td style="min-width: 150px;">
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-{{ $staffKpi['progress'] >= 100 ? 'success' : ($staffKpi['progress'] >= 75 ? 'info' : ($staffKpi['progress'] >= 50 ? 'warning' : 'danger')) }}" 
                                                 style="width: {{ $staffKpi['progress'] }}%;">
                                                {{ number_format($staffKpi['progress'], 1) }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($staffKpi['progress'] >= 100)
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Target Met</span>
                                        @elseif($staffKpi['progress'] >= 75)
                                        <span class="badge bg-info"><i class="bi bi-arrow-up"></i> On Track</span>
                                        @elseif($staffKpi['progress'] >= 50)
                                        <span class="badge bg-warning text-dark"><i class="bi bi-exclamation"></i> Needs Effort</span>
                                        @else
                                        <span class="badge bg-danger"><i class="bi bi-exclamation-triangle"></i> Behind</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No staff members found in this branch.
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
    // Monthly Sales Comparison Chart
    const monthlySalesData = @json($monthlySalesData);
    const ctx = document.getElementById('monthlySalesChart');
    
    @if(isset($benchmark) && $benchmark)
    const targetLine = {{ $benchmark->monthly_sales_target }};
    @else
    const targetLine = 0;
    @endif
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: monthlySalesData.labels,
            datasets: [
                {
                    label: 'Monthly Sales (RM)',
                    data: monthlySalesData.values,
                    backgroundColor: 'rgba(66, 58, 142, 0.8)',
                    borderColor: '#423A8E',
                    borderWidth: 2,
                    borderRadius: 5
                },
                @if(isset($benchmark) && $benchmark)
                {
                    label: 'Target (RM)',
                    data: Array(monthlySalesData.labels.length).fill(targetLine),
                    type: 'line',
                    borderColor: '#00CCCD',
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


