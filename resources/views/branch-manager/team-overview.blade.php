@extends('layouts.branch-manager')

@section('page-title', 'Team Overview')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header" style="background: linear-gradient(135deg, #423A8E 0%, #00CCCD 100%);">
                    <h5 class="mb-0 text-white">
                        <i class="bi bi-people"></i> Team Overview
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">View and manage your team members at {{ $branchManager->branch->name }}.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Branch Manager Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-person-badge"></i> Branch Manager</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <div class="user-avatar mx-auto" style="width: 100px; height: 100px; font-size: 3rem; background: linear-gradient(135deg, #423A8E 0%, #00CCCD 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                                {{ substr($branchManager->name, 0, 1) }}
                            </div>
                        </div>
                        <div class="col-md-5">
                            <h4>{{ $branchManager->name }}</h4>
                            <p class="mb-1"><i class="bi bi-envelope"></i> {{ $branchManager->email }}</p>
                            <p class="mb-1"><i class="bi bi-building"></i> {{ $branchManager->branch->name }}</p>
                            <p class="mb-0"><i class="bi bi-geo-alt"></i> {{ $branchManager->branch->address }}</p>
                        </div>
                        <div class="col-md-5">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h3 class="text-primary mb-0">{{ $staffMembers->count() }}</h3>
                                            <small class="text-muted">Team Members</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h3 class="text-success mb-0">{{ $staffMembers->count() }}</h3>
                                            <small class="text-muted">Active Staff</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Staff Members -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-people-fill"></i> Staff Members</h5>
                    <div class="input-group" style="max-width: 300px;">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="searchStaff" placeholder="Search staff...">
                    </div>
                </div>
                <div class="card-body">
                    @if($staffMembers->count() > 0)
                    <div class="row g-4" id="staffContainer">
                        @foreach($staffMembers as $staff)
                        <div class="col-md-6 col-lg-4 staff-card">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <div class="user-avatar mx-auto" style="width: 70px; height: 70px; font-size: 2rem; background: linear-gradient(135deg, #423A8E 0%, #00CCCD 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                                            {{ substr($staff->name, 0, 1) }}
                                        </div>
                                    </div>
                                    <h6 class="text-center mb-2">{{ $staff->name }}</h6>
                                    <p class="text-center text-muted small mb-3">
                                        <i class="bi bi-envelope"></i> {{ $staff->email }}
                                    </p>
                                    
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-sm btn-outline-primary" onclick="viewStaffPerformance({{ $staff->id }})">
                                            <i class="bi bi-graph-up"></i> View Performance
                                        </button>
                                    </div>
                                </div>
                                <div class="card-footer bg-light text-center">
                                    <small class="text-muted">
                                        <i class="bi bi-clock"></i> Member since {{ $staff->created_at->format('M Y') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No staff members registered yet.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Staff Schedule Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-calendar-week"></i> Staff Schedule</h5>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                        <i class="bi bi-plus-circle"></i> Add Schedule
                    </button>
                </div>
                <div class="card-body">
                    <!-- Week Navigation -->
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <a href="?week={{ $weekOffset - 1 }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-chevron-left"></i> Previous Week
                            </a>
                            <h6 class="mb-0 mx-2">
                                {{ $weekStart->format('M d') }} - {{ $weekEnd->format('M d, Y') }}
                            </h6>
                            <a href="?week={{ $weekOffset + 1 }}" class="btn btn-outline-primary btn-sm">
                                Next Week <i class="bi bi-chevron-right"></i>
                            </a>
                            @if($weekOffset != 0)
                            <a href="?week=0" class="btn btn-link btn-sm">Today</a>
                            @endif
                        </div>
                    </div>

                    <!-- Schedule Calendar View -->
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
                                                {{ $schedule->shift_label }}
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

                    <!-- Legend -->
                    <div class="mt-3 pt-3 border-top">
                        <h6 class="mb-2">Schedule Status Legend</h6>
                        <div class="d-flex flex-wrap gap-3">
                            <span><span class="badge bg-info">Scheduled</span> - Pending confirmation</span>
                            <span><span class="badge bg-primary">Confirmed</span> - Staff confirmed</span>
                            <span><span class="badge bg-success">Clocked In</span> - Currently working</span>
                            <span><span class="badge bg-dark">Completed</span> - Shift completed</span>
                            <span><span class="badge bg-danger">Absent</span> - Did not show</span>
                            <span><span class="badge bg-secondary">Cancelled</span> - Cancelled</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Staff Performance Modal -->
<div class="modal fade" id="staffPerformanceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Staff Performance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="performanceContent">
                <div class="text-center">
                    <div class="spinner-border" role="status"></div>
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
                    <div class="mb-3">
                        <label class="form-label">Shift Type</label>
                        <select class="form-select" name="shift_type" id="scheduleShiftType" required>
                            <option value="morning">Morning Shift (6:00 AM - 2:00 PM)</option>
                            <option value="afternoon">Afternoon Shift (2:00 PM - 10:00 PM)</option>
                            <option value="evening">Evening Shift (6:00 PM - 12:00 AM)</option>
                            <option value="full_day">Full Day (9:00 AM - 6:00 PM)</option>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const performanceModal = new bootstrap.Modal(document.getElementById('staffPerformanceModal'));
const addScheduleModal = new bootstrap.Modal(document.getElementById('addScheduleModal'));
const viewScheduleModal = new bootstrap.Modal(document.getElementById('viewScheduleModal'));
let currentScheduleId = null;

// Set default date to today
document.getElementById('scheduleDate').valueAsDate = new Date();

// Search staff
document.getElementById('searchStaff').addEventListener('input', function() {
    const query = this.value.toLowerCase();
    document.querySelectorAll('.staff-card').forEach(card => {
        const text = card.textContent.toLowerCase();
        card.style.display = text.includes(query) ? '' : 'none';
    });
});

// View staff performance
function viewStaffPerformance(staffId) {
    document.getElementById('performanceContent').innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status"></div>
        </div>
    `;
    
    performanceModal.show();
    
    fetch(`/branch-manager/staff/${staffId}/performance`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const staff = data.staff;
                document.getElementById('performanceContent').innerHTML = `
                    <div class="row mb-4">
                        <div class="col-md-4 text-center">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h3 class="text-primary">RM ${parseFloat(data.totalSales).toFixed(2)}</h3>
                                    <small>Total Sales</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h3 class="text-success">${data.totalTransactions}</h3>
                                    <small>Transactions</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h3 class="text-info">RM ${parseFloat(data.avgTransaction).toFixed(2)}</h3>
                                    <small>Avg. Transaction</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <h6>Sales Trend (Last 7 Days)</h6>
                            <canvas id="staffSalesChart"></canvas>
                        </div>
                    </div>
                `;
                
                // Create chart
                const ctx = document.getElementById('staffSalesChart');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.chartData.labels,
                        datasets: [{
                            label: 'Daily Sales',
                            data: data.chartData.values,
                            borderColor: '#423A8E',
                            backgroundColor: 'rgba(66, 58, 142, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'RM ' + value;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('performanceContent').innerHTML = `
                <div class="alert alert-danger">Failed to load performance data</div>
            `;
        });
}

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
    
    const statusOptions = ['scheduled', 'confirmed', 'clocked_in', 'completed', 'absent', 'cancelled'];
    const statusLabels = {
        'scheduled': 'Scheduled',
        'confirmed': 'Confirmed',
        'clocked_in': 'Clocked In',
        'completed': 'Completed',
        'absent': 'Absent',
        'cancelled': 'Cancelled'
    };
    
    let statusOptionsHtml = statusOptions.map(s => 
        `<option value="${s}" ${schedule.status === s ? 'selected' : ''}>${statusLabels[s]}</option>`
    ).join('');
    
    const shiftTimes = {
        'morning': '6:00 AM - 2:00 PM',
        'afternoon': '2:00 PM - 10:00 PM',
        'evening': '6:00 PM - 12:00 AM',
        'full_day': '9:00 AM - 6:00 PM'
    };

    // Format clock times
    let clockInfo = '';
    if (schedule.clock_in_time) {
        const clockIn = new Date(schedule.clock_in_time);
        clockInfo += `<div class="mb-2"><i class="bi bi-box-arrow-in-right text-success"></i> <strong>Clocked In:</strong> ${clockIn.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}</div>`;
    }
    if (schedule.clock_out_time) {
        const clockOut = new Date(schedule.clock_out_time);
        clockInfo += `<div class="mb-2"><i class="bi bi-box-arrow-right text-danger"></i> <strong>Clocked Out:</strong> ${clockOut.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}</div>`;
    }
    if (schedule.hours_worked) {
        clockInfo += `<div class="mb-2"><i class="bi bi-hourglass-split text-info"></i> <strong>Hours Worked:</strong> ${schedule.hours_worked} hrs</div>`;
    }
    if (schedule.is_late) {
        clockInfo += `<div class="mb-2"><span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle"></i> Late Arrival</span></div>`;
    }
    
    document.getElementById('scheduleDetailsContent').innerHTML = `
        <div class="mb-3">
            <label class="form-label text-muted">Staff Member</label>
            <p class="fw-bold">${schedule.staff ? schedule.staff.name : 'Unknown'}</p>
        </div>
        <div class="mb-3">
            <label class="form-label text-muted">Date</label>
            <p class="fw-bold">${new Date(schedule.schedule_date).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</p>
        </div>
        <div class="mb-3">
            <label class="form-label text-muted">Shift</label>
            <p class="fw-bold">${schedule.shift_type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</p>
            <small class="text-muted"><i class="bi bi-clock"></i> ${shiftTimes[schedule.shift_type] || 'N/A'}</small>
        </div>
        ${clockInfo ? `<div class="mb-3 p-3 bg-light rounded"><label class="form-label text-muted">Attendance</label>${clockInfo}</div>` : ''}
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


