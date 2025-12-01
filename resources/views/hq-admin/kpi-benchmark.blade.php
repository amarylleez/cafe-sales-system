@extends('layouts.hq-admin')

@section('page-title', 'KPI & Benchmark')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header" style="background: linear-gradient(135deg, #D35400 0%, #E67E22 100%);">
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
        <div class="col-md-6">
            <div class="card shadow-sm" style="background: linear-gradient(135deg, #D35400 0%, #E67E22 100%);">
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

        <div class="col-md-6">
            <div class="card shadow-sm" style="background: linear-gradient(135deg, #D35400 0%, #E67E22 100%);">
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
                        @endphp
                        <div class="col-md-4">
                            <div class="card h-100 border-{{ $salesProgress >= 100 ? 'success' : ($salesProgress >= 75 ? 'info' : 'warning') }}">
                                <div class="card-header bg-{{ $salesProgress >= 100 ? 'success' : ($salesProgress >= 75 ? 'info' : 'warning') }} text-white">
                                    <h6 class="mb-0">{{ $branch->name }}</h6>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-4">
                                        <div class="position-relative d-inline-block">
                                            <svg width="140" height="140">
                                                <circle cx="70" cy="70" r="60" fill="none" stroke="#e9ecef" stroke-width="12"/>
                                                <circle cx="70" cy="70" r="60" fill="none" 
                                                    stroke="{{ $salesProgress >= 100 ? '#28a745' : ($salesProgress >= 75 ? '#17a2b8' : '#ffc107') }}" 
                                                    stroke-width="12"
                                                    stroke-dasharray="{{ 2 * 3.14159 * 60 }}"
                                                    stroke-dashoffset="{{ 2 * 3.14159 * 60 * (1 - min($salesProgress, 100) / 100) }}"
                                                    transform="rotate(-90 70 70)"
                                                    stroke-linecap="round"/>
                                            </svg>
                                            <div class="position-absolute top-50 start-50 translate-middle">
                                                <h3 class="mb-0 fw-bold">{{ number_format($salesProgress, 1) }}%</h3>
                                                <small class="text-muted">Sales Target</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-center mb-3">
                                        <div class="row">
                                            <div class="col-6">
                                                <small class="text-muted d-block">Current Sales</small>
                                                <strong class="text-primary">RM {{ number_format($branch->currentSales, 2) }}</strong>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted d-block">Target</small>
                                                <strong>RM {{ number_format($monthlyBenchmark, 2) }}</strong>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card bg-light">
                                        <div class="card-body p-2 text-center">
                                            <small class="text-muted">Status:</small>
                                            <strong class="text-{{ $salesProgress >= 100 ? 'success' : ($salesProgress >= 75 ? 'info' : 'warning') }}">
                                                @if($salesProgress >= 100)
                                                <i class="bi bi-check-circle-fill"></i> Target Achieved
                                                @elseif($salesProgress >= 75)
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
                                            <div class="user-avatar me-2" style="width: 35px; height: 35px; font-size: 1rem; background: linear-gradient(135deg, #D35400 0%, #E67E22 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
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
                        <label class="form-label">Monthly Sales Target (Per Staff) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">RM</span>
                            <input type="number" class="form-control" name="staff_benchmark" step="0.01" value="{{ $staffBenchmark }}" required>
                        </div>
                    </div>
                    <div class="alert alert-success">
                        <i class="bi bi-lightning-fill"></i> These benchmarks will be applied immediately across all branches.
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
                backgroundColor: 'rgba(145, 118, 110, 0.8)',
                borderColor: '#D35400',
                borderWidth: 2,
                borderRadius: 5
            }, {
                label: 'Target (RM)',
                data: staffKPIData.targets,
                backgroundColor: 'rgba(183, 167, 169, 0.3)',
                borderColor: '#E67E22',
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
