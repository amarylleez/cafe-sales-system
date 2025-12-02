<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\BranchStock;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Show a single product's inventory details.
     */
    public function show($id)
    {
        $product = Product::with('category')->findOrFail($id);
        
        // Get branch-specific stock
        $user = auth()->user();
        $branchId = $user->branch_id;
        
        $branchStock = BranchStock::where('branch_id', $branchId)
            ->where('product_id', $id)
            ->first();
        
        $stockQuantity = $branchStock ? $branchStock->stock_quantity : 0;
        $isAvailable = $branchStock ? $branchStock->is_available : true;

        // Calculate sales statistics for this product (branch-specific)
        $statistics = [
            'total_sold' => $product->getTotalSold($branchId) ?? 0,
            'total_revenue' => $product->getTotalRevenue($branchId) ?? 0,
            'added_date' => $product->created_at->format('d M Y'),
            'stock_quantity' => $stockQuantity,
        ];
        
        // Override product's stock and availability with branch-specific values
        $product->stock_quantity = $stockQuantity;
        $product->is_available = $isAvailable;

        return response()->json([
            'success' => true,
            'product' => $product,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Mark a product as sold - reduces stock by quantity
     */
    public function markAsSold(Request $request, $id)
    {
        try {
            $request->validate([
                'quantity' => 'required|integer|min:1',
            ]);

            $user = auth()->user();
            $branchId = $user->branch_id;
            
            $product = Product::findOrFail($id);
            $quantity = $request->quantity;
            
            // Get or create branch-specific stock
            $branchStock = BranchStock::getOrCreate($branchId, $id);

            // Check if we have enough stock
            if ($branchStock->stock_quantity < $quantity) {
                return response()->json([
                    'success' => false,
                    'message' => "Not enough stock. Only {$branchStock->stock_quantity} units available.",
                ], 400);
            }

            // Reduce stock
            $branchStock->stock_quantity -= $quantity;
            
            // Auto-set unavailable if stock reaches 0
            if ($branchStock->stock_quantity <= 0) {
                $branchStock->is_available = false;
            }
            
            $branchStock->save();

            // Log the stock reduction
            \App\Models\StockLog::create([
                'product_id' => $product->id,
                'branch_id' => $branchId,
                'user_id' => auth()->id(),
                'quantity' => $quantity,
                'type' => 'remove',
                'notes' => "Sold {$quantity} units",
            ]);

            return response()->json([
                'success' => true,
                'message' => "{$quantity} unit(s) marked as sold. Stock remaining: {$branchStock->stock_quantity}",
                'new_quantity' => $branchStock->stock_quantity,
                'is_available' => $branchStock->is_available,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark as sold: ' . $e->getMessage(),
            ], 500);
        }
    }
}
