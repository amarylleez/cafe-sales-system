<?php

namespace App\Console\Commands;

use App\Models\BranchStock;
use App\Models\StockLoss;
use App\Models\StockLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessExpiredStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:process-expired {--dry-run : Show what would be processed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process expired stock - items past their expiry_date are marked as loss';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        $this->info('Processing expired stock...');
        
        $now = Carbon::now();
        
        // Get all stock that has expired (expiry_date has passed) and still has quantity
        $expiredStock = BranchStock::with(['product.category', 'branch'])
            ->where('stock_quantity', '>', 0)
            ->where(function($query) use ($now) {
                // Either has expiry_date that has passed
                $query->whereNotNull('expiry_date')
                      ->where('expiry_date', '<=', $now);
            })
            ->get();

        if ($expiredStock->isEmpty()) {
            $this->info('No expired stock found.');
            return 0;
        }

        $this->info("Found {$expiredStock->count()} expired stock record(s).");
        
        $totalLoss = 0;
        $processedCount = 0;

        if (!$isDryRun) {
            DB::beginTransaction();
        }

        try {
            foreach ($expiredStock as $stock) {
                $costPrice = $stock->cost_at_purchase ?? $stock->product->cost_price ?? 0;
                $loss = $stock->stock_quantity * $costPrice;
                $totalLoss += $loss;
                
                $branchName = $stock->branch->name ?? 'Unknown';
                $productName = $stock->product->name ?? 'Unknown';
                $expiryHours = $stock->product->category->expiry_hours ?? 24;
                
                $this->line("  - [{$branchName}] {$productName}: {$stock->stock_quantity} units (RM " . number_format($loss, 2) . " loss)");
                $this->line("    Received: " . ($stock->received_date ? $stock->received_date->format('Y-m-d H:i') : 'N/A'));
                $this->line("    Expired: " . ($stock->expiry_date ? $stock->expiry_date->format('Y-m-d H:i') : 'N/A'));

                if (!$isDryRun) {
                    // Create loss record
                    StockLoss::create([
                        'branch_id' => $stock->branch_id,
                        'product_id' => $stock->product_id,
                        'quantity' => $stock->stock_quantity,
                        'unit_cost' => $costPrice,
                        'total_loss' => $loss,
                        'loss_type' => 'expired',
                        'notes' => "Auto-processed: Stock expired after {$expiryHours} hours",
                        'loss_date' => $now->toDateString(),
                        'stock_added_at' => $stock->received_date,
                        'expired_at' => $stock->expiry_date,
                        'processed_by' => null, // System processed
                    ]);

                    // Log the expired stock
                    StockLog::create([
                        'product_id' => $stock->product_id,
                        'branch_id' => $stock->branch_id,
                        'user_id' => null, // System action
                        'quantity' => $stock->stock_quantity,
                        'type' => 'expired',
                        'notes' => "Expired stock. Loss: RM " . number_format($loss, 2),
                    ]);

                    // Reset the stock quantity and expiry date
                    $stock->stock_quantity = 0;
                    $stock->expiry_date = null;
                    $stock->received_date = null;
                    $stock->save();
                    
                    $processedCount++;
                }
            }

            if (!$isDryRun) {
                DB::commit();
            }

            $this->newLine();
            
            if ($isDryRun) {
                $this->warn("DRY RUN - No changes made.");
                $this->info("Would process {$expiredStock->count()} stock record(s) with total loss: RM " . number_format($totalLoss, 2));
            } else {
                $this->info("Processed {$processedCount} stock record(s).");
                $this->info("Total loss recorded: RM " . number_format($totalLoss, 2));
                Log::info("ProcessExpiredStock: Processed {$processedCount} items, total loss RM {$totalLoss}");
            }

            return 0;

        } catch (\Exception $e) {
            if (!$isDryRun) {
                DB::rollBack();
            }
            $this->error('Error processing expired stock: ' . $e->getMessage());
            Log::error('ProcessExpiredStock failed: ' . $e->getMessage());
            return 1;
        }
    }
}
