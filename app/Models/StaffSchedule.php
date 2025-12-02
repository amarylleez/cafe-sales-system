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
    ];

    protected $casts = [
        'schedule_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
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
            'completed' => 'bg-success',
            'absent' => 'bg-danger',
            'cancelled' => 'bg-secondary',
            default => 'bg-secondary'
        };
    }
}
