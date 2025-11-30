<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KPI extends Model
{
    use HasFactory;

    protected $table = 'kpis';

    protected $fillable = [
        'branch_id',
        'kpi_name',
        'kpi_type',
        'target_value',
        'target_month',
        'status',
        'priority',
        'reward_amount',
        'reward_description',
        'penalty_amount',
        'penalty_description',
        'created_by',
        'description',
    ];

    protected $casts = [
        'target_value' => 'decimal:2',
        'target_month' => 'date',
        'reward_amount' => 'decimal:2',
        'penalty_amount' => 'decimal:2',
    ];

    /**
     * Get the branch that owns this KPI
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user (HQ admin) who created this KPI
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all progress records for this KPI
     */
    public function progress(): HasMany
    {
        return $this->hasMany(KPIProgress::class, 'kpi_id');
    }

    /**
     * Get the current month's cumulative progress
     */
    public function getCurrentProgress()
    {
        return $this->progress()
            ->whereMonth('progress_date', $this->target_month->month)
            ->whereYear('progress_date', $this->target_month->year)
            ->sum('daily_value');
    }

    /**
     * Get progress percentage
     */
    public function getProgressPercentage()
    {
        $current = $this->getCurrentProgress();
        if ($this->target_value == 0) return 0;
        
        return min(($current / $this->target_value) * 100, 100);
    }

    /**
     * Check if KPI target is met
     */
    public function isTargetMet(): bool
    {
        return $this->getCurrentProgress() >= $this->target_value;
    }

    /**
     * Get remaining value to achieve target
     */
    public function getRemainingValue()
    {
        return max($this->target_value - $this->getCurrentProgress(), 0);
    }

    /**
     * Scope to get active KPIs
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get KPIs for specific month
     */
    public function scopeForMonth($query, $date)
    {
        return $query->whereMonth('target_month', $date->month)
                     ->whereYear('target_month', $date->year);
    }

    /**
     * Scope to get KPIs by priority
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }
}