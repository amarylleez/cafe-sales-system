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

        return response()->json([
            'success' => true,
            'product' => $product,
        ]);
    }

    /**
     * Mark a product as sold (basic implementation sets it unavailable).
     */
    public function markAsSold(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->is_available = false;
        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Product marked as sold',
        ]);
    }
}
