<?php

namespace App\Console\Commands;

use App\Models\BranchStock;
use App\Models\StockLog;
use Carbon\Carbon;
use Illuminate\Console\Command;

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
    protected $description = 'Process expired stock (24 hours old) - marks as loss and resets quantity';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        $this->info('Processing expired stock (received before today)...');
        
        // Get all stock that was received before today and has quantity > 0
        $expiredStock = BranchStock::with(['product', 'branch'])
            ->where('stock_quantity', '>', 0)
            ->whereNotNull('received_date')
            ->where('received_date', '<', Carbon::today())
            ->get();

        if ($expiredStock->isEmpty()) {
            $this->info('No expired stock found.');
            return 0;
        }

        $this->info("Found {$expiredStock->count()} expired stock record(s).");
        
        $totalLoss = 0;
        $processedCount = 0;

        foreach ($expiredStock as $stock) {
            $costPrice = $stock->cost_at_purchase ?? $stock->product->cost_price ?? 0;
            $loss = $stock->stock_quantity * $costPrice;
            $totalLoss += $loss;
            
            $branchName = $stock->branch->name ?? 'Unknown';
            $productName = $stock->product->name ?? 'Unknown';
            
            $this->line("  - [{$branchName}] {$productName}: {$stock->stock_quantity} units (RM " . number_format($loss, 2) . " loss)");
            $this->line("    Received: {$stock->received_date->format('Y-m-d')}");

            if (!$isDryRun) {
                // Log the expired stock
                StockLog::create([
                    'product_id' => $stock->product_id,
                    'branch_id' => $stock->branch_id,
                    'user_id' => null, // System action
                    'quantity' => $stock->stock_quantity,
                    'type' => 'expired',
                    'notes' => "Expired stock (unsold for 24+ hours). Loss: RM " . number_format($loss, 2) . ". Original received date: {$stock->received_date->format('Y-m-d')}",
                ]);

                // Reset the stock quantity and received date
                $stock->stock_quantity = 0;
                $stock->received_date = null;
                $stock->is_available = false;
                $stock->save();
                
                $processedCount++;
            }
        }

        $this->newLine();
        
        if ($isDryRun) {
            $this->warn("DRY RUN - No changes made.");
            $this->info("Would process {$expiredStock->count()} stock record(s) with total loss: RM " . number_format($totalLoss, 2));
        } else {
            $this->info("Processed {$processedCount} stock record(s).");
            $this->info("Total loss recorded: RM " . number_format($totalLoss, 2));
        }

        return 0;
    }
}
