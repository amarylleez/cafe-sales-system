@extends('layouts.staff')

@section('page-title', 'Staff Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="mb-1">Welcome, {{ $user->name }}!</h4>
                    <p class="text-muted mb-0">
                        <i class="bi bi-geo-alt"></i> Your Branch: {{ $branch->name }}
                        <br>
                        <small>{{ $branch->address }}</small>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Summary Section - Top Banner Style -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">
                        <i class="bi bi-pie-chart-fill text-primary"></i> Items Sold by Category
                        <small class="text-muted">- {{ now()->format('F Y') }}</small>
                    </h6>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    @if($categorySales->count() > 0)
                    <canvas id="categorySalesChart" style="max-height: 200px;"></canvas>
                    @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-pie-chart" style="font-size: 3rem;"></i>
                        <p class="mb-0 mt-2">No sales data this month</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100" style="background: linear-gradient(135deg, #423A8E 0%, #00CCCD 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="opacity-75 mb-2">Today's Sales</h6>
                            <h2 class="mb-1">RM {{ number_format($todaySales, 2) }}</h2>
                            <p class="mb-0 opacity-75">{{ $todayTransactions }} Transaction{{ $todayTransactions != 1 ? 's' : '' }} Today</p>
                        </div>
                        <div class="opacity-50">
                            <i class="bi bi-currency-dollar" style="font-size: 3rem;"></i>
                        </div>
                    </div>
                    
                    @php
                        $todayChange = $yesterdaySales > 0 ? (($todaySales - $yesterdaySales) / $yesterdaySales) * 100 : ($todaySales > 0 ? 100 : 0);
                        $monthChange = $lastMonthSales > 0 ? (($monthlySales - $lastMonthSales) / $lastMonthSales) * 100 : ($monthlySales > 0 ? 100 : 0);
                    @endphp
                    
                    <div class="mt-4 pt-2">
                        <div class="row">
                            <div class="col-6">
                                <small class="opacity-75">vs Yesterday</small>
                                <h5 class="mb-0">
                                    <i class="bi bi-{{ $todayChange >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                    {{ number_format(abs($todayChange), 1) }}%
                                </h5>
                            </div>
                            <div class="col-6 text-end">
                                <small class="opacity-75">This Week</small>
                                <h5 class="mb-0">RM {{ number_format($weeklySales, 2) }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Section -->
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="mb-3"><i class="bi bi-lightning-charge"></i> Quick Actions</h5>
        </div>
    </div>

    <!-- Main Dashboard Grid -->
    <div class="row g-4 mb-4">
        <!-- Submit Sales Card -->
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm h-100" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);">
                <div class="card-body text-white d-flex flex-column">
                    <h5 class="card-title">
                        <i class="bi bi-cart-plus"></i> Submit Sales
                    </h5>
                    <p class="card-text opacity-75">Record today's sales transactions</p>
                    <div class="mt-auto">
                        <a href="{{ route('staff.sales.create') }}" class="btn btn-light w-100">
                            <i class="bi bi-plus-circle"></i> Input Daily Sales
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- My KPI Card -->
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm h-100" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);">
                <div class="card-body text-white d-flex flex-column">
                    <h5 class="card-title">
                        <i class="bi bi-graph-up-arrow"></i> My KPI
                    </h5>
                    <p class="card-text opacity-75">Check your performance targets</p>
                    <div class="mt-auto">
                        <a href="{{ route('staff.kpi') }}" class="btn btn-light w-100">
                            <i class="bi bi-eye"></i> View Assigned KPIs
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts Card -->
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm h-100" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);">
                <div class="card-body text-white d-flex flex-column">
                    <h5 class="card-title">
                        <i class="bi bi-bell"></i> Alerts
                        @if($unreadNotifications > 0)
                        <span class="badge bg-danger ms-2">{{ $unreadNotifications }}</span>
                        @endif
                    </h5>
                    <p class="card-text opacity-75">Important notifications</p>
                    <div class="mt-auto">
                        <a href="{{ route('staff.alerts') }}" class="btn btn-light w-100">
                            <i class="bi bi-exclamation-triangle"></i> View Notices
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory Card -->
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm h-100" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);">
                <div class="card-body text-white d-flex flex-column">
                    <h5 class="card-title">
                        <i class="bi bi-box-seam"></i> Inventory
                    </h5>
                    <p class="card-text opacity-75">Manage product stock levels</p>
                    <div class="mt-auto">
                        <a href="{{ route('staff.inventory') }}" class="btn btn-light w-100">
                            <i class="bi bi-pencil-square"></i> Update Records
                        </a>
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
    // Category Sales Pie Chart
    @if($categorySales->count() > 0)
    const categoryData = @json($categorySales);
    const categoryCtx = document.getElementById('categorySalesChart');
    
    if (categoryCtx) {
        const colors = [
            '#423A8E', '#00CCCD', '#3d3581', '#FFC107', '#F8F9FA', 
            '#E74C3C', '#FEF5E7', '#423A8E', '#00CCCD', '#423A8E',
            '#F5D6BA', '#00CCCD', '#FFC107', '#F8F9FA'
        ];
        
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: categoryData.map(c => c.category_name),
                datasets: [{
                    data: categoryData.map(c => c.total_quantity),
                    backgroundColor: colors.slice(0, categoryData.length),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 12,
                            padding: 10,
                            font: { size: 11 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.raw / total) * 100).toFixed(1);
                                return `${context.label}: ${context.raw} items (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }
    @endif
});
</script>
@endpush
@endsection


