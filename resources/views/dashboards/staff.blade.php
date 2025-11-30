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

    <!-- Main Dashboard Grid -->
    <div class="row g-4 mb-4">
        <!-- Submit Sales Card -->
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm h-100" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);">
                <div class="card-body text-white">
                    <h5 class="card-title">
                        <i class="bi bi-cart-plus"></i> Submit Sales
                    </h5>
                    <p class="card-text opacity-75">Record today's sales transactions</p>
                    <a href="{{ route('staff.sales.create') }}" class="btn btn-light btn-lg w-100 mt-3">
                        <i class="bi bi-plus-circle"></i> Input Daily Sales
                    </a>
                </div>
            </div>
        </div>

        <!-- My KPI Card -->
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm h-100" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);">
                <div class="card-body text-white">
                    <h5 class="card-title">
                        <i class="bi bi-graph-up-arrow"></i> My KPI
                    </h5>
                    <p class="card-text opacity-75">Check your performance targets</p>
                    <a href="{{ route('staff.kpi') }}" class="btn btn-light btn-lg w-100 mt-3">
                        <i class="bi bi-eye"></i> View Assigned KPIs
                    </a>
                </div>
            </div>
        </div>

        <!-- Sales Summary Card -->
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm h-100" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);">
                <div class="card-body text-white">
                    <h5 class="card-title">
                        <i class="bi bi-currency-dollar"></i> Sales Summary
                    </h5>
                    <div class="mt-3">
                        <p class="mb-2">Today's Sales:</p>
                        <h3 class="mb-3">RM {{ number_format($todaySales, 2) }}</h3>
                        <p class="mb-2">Monthly Sales:</p>
                        <h3>RM {{ number_format($monthlySales, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Actions -->
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm h-100" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);">
                <div class="card-body text-white">
                    <h5 class="card-title">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </h5>
                    <p class="card-text opacity-75 mb-3">View performance metrics</p>
                    <div class="d-grid gap-2">
                        <a href="{{ route('staff.dashboard.target') }}" class="btn btn-light">
                            <i class="bi bi-bullseye"></i> KPI Target Overview
                        </a>
                        <a href="{{ route('staff.dashboard.progress') }}" class="btn btn-light">
                            <i class="bi bi-bar-chart"></i> KPI Progress Bar
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts Card -->
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm h-100" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);">
                <div class="card-body text-white">
                    <h5 class="card-title">
                        <i class="bi bi-bell"></i> Alerts
                        @if($unreadNotifications > 0)
                        <span class="badge bg-danger ms-2">{{ $unreadNotifications }}</span>
                        @endif
                    </h5>
                    <p class="card-text opacity-75">Important notifications</p>
                    <a href="{{ route('staff.alerts') }}" class="btn btn-light btn-lg w-100 mt-3">
                        <i class="bi bi-exclamation-triangle"></i> View Notices
                    </a>
                </div>
            </div>
        </div>

        <!-- Inventory Card -->
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm h-100" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);">
                <div class="card-body text-white">
                    <h5 class="card-title">
                        <i class="bi bi-box-seam"></i> Inventory
                    </h5>
                    <p class="card-text opacity-75">Manage product stock levels</p>
                    <a href="{{ route('staff.inventory') }}" class="btn btn-light btn-lg w-100 mt-3">
                        <i class="bi bi-pencil-square"></i> Update Records
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Progress Chart -->
    @if($kpis->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-bar-chart-line"></i> KPI Progress Overview
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="kpiChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Active KPIs List -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-list-check"></i> Active KPIs
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($kpis as $kpi)
                    @php
                        $currentProgress = 0;
                        foreach($kpi->progress as $p) {
                            $currentProgress += $p->daily_value;
                        }
                        $percentage = $kpi->target_value > 0 ? min(($currentProgress / $kpi->target_value) * 100, 100) : 0;
                        $progressColor = $percentage >= 100 ? 'success' : ($percentage >= 75 ? 'info' : ($percentage >= 50 ? 'warning' : 'danger'));
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
                                <div class="mt-1">
                                    <strong>{{ number_format($currentProgress, 2) }}</strong> / {{ number_format($kpi->target_value, 2) }}
                                </div>
                            </div>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-{{ $progressColor }}" role="progressbar" 
                                 style="width: {{ $percentage }}%;" 
                                 aria-valuenow="{{ $percentage }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                {{ number_format($percentage, 1) }}%
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No active KPIs for this month. Check back later!
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if($kpis->count() > 0)
    const kpiData = @json($kpiProgressData);
    
    const ctx = document.getElementById('kpiChart');
    if (ctx && kpiData.length > 0) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: kpiData[0]?.daily_progress?.map(p => {
                    const date = new Date(p.date);
                    return date.getDate() + '/' + (date.getMonth() + 1);
                }) || [],
                datasets: kpiData.map((kpi, index) => ({
                    label: kpi.kpi_name,
                    data: kpi.daily_progress?.map(p => p.cumulative) || [],
                    borderColor: `hsl(${index * 60}, 70%, 50%)`,
                    backgroundColor: `hsla(${index * 60}, 70%, 50%, 0.1)`,
                    tension: 0.4,
                    fill: true
                }))
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Monthly KPI Progress Tracking'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
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
    }
    @endif
});
</script>
@endpush
@endsection