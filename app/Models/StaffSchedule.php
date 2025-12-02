<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'branch_id',
        'created_by',
        'schedule_date',
        'start_time',
        'end_time',
        'shift_type',
        'status',
        'notes',
        'clock_in_time',
        'clock_out_time',
        'hours_worked',
        'is_late',
        'is_early_leave',
    ];

    protected $casts = [
        'schedule_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'clock_in_time' => 'datetime',
        'clock_out_time' => 'datetime',
        'is_late' => 'boolean',
        'is_early_leave' => 'boolean',
    ];

    /**
     * Get the staff member for this schedule
     */
    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    /**
     * Get the branch for this schedule
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the manager who created this schedule
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get shift type label
     */
    public function getShiftLabelAttribute()
    {
        return match($this->shift_type) {
            'morning' => 'Morning Shift',
            'afternoon' => 'Afternoon Shift',
            'evening' => 'Evening Shift',
            'full_day' => 'Full Day',
            default => 'Unknown'
        };
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'scheduled' => 'bg-info',
            'confirmed' => 'bg-primary',
            'clocked_in' => 'bg-success',
            'completed' => 'bg-dark',
            'absent' => 'bg-danger',
            'cancelled' => 'bg-secondary',
            default => 'bg-secondary'
        };
    }

    /**
     * Get shift times based on shift type
     */
    public function getShiftTimesAttribute()
    {
        return match($this->shift_type) {
            'morning' => ['start' => '06:00', 'end' => '14:00'],
            'afternoon' => ['start' => '14:00', 'end' => '22:00'],
            'evening' => ['start' => '18:00', 'end' => '00:00'],
            'full_day' => ['start' => '09:00', 'end' => '18:00'],
            default => ['start' => '09:00', 'end' => '18:00']
        };
    }

    /**
     * Check if staff can clock in (2 hours before to 2 hours after start time)
     */
    public function canClockIn()
    {
        if ($this->status !== 'confirmed' || $this->clock_in_time) {
            return false;
        }

        $now = now();
        $shiftDate = $this->schedule_date->format('Y-m-d');
        $shiftStart = \Carbon\Carbon::parse($shiftDate . ' ' . $this->shift_times['start']);
        
        // Can clock in 2 hours before to 2 hours after shift start
        $windowStart = $shiftStart->copy()->subHours(2);
        $windowEnd = $shiftStart->copy()->addHours(2);

        return $now->between($windowStart, $windowEnd);
    }

    /**
     * Check if staff can clock out
     */
    public function canClockOut()
    {
        return $this->status === 'clocked_in' && $this->clock_in_time && !$this->clock_out_time;
    }

    /**
     * Check if this is today's schedule
     */
    public function getIsTodayAttribute()
    {
        return $this->schedule_date->isToday();
    }
}
