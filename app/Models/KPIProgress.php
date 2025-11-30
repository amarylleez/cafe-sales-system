<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KPIProgress extends Model
{
    use HasFactory;

    protected $table = 'kpi_progress';

    protected $fillable = [
        'kpi_id',
        'branch_id',
        'progress_date',
        'daily_value',
        'cumulative_value',
        'progress_percentage',
        'is_completed',
        'recorded_by',
        'notes',
    ];

    protected $casts = [
        'progress_date' => 'date',
        'daily_value' => 'decimal:2',
        'cumulative_value' => 'decimal:2',
        'progress_percentage' => 'decimal:2',
        'is_completed' => 'boolean',
    ];

    /**
     * Get the KPI that owns this progress record
     */
    public function kpi(): BelongsTo
    {
        return $this->belongsTo(KPI::class);
    }

    /**
     * Get the branch that owns this progress record
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user (staff) who recorded this progress
     */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Calculate and update cumulative value
     */
    public function updateCumulativeValue()
    {
        $kpi = $this->kpi;
        
        // Get all progress for this KPI up to current date
        $cumulative = self::where('kpi_id', $this->kpi_id)
            ->whereMonth('progress_date', $this->progress_date->month)
            ->whereYear('progress_date', $this->progress_date->year)
            ->where('progress_date', '<=', $this->progress_date)
            ->sum('daily_value');
        
        $this->cumulative_value = $cumulative;
        
        // Calculate progress percentage
        if ($kpi->target_value > 0) {
            $this->progress_percentage = min(($cumulative / $kpi->target_value) * 100, 100);
        }
        
        $this->save();
    }

    /**
     * Mark progress as completed
     */
    public function markAsCompleted()
    {
        $this->is_completed = true;
        $this->save();
    }

    /**
     * Scope to get completed progress
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    /**
     * Scope to get progress for specific date
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('progress_date', $date);
    }

    /**
     * Scope to get progress for specific month
     */
    public function scopeForMonth($query, $date)
    {
        return $query->whereMonth('progress_date', $date->month)
                     ->whereYear('progress_date', $date->year);
    }

    /**
     * Check if daily target is met
     */
    public function isDailyTargetMet(): bool
    {
        $kpi = $this->kpi;
        
        // Calculate daily target (monthly target / days in month)
        $daysInMonth = $this->progress_date->daysInMonth;
        $dailyTarget = $kpi->target_value / $daysInMonth;
        
        return $this->daily_value >= $dailyTarget;
    }
}