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
        // Add expiry_hours column to categories
        Schema::table('categories', function (Blueprint $table) {
            $table->integer('expiry_hours')->nullable()->after('description');
        });

        // Set default expiry hours for each category
        // NULL = no expiration (made fresh upon order)
        // 24 = expires in 24 hours
        DB::table('categories')->where('name', 'Waffle')->update(['expiry_hours' => null]);
        DB::table('categories')->where('name', 'Air Cup')->update(['expiry_hours' => null]);
        DB::table('categories')->where('name', 'Aneka Mee/Bihun')->update(['expiry_hours' => 24]);
        DB::table('categories')->where('name', 'Aneka Roti')->update(['expiry_hours' => 24]);
        DB::table('categories')->where('name', 'Pau Kukus')->update(['expiry_hours' => 24]);
        DB::table('categories')->where('name', 'Spaghetti')->update(['expiry_hours' => 24]);
        DB::table('categories')->where('name', 'Kuih-Muih')->update(['expiry_hours' => 24]);
        DB::table('categories')->where('name', 'Goreng-goreng')->update(['expiry_hours' => 24]);
        DB::table('categories')->where('name', 'Lain-lain')->update(['expiry_hours' => 24]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('expiry_hours');
        });
    }
};
