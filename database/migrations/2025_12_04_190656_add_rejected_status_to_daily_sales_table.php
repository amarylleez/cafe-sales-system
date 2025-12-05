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
        // For PostgreSQL, we need to drop and recreate the check constraint
        // First, drop the existing constraint
        DB::statement("ALTER TABLE daily_sales DROP CONSTRAINT IF EXISTS daily_sales_status_check");
        
        // Add the new constraint with 'rejected' included
        DB::statement("ALTER TABLE daily_sales ADD CONSTRAINT daily_sales_status_check CHECK (status::text = ANY (ARRAY['completed'::text, 'pending'::text, 'cancelled'::text, 'refunded'::text, 'rejected'::text]))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original constraint without 'rejected'
        DB::statement("ALTER TABLE daily_sales DROP CONSTRAINT IF EXISTS daily_sales_status_check");
        DB::statement("ALTER TABLE daily_sales ADD CONSTRAINT daily_sales_status_check CHECK (status::text = ANY (ARRAY['completed'::text, 'pending'::text, 'cancelled'::text, 'refunded'::text]))");
    }
};