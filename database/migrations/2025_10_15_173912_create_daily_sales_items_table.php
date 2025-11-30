<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('daily_sales_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_sale_id')->constrained('daily_sales')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity'); // How many of this product sold
            $table->decimal('unit_price', 10, 2); // Price per unit at time of sale
            $table->decimal('subtotal', 15, 2); // quantity * unit_price
            $table->decimal('discount', 10, 2)->default(0); // Any discount applied
            $table->decimal('total', 15, 2); // subtotal - discount
            $table->text('notes')->nullable(); // Special requests, customizations
            $table->timestamps();
            
            // Indexes for faster queries
            $table->index(['daily_sale_id']);
            $table->index(['product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_sales_items');
    }
};