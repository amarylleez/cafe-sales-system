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
        'is_available',
    ];

    protected $casts = [
        'is_available' => 'boolean',
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
            ['stock_quantity' => $defaultQuantity, 'is_available' => true]
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
}
