@extends('layouts.hq-admin')

@section('page-title', 'Analytics')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #423A8E 0%, #00CCCD 100%);">
                    <h5 class="mb-0 text-white">
                        <i class="bi bi-graph-up-arrow"></i> Branch Performance Analytics
                    </h5>
                    <a href="{{ route('hq-admin.analytics.export-pdf', request()->query()) }}" class="btn btn-light btn-sm">
                        <i class="bi bi-file-earmark-pdf"></i> Export to PDF
                    </a>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">Analyze and compare branch performance across all locations.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Profit/Loss Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Revenue</p>
                            <h4 class="mb-0 text-primary">RM {{ number_format($totalRevenue, 2) }}</h4>
                        </div>
                        <div class="icon-box bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-currency-dollar text-primary fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Gross Profit</p>
                            <h4 class="mb-0 text-success">RM {{ number_format($grossProfit, 2) }}</h4>
                        </div>
                        <div class="icon-box bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-graph-up-arrow text-success fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1">Stock Loss</p>
                            <h4 class="mb-2 text-danger">RM {{ number_format($stockLoss, 2) }}</h4>
                            <div class="d-flex flex-column gap-1">
                                @if($expiredLoss > 0)
                                <span class="badge bg-danger" style="font-size: 0.7rem;">
                                    <i class="bi bi-calendar-x"></i> Expired: RM {{ number_format($expiredLoss, 2) }}
                                </span>
                                @endif
                                @if($rejectedSalesLoss > 0)
                                <span class="badge bg-danger" style="font-size: 0.7rem;">
                                    <i class="bi bi-x-circle"></i> Rejected: RM {{ number_format($rejectedSalesLoss, 2) }}
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="icon-box bg-danger bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-exclamation-triangle text-danger fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Profit Margin</p>
                            <h4 class="mb-0" style="color: #423A8E;">{{ number_format($profitMargin, 1) }}%</h4>
                        </div>
                        <div class="icon-box rounded-circle p-3" style="background: rgba(66, 58, 142, 0.1);">
                            <i class="bi bi-percent fs-4" style="color: #423A8E;"></i>
                        </div>
                    </div>
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
                                            <div class="user-avatar mx-auto my-2" style="width: 50px; height: 50px; font-size: 1.2rem; background: linear-gradient(135deg, #423A8E 0%, #00CCCD 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
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

    <!-- Branch Profit Analysis -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-cash-coin"></i> Branch Profit Analysis</h5>
                    <span class="badge bg-secondary">{{ $startDate->format('M d') }} - {{ $endDate->format('M d, Y') }}</span>
                </div>
                <div class="card-body">
                    <canvas id="branchProfitChart" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-star-fill text-warning"></i> Top Profitable Products</h5>
                </div>
                <div class="card-body">
                    @if($topProfitableProducts->count() > 0)
                        @foreach($topProfitableProducts as $index => $productData)
                        <div class="d-flex justify-content-between align-items-center {{ $index < $topProfitableProducts->count() - 1 ? 'mb-3 pb-3 border-bottom' : '' }}">
                            <div>
                                <strong>{{ $productData['product']->name ?? 'Unknown' }}</strong>
                                <br><small class="text-muted">{{ $productData['quantity_sold'] }} sold</small>
                            </div>
                            <div class="text-end">
                                <span class="text-success fw-bold">RM {{ number_format($productData['profit'], 2) }}</span>
                                <br><small class="text-muted">{{ $productData['revenue'] > 0 ? number_format(($productData['profit'] / $productData['revenue']) * 100, 1) : 0 }}% margin</small>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center mb-0">No sales data available</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Loss Visualizations -->
    <div class="row mb-4">
        <!-- Stock Loss Trend (30 Days) -->
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-graph-down text-danger"></i> Stock Loss Trend (Last 30 Days)</h5>
                </div>
                <div class="card-body">
                    <canvas id="stockLossTrendChart" height="100"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Stock Loss Breakdown Pie Chart -->
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-pie-chart-fill text-danger"></i> Loss Breakdown</h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <div class="flex-grow-1" style="position: relative; min-height: 200px;">
                        <canvas id="stockLossBreakdownChart"></canvas>
                    </div>
                    <div class="mt-3 pt-3 border-top">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-warning me-2" style="width: 12px; height: 12px;"></span>
                                <span>Expired Stock</span>
                            </div>
                            <strong class="text-warning">RM {{ number_format($expiredLoss, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-danger me-2" style="width: 12px; height: 12px;"></span>
                                <span>Rejected Sales</span>
                            </div>
                            <strong class="text-danger">RM {{ number_format($rejectedSalesLoss, 2) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Loss by Category & Branch -->
    <div class="row mb-4">
        <!-- Loss by Category -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-tags-fill text-danger"></i> Stock Loss by Category</h5>
                </div>
                <div class="card-body">
                    @if($stockLossByCategory->count() > 0)
                    <canvas id="stockLossByCategoryChart" height="150"></canvas>
                    @else
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle"></i> No stock loss data available for categories.
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Loss by Branch -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-building text-danger"></i> Stock Loss by Branch</h5>
                </div>
                <div class="card-body">
                    @if($stockLossByBranch->count() > 0)
                    <canvas id="stockLossByBranchChart" height="150"></canvas>
                    @else
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle"></i> No stock loss data available for branches.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Loss Warning -->
    @if($potentialLossStock->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-exclamation-triangle-fill"></i> Unsold Stock (Potential Loss)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Branch</th>
                                    <th>Quantity</th>
                                    <th>Stocked Date</th>
                                    <th>Days Unsold</th>
                                    <th>Potential Loss</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($potentialLossStock->take(5) as $stock)
                                <tr>
                                    <td><strong>{{ $stock->product->name ?? 'Unknown' }}</strong></td>
                                    <td>{{ $stock->branch->name ?? 'Unknown' }}</td>
                                    <td>{{ $stock->stock_quantity }}</td>
                                    <td>{{ $stock->received_date ? \Carbon\Carbon::parse($stock->received_date)->format('M d, Y') : 'N/A' }}</td>
                                    <td>
                                        @php $daysUnsold = $stock->received_date ? now()->diffInDays($stock->received_date) : 0; @endphp
                                        <span class="badge bg-{{ $daysUnsold >= 3 ? 'danger' : 'warning' }}">
                                            {{ $daysUnsold }} days
                                        </span>
                                    </td>
                                    <td class="text-danger">RM {{ number_format($stock->stock_quantity * ($stock->cost_at_purchase ?? $stock->product->cost_price ?? 0), 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Rejected Transactions Warning -->
    @if($rejectedSales->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-x-circle-fill"></i> Rejected Transactions (Loss from Invalid Sales)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Transaction ID</th>
                                    <th>Branch</th>
                                    <th>Staff</th>
                                    <th>Date</th>
                                    <th>Items</th>
                                    <th>Loss Amount</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rejectedSales->take(5) as $sale)
                                @php
                                    $saleLoss = $sale->items->sum(function($item) {
                                        return $item->quantity * ($item->product->cost_price ?? 0);
                                    });
                                    // Extract reason from notes field
                                    $reason = 'No reason provided';
                                    if ($sale->notes && preg_match('/Reason:\s*(.+?)(?:\n|$)/i', $sale->notes, $matches)) {
                                        $reason = trim($matches[1]);
                                    }
                                @endphp
                                <tr>
                                    <td><strong>{{ $sale->transaction_id ?? 'TXN-'.$sale->id }}</strong></td>
                                    <td>{{ $sale->branch->name ?? 'Unknown' }}</td>
                                    <td>{{ $sale->staff->name ?? 'Unknown' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('M d, Y') }}</td>
                                    <td>{{ $sale->items->sum('quantity') }} items</td>
                                    <td class="text-danger"><strong>RM {{ number_format($saleLoss, 2) }}</strong></td>
                                    <td><small class="text-muted">{{ $reason }}</small></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($rejectedSales->count() > 5)
                    <div class="text-center mt-3">
                        <small class="text-muted">Showing 5 of {{ $rejectedSales->count() }} rejected transactions</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Monthly Profit Trend -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-graph-up"></i> Monthly Profit Trend (Last 6 Months)</h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlyProfitTrendChart" height="80"></canvas>
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
                                    <th>Revenue</th>
                                    <th>Cost</th>
                                    <th>Profit</th>
                                    <th>Margin</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($branchAnalysis as $branch)
                                @php
                                    $profitInfo = $branchProfitData->firstWhere('branch.id', $branch->id);
                                @endphp
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
                                    <td>RM {{ number_format($profitInfo['revenue'] ?? 0, 2) }}</td>
                                    <td>RM {{ number_format($profitInfo['cost'] ?? 0, 2) }}</td>
                                    <td class="text-{{ ($profitInfo['gross_profit'] ?? 0) >= 0 ? 'success' : 'danger' }}">
                                        <strong>RM {{ number_format($profitInfo['gross_profit'] ?? 0, 2) }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ ($profitInfo['margin'] ?? 0) >= 30 ? 'success' : (($profitInfo['margin'] ?? 0) >= 15 ? 'warning' : 'danger') }}">
                                            {{ number_format($profitInfo['margin'] ?? 0, 1) }}%
                                        </span>
                                    </td>
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
                borderColor: ['#423A8E', '#00CCCD', '#3d3581'][index],
                backgroundColor: ['rgba(66, 58, 142, 0.1)', 'rgba(0, 204, 205, 0.1)', 'rgba(107, 87, 80, 0.1)'][index],
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

    // Branch Profit Chart
    const branchProfitData = @json($branchProfitData);
    const profitCtx = document.getElementById('branchProfitChart');
    
    new Chart(profitCtx, {
        type: 'bar',
        data: {
            labels: branchProfitData.map(b => b.branch.name),
            datasets: [
                {
                    label: 'Revenue',
                    data: branchProfitData.map(b => b.revenue),
                    backgroundColor: 'rgba(66, 58, 142, 0.7)',
                    borderColor: '#423A8E',
                    borderWidth: 1
                },
                {
                    label: 'Cost',
                    data: branchProfitData.map(b => b.cost),
                    backgroundColor: 'rgba(220, 53, 69, 0.7)',
                    borderColor: '#dc3545',
                    borderWidth: 1
                },
                {
                    label: 'Profit',
                    data: branchProfitData.map(b => b.gross_profit),
                    backgroundColor: 'rgba(25, 135, 84, 0.7)',
                    borderColor: '#198754',
                    borderWidth: 1
                }
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

    // Monthly Profit Trend Chart
    const monthlyTrendData = @json($monthlyProfitTrend);
    const trendCtx = document.getElementById('monthlyProfitTrendChart');
    
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: monthlyTrendData.labels,
            datasets: [
                {
                    label: 'Revenue',
                    data: monthlyTrendData.revenue,
                    borderColor: '#423A8E',
                    backgroundColor: 'rgba(66, 58, 142, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Cost',
                    data: monthlyTrendData.cost,
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Profit',
                    data: monthlyTrendData.profit,
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    tension: 0.4,
                    fill: true
                }
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

    // Stock Loss Trend Chart (30 days)
    const stockLossTrend = @json($stockLossTrend);
    const stockLossTrendCtx = document.getElementById('stockLossTrendChart');
    
    new Chart(stockLossTrendCtx, {
        type: 'line',
        data: {
            labels: stockLossTrend.labels,
            datasets: [
                {
                    label: 'Expired Stock Loss',
                    data: stockLossTrend.expired,
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 2
                },
                {
                    label: 'Rejected Sales Loss',
                    data: stockLossTrend.rejected,
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 2
                },
                {
                    label: 'Total Loss',
                    data: stockLossTrend.total,
                    borderColor: '#6c757d',
                    backgroundColor: 'rgba(108, 117, 125, 0.1)',
                    tension: 0.4,
                    fill: false,
                    borderWidth: 3,
                    borderDash: [5, 5]
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
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
                            return context.dataset.label + ': RM ' + context.parsed.y.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'RM ' + value.toFixed(0);
                        }
                    }
                },
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    });

    // Stock Loss Breakdown Pie Chart
    const expiredLoss = {{ $expiredLoss }};
    const rejectedLoss = {{ $rejectedSalesLoss }};
    const stockLossBreakdownCtx = document.getElementById('stockLossBreakdownChart');
    
    new Chart(stockLossBreakdownCtx, {
        type: 'doughnut',
        data: {
            labels: ['Expired Stock', 'Rejected Sales'],
            datasets: [{
                data: [expiredLoss, rejectedLoss],
                backgroundColor: [
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(220, 53, 69, 0.8)'
                ],
                borderColor: [
                    '#ffc107',
                    '#dc3545'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = expiredLoss + rejectedLoss;
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return label + ': RM ' + value.toFixed(2) + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });

    // Stock Loss by Category Chart
    @if($stockLossByCategory->count() > 0)
    const stockLossByCategory = @json($stockLossByCategory);
    const categoryCtx = document.getElementById('stockLossByCategoryChart');
    
    new Chart(categoryCtx, {
        type: 'bar',
        data: {
            labels: stockLossByCategory.map(c => c.category),
            datasets: [
                {
                    label: 'Expired',
                    data: stockLossByCategory.map(c => c.expired),
                    backgroundColor: 'rgba(255, 193, 7, 0.7)',
                    borderColor: '#ffc107',
                    borderWidth: 1
                },
                {
                    label: 'Rejected',
                    data: stockLossByCategory.map(c => c.rejected),
                    backgroundColor: 'rgba(220, 53, 69, 0.7)',
                    borderColor: '#dc3545',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': RM ' + context.parsed.y.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    stacked: true,
                    ticks: {
                        callback: function(value) {
                            return 'RM ' + value.toFixed(0);
                        }
                    }
                },
                x: {
                    stacked: true
                }
            }
        }
    });
    @endif

    // Stock Loss by Branch Chart
    @if($stockLossByBranch->count() > 0)
    const stockLossByBranch = @json($stockLossByBranch);
    const branchLossCtx = document.getElementById('stockLossByBranchChart');
    
    new Chart(branchLossCtx, {
        type: 'bar',
        data: {
            labels: stockLossByBranch.map(b => b.branch),
            datasets: [
                {
                    label: 'Expired',
                    data: stockLossByBranch.map(b => b.expired),
                    backgroundColor: 'rgba(255, 193, 7, 0.7)',
                    borderColor: '#ffc107',
                    borderWidth: 1
                },
                {
                    label: 'Rejected',
                    data: stockLossByBranch.map(b => b.rejected),
                    backgroundColor: 'rgba(220, 53, 69, 0.7)',
                    borderColor: '#dc3545',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': RM ' + context.parsed.y.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    stacked: true,
                    ticks: {
                        callback: function(value) {
                            return 'RM ' + value.toFixed(0);
                        }
                    }
                },
                x: {
                    stacked: true
                }
            }
        }
    });
    @endif
});
</script>
@endpush
@endsection


