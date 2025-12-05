<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'product_id',
        'stock_quantity',
        'batch_number',
        'expiry_date',
        'received_date',
        'cost_at_purchase',
        'is_available',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'expiry_date' => 'date',
        'received_date' => 'date',
        'cost_at_purchase' => 'decimal:2',
    ];

    /**
     * Get the branch that owns this stock
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the product for this stock
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get or create stock for a branch and product
     */
    public static function getOrCreate($branchId, $productId, $defaultQuantity = 0)
    {
        return self::firstOrCreate(
            ['branch_id' => $branchId, 'product_id' => $productId],
            ['stock_quantity' => $defaultQuantity, 'is_available' => true, 'received_date' => now()]
        );
    }

    /**
     * Check if stock is low (less than 10)
     */
    public function isLowStock(): bool
    {
        return $this->stock_quantity < 10;
    }

    /**
     * Check if stock is out
     */
    public function isOutOfStock(): bool
    {
        return $this->stock_quantity <= 0;
    }

    /**
     * Check if stock is expired
     */
    public function isExpired(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date->isPast();
    }

    /**
     * Check if stock is expiring soon (within specified days)
     */
    public function isExpiringSoon(int $days = 7): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date->isBetween(now(), now()->addDays($days));
    }

    /**
     * Get days until expiry
     */
    public function getDaysUntilExpiry(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }
        return now()->diffInDays($this->expiry_date, false);
    }

    /**
     * Calculate potential loss from expired/expiring stock
     */
    public function getPotentialLoss(): float
    {
        if (!$this->isExpired() && !$this->isExpiringSoon()) {
            return 0;
        }
        
        $costPrice = $this->cost_at_purchase ?? $this->product->cost_price ?? 0;
        return $this->stock_quantity * $costPrice;
    }

    /**
     * Scope to get expired stock
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
                     ->where('expiry_date', '<', now())
                     ->where('stock_quantity', '>', 0);
    }

    /**
     * Scope to get expiring soon stock
     */
    public function scopeExpiringSoon($query, int $days = 7)
    {
        return $query->whereNotNull('expiry_date')
                     ->whereBetween('expiry_date', [now(), now()->addDays($days)])
                     ->where('stock_quantity', '>', 0);
    }
}
