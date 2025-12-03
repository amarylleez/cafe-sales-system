@extends('layouts.hq-admin')

@section('page-title', 'HQ Admin Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="mb-1">Welcome, {{ auth()->user()->name }}!</h4>
                    <p class="text-muted mb-0">
                        <i class="bi bi-building"></i> Managing {{ $totalBranches }} Branches
                        <span class="mx-2">|</span>
                        <i class="bi bi-calendar"></i> {{ now()->format('l, F d, Y') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm" style="background: linear-gradient(135deg, #423A8E 0%, #00CCCD 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="opacity-75">Total Sales (All Branches)</small>
                            <h3 class="mb-0 mt-2">RM {{ number_format($totalSales, 2) }}</h3>
                        </div>
                        <div>
                            <i class="bi bi-cash-stack" style="font-size: 2.5rem; opacity: 0.5;"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small>This Month</small>
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
                        <small>This Month</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm" style="background: linear-gradient(135deg, #423A8E 0%, #00CCCD 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="opacity-75">Active Branches</small>
                            <h3 class="mb-0 mt-2">{{ $activeBranches }}</h3>
                        </div>
                        <div>
                            <i class="bi bi-shop" style="font-size: 2.5rem; opacity: 0.5;"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small>Out of {{ $totalBranches }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm" style="background: linear-gradient(135deg, #423A8E 0%, #00CCCD 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="opacity-75">Total Staff</small>
                            <h3 class="mb-0 mt-2">{{ $totalStaff }}</h3>
                        </div>
                        <div>
                            <i class="bi bi-people" style="font-size: 2.5rem; opacity: 0.5;"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small>Across All Branches</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Sales Across Branches & Sales Variance -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-graph-up"></i> Total Sales Across Branches (This Month)</h5>
                </div>
                <div class="card-body">
                    <canvas id="branchSalesChart" height="80"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-activity"></i> Sales Variance</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h6 class="text-muted">vs Last Month</h6>
                        <h2 class="mb-0 {{ $salesVariance >= 0 ? 'text-success' : 'text-danger' }}">
                            <i class="bi bi-{{ $salesVariance >= 0 ? 'arrow-up' : 'arrow-down' }}-circle-fill"></i>
                            {{ number_format(abs($salesVariance), 1) }}%
                        </h2>
                    </div>

                    <div class="alert alert-{{ $salesVariance >= 0 ? 'success' : 'danger' }}">
                        @if($salesVariance >= 0)
                        <i class="bi bi-check-circle"></i> <strong>Great!</strong> Sales increased by RM {{ number_format($salesVarianceAmount, 2) }} compared to last month.
                        @else
                        <i class="bi bi-exclamation-triangle"></i> <strong>Attention!</strong> Sales decreased by RM {{ number_format(abs($salesVarianceAmount), 2) }} compared to last month.
                        @endif
                    </div>

                    <div class="mt-3">
                        <small class="text-muted">Last Month Sales:</small>
                        <h6>RM {{ number_format($lastMonthSales, 2) }}</h6>
                        <small class="text-muted">This Month Sales:</small>
                        <h6>RM {{ number_format($totalSales, 2) }}</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Performing Branch -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-trophy-fill"></i> Top Performing Branch</h5>
                </div>
                <div class="card-body">
                    @if($topBranch)
                    <div class="row align-items-center">
                        <div class="col-md-3 text-center">
                            <div class="display-1 text-success">
                                <i class="bi bi-award-fill"></i>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h3>{{ $topBranch->name ?? 'N/A' }}</h3>
                            <p class="mb-2"><i class="bi bi-geo-alt"></i> {{ $topBranch->address ?? '—' }}</p>
                            <div class="row mt-3">
                                <div class="col-6">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h4 class="text-success mb-0">RM {{ number_format($topBranch->sales, 2) }}</h4>
                                            <small class="text-muted">Total Sales</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h4 class="text-primary mb-0">{{ number_format($topBranch->transactions ?? 0) }}</h4>
                                            <small class="text-muted">Transactions</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="text-muted">Branch Manager</h6>
                                    <div class="user-avatar mx-auto mb-2" style="width: 60px; height: 60px; font-size: 1.5rem; background: linear-gradient(135deg, #423A8E 0%, #00CCCD 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                                        {{ substr($topBranch->manager->name ?? 'N', 0, 1) }}
                                    </div>
                                    <strong>{{ $topBranch->manager->name ?? 'Not Assigned' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> No branch performance data available yet. Add branches and sales to see insights here.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Branch Performance Summary -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-building"></i> All Branches Performance Summary</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Rank</th>
                                    <th>Branch</th>
                                    <th>Manager</th>
                                    <th>Sales (This Month)</th>
                                    <th>Transactions</th>
                                    <th>Avg. Transaction</th>
                                    <th>Performance</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($branchesPerformance as $branch)
                                @php
                                    $avgTransaction = $branch->transactions > 0 ? $branch->sales / $branch->transactions : 0;
                                    $performance = $totalSales > 0 ? ($branch->sales / $totalSales) * 100 : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <span class="badge bg-{{ $loop->iteration === 1 ? 'success' : ($loop->iteration === 2 ? 'info' : 'secondary') }} fs-6">
                                            #{{ $loop->iteration }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>{{ $branch->name }}</strong>
                                        <br><small class="text-muted">{{ $branch->address }}</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar me-2" style="width: 30px; height: 30px; font-size: 0.9rem; background: linear-gradient(135deg, #423A8E 0%, #00CCCD 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                                                {{ substr($branch->manager->name ?? 'N', 0, 1) }}
                                            </div>
                                            {{ $branch->manager->name ?? 'Not Assigned' }}
                                        </div>
                                    </td>
                                    <td><strong class="text-primary">RM {{ number_format($branch->sales, 2) }}</strong></td>
                                    <td>{{ number_format($branch->transactions) }}</td>
                                    <td>RM {{ number_format($avgTransaction, 2) }}</td>
                                    <td>
                                        <div class="progress" style="height: 20px; min-width: 100px;">
                                            <div class="progress-bar bg-{{ $performance >= 40 ? 'success' : ($performance >= 30 ? 'info' : 'warning') }}" 
                                                 style="width: {{ $performance }}%;">
                                                {{ number_format($performance, 1) }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('hq-admin.analytics.branch', $branch->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-graph-up"></i> View Analytics
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Branch Sales Comparison Chart
    const branchSalesData = @json($branchSalesData);
    const ctx = document.getElementById('branchSalesChart');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: branchSalesData.labels,
            datasets: [{
                label: 'Sales (RM)',
                data: branchSalesData.values,
                backgroundColor: [
                    'rgba(66, 58, 142, 0.8)',
                    'rgba(0, 204, 205, 0.8)',
                    'rgba(246, 236, 227, 0.8)'
                ],
                borderColor: [
                    'rgba(66, 58, 142, 1)',
                    'rgba(2, 183, 183, 1)',
                    'rgba(220, 200, 180, 1)'
                ],
                borderWidth: [2, 2, 2],
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
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
</script>
@endpush
@endsection


