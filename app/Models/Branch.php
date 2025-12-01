<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'is_active',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all stock entries for this branch
     */
    public function stocks()
    {
        return $this->hasMany(BranchStock::class);
    }

    /**
     * Get stock for a specific product at this branch
     */
    public function getProductStock($productId)
    {
        return $this->stocks()->where('product_id', $productId)->first();
    }
}