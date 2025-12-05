<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailySalesItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_sale_id',
        'product_id',
        'quantity',
        'unit_price',
        'subtotal',
        'discount',
        'total',
        'notes',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Boot method to auto-calculate amounts
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            $item->calculateAmounts();
        });

        static::updating(function ($item) {
            $item->calculateAmounts();
        });
    }

    /**
     * Get the sale that owns this item
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(DailySale::class, 'daily_sale_id');
    }

    /**
     * Alias for sale() relationship
     */
    public function dailySale(): BelongsTo
    {
        return $this->belongsTo(DailySale::class, 'daily_sale_id');
    }

    /**
     * Get the product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate subtotal, discount, and total
     */
    public function calculateAmounts()
    {
        $this->subtotal = $this->quantity * $this->unit_price;
        $this->total = $this->subtotal - $this->discount;
    }

    /**
     * Apply discount
     */
    public function applyDiscount($discountAmount)
    {
        $this->discount = $discountAmount;
        $this->calculateAmounts();
        $this->save();
    }

    /**
     * Get product name (convenience method)
     */
    public function getProductName()
    {
        return $this->product ? $this->product->name : 'Unknown Product';
    }

    /**
     * Scope to get items for specific product
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }
}