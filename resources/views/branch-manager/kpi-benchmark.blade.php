@extends('layouts.branch-manager')

@section('page-title', 'KPI & Benchmark')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header" style="background: linear-gradient(135deg, #423A8E 0%, #00CCCD 100%);">
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

        <!-- Transaction Target Card -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100" style="background: linear-gradient(135deg, #423A8E 0%, #00CCCD 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="opacity-75 mb-2">Transaction Target</h6>
                            <h2 class="mb-1">{{ number_format($benchmark->transaction_target) }}</h2>
                            <p class="mb-0 opacity-75">Monthly Transactions</p>
                        </div>
                        <div class="opacity-50">
                            <i class="bi bi-receipt" style="font-size: 2.5rem;"></i>
                        </div>
                    </div>
                    
                    @php
                        $branchTxnPercentage = $benchmark->transaction_target > 0 
                            ? min(($branchTransactionCount / $benchmark->transaction_target) * 100, 100) 
                            : 0;
                    @endphp
                    
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Progress</span>
                            <span>{{ number_format($branchTxnPercentage, 1) }}%</span>
                        </div>
                        <div class="progress" style="height: 8px; background: rgba(255,255,255,0.3);">
                            <div class="progress-bar bg-white" style="width: {{ $branchTxnPercentage }}%;"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <small>{{ number_format($branchTransactionCount) }} completed</small>
                            <small>{{ number_format($benchmark->transaction_target) }} target</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Staff Target Card -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100" style="background: linear-gradient(135deg, #423A8E 0%, #00CCCD 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="opacity-75 mb-2">Staff Sales Target</h6>
                            <h2 class="mb-1">RM {{ number_format($benchmark->staff_sales_target, 2) }}</h2>
                            <p class="mb-0 opacity-75">Per Staff Monthly</p>
                        </div>
                        <div class="opacity-50">
                            <i class="bi bi-person-check" style="font-size: 2.5rem;"></i>
                        </div>
                    </div>
                    
                    @php
                        $staffCount = $staffKpis->count();
                        $staffMeetingTarget = $staffKpis->filter(fn($s) => $s['progress'] >= 100)->count();
                    @endphp
                    
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Staff Meeting Target</span>
                            <span>{{ $staffMeetingTarget }}/{{ $staffCount }}</span>
                        </div>
                        <div class="progress" style="height: 8px; background: rgba(255,255,255,0.3);">
                            <div class="progress-bar bg-white" style="width: {{ $staffCount > 0 ? ($staffMeetingTarget / $staffCount) * 100 : 0 }}%;"></div>
                        </div>
                        <div class="mt-2">
                            <small>{{ $staffMeetingTarget }} staff achieved target this month</small>
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
                        <i class="bi bi-info-circle"></i> No additional branch-specific KPIs set for this month.
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


