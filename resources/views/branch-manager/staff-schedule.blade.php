@extends('layouts.branch-manager')

@section('page-title', 'Staff Schedule')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header" style="background: linear-gradient(135deg, #423A8E 0%, #00CCCD 100%);">
                    <h5 class="mb-0 text-white">
                        <i class="bi bi-calendar-week"></i> Staff Schedule
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">Manage and view staff schedules for {{ $branch->name }}.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Week Navigation & Actions -->
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
                        <a href="?week=0" class="btn btn-link">Today</a>
                        @endif
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                        <i class="bi bi-plus-circle"></i> Add Schedule
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Schedule Calendar View -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead>
                                <tr class="bg-light">
                                    <th style="width: 150px;" class="text-center">Staff</th>
                                    @for($date = $weekStart->copy(); $date <= $weekEnd; $date->addDay())
                                    <th class="text-center {{ $date->isToday() ? 'bg-primary text-white' : '' }}">
                                        <div>{{ $date->format('D') }}</div>
                                        <small>{{ $date->format('M d') }}</small>
                                    </th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($staffMembers as $staff)
                                <tr>
                                    <td class="align-middle">
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar me-2" style="width: 35px; height: 35px; font-size: 0.9rem; background: linear-gradient(135deg, #423A8E 0%, #00CCCD 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                                {{ substr($staff->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="fw-medium">{{ $staff->name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    @for($date = $weekStart->copy(); $date <= $weekEnd; $date->addDay())
                                    @php
                                        $dateKey = $date->format('Y-m-d');
                                        $daySchedules = $schedules->where('staff_id', $staff->id)
                                                                  ->filter(fn($s) => $s->schedule_date->format('Y-m-d') === $dateKey);
                                    @endphp
                                    <td class="text-center align-middle {{ $date->isToday() ? 'bg-light' : '' }}" style="min-width: 120px;">
                                        @forelse($daySchedules as $schedule)
                                        <div class="schedule-item mb-1" 
                                             data-schedule-id="{{ $schedule->id }}"
                                             style="cursor: pointer;"
                                             onclick="viewScheduleDetails({{ $schedule->id }}, {{ json_encode($schedule) }})">
                                            <span class="badge {{ $schedule->status_badge }} w-100 py-2">
                                                <i class="bi bi-clock"></i>
                                                {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }}
                                                <br>
                                                <small>{{ $schedule->shift_label }}</small>
                                            </span>
                                        </div>
                                        @empty
                                        <button class="btn btn-sm btn-outline-secondary border-dashed w-100" 
                                                onclick="openAddScheduleForStaff({{ $staff->id }}, '{{ $staff->name }}', '{{ $dateKey }}')">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                        @endforelse
                                    </td>
                                    @endfor
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-2">No staff members in this branch yet.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
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
                        <span><span class="badge bg-info">Scheduled</span> - Pending confirmation</span>
                        <span><span class="badge bg-primary">Confirmed</span> - Staff confirmed</span>
                        <span><span class="badge bg-success">Completed</span> - Shift completed</span>
                        <span><span class="badge bg-danger">Absent</span> - Did not show</span>
                        <span><span class="badge bg-secondary">Cancelled</span> - Cancelled</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Schedule Modal -->
<div class="modal fade" id="addScheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-calendar-plus"></i> Add New Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addScheduleForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Staff Member</label>
                        <select class="form-select" name="staff_id" id="scheduleStaffId" required>
                            <option value="">Select Staff</option>
                            @foreach($staffMembers as $staff)
                            <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" name="schedule_date" id="scheduleDate" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">Start Time</label>
                            <input type="time" class="form-control" name="start_time" id="scheduleStartTime" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">End Time</label>
                            <input type="time" class="form-control" name="end_time" id="scheduleEndTime" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Shift Type</label>
                        <select class="form-select" name="shift_type" id="scheduleShiftType" required>
                            <option value="morning">Morning Shift (6AM - 2PM)</option>
                            <option value="afternoon">Afternoon Shift (2PM - 10PM)</option>
                            <option value="evening">Evening Shift (6PM - 12AM)</option>
                            <option value="full_day">Full Day</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" name="notes" id="scheduleNotes" rows="2" placeholder="Any special instructions..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Create Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View/Edit Schedule Modal -->
<div class="modal fade" id="viewScheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-calendar-event"></i> Schedule Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="scheduleDetailsContent">
                <!-- Content loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="deleteScheduleBtn" onclick="deleteSchedule()">
                    <i class="bi bi-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .border-dashed {
        border-style: dashed !important;
    }
    .schedule-item:hover .badge {
        opacity: 0.8;
        transform: scale(1.02);
    }
    .schedule-item .badge {
        transition: all 0.2s;
    }
</style>
@endpush

@push('scripts')
<script>
const addScheduleModal = new bootstrap.Modal(document.getElementById('addScheduleModal'));
const viewScheduleModal = new bootstrap.Modal(document.getElementById('viewScheduleModal'));
let currentScheduleId = null;

// Set default date to today
document.getElementById('scheduleDate').valueAsDate = new Date();

// Auto-fill times based on shift type
document.getElementById('scheduleShiftType').addEventListener('change', function() {
    const times = {
        'morning': { start: '06:00', end: '14:00' },
        'afternoon': { start: '14:00', end: '22:00' },
        'evening': { start: '18:00', end: '00:00' },
        'full_day': { start: '09:00', end: '18:00' }
    };
    
    if (times[this.value]) {
        document.getElementById('scheduleStartTime').value = times[this.value].start;
        document.getElementById('scheduleEndTime').value = times[this.value].end;
    }
});

// Open modal with pre-filled staff and date
function openAddScheduleForStaff(staffId, staffName, date) {
    document.getElementById('scheduleStaffId').value = staffId;
    document.getElementById('scheduleDate').value = date;
    addScheduleModal.show();
}

// Submit new schedule
document.getElementById('addScheduleForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        staff_id: document.getElementById('scheduleStaffId').value,
        schedule_date: document.getElementById('scheduleDate').value,
        start_time: document.getElementById('scheduleStartTime').value,
        end_time: document.getElementById('scheduleEndTime').value,
        shift_type: document.getElementById('scheduleShiftType').value,
        notes: document.getElementById('scheduleNotes').value
    };
    
    fetch('/branch-manager/staff-schedule', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            addScheduleModal.hide();
            location.reload();
        } else {
            alert(data.message || 'Failed to create schedule');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while creating the schedule.');
    });
});

// View schedule details
function viewScheduleDetails(scheduleId, schedule) {
    currentScheduleId = scheduleId;
    
    const statusOptions = ['scheduled', 'confirmed', 'completed', 'absent', 'cancelled'];
    const statusLabels = {
        'scheduled': 'Scheduled',
        'confirmed': 'Confirmed',
        'completed': 'Completed',
        'absent': 'Absent',
        'cancelled': 'Cancelled'
    };
    
    let statusOptionsHtml = statusOptions.map(s => 
        `<option value="${s}" ${schedule.status === s ? 'selected' : ''}>${statusLabels[s]}</option>`
    ).join('');
    
    document.getElementById('scheduleDetailsContent').innerHTML = `
        <div class="mb-3">
            <label class="form-label text-muted">Staff Member</label>
            <p class="fw-bold">${schedule.staff ? schedule.staff.name : 'Unknown'}</p>
        </div>
        <div class="mb-3">
            <label class="form-label text-muted">Date</label>
            <p class="fw-bold">${new Date(schedule.schedule_date).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</p>
        </div>
        <div class="row mb-3">
            <div class="col-6">
                <label class="form-label text-muted">Start Time</label>
                <p class="fw-bold">${schedule.start_time}</p>
            </div>
            <div class="col-6">
                <label class="form-label text-muted">End Time</label>
                <p class="fw-bold">${schedule.end_time}</p>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label text-muted">Shift Type</label>
            <p class="fw-bold">${schedule.shift_type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</p>
        </div>
        <div class="mb-3">
            <label class="form-label">Update Status</label>
            <select class="form-select" id="scheduleStatusSelect" onchange="updateScheduleStatus(${scheduleId}, this.value)">
                ${statusOptionsHtml}
            </select>
        </div>
        ${schedule.notes ? `
        <div class="mb-3">
            <label class="form-label text-muted">Notes</label>
            <p>${schedule.notes}</p>
        </div>
        ` : ''}
    `;
    
    viewScheduleModal.show();
}

// Update schedule status
function updateScheduleStatus(scheduleId, status) {
    fetch(`/branch-manager/staff-schedule/${scheduleId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || 'Failed to update status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the status.');
    });
}

// Delete schedule
function deleteSchedule() {
    if (!currentScheduleId) return;
    
    if (confirm('Are you sure you want to delete this schedule?')) {
        fetch(`/branch-manager/staff-schedule/${currentScheduleId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                viewScheduleModal.hide();
                location.reload();
            } else {
                alert(data.message || 'Failed to delete schedule');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the schedule.');
        });
    }
}
</script>
@endpush
@endsection
