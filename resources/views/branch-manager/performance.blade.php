@extends('layouts.branch-manager')

@section('page-title', 'Performance')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header" style="background: linear-gradient(135deg, #423A8E 0%, #00CCCD 100%);">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-white">
                            <i class="bi bi-graph-up"></i> Branch Performance
                        </h5>
                        <span class="badge bg-white text-dark">{{ $branch->name }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <p class="text-muted mb-0">Track your branch's performance, profit/loss, and staff benchmarks.</p>
                        </div>
                        <div class="col-md-6">
                            <!-- Date Range Filter for Profit/Loss -->
                            <form method="GET" class="d-flex gap-2 justify-content-end">
                                <select name="range" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                                    <option value="today" {{ $dateRange == 'today' ? 'selected' : '' }}>Today</option>
                                    <option value="this_week" {{ $dateRange == 'this_week' ? 'selected' : '' }}>This Week</option>
                                    <option value="last_week" {{ $dateRange == 'last_week' ? 'selected' : '' }}>Last Week</option>
                                    <option value="this_month" {{ $dateRange == 'this_month' ? 'selected' : '' }}>This Month</option>
                                    <option value="last_month" {{ $dateRange == 'last_month' ? 'selected' : '' }}>Last Month</option>
                                </select>
                                <span class="badge bg-secondary align-self-center">{{ $startDate->format('M d') }} - {{ $endDate->format('M d, Y') }}</span>
                            </form>
                        </div>
                    </div>
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
                                <span class="badge bg-warning text-dark" style="font-size: 0.7rem;">
                                    <i class="bi bi-clock-history"></i> Unsold: RM {{ number_format($unsoldStockLoss, 2) }}
                                </span>
                                <span class="badge bg-danger" style="font-size: 0.7rem;">
                                    <i class="bi bi-x-circle"></i> Rejected: RM {{ number_format($rejectedSalesLoss, 2) }}
                                </span>
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

    <!-- Net Profit Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-left: 4px solid {{ $netProfit >= 0 ? '#198754' : '#dc3545' }} !important;">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="mb-1">Net Profit (After Stock Loss)</h5>
                            <p class="text-muted small mb-0">Revenue - Cost - Stock Loss = Net Profit</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <h2 class="{{ $netProfit >= 0 ? 'text-success' : 'text-danger' }} mb-0">
                                RM {{ number_format($netProfit, 2) }}
                            </h2>
                        </div>
                    </div>
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

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-bar-chart-line"></i> Monthly Sales Comparison (Last 6 Months)</h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlySalesChart" height="100"></canvas>
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

    <!-- Revenue & Profit Trend -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-graph-up"></i> Revenue & Profit Trend</h5>
                </div>
                <div class="card-body">
                    <canvas id="profitTrendChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Profit/Loss Breakdown -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <h5 class="mb-0"><i class="bi bi-calendar3"></i> Daily Profit/Loss Breakdown (This Month)</h5>
                        <!-- View Toggle Buttons -->
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary active" id="chartViewBtn" onclick="toggleBreakdownView('chart')">
                                <i class="bi bi-bar-chart-fill"></i> Chart
                            </button>
                            <button type="button" class="btn btn-outline-primary" id="tableViewBtn" onclick="toggleBreakdownView('table')">
                                <i class="bi bi-table"></i> Table
                            </button>
                        </div>
                    </div>
                    @if(isset($dailyProfitLossBreakdown['monthly_totals']))
                    <span class="badge bg-{{ $dailyProfitLossBreakdown['monthly_totals']['totalNetProfit'] >= 0 ? 'success' : 'danger' }} fs-6">
                        Monthly Net: RM {{ number_format($dailyProfitLossBreakdown['monthly_totals']['totalNetProfit'], 2) }}
                    </span>
                    @endif
                </div>
                <div class="card-body">
                    @if(isset($dailyProfitLossBreakdown['days']) && count($dailyProfitLossBreakdown['days']) > 0)
                    
                    <!-- Chart View -->
                    <div id="breakdownChartView">
                        <!-- Summary Cards Row -->
                        <div class="row g-3 mb-4">
                            <div class="col-6 col-md-3">
                                <div class="border rounded p-3 text-center h-100" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                    <i class="bi bi-cash-stack text-primary fs-4"></i>
                                    <div class="text-muted small mt-1">Revenue</div>
                                    <div class="fw-bold text-primary">RM {{ number_format($dailyProfitLossBreakdown['monthly_totals']['totalRevenue'], 2) }}</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="border rounded p-3 text-center h-100" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                    <i class="bi bi-cart-dash text-secondary fs-4"></i>
                                    <div class="text-muted small mt-1">Cost</div>
                                    <div class="fw-bold text-secondary">RM {{ number_format($dailyProfitLossBreakdown['monthly_totals']['totalCost'], 2) }}</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="border rounded p-3 text-center h-100" style="background: linear-gradient(135deg, #fff5f5 0%, #ffe3e3 100%);">
                                    <i class="bi bi-x-circle text-danger fs-4"></i>
                                    <div class="text-muted small mt-1">Rejected Loss</div>
                                    <div class="fw-bold text-danger">-RM {{ number_format($dailyProfitLossBreakdown['monthly_totals']['totalRejectedLoss'], 2) }}</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="border rounded p-3 text-center h-100" style="background: linear-gradient(135deg, {{ $dailyProfitLossBreakdown['monthly_totals']['totalNetProfit'] >= 0 ? '#f0fff4 0%, #c6f6d5 100%' : '#fff5f5 0%, #ffe3e3 100%' }});">
                                    <i class="bi bi-graph-up-arrow {{ $dailyProfitLossBreakdown['monthly_totals']['totalNetProfit'] >= 0 ? 'text-success' : 'text-danger' }} fs-4"></i>
                                    <div class="text-muted small mt-1">Net Profit</div>
                                    <div class="fw-bold {{ $dailyProfitLossBreakdown['monthly_totals']['totalNetProfit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        RM {{ number_format($dailyProfitLossBreakdown['monthly_totals']['totalNetProfit'], 2) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Daily Visual Breakdown -->
                        <div class="daily-breakdown-visual" style="max-height: 320px; overflow-y: auto;">
                            @php
                                $sortedDays = collect($dailyProfitLossBreakdown['days'])->sortByDesc('date');
                                $maxRevenue = $sortedDays->max('revenue') ?: 1;
                            @endphp
                            @foreach($sortedDays as $date => $dayData)
                            <div class="day-row d-flex align-items-center py-2 px-3 mb-2 rounded {{ $dayData['netProfit'] < 0 ? 'bg-danger bg-opacity-10' : 'bg-light' }}" style="border-left: 4px solid {{ $dayData['netProfit'] >= 0 ? '#198754' : '#dc3545' }};">
                                <!-- Date -->
                                <div class="flex-shrink-0" style="width: 90px;">
                                    <div class="fw-bold small">{{ \Carbon\Carbon::parse($date)->format('D') }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($date)->format('M d') }}</div>
                                </div>
                                
                                <!-- Progress Bar Visual -->
                                <div class="flex-grow-1 mx-3">
                                    <div class="progress" style="height: 22px; background: #e9ecef;">
                                        <div class="progress-bar {{ $dayData['netProfit'] >= 0 ? 'bg-success' : 'bg-danger' }}" 
                                             role="progressbar" 
                                             style="width: {{ ($dayData['revenue'] / $maxRevenue) * 100 }}%;"
                                             title="Revenue: RM {{ number_format($dayData['revenue'], 2) }}">
                                            <span class="small fw-medium px-2">RM {{ number_format($dayData['revenue'], 0) }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Net Profit -->
                                <div class="flex-shrink-0 text-end" style="width: 100px;">
                                    <span class="fw-bold {{ $dayData['netProfit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $dayData['netProfit'] >= 0 ? '+' : '' }}RM {{ number_format($dayData['netProfit'], 2) }}
                                    </span>
                                    @if($dayData['rejectedLoss'] > 0)
                                    <div style="font-size: 0.7rem;" class="text-danger">
                                        <i class="bi bi-exclamation-circle"></i> -RM {{ number_format($dayData['rejectedLoss'], 2) }} loss
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Table View (Hidden by default) -->
                    <div id="breakdownTableView" class="d-none">
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-hover table-sm">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Date</th>
                                    <th class="text-end">Revenue</th>
                                    <th class="text-end">Cost</th>
                                    <th class="text-end">Gross Profit</th>
                                    <th class="text-end">Rejected Loss</th>
                                    <th class="text-end">Net Profit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dailyProfitLossBreakdown['days'] as $date => $dayData)
                                <tr class="{{ $dayData['netProfit'] < 0 ? 'table-danger' : '' }}">
                                    <td>
                                        <strong>{{ \Carbon\Carbon::parse($date)->format('D, M d') }}</strong>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-primary">RM {{ number_format($dayData['revenue'], 2) }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-muted">RM {{ number_format($dayData['cost'], 2) }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-{{ $dayData['grossProfit'] >= 0 ? 'success' : 'danger' }}">
                                            RM {{ number_format($dayData['grossProfit'], 2) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        @if($dayData['rejectedLoss'] > 0)
                                        <span class="text-danger">-RM {{ number_format($dayData['rejectedLoss'], 2) }}</span>
                                        @else
                                        <span class="text-muted">RM 0.00</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-{{ $dayData['netProfit'] >= 0 ? 'success' : 'danger' }}">
                                            RM {{ number_format($dayData['netProfit'], 2) }}
                                        </strong>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <!-- Monthly Totals Footer -->
                            <tfoot class="table-dark">
                                <tr>
                                    <th><strong>MONTHLY TOTAL</strong></th>
                                    <th class="text-end">
                                        <strong>RM {{ number_format($dailyProfitLossBreakdown['monthly_totals']['totalRevenue'], 2) }}</strong>
                                    </th>
                                    <th class="text-end">
                                        <strong>RM {{ number_format($dailyProfitLossBreakdown['monthly_totals']['totalCost'], 2) }}</strong>
                                    </th>
                                    <th class="text-end">
                                        <strong>RM {{ number_format($dailyProfitLossBreakdown['monthly_totals']['totalGrossProfit'], 2) }}</strong>
                                    </th>
                                    <th class="text-end">
                                        <strong class="text-danger">-RM {{ number_format($dailyProfitLossBreakdown['monthly_totals']['totalRejectedLoss'], 2) }}</strong>
                                    </th>
                                    <th class="text-end">
                                        <strong class="{{ $dailyProfitLossBreakdown['monthly_totals']['totalNetProfit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                            RM {{ number_format($dailyProfitLossBreakdown['monthly_totals']['totalNetProfit'], 2) }}
                                        </strong>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    </div><!-- End Table View -->
                    
                    <div class="mt-3 small text-muted">
                        <i class="bi bi-info-circle"></i> Net Profit = Gross Profit - Rejected Transaction Losses
                    </div>
                    @else
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle"></i> No daily data available for this month yet.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Staff Performance Tracking -->
    <div class="row mb-4">
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
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle"></i> No staff members found in this branch.
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
                    <p class="text-muted small mb-3">These items have been in stock for more than 1 day without being sold.</p>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
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
                    @if($potentialLossStock->count() > 5)
                    <div class="text-center mt-3">
                        <small class="text-muted">Showing 5 of {{ $potentialLossStock->count() }} unsold items</small>
                    </div>
                    @endif
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
                    <h5 class="mb-0"><i class="bi bi-x-circle-fill"></i> Rejected Transactions</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Transaction ID</th>
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
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Toggle between chart and table view
function toggleBreakdownView(view) {
    const chartView = document.getElementById('breakdownChartView');
    const tableView = document.getElementById('breakdownTableView');
    const chartBtn = document.getElementById('chartViewBtn');
    const tableBtn = document.getElementById('tableViewBtn');
    
    if (view === 'chart') {
        chartView.classList.remove('d-none');
        tableView.classList.add('d-none');
        chartBtn.classList.add('active');
        tableBtn.classList.remove('active');
    } else {
        chartView.classList.add('d-none');
        tableView.classList.remove('d-none');
        chartBtn.classList.remove('active');
        tableBtn.classList.add('active');
    }
}

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

    // Profit Trend Chart
    const profitTrendCtx = document.getElementById('profitTrendChart').getContext('2d');
    new Chart(profitTrendCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($dailyProfitTrend['labels']) !!},
            datasets: [
                {
                    label: 'Revenue',
                    data: {!! json_encode($dailyProfitTrend['revenue']) !!},
                    borderColor: '#423A8E',
                    backgroundColor: 'rgba(66, 58, 142, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Profit',
                    data: {!! json_encode($dailyProfitTrend['profit']) !!},
                    borderColor: '#00CCCD',
                    backgroundColor: 'rgba(0, 204, 205, 0.1)',
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
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
});
</script>
@endpush
@endsection
