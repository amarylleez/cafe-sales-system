<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Show a single product's inventory details.
     */
    public function show($id)
    {
        $product = Product::with('category')->findOrFail($id);

        // Calculate sales statistics for this product
        $statistics = [
            'total_sold' => $product->getTotalSold() ?? 0,
            'total_revenue' => $product->getTotalRevenue() ?? 0,
            'added_date' => $product->created_at->format('d M Y'),
            'stock_quantity' => $product->stock_quantity ?? 0,
        ];

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

            $product = Product::findOrFail($id);
            $quantity = $request->quantity;

            // Check if we have enough stock
            if ($product->stock_quantity < $quantity) {
                return response()->json([
                    'success' => false,
                    'message' => "Not enough stock. Only {$product->stock_quantity} units available.",
                ], 400);
            }

            // Reduce stock
            $product->stock_quantity -= $quantity;
            
            // Auto-set unavailable if stock reaches 0
            if ($product->stock_quantity <= 0) {
                $product->is_available = false;
            }
            
            $product->save();

            // Log the stock reduction
            \App\Models\StockLog::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'quantity' => $quantity,
                'type' => 'remove',
                'notes' => "Sold {$quantity} units",
            ]);

            return response()->json([
                'success' => true,
                'message' => "{$quantity} unit(s) marked as sold. Stock remaining: {$product->stock_quantity}",
                'new_quantity' => $product->stock_quantity,
                'is_available' => $product->is_available,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark as sold: ' . $e->getMessage(),
            ], 500);
        }
    }
}
