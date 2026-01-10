<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupDuplicateProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:cleanup-duplicates {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove duplicate products, keeping the one with the lowest ID and merging stock quantities';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('Running in DRY-RUN mode - no changes will be made.');
            $this->newLine();
        }

        // Find duplicate products (same name and category_id)
        $duplicates = DB::table('products')
            ->select('name', 'category_id', DB::raw('COUNT(*) as count'), DB::raw('MIN(id) as keep_id'))
            ->groupBy('name', 'category_id')
            ->having(DB::raw('COUNT(*)'), '>', 1)
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('No duplicate products found. Database is clean!');
            return 0;
        }

        $this->warn("Found {$duplicates->count()} products with duplicates:");
        $this->newLine();

        $totalRemoved = 0;

        foreach ($duplicates as $duplicate) {
            // Get all product IDs for this duplicate
            $productIds = DB::table('products')
                ->where('name', $duplicate->name)
                ->where('category_id', $duplicate->category_id)
                ->pluck('id')
                ->toArray();

            $keepId = $duplicate->keep_id;
            $removeIds = array_filter($productIds, fn($id) => $id != $keepId);

            $this->line("Product: <info>{$duplicate->name}</info> (Category ID: {$duplicate->category_id})");
            $this->line("  - Keeping ID: {$keepId}");
            $this->line("  - Removing IDs: " . implode(', ', $removeIds));

            if (!$isDryRun) {
                // Merge branch_stocks: sum quantities from duplicates into the kept product
                foreach ($removeIds as $removeId) {
                    // Get all branch stocks for the duplicate product
                    $duplicateStocks = DB::table('branch_stocks')
                        ->where('product_id', $removeId)
                        ->get();

                    foreach ($duplicateStocks as $duplicateStock) {
                        // Check if the kept product already has stock for this branch
                        $existingStock = DB::table('branch_stocks')
                            ->where('product_id', $keepId)
                            ->where('branch_id', $duplicateStock->branch_id)
                            ->first();

                        if ($existingStock) {
                            // Add the duplicate's quantity to the existing stock
                            DB::table('branch_stocks')
                                ->where('id', $existingStock->id)
                                ->update([
                                    'stock_quantity' => $existingStock->stock_quantity + $duplicateStock->stock_quantity,
                                    'updated_at' => now(),
                                ]);
                        } else {
                            // Update the branch stock to point to the kept product
                            DB::table('branch_stocks')
                                ->where('id', $duplicateStock->id)
                                ->update([
                                    'product_id' => $keepId,
                                    'updated_at' => now(),
                                ]);
                        }
                    }

                    // Delete remaining branch stocks for the duplicate (those that were merged)
                    DB::table('branch_stocks')
                        ->where('product_id', $removeId)
                        ->delete();

                    // Update any sale items to point to the kept product
                    DB::table('daily_sales_items')
                        ->where('product_id', $removeId)
                        ->update(['product_id' => $keepId]);

                    // Update any stock logs to point to the kept product
                    DB::table('stock_logs')
                        ->where('product_id', $removeId)
                        ->update(['product_id' => $keepId]);

                    // Update any stock losses to point to the kept product
                    DB::table('stock_losses')
                        ->where('product_id', $removeId)
                        ->update(['product_id' => $keepId]);
                }

                // Delete the duplicate products
                DB::table('products')
                    ->whereIn('id', $removeIds)
                    ->delete();
            }

            $totalRemoved += count($removeIds);
            $this->newLine();
        }

        if ($isDryRun) {
            $this->warn("DRY-RUN: Would remove {$totalRemoved} duplicate product(s).");
            $this->info("Run without --dry-run to apply changes.");
        } else {
            $this->info("Successfully removed {$totalRemoved} duplicate product(s).");
        }

        return 0;
    }
}
