<?php

namespace App\Http\Controllers;

use App\Models\KPI;
use App\Models\KPIProgress;
use App\Models\DailySale;
use App\Models\DailySalesItem;
use App\Models\Product;
use App\Models\Category;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StaffController extends Controller
{
    /**
     * Main Staff Dashboard
     */
    public function index()
    {
        $user = auth()->user();
        $branch = $user->branch;
        $branchId = $user->branch_id;
        $currentMonth = Carbon::now();

        // Get active KPIs for current month
        $kpis = KPI::where('branch_id', $branchId)
            ->where('status', 'active')
            ->whereMonth('target_month', $currentMonth->month)
            ->whereYear('target_month', $currentMonth->year)
            ->with('progress')
            ->get();

        // Get unread notifications count
        $unreadNotifications = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        // Get today's sales summary
        $todaySales = DailySale::where('branch_id', $branchId)
            ->whereDate('sale_date', Carbon::today())
            ->where('status', 'completed')
            ->sum('total_amount');

        // Get this month's sales summary
        $monthlySales = DailySale::where('branch_id', $branchId)
            ->whereMonth('sale_date', $currentMonth->month)
            ->whereYear('sale_date', $currentMonth->year)
            ->where('status', 'completed')
            ->sum('total_amount');

        // Get KPI progress data for charts
        $kpiProgressData = $this->getKPIProgressData($branchId, $currentMonth);

        return view('dashboards.staff', compact(
            'user',
            'branch',
            'kpis',
            'unreadNotifications',
            'todaySales',
            'monthlySales',
            'kpiProgressData'
        ));
    }

    /**
     * Get KPI progress data for charts
     */
    private function getKPIProgressData($branchId, $month)
    {
        $kpis = KPI::where('branch_id', $branchId)
            ->where('status', 'active')
            ->whereMonth('target_month', $month->month)
            ->whereYear('target_month', $month->year)
            ->get();

        $data = [];
        foreach ($kpis as $kpi) {
            $progress = KPIProgress::where('kpi_id', $kpi->id)
                ->whereMonth('progress_date', $month->month)
                ->whereYear('progress_date', $month->year)
                ->orderBy('progress_date')
                ->get();

            $currentProgress = $progress->sum('daily_value');
            $progressPercentage = $kpi->target_value > 0 
                ? min(($currentProgress / $kpi->target_value) * 100, 100) 
                : 0;

            $data[] = [
                'kpi_id' => $kpi->id,
                'kpi_name' => $kpi->kpi_name,
                'target_value' => $kpi->target_value,
                'current_progress' => $currentProgress,
                'progress_percentage' => round($progressPercentage, 2),
                'priority' => $kpi->priority,
                'daily_progress' => $progress->map(function ($p) {
                    return [
                        'date' => $p->progress_date->format('Y-m-d'),
                        'value' => $p->daily_value,
                        'cumulative' => $p->cumulative_value,
                    ];
                }),
            ];
        }

        return $data;
    }

    // ========== SALES METHODS ==========
    
    /**
     * Show create sales form
     */
    public function createSales()
    {
        $products = Product::with('category')
            ->where('is_available', true)
            ->orderBy('name')
            ->get();

        $categories = Category::all();

        return view('staff.sales.create', compact('products', 'categories'));
    }

    /**
     * Store new sale
     */
    public function storeSales(Request $request)
    {
        $request->validate([
            'sale_date' => 'required|date',
            'payment_method' => 'required|in:cash,card,e-wallet,bank_transfer,other',
            'payment_details' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $user = auth()->user();
            $branchId = $user->branch_id;

            // Calculate totals
            $totalAmount = 0;
            $itemsCount = 0;

            foreach ($request->items as $item) {
                $subtotal = $item['quantity'] * $item['unit_price'];
                $discount = $item['discount'] ?? 0;
                $total = $subtotal - $discount;
                $totalAmount += $total;
                $itemsCount += $item['quantity'];
            }

            // Create sale
            $sale = DailySale::create([
                'branch_id' => $branchId,
                'staff_id' => $user->id,
                'sale_date' => $request->sale_date,
                'total_amount' => $totalAmount,
                'items_count' => $itemsCount,
                'payment_method' => $request->payment_method,
                'payment_details' => $request->payment_details,
                'notes' => $request->notes,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Create sale items and reduce stock
            foreach ($request->items as $item) {
                $subtotal = $item['quantity'] * $item['unit_price'];
                $discount = $item['discount'] ?? 0;
                $total = $subtotal - $discount;

                DailySalesItem::create([
                    'daily_sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'total' => $total,
                ]);
                
                // Reduce stock
                $product = Product::find($item['product_id']);
                if ($product) {
                    $product->stock_quantity = max(0, $product->stock_quantity - $item['quantity']);
                    if ($product->stock_quantity <= 0) {
                        $product->is_available = false;
                    }
                    $product->save();
                    
                    // Log stock reduction
                    \App\Models\StockLog::create([
                        'product_id' => $product->id,
                        'user_id' => auth()->id(),
                        'quantity' => $item['quantity'],
                        'type' => 'remove',
                        'notes' => 'Sold via sales transaction #' . $sale->transaction_id,
                    ]);
                }
            }

            // Update KPI progress
            $this->updateKPIProgress($branchId, $request->sale_date, $totalAmount, $itemsCount);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sale recorded successfully',
                'transaction_id' => $sale->transaction_id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to record sale: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update KPI Progress
     */
    private function updateKPIProgress($branchId, $saleDate, $totalAmount, $itemsCount)
    {
        $date = Carbon::parse($saleDate);
        $month = $date->copy()->startOfMonth();

        $kpis = KPI::where('branch_id', $branchId)
            ->where('status', 'active')
            ->whereMonth('target_month', $month->month)
            ->whereYear('target_month', $month->year)
            ->get();

        foreach ($kpis as $kpi) {
            $dailyValue = 0;
            switch ($kpi->kpi_type) {
                case 'sales_amount':
                    $dailyValue = $totalAmount;
                    break;
                case 'transaction_count':
                    $dailyValue = 1;
                    break;
                case 'items_sold':
                    $dailyValue = $itemsCount;
                    break;
            }

            $progress = KPIProgress::firstOrNew([
                'kpi_id' => $kpi->id,
                'progress_date' => $date,
            ]);

            $progress->branch_id = $branchId;
            $progress->daily_value = ($progress->daily_value ?? 0) + $dailyValue;
            $progress->recorded_by = auth()->id();
            $progress->save();

            // Update cumulative
            $cumulative = KPIProgress::where('kpi_id', $kpi->id)
                ->whereMonth('progress_date', $date->month)
                ->whereYear('progress_date', $date->year)
                ->where('progress_date', '<=', $date)
                ->sum('daily_value');

            $progress->cumulative_value = $cumulative;
            $progress->progress_percentage = $kpi->target_value > 0 
                ? min(($cumulative / $kpi->target_value) * 100, 100) 
                : 0;
            $progress->save();
        }
    }

    // ========== KPI METHODS ==========
    
    /**
     * View KPI page
     */
    public function kpi()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;
        $currentMonth = Carbon::now();

        $kpis = KPI::where('branch_id', $branchId)
            ->where('status', 'active')
            ->whereMonth('target_month', $currentMonth->month)
            ->whereYear('target_month', $currentMonth->year)
            ->with(['progress' => function ($query) use ($currentMonth) {
                $query->whereMonth('progress_date', $currentMonth->month)
                      ->whereYear('progress_date', $currentMonth->year)
                      ->orderBy('progress_date', 'desc');
            }])
            ->get();

        return view('staff.kpi', compact('kpis'));
    }

    /**
     * KPI Target Overview
     */
    public function targetOverview()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;
        $currentMonth = Carbon::now();

        $kpis = KPI::where('branch_id', $branchId)
            ->where('status', 'active')
            ->whereMonth('target_month', $currentMonth->month)
            ->whereYear('target_month', $currentMonth->year)
            ->with(['progress', 'branch'])
            ->get();

        return view('staff.dashboard.target', compact('kpis'));
    }

    /**
     * KPI Progress Bar
     */
    public function progressBar()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;
        $currentMonth = Carbon::now();

        $kpis = KPI::where('branch_id', $branchId)
            ->where('status', 'active')
            ->whereMonth('target_month', $currentMonth->month)
            ->whereYear('target_month', $currentMonth->year)
            ->get();

        $progressData = [];
        foreach ($kpis as $kpi) {
            $currentProgress = KPIProgress::where('kpi_id', $kpi->id)
                ->whereMonth('progress_date', $currentMonth->month)
                ->whereYear('progress_date', $currentMonth->year)
                ->sum('daily_value');

            $progressPercentage = $kpi->target_value > 0 
                ? min(($currentProgress / $kpi->target_value) * 100, 100) 
                : 0;

            $progressData[] = [
                'kpi' => $kpi,
                'progress_percentage' => round($progressPercentage, 2),
                'current_value' => $currentProgress,
                'target_value' => $kpi->target_value,
                'remaining_value' => max($kpi->target_value - $currentProgress, 0),
                'is_met' => $currentProgress >= $kpi->target_value,
            ];
        }

        return view('staff.dashboard.progress', compact('progressData'));
    }

    /**
     * Toggle KPI Completion
     */
    public function toggleKPICompletion(Request $request, $kpiId)
    {
        $date = $request->input('date', Carbon::today());

        $progress = KPIProgress::where('kpi_id', $kpiId)
            ->whereDate('progress_date', $date)
            ->first();

        if ($progress) {
            $progress->is_completed = !$progress->is_completed;
            $progress->save();

            return response()->json([
                'success' => true,
                'is_completed' => $progress->is_completed,
            ]);
        }

        return response()->json(['success' => false], 404);
    }

    // ========== ALERTS METHODS ==========
    
    /**
     * Show alerts
     */
    public function alerts()
    {
        $user = auth()->user();

        $alerts = Notification::where('user_id', $user->id)
            ->whereIn('type', ['kpi_target_not_met', 'low_stock_alert'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('staff.alerts', compact('alerts'));
    }

    // ========== INVENTORY METHODS ==========
    
    /**
     * Show inventory
     */
    public function inventory()
    {
        $products = Product::with('category')
            ->orderBy('name')
            ->paginate(20);

        $categories = Category::all();

        return view('staff.inventory', compact('products', 'categories'));
    }

    /**
     * Update product availability
     */
    public function updateProductAvailability(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->is_available = $request->boolean('is_available');
            $product->save();

            return response()->json([
                'success' => true,
                'message' => 'Product availability updated',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update availability: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display stock management page
     */
    public function stock()
    {
        $products = Product::with('category')->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('staff.stock', compact('products', 'categories'));
    }

    /**
     * Add stock to a product
     */
    public function addStock(Request $request, $id)
    {
        try {
            $request->validate([
                'quantity' => 'required|integer|min:1',
                'notes' => 'nullable|string|max:255',
            ]);

            $product = Product::findOrFail($id);
            $product->stock_quantity += $request->quantity;
            $product->save();

            // Log the stock addition
            \App\Models\StockLog::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'quantity' => $request->quantity,
                'type' => 'add',
                'notes' => $request->notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Stock added successfully',
                'new_quantity' => $product->stock_quantity,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add stock: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Adjust stock (add or remove)
     */
    public function adjustStock(Request $request, $id)
    {
        try {
            $request->validate([
                'quantity' => 'required|integer|min:1',
                'type' => 'required|in:add,remove',
            ]);

            $product = Product::findOrFail($id);
            
            if ($request->type === 'add') {
                $product->stock_quantity += $request->quantity;
            } else {
                if ($product->stock_quantity < $request->quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot remove more than current stock',
                    ], 400);
                }
                $product->stock_quantity -= $request->quantity;
            }
            
            $product->save();

            // Log the stock change
            \App\Models\StockLog::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'quantity' => $request->quantity,
                'type' => $request->type,
                'notes' => $request->type === 'add' ? 'Stock added via inventory' : 'Stock removed via inventory',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Stock adjusted successfully',
                'new_quantity' => $product->stock_quantity,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to adjust stock: ' . $e->getMessage(),
            ], 500);
        }
    }
}