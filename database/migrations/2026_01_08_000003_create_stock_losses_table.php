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
        Schema::create('stock_losses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('unit_cost', 10, 2)->default(0); // Cost per unit at time of loss
            $table->decimal('total_loss', 10, 2)->default(0); // quantity * unit_cost
            $table->enum('loss_type', ['expired', 'damaged', 'manual_writeoff', 'other'])->default('expired');
            $table->text('notes')->nullable();
            $table->date('loss_date');
            $table->timestamp('stock_added_at')->nullable(); // When the stock was originally added
            $table->timestamp('expired_at')->nullable(); // When the stock expired
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Indexes for reporting
            $table->index(['branch_id', 'loss_date']);
            $table->index(['product_id', 'loss_date']);
            $table->index(['loss_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_losses');
    }
};
