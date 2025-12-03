<?php

namespace App\Http\Controllers;

use App\Models\KPI;
use App\Models\KPIProgress;
use App\Models\DailySale;
use App\Models\DailySalesItem;
use App\Models\Product;
use App\Models\Category;
use App\Models\Notification;
use App\Models\Benchmark;
use App\Models\BranchStock;
use App\Models\StaffSchedule;
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

        // Get unread notifications count
        $unreadNotifications = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        // Get today's sales summary
        $todaySales = DailySale::where('branch_id', $branchId)
            ->whereDate('sale_date', Carbon::today())
            ->where('status', 'completed')
            ->sum('total_amount');

        // Get today's transaction count
        $todayTransactions = DailySale::where('branch_id', $branchId)
            ->whereDate('sale_date', Carbon::today())
            ->where('status', 'completed')
            ->count();

        // Get this week's sales summary (Monday to Sunday)
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();
        $weeklySales = DailySale::where('branch_id', $branchId)
            ->whereBetween('sale_date', [$weekStart, $weekEnd])
            ->where('status', 'completed')
            ->sum('total_amount');

        // Get this month's sales summary
        $monthlySales = DailySale::where('branch_id', $branchId)
            ->whereMonth('sale_date', $currentMonth->month)
            ->whereYear('sale_date', $currentMonth->year)
            ->where('status', 'completed')
            ->sum('total_amount');

        // Get this month's transaction count
        $monthlyTransactions = DailySale::where('branch_id', $branchId)
            ->whereMonth('sale_date', $currentMonth->month)
            ->whereYear('sale_date', $currentMonth->year)
            ->where('status', 'completed')
            ->count();

        // Get yesterday's sales for comparison
        $yesterdaySales = DailySale::where('branch_id', $branchId)
            ->whereDate('sale_date', Carbon::yesterday())
            ->where('status', 'completed')
            ->sum('total_amount');

        // Get last month's sales for comparison
        $lastMonth = Carbon::now()->subMonth();
        $lastMonthSales = DailySale::where('branch_id', $branchId)
            ->whereMonth('sale_date', $lastMonth->month)
            ->whereYear('sale_date', $lastMonth->year)
            ->where('status', 'completed')
            ->sum('total_amount');

        // Get items sold by category this month for pie chart
        $categorySales = DailySalesItem::select(
                'categories.name as category_name',
                DB::raw('SUM(daily_sales_items.quantity) as total_quantity')
            )
            ->join('daily_sales', 'daily_sales_items.daily_sale_id', '=', 'daily_sales.id')
            ->join('products', 'daily_sales_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('daily_sales.branch_id', $branchId)
            ->whereMonth('daily_sales.sale_date', $currentMonth->month)
            ->whereYear('daily_sales.sale_date', $currentMonth->year)
            ->where('daily_sales.status', 'completed')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_quantity')
            ->get();

        return view('dashboards.staff', compact(
            'user',
            'branch',
            'unreadNotifications',
            'todaySales',
            'todayTransactions',
            'weeklySales',
            'monthlySales',
            'monthlyTransactions',
            'yesterdaySales',
            'lastMonthSales',
            'categorySales'
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
                'status' => 'pending',
                'completed_at' => null,
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
                
                // Reduce stock for this branch
                $branchStock = BranchStock::getOrCreate($branchId, $item['product_id']);
                $branchStock->stock_quantity = max(0, $branchStock->stock_quantity - $item['quantity']);
                if ($branchStock->stock_quantity <= 0) {
                    $branchStock->is_available = false;
                }
                $branchStock->save();
                
                // Log stock reduction
                \App\Models\StockLog::create([
                    'product_id' => $item['product_id'],
                    'branch_id' => $branchId,
                    'user_id' => auth()->id(),
                    'quantity' => $item['quantity'],
                    'type' => 'remove',
                    'notes' => 'Sold via sales transaction #' . $sale->transaction_id,
                ]);
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

        // Get branch-specific KPIs
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

        // Get active benchmarks set by HQ Admin
        $benchmark = Benchmark::where('is_active', true)->first();

        // Calculate staff's monthly sales (sales submitted by this staff member)
        $staffMonthlySales = DailySale::where('staff_id', $user->id)
            ->whereMonth('sale_date', $currentMonth->month)
            ->whereYear('sale_date', $currentMonth->year)
            ->where('status', 'completed')
            ->sum('total_amount');

        // Calculate staff's transaction count
        $staffTransactionCount = DailySale::where('staff_id', $user->id)
            ->whereMonth('sale_date', $currentMonth->month)
            ->whereYear('sale_date', $currentMonth->year)
            ->where('status', 'completed')
            ->count();

        // Calculate branch's total monthly sales
        $branchMonthlySales = DailySale::where('branch_id', $branchId)
            ->whereMonth('sale_date', $currentMonth->month)
            ->whereYear('sale_date', $currentMonth->year)
            ->where('status', 'completed')
            ->sum('total_amount');

        // Calculate branch's transaction count
        $branchTransactionCount = DailySale::where('branch_id', $branchId)
            ->whereMonth('sale_date', $currentMonth->month)
            ->whereYear('sale_date', $currentMonth->year)
            ->where('status', 'completed')
            ->count();

        return view('staff.kpi', compact(
            'kpis', 
            'benchmark', 
            'staffMonthlySales', 
            'staffTransactionCount',
            'branchMonthlySales',
            'branchTransactionCount'
        ));
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
        $branchId = $user->branch_id;

        // Get regular notifications
        $notifications = Notification::where('user_id', $user->id)
            ->whereIn('type', ['kpi_target_not_met', 'low_stock_alert', 'system_announcement', 'important_notice'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get low stock products for THIS branch only
        $lowStockProducts = BranchStock::with(['product.category'])
            ->where('branch_id', $branchId)
            ->where('stock_quantity', '<', 10)
            ->where('is_available', true)
            ->orderBy('stock_quantity', 'asc')
            ->get()
            ->map(function ($stock) {
                // Attach stock info to product for easy access in view
                $product = $stock->product;
                $product->stock_quantity = $stock->stock_quantity;
                $product->is_available = $stock->is_available;
                return $product;
            });

        return view('staff.alerts', compact('notifications', 'lowStockProducts'));
    }

    // ========== INVENTORY METHODS ==========
    
    /**
     * Show inventory
     */
    public function inventory()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;
        $selectedCategory = request()->get('category');
        $search = request()->get('search');
        
        // Get products with branch-specific stock
        $productsQuery = Product::with('category')
            ->orderBy('name');
        
        // Filter by category if selected
        if ($selectedCategory) {
            $productsQuery->where('category_id', $selectedCategory);
        }
        
        // Filter by search term
        if ($search) {
            $productsQuery->where('name', 'ilike', '%' . $search . '%');
        }
        
        $products = $productsQuery->get()
            ->map(function ($product) use ($branchId) {
                $branchStock = BranchStock::where('branch_id', $branchId)
                    ->where('product_id', $product->id)
                    ->first();
                
                $product->stock_quantity = $branchStock ? $branchStock->stock_quantity : 0;
                $product->is_available = $branchStock ? $branchStock->is_available : true;
                $product->branch_stock_id = $branchStock ? $branchStock->id : null;
                
                return $product;
            });
        
        // Paginate manually
        $page = request()->get('page', 1);
        $perPage = 20;
        $products = new \Illuminate\Pagination\LengthAwarePaginator(
            $products->forPage($page, $perPage),
            $products->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $categories = Category::orderByRaw("CASE WHEN name = 'Lain-lain' THEN 1 ELSE 0 END")
            ->orderBy('name')
            ->get();

        return view('staff.inventory', compact('products', 'categories', 'selectedCategory'));
    }

    /**
     * Update product availability for this branch
     */
    public function updateProductAvailability(Request $request, $id)
    {
        try {
            $user = auth()->user();
            $branchId = $user->branch_id;
            
            // Get or create branch stock
            $branchStock = BranchStock::getOrCreate($branchId, $id);
            $branchStock->is_available = $request->boolean('is_available');
            $branchStock->save();

            return response()->json([
                'success' => true,
                'message' => 'Product availability updated for your branch',
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
        $user = auth()->user();
        $branchId = $user->branch_id;
        
        // Get products with branch-specific stock
        $products = Product::with('category')
            ->orderBy('name')
            ->get()
            ->map(function ($product) use ($branchId) {
                $branchStock = BranchStock::where('branch_id', $branchId)
                    ->where('product_id', $product->id)
                    ->first();
                
                $product->stock_quantity = $branchStock ? $branchStock->stock_quantity : 0;
                $product->is_available = $branchStock ? $branchStock->is_available : true;
                
                return $product;
            });
        
        $categories = Category::orderBy('name')->get();

        return view('staff.stock', compact('products', 'categories'));
    }

    /**
     * Add stock to a product for this branch
     */
    public function addStock(Request $request, $id)
    {
        try {
            $request->validate([
                'quantity' => 'required|integer|min:1',
                'notes' => 'nullable|string|max:255',
            ]);

            $user = auth()->user();
            $branchId = $user->branch_id;
            
            // Get or create branch stock
            $branchStock = BranchStock::getOrCreate($branchId, $id);
            $branchStock->stock_quantity += $request->quantity;
            $branchStock->save();

            // Log the stock addition
            \App\Models\StockLog::create([
                'product_id' => $id,
                'branch_id' => $branchId,
                'user_id' => auth()->id(),
                'quantity' => $request->quantity,
                'type' => 'add',
                'notes' => $request->notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Stock added successfully',
                'new_quantity' => $branchStock->stock_quantity,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add stock: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Adjust stock (add or remove) for this branch
     */
    public function adjustStock(Request $request, $id)
    {
        try {
            $request->validate([
                'quantity' => 'required|integer|min:1',
                'type' => 'required|in:add,remove',
            ]);

            $user = auth()->user();
            $branchId = $user->branch_id;
            
            // Get or create branch stock
            $branchStock = BranchStock::getOrCreate($branchId, $id);
            
            if ($request->type === 'add') {
                $branchStock->stock_quantity += $request->quantity;
            } else {
                if ($branchStock->stock_quantity < $request->quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot remove more than current stock',
                    ], 400);
                }
                $branchStock->stock_quantity -= $request->quantity;
            }
            
            $branchStock->save();

            // Log the stock change
            \App\Models\StockLog::create([
                'product_id' => $id,
                'branch_id' => $branchId,
                'user_id' => auth()->id(),
                'quantity' => $request->quantity,
                'type' => $request->type,
                'notes' => $request->type === 'add' ? 'Stock added via inventory' : 'Stock removed via inventory',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Stock adjusted successfully',
                'new_quantity' => $branchStock->stock_quantity,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to adjust stock: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display staff's own schedule
     */
    public function mySchedule(Request $request)
    {
        $user = auth()->user();
        $branchId = $user->branch_id;
        $branch = $user->branch;

        // Get week offset from request (for navigation)
        $weekOffset = (int) $request->input('week', 0);
        $weekStart = Carbon::now()->startOfWeek()->addWeeks($weekOffset);
        $weekEnd = $weekStart->copy()->endOfWeek();

        // Get schedules for this week
        $schedules = StaffSchedule::where('staff_id', $user->id)
            ->whereBetween('schedule_date', [$weekStart, $weekEnd])
            ->orderBy('schedule_date')
            ->orderBy('start_time')
            ->get();

        // Get upcoming schedules (next 7 days)
        $upcomingSchedules = StaffSchedule::where('staff_id', $user->id)
            ->where('schedule_date', '>=', Carbon::today())
            ->where('schedule_date', '<=', Carbon::today()->addDays(7))
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->orderBy('schedule_date')
            ->orderBy('start_time')
            ->get();

        // Get this month's total scheduled hours
        $monthSchedules = StaffSchedule::where('staff_id', $user->id)
            ->whereMonth('schedule_date', Carbon::now()->month)
            ->whereYear('schedule_date', Carbon::now()->year)
            ->whereIn('status', ['scheduled', 'confirmed', 'completed'])
            ->get();

        $totalHours = 0;
        foreach ($monthSchedules as $schedule) {
            $start = Carbon::parse($schedule->start_time);
            $end = Carbon::parse($schedule->end_time);
            $totalHours += $end->diffInHours($start);
        }

        return view('staff.my-schedule', compact(
            'schedules',
            'upcomingSchedules',
            'weekStart',
            'weekEnd',
            'weekOffset',
            'totalHours',
            'branch'
        ));
    }

    /**
     * Confirm a schedule (staff acknowledging their shift)
     */
    public function confirmSchedule($id)
    {
        $user = auth()->user();

        $schedule = StaffSchedule::where('id', $id)
            ->where('staff_id', $user->id)
            ->where('status', 'scheduled')
            ->firstOrFail();

        $schedule->update(['status' => 'confirmed']);

        return response()->json([
            'success' => true,
            'message' => 'Schedule confirmed successfully.'
        ]);
    }

    /**
     * Clock in for a shift
     */
    public function clockIn($id)
    {
        $user = auth()->user();

        $schedule = StaffSchedule::where('id', $id)
            ->where('staff_id', $user->id)
            ->where('status', 'confirmed')
            ->whereNull('clock_in_time')
            ->firstOrFail();

        // Check if it's the right day
        if (!$schedule->schedule_date->isToday()) {
            return response()->json([
                'success' => false,
                'message' => 'You can only clock in on the scheduled day.'
            ], 422);
        }

        $now = now();
        $shiftDate = $schedule->schedule_date->format('Y-m-d');
        $shiftStart = Carbon::parse($shiftDate . ' ' . $schedule->shift_times['start']);
        
        // Check if within clock-in window (2 hours before to 2 hours after shift start)
        $windowStart = $shiftStart->copy()->subHours(2);
        $windowEnd = $shiftStart->copy()->addHours(2);

        if (!$now->between($windowStart, $windowEnd)) {
            return response()->json([
                'success' => false,
                'message' => 'Clock in is available from ' . $windowStart->format('h:i A') . ' to ' . $windowEnd->format('h:i A') . '. Current time: ' . $now->format('h:i A')
            ], 422);
        }

        // Check if late (more than 5 minutes after shift start)
        $isLate = $now->gt($shiftStart->copy()->addMinutes(5));

        $schedule->update([
            'status' => 'clocked_in',
            'clock_in_time' => $now,
            'is_late' => $isLate,
        ]);

        $message = 'Clocked in successfully at ' . $now->format('h:i A');
        if ($isLate) {
            $message .= ' (Late arrival)';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'clock_in_time' => $now->format('h:i A'),
            'is_late' => $isLate
        ]);
    }

    /**
     * Clock out from a shift
     */
    public function clockOut($id)
    {
        $user = auth()->user();

        $schedule = StaffSchedule::where('id', $id)
            ->where('staff_id', $user->id)
            ->where('status', 'clocked_in')
            ->whereNotNull('clock_in_time')
            ->whereNull('clock_out_time')
            ->firstOrFail();

        $now = now();
        $clockInTime = Carbon::parse($schedule->clock_in_time);
        
        // Calculate hours worked
        $hoursWorked = round($clockInTime->diffInMinutes($now) / 60, 2);

        // Check if early leave (more than 30 mins before scheduled end)
        $shiftTimes = $schedule->shift_times;
        $shiftDate = $schedule->schedule_date->format('Y-m-d');
        $shiftEnd = Carbon::parse($shiftDate . ' ' . $shiftTimes['end']);
        $isEarlyLeave = $now->lt($shiftEnd->copy()->subMinutes(30));

        $schedule->update([
            'status' => 'completed',
            'clock_out_time' => $now,
            'hours_worked' => $hoursWorked,
            'is_early_leave' => $isEarlyLeave,
        ]);

        $message = 'Clocked out successfully. You worked ' . $hoursWorked . ' hours.';
        if ($isEarlyLeave) {
            $message .= ' (Early leave recorded)';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'clock_out_time' => $now->format('h:i A'),
            'hours_worked' => $hoursWorked,
            'is_early_leave' => $isEarlyLeave
        ]);
    }
}