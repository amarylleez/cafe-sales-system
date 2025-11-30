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
        Schema::create('daily_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('staff_id')->constrained('users')->onDelete('cascade'); // Staff who made the sale
            $table->date('sale_date');
            $table->string('transaction_id')->unique(); // Unique transaction identifier
            $table->decimal('total_amount', 15, 2); // Total sale amount
            $table->integer('items_count')->default(1); // Number of items in this transaction
            $table->enum('payment_method', ['cash', 'card', 'e-wallet', 'bank_transfer', 'other'])->default('cash');
            $table->text('payment_details')->nullable(); // Additional payment info
            $table->text('notes')->nullable(); // Any special notes about the sale
            $table->enum('status', ['completed', 'pending', 'cancelled', 'refunded'])->default('completed');
            $table->timestamp('completed_at')->nullable(); // When sale was completed
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null'); // Branch manager verification
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            
            // Indexes for faster queries
            $table->index(['branch_id', 'sale_date']);
            $table->index(['staff_id', 'sale_date']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_sales');
    }
};