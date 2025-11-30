@extends('layouts.staff')

@section('page-title', 'KPI Target Overview')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-bullseye"></i> KPI Target Overview - {{ now()->format('F Y') }}
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">Overview of all KPI targets set by HQ Admin for your branch.</p>
                </div>
            </div>
        </div>
    </div>

    @if($kpis->count() > 0)
    <div class="row g-4">
        @foreach($kpis as $kpi)
        @php
            $currentProgress = 0;
            foreach($kpi->progress as $p) {
                $currentProgress += $p->daily_value;
            }
            $percentage = $kpi->target_value > 0 ? min(($currentProgress / $kpi->target_value) * 100, 100) : 0;
            $isAchieved = $currentProgress >= $kpi->target_value;
        @endphp
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm h-100 border-{{ $isAchieved ? 'success' : ($kpi->priority === 'critical' ? 'danger' : ($kpi->priority === 'high' ? 'warning' : 'primary')) }}">
                <div class="card-header bg-{{ $isAchieved ? 'success' : ($kpi->priority === 'critical' ? 'danger' : ($kpi->priority === 'high' ? 'warning' : 'primary')) }} text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">{{ $kpi->kpi_name }}</h6>
                        @if($isAchieved)
                        <i class="bi bi-check-circle-fill"></i>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Type:</small>
                        <div class="fw-bold">{{ ucfirst(str_replace('_', ' ', $kpi->kpi_type)) }}</div>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">Target for {{ $kpi->target_month->format('F Y') }}:</small>
                        <h4 class="text-primary mb-0">{{ number_format($kpi->target_value, 2) }}</h4>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">Current Progress:</small>
                        <h5 class="mb-0">{{ number_format($currentProgress, 2) }}</h5>
                        <div class="progress mt-2" style="height: 20px;">
                            <div class="progress-bar bg-{{ $percentage >= 100 ? 'success' : ($percentage >= 75 ? 'info' : ($percentage >= 50 ? 'warning' : 'danger')) }}" 
                                 style="width: {{ $percentage }}%;">
                                {{ number_format($percentage, 1) }}%
                            </div>
                        </div>
                    </div>

                    @if($kpi->description)
                    <div class="mb-3">
                        <small class="text-muted">Description:</small>
                        <p class="small mb-0">{{ $kpi->description }}</p>
                    </div>
                    @endif

                    <div class="d-flex justify-content-between mb-2">
                        <span class="badge bg-{{ $kpi->priority === 'critical' ? 'danger' : ($kpi->priority === 'high' ? 'warning' : 'info') }}">
                            {{ ucfirst($kpi->priority) }} Priority
                        </span>
                        <span class="badge bg-{{ $isAchieved ? 'success' : 'secondary' }}">
                            {{ $isAchieved ? 'Achieved' : 'In Progress' }}
                        </span>
                    </div>

                    @if($kpi->reward_amount)
                    <div class="alert alert-success py-2 mb-2">
                        <small>
                            <i class="bi bi-gift"></i> <strong>Reward:</strong> RM {{ number_format($kpi->reward_amount, 2) }}
                        </small>
                    </div>
                    @endif

                    @if($kpi->penalty_amount)
                    <div class="alert alert-warning py-2 mb-0">
                        <small>
                            <i class="bi bi-exclamation-triangle"></i> <strong>Penalty:</strong> RM {{ number_format($kpi->penalty_amount, 2) }}
                        </small>
                    </div>
                    @endif
                </div>
                <div class="card-footer bg-light">
                    <small class="text-muted">
                        <i class="bi bi-person"></i> Set by: {{ $kpi->creator->name ?? 'HQ Admin' }}
                    </small>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No KPI targets set for this month.
            </div>
        </div>
    </div>
    @endif
</div>
@endsection