@extends('layouts.staff')

@section('page-title', 'My Schedule')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header" style="background: linear-gradient(135deg, #423A8E 0%, #00CCCD 100%);">
                    <h5 class="mb-0 text-white">
                        <i class="bi bi-calendar-week"></i> My Schedule
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">View your work schedule at {{ $branch->name }}.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-check" style="font-size: 2rem;"></i>
                    <h3 class="mt-2">{{ $upcomingSchedules->count() }}</h3>
                    <p class="mb-0">Upcoming Shifts</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm bg-success text-white">
                <div class="card-body text-center">
                    <i class="bi bi-clock-history" style="font-size: 2rem;"></i>
                    <h3 class="mt-2">{{ $totalHours }}</h3>
                    <p class="mb-0">Hours This Month</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm bg-info text-white">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-range" style="font-size: 2rem;"></i>
                    <h3 class="mt-2">{{ $schedules->count() }}</h3>
                    <p class="mb-0">Shifts This Week</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Shifts Alert -->
    @if($upcomingSchedules->where('status', 'scheduled')->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-2" style="font-size: 1.5rem;"></i>
                <div>
                    <strong>Pending Confirmation!</strong> You have {{ $upcomingSchedules->where('status', 'scheduled')->count() }} shift(s) awaiting your confirmation.
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Week Navigation -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <a href="?week={{ $weekOffset - 1 }}" class="btn btn-outline-primary">
                            <i class="bi bi-chevron-left"></i> Previous Week
                        </a>
                        <h5 class="mb-0">
                            {{ $weekStart->format('M d') }} - {{ $weekEnd->format('M d, Y') }}
                        </h5>
                        <a href="?week={{ $weekOffset + 1 }}" class="btn btn-outline-primary">
                            Next Week <i class="bi bi-chevron-right"></i>
                        </a>
                        @if($weekOffset != 0)
                        <a href="?week=0" class="btn btn-link">This Week</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Weekly Schedule -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-calendar3"></i> Weekly Schedule</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @for($date = $weekStart->copy(); $date <= $weekEnd; $date->addDay())
                        @php
                            $dateKey = $date->format('Y-m-d');
                            $daySchedules = $schedules->filter(fn($s) => $s->schedule_date->format('Y-m-d') === $dateKey);
                            $isToday = $date->isToday();
                            $isPast = $date->isPast() && !$isToday;
                        @endphp
                        <div class="col-md-6 col-lg-3">
                            <div class="card h-100 {{ $isToday ? 'border-primary border-2' : '' }} {{ $isPast ? 'bg-light' : '' }}">
                                <div class="card-header {{ $isToday ? 'bg-primary text-white' : 'bg-light' }}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong>{{ $date->format('l') }}</strong>
                                        <span>{{ $date->format('M d') }}</span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @forelse($daySchedules as $schedule)
                                    <div class="schedule-card p-2 rounded mb-2 {{ $schedule->status_badge }}" style="color: white;">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <div class="fw-bold">
                                                    {{ $schedule->shift_label }}
                                                </div>
                                            </div>
                                        </div>
                                        @if($schedule->notes)
                                        <small class="d-block mt-1 opacity-75">
                                            <i class="bi bi-chat-text"></i> {{ $schedule->notes }}
                                        </small>
                                        @endif
                                        
                                        @if($schedule->status === 'scheduled' && !$isPast)
                                        <button class="btn btn-sm btn-light mt-2 w-100" onclick="confirmShift({{ $schedule->id }})">
                                            <i class="bi bi-check-circle"></i> Confirm Shift
                                        </button>
                                        @endif
                                    </div>
                                    @empty
                                    <div class="text-center py-4 text-muted">
                                        <i class="bi bi-calendar-x" style="font-size: 2rem;"></i>
                                        <p class="mb-0 mt-2">No shift scheduled</p>
                                    </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="mb-3">Schedule Status Legend</h6>
                    <div class="d-flex flex-wrap gap-3">
                        <span><span class="badge bg-info">Scheduled</span> - Awaiting your confirmation</span>
                        <span><span class="badge bg-primary">Confirmed</span> - You confirmed this shift</span>
                        <span><span class="badge bg-success">Completed</span> - Shift completed</span>
                        <span><span class="badge bg-danger">Absent</span> - Marked as absent</span>
                        <span><span class="badge bg-secondary">Cancelled</span> - Shift cancelled</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Shifts List -->
    @if($upcomingSchedules->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-list-check"></i> Upcoming Shifts (Next 7 Days)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Day</th>
                                    <th>Shift</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($upcomingSchedules as $schedule)
                                <tr>
                                    <td>{{ $schedule->schedule_date->format('M d, Y') }}</td>
                                    <td>{{ $schedule->schedule_date->format('l') }}</td>
                                    <td>{{ $schedule->shift_label }}</td>
                                    <td><span class="badge {{ $schedule->status_badge }}">{{ ucfirst($schedule->status) }}</span></td>
                                    <td>
                                        @if($schedule->status === 'scheduled')
                                        <button class="btn btn-sm btn-success" onclick="confirmShift({{ $schedule->id }})">
                                            <i class="bi bi-check-circle"></i> Confirm
                                        </button>
                                        @else
                                        <span class="text-muted">-</span>
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
    @endif
</div>

@push('scripts')
<script>
function confirmShift(scheduleId) {
    if (confirm('Are you sure you want to confirm this shift?')) {
        fetch(`/staff/schedule/${scheduleId}/confirm`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message || 'Failed to confirm shift');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while confirming the shift.');
        });
    }
}
</script>
@endpush
@endsection
