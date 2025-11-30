<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class DailySale extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'staff_id',
        'sale_date',
        'transaction_id',
        'total_amount',
        'items_count',
        'payment_method',
        'payment_details',
        'notes',
        'status',
        'completed_at',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'total_amount' => 'decimal:2',
        'completed_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Boot method to auto-generate transaction ID
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sale) {
            if (empty($sale->transaction_id)) {
                $sale->transaction_id = 'TXN-' . strtoupper(Str::random(10));
            }
        });
    }

    /**
     * Get the branch that owns this sale
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the staff who made this sale
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    /**
     * Get the user who verified this sale
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get all items in this sale
     */
    public function items(): HasMany
    {
        return $this->hasMany(DailySalesItem::class, 'daily_sale_id');
    }

    /**
     * Calculate total items count from sale items
     */
    public function calculateItemsCount()
    {
        return $this->items()->sum('quantity');
    }

    /**
     * Calculate total amount from sale items
     */
    public function calculateTotalAmount()
    {
        return $this->items()->sum('total');
    }

    /**
     * Verify the sale
     */
    public function verify($userId)
    {
        $this->verified_by = $userId;
        $this->verified_at = now();
        $this->save();
    }

    /**
     * Complete the sale
     */
    public function complete()
    {
        $this->status = 'completed';
        $this->completed_at = now();
        $this->save();
    }

    /**
     * Cancel the sale
     */
    public function cancel()
    {
        $this->status = 'cancelled';
        $this->save();
    }

    /**
     * Scope to get completed sales
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get sales for specific date
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('sale_date', $date);
    }

    /**
     * Scope to get sales for date range
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('sale_date', [$startDate, $endDate]);
    }

    /**
     * Scope to get verified sales
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_by');
    }

    /**
     * Scope to get unverified sales
     */
    public function scopeUnverified($query)
    {
        return $query->whereNull('verified_by');
    }

    /**
     * Check if sale is verified
     */
    public function isVerified(): bool
    {
        return !is_null($this->verified_by);
    }
}