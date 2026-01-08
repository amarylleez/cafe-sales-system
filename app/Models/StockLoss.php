<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockLoss extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'product_id',
        'quantity',
        'unit_cost',
        'total_loss',
        'loss_type',
        'notes',
        'loss_date',
        'stock_added_at',
        'expired_at',
        'processed_by',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'total_loss' => 'decimal:2',
        'loss_date' => 'date',
        'stock_added_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    /**
     * Get the branch for this loss
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the product for this loss
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who processed this loss
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Scope to filter by loss type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('loss_type', $type);
    }

    /**
     * Scope to filter by branch
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('loss_date', [$startDate, $endDate]);
    }

    /**
     * Get total loss amount for a branch within a date range
     */
    public static function getTotalLoss($branchId, $startDate = null, $endDate = null)
    {
        $query = self::where('branch_id', $branchId);
        
        if ($startDate && $endDate) {
            $query->whereBetween('loss_date', [$startDate, $endDate]);
        }
        
        return $query->sum('total_loss');
    }
}
