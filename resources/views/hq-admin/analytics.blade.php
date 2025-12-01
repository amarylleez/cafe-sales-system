@extends('layouts.hq-admin')

@section('page-title', 'Analytics')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header" style="background: linear-gradient(135deg, #D35400 0%, #E67E22 100%);">
                    <h5 class="mb-0 text-white">
                        <i class="bi bi-graph-up-arrow"></i> Branch Performance Analytics
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">Analyze and compare branch performance across all locations.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Branch Performance Comparison (Monthly Sales) -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-bar-chart-line"></i> Monthly Sales Comparison (Last 6 Months)</h5>
                </div>
                <div class="card-body">
                    <canvas id="branchComparisonChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Performing Branches -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-trophy"></i> Top Performing Branches</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        @foreach($topBranches as $index => $branch)
                        <div class="col-md-4">
                            <div class="card h-100 border-{{ $index === 0 ? 'success' : ($index === 1 ? 'info' : 'warning') }}">
                                <div class="card-header bg-{{ $index === 0 ? 'success' : ($index === 1 ? 'info' : 'warning') }} text-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">
                                            @if($index === 0)
                                            <i class="bi bi-trophy-fill"></i> 1st Place
                                            @elseif($index === 1)
                                            <i class="bi bi-award-fill"></i> 2nd Place
                                            @else
                                            <i class="bi bi-star-fill"></i> 3rd Place
                                            @endif
                                        </h6>
                                        <span class="badge bg-white text-dark">{{ number_format($branch->percentage, 1) }}%</span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h5>{{ $branch->name }}</h5>
                                    <p class="text-muted small mb-3">{{ $branch->address }}</p>
                                    
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small>Total Sales</small>
                                            <strong class="text-primary">RM {{ number_format($branch->sales, 2) }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <small>Transactions</small>
                                            <strong>{{ number_format($branch->transactions) }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <small>Avg. Transaction</small>
                                            <strong>RM {{ number_format($branch->avgTransaction, 2) }}</strong>
                                        </div>
                                    </div>

                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <small class="text-muted">Branch Manager</small>
                                            <div class="user-avatar mx-auto my-2" style="width: 50px; height: 50px; font-size: 1.2rem; background: linear-gradient(135deg, #D35400 0%, #E67E22 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                                                {{ substr($branch->manager->name ?? 'N', 0, 1) }}
                                            </div>
                                            <strong>{{ $branch->manager->name ?? 'Not Assigned' }}</strong>
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

    <!-- Detailed Branch Analysis -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-building"></i> Detailed Branch Analysis</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Branch</th>
                                    <th>This Month</th>
                                    <th>Last Month</th>
                                    <th>Growth</th>
                                    <th>Transactions</th>
                                    <th>Staff Count</th>
                                    <th>Avg. Sales/Staff</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($branchAnalysis as $branch)
                                <tr>
                                    <td>
                                        <strong>{{ $branch->name }}</strong>
                                        <br><small class="text-muted">{{ $branch->address }}</small>
                                    </td>
                                    <td><strong class="text-primary">RM {{ number_format($branch->currentMonth, 2) }}</strong></td>
                                    <td>RM {{ number_format($branch->lastMonth, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $branch->growth >= 0 ? 'success' : 'danger' }}">
                                            <i class="bi bi-{{ $branch->growth >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                            {{ number_format(abs($branch->growth), 1) }}%
                                        </span>
                                    </td>
                                    <td>{{ number_format($branch->transactions) }}</td>
                                    <td>{{ $branch->staffCount }}</td>
                                    <td>RM {{ number_format($branch->avgSalesPerStaff, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $branch->growth >= 10 ? 'success' : ($branch->growth >= 0 ? 'info' : 'warning') }}">
                                            @if($branch->growth >= 10)
                                            Excellent
                                            @elseif($branch->growth >= 0)
                                            Good
                                            @else
                                            Needs Improvement
                                            @endif
                                        </span>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Branch Comparison Chart (6 months)
    const comparisonData = @json($comparisonData);
    const ctx = document.getElementById('branchComparisonChart');
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: comparisonData.labels,
            datasets: comparisonData.datasets.map((dataset, index) => ({
                label: dataset.name,
                data: dataset.values,
                borderColor: ['#D35400', '#E67E22', '#C0392B'][index],
                backgroundColor: ['rgba(145, 118, 110, 0.1)', 'rgba(183, 167, 169, 0.1)', 'rgba(107, 87, 80, 0.1)'][index],
                tension: 0.4,
                fill: true
            }))
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
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
