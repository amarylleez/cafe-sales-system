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
        Schema::create('kpis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->string('kpi_name'); // e.g., "Monthly Sales Target", "Items Sold Target"
            $table->string('kpi_type'); // 'sales_amount', 'transaction_count', 'items_sold'
            $table->decimal('target_value', 15, 2); // The target amount/number
            $table->date('target_month'); // Which month this target is for
            $table->enum('status', ['active', 'completed', 'expired'])->default('active');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->decimal('reward_amount', 10, 2)->nullable(); // Bonus if target met
            $table->text('reward_description')->nullable(); // Description of reward
            $table->decimal('penalty_amount', 10, 2)->nullable(); // Penalty if not met
            $table->text('penalty_description')->nullable(); // Description of penalty
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // HQ admin who set this
            $table->text('description')->nullable();
            $table->timestamps();
            
            // Index for faster queries
            $table->index(['branch_id', 'target_month', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpis');
    }
};