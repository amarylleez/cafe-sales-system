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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Who receives this notification
            $table->foreignId('sender_id')->nullable()->constrained('users')->onDelete('set null'); // Who sent it (system or HQ)
            $table->enum('type', [
                'kpi_target_not_met',
                'kpi_target_achieved', 
                'important_notice',
                'low_stock_alert',
                'sales_milestone',
                'system_announcement',
                'other'
            ])->default('other');
            $table->string('title'); // Notification title
            $table->text('message'); // Notification content
            $table->json('data')->nullable(); // Additional data (KPI details, loss amount, etc.)
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->string('action_url')->nullable(); // Link to relevant page
            $table->timestamps();
            
            // Indexes for faster queries
            $table->index(['user_id', 'is_read']);
            $table->index(['branch_id', 'created_at']);
            $table->index(['type', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};