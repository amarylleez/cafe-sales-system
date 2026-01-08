<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get the Aneka Nasi category ID
        $category = DB::table('categories')->where('name', 'Aneka Nasi')->first();
        
        if ($category) {
            // Get all product IDs in this category
            $productIds = DB::table('products')->where('category_id', $category->id)->pluck('id');
            
            if ($productIds->count() > 0) {
                // Delete related branch stocks
                DB::table('branch_stocks')->whereIn('product_id', $productIds)->delete();
                
                // Delete related stock logs
                DB::table('stock_logs')->whereIn('product_id', $productIds)->delete();
                
                // Delete related daily sales items (keep for history but set product_id to null would break FK)
                // Instead, we'll leave sales items intact - they reference product_id which will be deleted
                // The cascade should handle this if FK is set up, otherwise we delete manually
                DB::table('daily_sales_items')->whereIn('product_id', $productIds)->delete();
                
                // Delete all products in this category
                DB::table('products')->where('category_id', $category->id)->delete();
            }
            
            // Delete the category itself
            DB::table('categories')->where('id', $category->id)->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-create the category (products would need to be re-seeded separately)
        DB::table('categories')->insert([
            'name' => 'Aneka Nasi',
            'description' => 'Rice dishes',
            'expiry_hours' => 24,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
