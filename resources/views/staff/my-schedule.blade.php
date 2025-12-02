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

    @php
        $todaySchedule = $schedules->first(fn($s) => $s->schedule_date->isToday());
    @endphp

    <!-- Today's Shift - Clock In/Out Section -->
    @if($todaySchedule)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-primary border-2">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-clock-fill"></i> Today's Shift - {{ now()->format('l, M d, Y') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-4 text-center border-end">
                            <h6 class="text-muted">Shift</h6>
                            <h4 class="mb-1">{{ $todaySchedule->shift_label }}</h4>
                            @if($todaySchedule->is_late && $todaySchedule->status === 'clocked_in')
                            <span class="badge bg-warning text-dark fs-6">
                                <i class="bi bi-exclamation-triangle-fill"></i> Clocked In Late
                            </span>
                            @else
                            <span class="badge {{ $todaySchedule->status_badge }} fs-6">
                                {{ ucfirst(str_replace('_', ' ', $todaySchedule->status)) }}
                            </span>
                            @endif
                        </div>
                        <div class="col-md-4 text-center border-end">
                            <div class="row">
                                <div class="col-6">
                                    <h6 class="text-muted">Clock In</h6>
                                    <h4 class="mb-0 {{ $todaySchedule->clock_in_time ? 'text-success' : 'text-muted' }}">
                                        {{ $todaySchedule->clock_in_time ? $todaySchedule->clock_in_time->format('h:i A') : '--:--' }}
                                    </h4>
                                </div>
                                <div class="col-6">
                                    <h6 class="text-muted">Clock Out</h6>
                                    <h4 class="mb-0 {{ $todaySchedule->clock_out_time ? 'text-danger' : 'text-muted' }}">
                                        {{ $todaySchedule->clock_out_time ? $todaySchedule->clock_out_time->format('h:i A') : '--:--' }}
                                    </h4>
                                </div>
                            </div>
                            @if($todaySchedule->hours_worked)
                            <small class="text-muted mt-2 d-block">
                                <i class="bi bi-hourglass-split"></i> Worked: {{ $todaySchedule->hours_worked }} hours
                            </small>
                            @endif
                        </div>
                        <div class="col-md-4 text-center">
                            @if($todaySchedule->status === 'scheduled')
                                <p class="text-muted mb-2">Please confirm your shift first</p>
                                <button class="btn btn-primary btn-lg" onclick="confirmShift({{ $todaySchedule->id }})">
                                    <i class="bi bi-check-circle"></i> Confirm Shift
                                </button>
                            @elseif($todaySchedule->status === 'confirmed')
                                <button class="btn btn-success btn-lg" onclick="clockIn({{ $todaySchedule->id }})" id="clockInBtn">
                                    <i class="bi bi-box-arrow-in-right"></i> Clock In
                                </button>
                                <p class="text-muted mt-2 small">
                                    <i class="bi bi-info-circle"></i> Available 2 hours before shift
                                </p>
                            @elseif($todaySchedule->status === 'clocked_in')
                                <button class="btn btn-danger btn-lg" onclick="clockOut({{ $todaySchedule->id }})" id="clockOutBtn">
                                    <i class="bi bi-box-arrow-right"></i> Clock Out
                                </button>
                                <p class="text-muted mt-2 small">
                                    <i class="bi bi-clock"></i> Working since {{ $todaySchedule->clock_in_time->format('h:i A') }}
                                </p>
                            @elseif($todaySchedule->status === 'completed')
                                <div class="text-success">
                                    <i class="bi bi-check-circle-fill" style="font-size: 3rem;"></i>
                                    <p class="mb-0 mt-2">Shift Completed!</p>
                                </div>
                            @elseif($todaySchedule->status === 'absent')
                                <div class="text-danger">
                                    <i class="bi bi-x-circle-fill" style="font-size: 3rem;"></i>
                                    <p class="mb-0 mt-2">Marked as Absent</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm bg-light">
                <div class="card-body text-center py-4">
                    <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 text-muted">No Shift Today</h5>
                    <p class="text-muted mb-0">You don't have any scheduled shift for today.</p>
                </div>
            </div>
        </div>
    </div>
    @endif

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
                                        <div class="fw-bold">{{ $schedule->shift_label }}</div>
                                        @if($schedule->clock_in_time)
                                        <small class="d-block">
                                            <i class="bi bi-box-arrow-in-right"></i> {{ $schedule->clock_in_time->format('h:i A') }}
                                            @if($schedule->clock_out_time)
                                            - <i class="bi bi-box-arrow-right"></i> {{ $schedule->clock_out_time->format('h:i A') }}
                                            @endif
                                        </small>
                                        @endif
                                        @if($schedule->is_late)
                                        <span class="badge bg-warning text-dark">Late</span>
                                        @endif
                                        
                                        @if($schedule->status === 'scheduled' && !$isPast)
                                        <button class="btn btn-sm btn-light mt-2 w-100" onclick="confirmShift({{ $schedule->id }})">
                                            <i class="bi bi-check-circle"></i> Confirm
                                        </button>
                                        @endif
                                    </div>
                                    @empty
                                    <div class="text-center py-4 text-muted">
                                        <i class="bi bi-calendar-x" style="font-size: 2rem;"></i>
                                        <p class="mb-0 mt-2">Day off</p>
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
                        <span><span class="badge bg-info">Scheduled</span> - Awaiting confirmation</span>
                        <span><span class="badge bg-primary">Confirmed</span> - Ready to clock in</span>
                        <span><span class="badge bg-success">Clocked In</span> - Currently working</span>
                        <span><span class="badge bg-dark">Completed</span> - Shift done</span>
                        <span><span class="badge bg-danger">Absent</span> - No show</span>
                        <span><span class="badge bg-secondary">Cancelled</span> - Cancelled</span>
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
                    <h5 class="mb-0"><i class="bi bi-list-check"></i> Upcoming Shifts</h5>
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
                                <tr class="{{ $schedule->schedule_date->isToday() ? 'table-primary' : '' }}">
                                    <td>
                                        {{ $schedule->schedule_date->format('M d, Y') }}
                                        @if($schedule->schedule_date->isToday())
                                        <span class="badge bg-primary">Today</span>
                                        @endif
                                    </td>
                                    <td>{{ $schedule->schedule_date->format('l') }}</td>
                                    <td>{{ $schedule->shift_label }}</td>
                                    <td>
                                        <span class="badge {{ $schedule->status_badge }}">{{ ucfirst(str_replace('_', ' ', $schedule->status)) }}</span>
                                        @if($schedule->is_late)
                                        <span class="badge bg-warning text-dark">Late</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($schedule->status === 'scheduled')
                                        <button class="btn btn-sm btn-primary" onclick="confirmShift({{ $schedule->id }})">
                                            <i class="bi bi-check-circle"></i> Confirm
                                        </button>
                                        @elseif($schedule->status === 'confirmed' && $schedule->schedule_date->isToday())
                                        <button class="btn btn-sm btn-success" onclick="clockIn({{ $schedule->id }})">
                                            <i class="bi bi-box-arrow-in-right"></i> Clock In
                                        </button>
                                        @elseif($schedule->status === 'clocked_in')
                                        <button class="btn btn-sm btn-danger" onclick="clockOut({{ $schedule->id }})">
                                            <i class="bi bi-box-arrow-right"></i> Clock Out
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

function clockIn(scheduleId) {
    if (confirm('Are you sure you want to clock in now?')) {
        fetch(`/staff/schedule/${scheduleId}/clock-in`, {
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
                alert(data.message || 'Failed to clock in');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while clocking in.');
        });
    }
}

function clockOut(scheduleId) {
    if (confirm('Are you sure you want to clock out now?')) {
        fetch(`/staff/schedule/${scheduleId}/clock-out`, {
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
                alert(data.message || 'Failed to clock out');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while clocking out.');
        });
    }
}
</script>
@endpush
@endsection
