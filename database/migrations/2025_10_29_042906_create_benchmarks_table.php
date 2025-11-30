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
        Schema::create('benchmarks', function (Blueprint $table) {
            $table->id();
            $table->decimal('monthly_sales_target', 10, 2)->default(50000.00);
            $table->integer('transaction_target')->default(100);
            $table->decimal('staff_sales_target', 10, 2)->default(10000.00);
            $table->boolean('is_active')->default(true);
            $table->date('effective_from');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('benchmarks');
    }
};