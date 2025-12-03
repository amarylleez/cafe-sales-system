<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'user_id',
        'sender_id',
        'type',
        'title',
        'message',
        'data',
        'priority',
        'is_read',
        'read_at',
        'action_url',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Get the branch that owns this notification
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user who receives this notification
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who sent this notification
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the target branch ID from data (for broadcasts)
     */
    public function getTargetBranchIdAttribute()
    {
        return $this->data['target_branch_id'] ?? null;
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        $this->is_read = true;
        $this->read_at = now();
        $this->save();
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread()
    {
        $this->is_read = false;
        $this->read_at = null;
        $this->save();
    }

    /**
     * Create a KPI target not met notification
     */
    public static function createKPITargetNotMet($kpi, $userId, $loss)
    {
        return self::create([
            'branch_id' => $kpi->branch_id,
            'user_id' => $userId,
            'sender_id' => null, // System generated
            'type' => 'kpi_target_not_met',
            'title' => 'KPI Target Not Met',
            'message' => "The KPI '{$kpi->kpi_name}' for " . $kpi->target_month->format('F Y') . " has not been met.",
            'data' => [
                'kpi_id' => $kpi->id,
                'target_value' => $kpi->target_value,
                'current_value' => $kpi->getCurrentProgress(),
                'loss' => $loss,
                'date' => now()->toDateString(),
            ],
            'priority' => 'high',
            'action_url' => route('staff.kpi.view', $kpi->id),
        ]);
    }

    /**
     * Create a KPI target achieved notification
     */
    public static function createKPITargetAchieved($kpi, $userId)
    {
        return self::create([
            'branch_id' => $kpi->branch_id,
            'user_id' => $userId,
            'sender_id' => null,
            'type' => 'kpi_target_achieved',
            'title' => 'Congratulations! KPI Target Achieved',
            'message' => "The KPI '{$kpi->kpi_name}' target has been achieved!",
            'data' => [
                'kpi_id' => $kpi->id,
                'target_value' => $kpi->target_value,
                'achieved_value' => $kpi->getCurrentProgress(),
                'reward' => $kpi->reward_amount,
                'date' => now()->toDateString(),
            ],
            'priority' => 'medium',
            'action_url' => route('staff.kpi.view', $kpi->id),
        ]);
    }

    /**
     * Create an important notice
     */
    public static function createImportantNotice($branchId, $userId, $senderId, $title, $message, $actionUrl = null)
    {
        return self::create([
            'branch_id' => $branchId,
            'user_id' => $userId,
            'sender_id' => $senderId,
            'type' => 'important_notice',
            'title' => $title,
            'message' => $message,
            'priority' => 'high',
            'action_url' => $actionUrl,
        ]);
    }

    /**
     * Scope to get unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope to get read notifications
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope to get notifications by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get notifications by priority
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope to get recent notifications
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get unread count for a user
     */
    public static function getUnreadCount($userId)
    {
        return self::where('user_id', $userId)->unread()->count();
    }
}