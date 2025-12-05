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
        Schema::table('branch_stocks', function (Blueprint $table) {
            $table->string('batch_number')->nullable()->after('stock_quantity');
            $table->date('expiry_date')->nullable()->after('batch_number');
            $table->date('received_date')->nullable()->after('expiry_date');
            $table->decimal('cost_at_purchase', 8, 2)->nullable()->after('received_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branch_stocks', function (Blueprint $table) {
            $table->dropColumn(['batch_number', 'expiry_date', 'received_date', 'cost_at_purchase']);
        });
    }
};
