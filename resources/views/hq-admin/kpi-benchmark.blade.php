@extends('layouts.hq-admin')

@section('page-title', 'KPI & Benchmark')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-white">
                            <i class="bi bi-graph-up"></i> KPI & Benchmark Management
                        </h5>
                        <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#setBenchmarkModal">
                            <i class="bi bi-plus-circle"></i> Set New Benchmark
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">Set sales performance benchmarks and monitor KPI achievements across all branches.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Benchmarks -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="opacity-75">Monthly Sales Target</small>
                            <h3 class="mb-0 mt-2">RM {{ number_format($monthlyBenchmark, 2) }}</h3>
                        </div>
                        <div>
                            <i class="bi bi-bullseye" style="font-size: 2.5rem; opacity: 0.5;"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small>Per Branch</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="opacity-75">Transaction Target</small>
                            <h3 class="mb-0 mt-2">{{ number_format($transactionBenchmark) }}</h3>
                        </div>
                        <div>
                            <i class="bi bi-receipt" style="font-size: 2.5rem; opacity: 0.5;"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small>Monthly Per Branch</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="opacity-75">Staff Target</small>
                            <h3 class="mb-0 mt-2">RM {{ number_format($staffBenchmark, 2) }}</h3>
                        </div>
                        <div>
                            <i class="bi bi-person-badge" style="font-size: 2.5rem; opacity: 0.5;"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small>Monthly Per Staff</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Branch KPI Achievement -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-trophy"></i> Branch KPI Achievement</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        @foreach($branchKPIs as $branch)
                        @php
                            $salesProgress = $monthlyBenchmark > 0 ? ($branch->currentSales / $monthlyBenchmark) * 100 : 0;
                            $transactionProgress = $transactionBenchmark > 0 ? ($branch->transactions / $transactionBenchmark) * 100 : 0;
                            $overallProgress = ($salesProgress + $transactionProgress) / 2;
                        @endphp
                        <div class="col-md-4">
                            <div class="card h-100 border-{{ $overallProgress >= 100 ? 'success' : ($overallProgress >= 75 ? 'info' : 'warning') }}">
                                <div class="card-header bg-{{ $overallProgress >= 100 ? 'success' : ($overallProgress >= 75 ? 'info' : 'warning') }} text-white">
                                    <h6 class="mb-0">{{ $branch->name }}</h6>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <div class="position-relative d-inline-block">
                                            <svg width="120" height="120">
                                                <circle cx="60" cy="60" r="50" fill="none" stroke="#e9ecef" stroke-width="10"/>
                                                <circle cx="60" cy="60" r="50" fill="none" 
                                                    stroke="{{ $overallProgress >= 100 ? '#28a745' : ($overallProgress >= 75 ? '#17a2b8' : '#ffc107') }}" 
                                                    stroke-width="10"
                                                    stroke-dasharray="{{ 2 * 3.14159 * 50 }}"
                                                    stroke-dashoffset="{{ 2 * 3.14159 * 50 * (1 - $overallProgress / 100) }}"
                                                    transform="rotate(-90 60 60)"
                                                    stroke-linecap="round"/>
                                            </svg>
                                            <div class="position-absolute top-50 start-50 translate-middle">
                                                <h4 class="mb-0">{{ number_format($overallProgress, 1) }}%</h4>
                                                <small class="text-muted">Overall</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small>Sales Target</small>
                                            <small class="text-primary">{{ number_format($salesProgress, 1) }}%</small>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-primary" style="width: {{ min($salesProgress, 100) }}%"></div>
                                        </div>
                                        <div class="d-flex justify-content-between mt-1">
                                            <small class="text-muted">RM {{ number_format($branch->currentSales, 2) }}</small>
                                            <small class="text-muted">RM {{ number_format($monthlyBenchmark, 2) }}</small>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small>Transaction Target</small>
                                            <small class="text-info">{{ number_format($transactionProgress, 1) }}%</small>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-info" style="width: {{ min($transactionProgress, 100) }}%"></div>
                                        </div>
                                        <div class="d-flex justify-content-between mt-1">
                                            <small class="text-muted">{{ number_format($branch->transactions) }}</small>
                                            <small class="text-muted">{{ number_format($transactionBenchmark) }}</small>
                                        </div>
                                    </div>

                                    <div class="card bg-light">
                                        <div class="card-body p-2">
                                            <small class="text-muted">Status:</small>
                                            <strong class="text-{{ $overallProgress >= 100 ? 'success' : ($overallProgress >= 75 ? 'info' : 'warning') }}">
                                                @if($overallProgress >= 100)
                                                <i class="bi bi-check-circle-fill"></i> Target Achieved
                                                @elseif($overallProgress >= 75)
                                                <i class="bi bi-info-circle-fill"></i> On Track
                                                @else
                                                <i class="bi bi-exclamation-circle-fill"></i> Needs Attention
                                                @endif
                                            </strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Staff KPI Comparison -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-people"></i> Staff KPI Achievement Comparison</h5>
                </div>
                <div class="card-body">
                    <canvas id="staffKPIChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-table"></i> Detailed Staff Performance</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Staff Name</th>
                                    <th>Branch</th>
                                    <th>Target</th>
                                    <th>Current Sales</th>
                                    <th>Achievement</th>
                                    <th>Progress</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($staffPerformance as $staff)
                                @php
                                    $progress = $staffBenchmark > 0 ? ($staff->currentSales / $staffBenchmark) * 100 : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar me-2" style="width: 35px; height: 35px; font-size: 1rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                                                {{ substr($staff->name, 0, 1) }}
                                            </div>
                                            {{ $staff->name }}
                                        </div>
                                    </td>
                                    <td>{{ $staff->branch->name ?? 'N/A' }}</td>
                                    <td>RM {{ number_format($staffBenchmark, 2) }}</td>
                                    <td><strong class="text-primary">RM {{ number_format($staff->currentSales, 2) }}</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $progress >= 100 ? 'success' : ($progress >= 75 ? 'info' : 'warning') }}">
                                            {{ number_format($progress, 1) }}%
                                        </span>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px; min-width: 150px;">
                                            <div class="progress-bar bg-{{ $progress >= 100 ? 'success' : ($progress >= 75 ? 'info' : 'warning') }}" 
                                                 style="width: {{ min($progress, 100) }}%;">
                                                {{ number_format($progress, 1) }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($progress >= 100)
                                        <span class="badge bg-success"><i class="bi bi-trophy-fill"></i> Achieved</span>
                                        @elseif($progress >= 75)
                                        <span class="badge bg-info"><i class="bi bi-graph-up"></i> On Track</span>
                                        @else
                                        <span class="badge bg-warning"><i class="bi bi-exclamation-triangle"></i> Below Target</span>
                                        @endif
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

<!-- Set Benchmark Modal -->
<div class="modal fade" id="setBenchmarkModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Set Sales Benchmarks</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('hq-admin.kpi-benchmark.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Monthly Sales Target (Per Branch) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">RM</span>
                            <input type="number" class="form-control" name="monthly_benchmark" step="0.01" value="{{ $monthlyBenchmark }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Monthly Transaction Target (Per Branch) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="transaction_benchmark" value="{{ $transactionBenchmark }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Monthly Sales Target (Per Staff) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">RM</span>
                            <input type="number" class="form-control" name="staff_benchmark" step="0.01" value="{{ $staffBenchmark }}" required>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> These benchmarks will be applied company-wide starting next month.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Set Benchmarks
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Staff KPI Comparison Chart
    const staffKPIData = @json($staffKPIChartData);
    const ctx = document.getElementById('staffKPIChart');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: staffKPIData.labels,
            datasets: [{
                label: 'Current Sales (RM)',
                data: staffKPIData.sales,
                backgroundColor: 'rgba(102, 126, 234, 0.8)',
                borderColor: '#667eea',
                borderWidth: 2,
                borderRadius: 5
            }, {
                label: 'Target (RM)',
                data: staffKPIData.targets,
                backgroundColor: 'rgba(240, 147, 251, 0.3)',
                borderColor: '#f093fb',
                borderWidth: 2,
                borderRadius: 5,
                borderDash: [5, 5]
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