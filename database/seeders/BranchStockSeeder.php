<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\Product;
use App\Models\BranchStock;

class BranchStockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = Branch::all();
        $products = Product::all();

        foreach ($branches as $branch) {
            foreach ($products as $product) {
                // Create stock entry for each branch-product combination
                // Copy current global stock or set random initial stock
                BranchStock::firstOrCreate(
                    [
                        'branch_id' => $branch->id,
                        'product_id' => $product->id,
                    ],
                    [
                        'stock_quantity' => $product->stock_quantity ?? rand(5, 50),
                        'is_available' => $product->is_available ?? true,
                    ]
                );
            }
        }
    }
}
