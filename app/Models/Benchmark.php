<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Benchmark extends Model
{
    use HasFactory;

    protected $fillable = [
        'monthly_sales_target',
        'transaction_target',
        'staff_sales_target',
        'is_active',
        'effective_from'
    ];

    protected $casts = [
        'monthly_sales_target' => 'decimal:2',
        'staff_sales_target' => 'decimal:2',
        'is_active' => 'boolean',
        'effective_from' => 'date'
    ];
}