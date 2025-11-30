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
        Schema::create('kpi_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_id')->constrained('kpis')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->date('progress_date'); // Daily tracking
            $table->decimal('daily_value', 15, 2); // Daily achievement value
            $table->decimal('cumulative_value', 15, 2); // Running total for the month
            $table->decimal('progress_percentage', 5, 2)->default(0); // % of target achieved
            $table->boolean('is_completed')->default(false); // Staff can tick when fulfilled
            $table->foreignId('recorded_by')->constrained('users')->onDelete('cascade'); // Staff who recorded
            $table->text('notes')->nullable(); // Any additional notes
            $table->timestamps();
            
            // Unique constraint: one progress entry per KPI per day
            $table->unique(['kpi_id', 'progress_date']);
            
            // Index for faster queries
            $table->index(['branch_id', 'progress_date']);
            $table->index(['kpi_id', 'is_completed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_progress');
    }
};