<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the old constraint
        DB::statement('ALTER TABLE staff_schedules DROP CONSTRAINT IF EXISTS staff_schedules_status_check');
        
        // Add new constraint that includes 'clocked_in'
        DB::statement("ALTER TABLE staff_schedules ADD CONSTRAINT staff_schedules_status_check CHECK (status IN ('scheduled', 'confirmed', 'clocked_in', 'completed', 'absent', 'cancelled'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE staff_schedules DROP CONSTRAINT IF EXISTS staff_schedules_status_check');
        DB::statement("ALTER TABLE staff_schedules ADD CONSTRAINT staff_schedules_status_check CHECK (status IN ('scheduled', 'confirmed', 'completed', 'absent', 'cancelled'))");
    }
};
