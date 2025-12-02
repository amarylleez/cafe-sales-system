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
        Schema::table('staff_schedules', function (Blueprint $table) {
            $table->timestamp('clock_in_time')->nullable()->after('status');
            $table->timestamp('clock_out_time')->nullable()->after('clock_in_time');
            $table->decimal('hours_worked', 5, 2)->nullable()->after('clock_out_time');
            $table->boolean('is_late')->default(false)->after('hours_worked');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_schedules', function (Blueprint $table) {
            $table->dropColumn(['clock_in_time', 'clock_out_time', 'hours_worked', 'is_late']);
        });
    }
};
