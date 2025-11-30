@extends('layouts.staff')

@section('page-title', 'KPI Progress Bar')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-bar-chart"></i> KPI Progress Bar - {{ now()->format('F Y') }}
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">Visual representation of your KPI progress for the current month.</p>
                </div>
            </div>
        </div>
    </div>

    @if(count($progressData) > 0)
    <div class="row g-4">
        @foreach($progressData as $data)
        @php
            $kpi = $data['kpi'];
            $percentage = $data['progress_percentage'];
            $isAchieved = $data['is_met'];
            $progressColor = $percentage >= 100 ? 'success' : ($percentage >= 75 ? 'info' : ($percentage >= 50 ? 'warning' : 'danger'));
        @endphp
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-lg-3">
                            <h5 class="mb-2">{{ $kpi->kpi_name }}</h5>
                            <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $kpi->kpi_type)) }}</small>
                            <div class="mt-2">
                                <span class="badge bg-{{ $kpi->priority === 'critical' ? 'danger' : ($kpi->priority === 'high' ? 'warning' : 'info') }}">
                                    {{ ucfirst($kpi->priority) }}
                                </span>
                                @if($isAchieved)
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle-fill"></i> Achieved
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-lg-9">
                            <div class="mb-2">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Progress: {{ number_format($data['current_value'], 2) }} / {{ number_format($data['target_value'], 2) }}</span>
                                    <span class="fw-bold text-{{ $progressColor }}">{{ number_format($percentage, 1) }}%</span>
                                </div>
                            </div>
                            <div class="progress" style="height: 40px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-{{ $progressColor }}" 
                                     role="progressbar" 
                                     style="width: {{ min($percentage, 100) }}%;">
                                    <strong style="font-size: 1.1rem;">{{ number_format($percentage, 1) }}%</strong>
                                </div>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">
                                    @if($data['remaining_value'] > 0)
                                    <i class="bi bi-arrow-up-right"></i> Remaining: {{ number_format($data['remaining_value'], 2) }}
                                    @else
                                    <i class="bi bi-check-circle"></i> Target Achieved!
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>

                    @if($kpi->reward_amount || $kpi->penalty_amount)
                    <hr>
                    <div class="row">
                        @if($kpi->reward_amount)
                        <div class="col-md-6">
                            <div class="alert alert-success py-2 mb-0">
                                <small>
                                    <i class="bi bi-gift"></i> <strong>Reward if achieved:</strong> RM {{ number_format($kpi->reward_amount, 2) }}
                                    @if($kpi->reward_description)
                                    <br>{{ $kpi->reward_description }}
                                    @endif
                                </small>
                            </div>
                        </div>
                        @endif
                        @if($kpi->penalty_amount)
                        <div class="col-md-6">
                            <div class="alert alert-warning py-2 mb-0">
                                <small>
                                    <i class="bi bi-exclamation-triangle"></i> <strong>Penalty if not met:</strong> RM {{ number_format($kpi->penalty_amount, 2) }}
                                    @if($kpi->penalty_description)
                                    <br>{{ $kpi->penalty_description }}
                                    @endif
                                </small>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Summary Card -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-clipboard-data"></i> Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <h2>{{ count($progressData) }}</h2>
                            <small class="text-muted">Total KPIs</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="text-success">{{ collect($progressData)->where('is_met', true)->count() }}</h2>
                            <small class="text-muted">Achieved</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="text-warning">{{ collect($progressData)->where('is_met', false)->count() }}</h2>
                            <small class="text-muted">In Progress</small>
                        </div>
                        <div class="col-md-3">
                            @php
                                $avgProgress = collect($progressData)->avg('progress_percentage');
                            @endphp
                            <h2 class="text-info">{{ number_format($avgProgress, 1) }}%</h2>
                            <small class="text-muted">Average Progress</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No KPI progress data available for this month.
            </div>
        </div>
    </div>
    @endif
</div>
@endsection