@extends('layouts.staff')

@section('page-title', 'My KPI')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up-arrow"></i> View Assigned KPIs & Progress
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Track your performance targets and progress for {{ now()->format('F Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    @if($kpis->count() > 0)
        @foreach($kpis as $kpi)
        @php
            $currentProgress = 0;
            foreach($kpi->progress as $p) {
                $currentProgress += $p->daily_value;
            }
            $percentage = $kpi->target_value > 0 ? min(($currentProgress / $kpi->target_value) * 100, 100) : 0;
            $isAchieved = $currentProgress >= $kpi->target_value;
            $remaining = max($kpi->target_value - $currentProgress, 0);
        @endphp
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-{{ $isAchieved ? 'success' : 'primary' }}">
                    <div class="card-header bg-{{ $isAchieved ? 'success' : 'primary' }} text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-bullseye"></i> {{ $kpi->kpi_name }}
                            </h5>
                            <div>
                                <span class="badge bg-{{ $kpi->priority === 'critical' ? 'danger' : ($kpi->priority === 'high' ? 'warning' : 'light') }}">
                                    {{ ucfirst($kpi->priority) }} Priority
                                </span>
                                @if($isAchieved)
                                <span class="badge bg-light text-success">
                                    <i class="bi bi-check-circle-fill"></i> Target Achieved!
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- KPI Overview -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <small class="text-muted">Target Value</small>
                                    <h4 class="mb-0 text-primary">{{ number_format($kpi->target_value, 2) }}</h4>
                                    <small>{{ ucfirst(str_replace('_', ' ', $kpi->kpi_type)) }}</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <small class="text-muted">Current Progress</small>
                                    <h4 class="mb-0 text-info">{{ number_format($currentProgress, 2) }}</h4>
                                    <small>Achieved</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <small class="text-muted">Remaining</small>
                                    <h4 class="mb-0 text-warning">{{ number_format($remaining, 2) }}</h4>
                                    <small>To achieve</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <small class="text-muted">Progress</small>
                                    <h4 class="mb-0 text-success">{{ number_format($percentage, 1) }}%</h4>
                                    <small>Completion</small>
                                </div>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="mb-4">
                            <div class="progress" style="height: 30px;">
                                <div class="progress-bar bg-{{ $percentage >= 100 ? 'success' : ($percentage >= 75 ? 'info' : ($percentage >= 50 ? 'warning' : 'danger')) }}" 
                                     role="progressbar" 
                                     style="width: {{ $percentage }}%;">
                                    <strong>{{ number_format($percentage, 1) }}%</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Description & Rewards -->
                        @if($kpi->description || $kpi->reward_amount || $kpi->penalty_amount)
                        <div class="row mb-3">
                            @if($kpi->description)
                            <div class="col-md-6">
                                <div class="alert alert-info">
                                    <strong><i class="bi bi-info-circle"></i> Description:</strong>
                                    <p class="mb-0 mt-2">{{ $kpi->description }}</p>
                                </div>
                            </div>
                            @endif
                            @if($kpi->reward_amount || $kpi->penalty_amount)
                            <div class="col-md-6">
                                @if($kpi->reward_amount)
                                <div class="alert alert-success">
                                    <strong><i class="bi bi-gift"></i> Reward:</strong>
                                    <p class="mb-0 mt-2">
                                        RM {{ number_format($kpi->reward_amount, 2) }}
                                        @if($kpi->reward_description)
                                        <br><small>{{ $kpi->reward_description }}</small>
                                        @endif
                                    </p>
                                </div>
                                @endif
                                @if($kpi->penalty_amount)
                                <div class="alert alert-warning">
                                    <strong><i class="bi bi-exclamation-triangle"></i> Penalty:</strong>
                                    <p class="mb-0 mt-2">
                                        RM {{ number_format($kpi->penalty_amount, 2) }}
                                        @if($kpi->penalty_description)
                                        <br><small>{{ $kpi->penalty_description }}</small>
                                        @endif
                                    </p>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>
                        @endif

                        <!-- Daily Progress Table -->
                        <h6 class="mb-3"><i class="bi bi-calendar-check"></i> Daily Progress</h6>
                        @if($kpi->progress->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Daily Value</th>
                                        <th>Cumulative</th>
                                        <th>Progress %</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($kpi->progress->sortByDesc('progress_date') as $progress)
                                    <tr>
                                        <td>{{ $progress->progress_date->format('d M Y') }}</td>
                                        <td>{{ number_format($progress->daily_value, 2) }}</td>
                                        <td>{{ number_format($progress->cumulative_value, 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $progress->progress_percentage >= 100 ? 'success' : 'info' }}">
                                                {{ number_format($progress->progress_percentage, 1) }}%
                                            </span>
                                        </td>
                                        <td>
                                            @if($progress->is_completed)
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle-fill"></i> Completed
                                            </span>
                                            @else
                                            <span class="badge bg-secondary">In Progress</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-{{ $progress->is_completed ? 'secondary' : 'success' }} toggle-completion" 
                                                    data-kpi-id="{{ $kpi->id }}" 
                                                    data-date="{{ $progress->progress_date->format('Y-m-d') }}"
                                                    data-completed="{{ $progress->is_completed ? 'true' : 'false' }}">
                                                <i class="bi bi-{{ $progress->is_completed ? 'x-circle' : 'check-circle' }}"></i>
                                                {{ $progress->is_completed ? 'Unmark' : 'Mark Complete' }}
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> No progress recorded yet for this KPI.
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    @else
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No active KPIs assigned for this month.
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle completion status
    document.querySelectorAll('.toggle-completion').forEach(btn => {
        btn.addEventListener('click', function() {
            const kpiId = this.dataset.kpiId;
            const date = this.dataset.date;
            const isCompleted = this.dataset.completed === 'true';
            
            if (confirm(`Are you sure you want to ${isCompleted ? 'unmark' : 'mark'} this as complete?`)) {
                fetch(`/staff/kpi/${kpiId}/toggle-completion`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ date: date })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Failed to update status');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred');
                });
            }
        });
    });
});
</script>
@endpush
@endsection