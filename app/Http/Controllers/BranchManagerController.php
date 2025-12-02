<?php

namespace App\Http\Controllers;

use App\Models\DailySale;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Models\KPI;
use App\Models\Branch;
use App\Models\Benchmark;
use App\Models\BranchStock;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BranchManagerController extends Controller
{
    /**
     * Display branch manager dashboard
     */
    public function dashboard()
    {
        $user = auth()->user();
        $branch = $user->branch;
        $branchId = $user->branch_id;
        
        // This week sales
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();
        $weekSales = DailySale::where('branch_id', $branchId)
            ->whereBetween('sale_date', [$weekStart, $weekEnd])
            ->where('status', 'completed')
            ->sum('total_amount');

        // Last week sales for growth calculation
        $lastWeekStart = Carbon::now()->subWeek()->startOfWeek();
        $lastWeekEnd = Carbon::now()->subWeek()->endOfWeek();
        $lastWeekSales = DailySale::where('branch_id', $branchId)
            ->whereBetween('sale_date', [$lastWeekStart, $lastWeekEnd])
            ->where('status', 'completed')
            ->sum('total_amount');

        $weekGrowth = $lastWeekSales > 0 
            ? (($weekSales - $lastWeekSales) / $lastWeekSales) * 100 
            : 0;

        // Monthly sales
        $monthSales = DailySale::where('branch_id', $branchId)
            ->whereMonth('sale_date', Carbon::now()->month)
            ->whereYear('sale_date', Carbon::now()->year)
            ->where('status', 'completed')
            ->sum('total_amount');

        // Get monthly target from HQ Admin Benchmark (not from branch KPIs)
        $benchmark = Benchmark::where('is_active', true)->first();
        $monthlyTarget = $benchmark ? $benchmark->monthly_sales_target : 0;

        // Total transactions this month
        $totalTransactions = DailySale::where('branch_id', $branchId)
            ->whereMonth('sale_date', Carbon::now()->month)
            ->whereYear('sale_date', Carbon::now()->year)
            ->where('status', 'completed')
            ->count();

        // Active staff count
        $activeStaff = User::where('branch_id', $branchId)
            ->where('id', '!=', $user->id)
            ->count();

        // Sales trend data (last 7 days)
        $salesTrendData = $this->getSalesTrendData($branchId);

        // Sales by category
        $categoryData = $this->getCategoryData($branchId);

        // Monthly sales data (own branch only - last 6 months)
        $monthlySalesData = $this->getMonthlySalesData($branchId);

        // Pending reports count
        $pendingReports = DailySale::where('branch_id', $branchId)
            ->where('status', 'pending')
            ->count();

        // Low stock items for THIS branch
        $lowStockItems = BranchStock::where('branch_id', $branchId)
            ->where('stock_quantity', '<', 10)
            ->where('is_available', true)
            ->count();

        return view('branch-manager.dashboard', compact(
            'branch',
            'weekSales',
            'weekGrowth',
            'monthSales',
            'monthlyTarget',
            'totalTransactions',
            'activeStaff',
            'salesTrendData',
            'categoryData',
            'monthlySalesData',
            'pendingReports',
            'lowStockItems',
            'benchmark'
        ));
    }

    /**
     * Get sales trend data for last 7 days
     */
    private function getSalesTrendData($branchId)
    {
        $labels = [];
        $values = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('M d');
            
            $sales = DailySale::where('branch_id', $branchId)
                ->whereDate('sale_date', $date)
                ->where('status', 'completed')
                ->sum('total_amount');
            
            $values[] = $sales;
        }

        return [
            'labels' => $labels,
            'values' => $values
        ];
    }

    /**
     * Get sales by category data
     */
    private function getCategoryData($branchId)
    {
        $categories = DB::table('daily_sales_items')
            ->join('daily_sales', 'daily_sales_items.daily_sale_id', '=', 'daily_sales.id')
            ->join('products', 'daily_sales_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('daily_sales.branch_id', $branchId)
            ->whereMonth('daily_sales.sale_date', Carbon::now()->month)
            ->whereYear('daily_sales.sale_date', Carbon::now()->year)
            ->where('daily_sales.status', 'completed')
            ->select('categories.name', DB::raw('SUM(daily_sales_items.total) as total'))
            ->groupBy('categories.name')
            ->get();

        return [
            'labels' => $categories->pluck('name')->toArray(),
            'values' => $categories->pluck('total')->toArray()
        ];
    }

    /**
     * Get monthly sales data for own branch (last 6 months)
     */
    private function getMonthlySalesData($branchId)
    {
        $labels = [];
        $values = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $labels[] = $date->format('M Y');
            
            $sales = DailySale::where('branch_id', $branchId)
                ->whereMonth('sale_date', $date->month)
                ->whereYear('sale_date', $date->year)
                ->where('status', 'completed')
                ->sum('total_amount');
            
            $values[] = $sales;
        }

        return [
            'labels' => $labels,
            'values' => $values
        ];
    }

    /**
     * Show sales report page
     */
    public function salesReport(Request $request)
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        // Build query
        $query = DailySale::where('branch_id', $branchId)
            ->with(['staff', 'items.product']);

        // Apply date filter
        $dateRange = $request->input('date_range');
        if ($dateRange && $dateRange !== '') {
            switch ($dateRange) {
                case 'today':
                    $query->whereDate('sale_date', Carbon::today());
                    break;
                case 'week':
                    $query->whereBetween('sale_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('sale_date', Carbon::now()->month)
                          ->whereYear('sale_date', Carbon::now()->year);
                    break;
                case 'custom':
                    $startDate = $request->input('start_date');
                    $endDate = $request->input('end_date');
                    if ($startDate && $endDate && $startDate !== '' && $endDate !== '') {
                        $query->whereBetween('sale_date', [$startDate, $endDate]);
                    }
                    break;
            }
        }
        // If no date_range provided, show all data for this branch

        // Apply status filter
        $status = $request->input('status');
        if ($status && $status !== '') {
            if ($status === 'approved') {
                $query->whereNotNull('verified_by');
            } elseif ($status === 'pending') {
                $query->whereNull('verified_by');
            }
        }

        // Clone for summary calculations
        $summaryQuery = clone $query;

        $reports = $query->orderBy('sale_date', 'desc')->paginate(20);

        // Calculate summary based on filtered data (include all matching records, not just completed)
        $totalSales = (clone $summaryQuery)->sum('total_amount');
        $totalTransactions = (clone $summaryQuery)->count();

        return view('branch-manager.sales-report', compact('reports', 'totalSales', 'totalTransactions'));
    }

    /**
     * Export sales report to Excel/CSV
     */
    public function exportSalesReport(Request $request)
    {
        $user = auth()->user();
        $branchId = $user->branch_id;
        $branch = $user->branch;

        // Get all sales for this branch
        $sales = DailySale::where('branch_id', $branchId)
            ->with(['staff', 'items.product'])
            ->orderBy('sale_date', 'desc')
            ->get();

        // Generate CSV
        $filename = 'sales_report_' . $branch->name . '_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($sales) {
            $file = fopen('php://output', 'w');
            
            // CSV Header
            fputcsv($file, [
                'Transaction ID',
                'Date',
                'Staff',
                'Items',
                'Total Amount (RM)',
                'Payment Method',
                'Status',
                'Verified By',
                'Notes'
            ]);

            // CSV Data
            foreach ($sales as $sale) {
                $itemsList = $sale->items->map(function($item) {
                    return $item->product->name . ' x' . $item->quantity;
                })->implode(', ');

                // Format date in a way Excel will recognize and display properly
                $dateFormatted = \Carbon\Carbon::parse($sale->sale_date)->format('Y-m-d');

                fputcsv($file, [
                    $sale->transaction_id,
                    $dateFormatted,
                    $sale->staff->name ?? 'N/A',
                    $itemsList,
                    $sale->total_amount, // Raw number without formatting for Excel
                    ucfirst(str_replace('_', ' ', $sale->payment_method)),
                    ucfirst($sale->status),
                    $sale->verifier->name ?? 'Pending',
                    $sale->notes ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Show KPI & Benchmark page
     */
    public function kpiBenchmark()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;
        $branch = $user->branch;
        $currentMonth = Carbon::now();

        // Get active benchmarks set by HQ Admin
        $benchmark = Benchmark::where('is_active', true)->first();

        // Calculate branch's monthly sales
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

        // Get branch KPIs
        $kpis = KPI::where('branch_id', $branchId)
            ->whereMonth('target_month', $currentMonth->month)
            ->whereYear('target_month', $currentMonth->year)
            ->with('progress')
            ->get();

        // Get staff KPIs with benchmark comparison
        $staffKpis = User::where('branch_id', $branchId)
            ->where('role', 'staff')
            ->get()
            ->map(function($staff) use ($currentMonth, $benchmark) {
                $staffSales = DailySale::where('staff_id', $staff->id)
                    ->whereMonth('sale_date', $currentMonth->month)
                    ->whereYear('sale_date', $currentMonth->year)
                    ->where('status', 'completed')
                    ->sum('total_amount');

                $transactions = DailySale::where('staff_id', $staff->id)
                    ->whereMonth('sale_date', $currentMonth->month)
                    ->whereYear('sale_date', $currentMonth->year)
                    ->where('status', 'completed')
                    ->count();

                // Calculate progress against staff target
                $staffTarget = $benchmark ? $benchmark->staff_sales_target : 0;
                $progress = $staffTarget > 0 ? min(($staffSales / $staffTarget) * 100, 100) : 0;

                return [
                    'staff' => $staff,
                    'sales' => $staffSales,
                    'transactions' => $transactions,
                    'target' => $staffTarget,
                    'progress' => $progress
                ];
            });

        // Monthly sales comparison (last 6 months)
        $monthlySalesData = $this->getMonthlySalesComparison($branchId);

        return view('branch-manager.kpi-benchmark', compact(
            'kpis', 
            'staffKpis', 
            'monthlySalesData', 
            'benchmark',
            'branch',
            'branchMonthlySales',
            'branchTransactionCount'
        ));
    }

    /**
     * Get monthly sales comparison data
     */
    private function getMonthlySalesComparison($branchId)
    {
        $labels = [];
        $values = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $labels[] = $date->format('M Y');
            
            $sales = DailySale::where('branch_id', $branchId)
                ->whereMonth('sale_date', $date->month)
                ->whereYear('sale_date', $date->year)
                ->where('status', 'completed')
                ->sum('total_amount');
            
            $values[] = $sales;
        }

        return [
            'labels' => $labels,
            'values' => $values
        ];
    }

    /**
     * Show team overview page
     */
    public function teamOverview()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        // Get branch manager info
        $branchManager = $user;

        // Get staff members
        $staffMembers = User::where('branch_id', $branchId)
            ->where('id', '!=', $user->id)
            ->get();

        return view('branch-manager.team-overview', compact('branchManager', 'staffMembers'));
    }

    /**
     * Show inventory page for this branch
     */
    public function inventory()
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
        
        // Paginate manually
        $page = request()->get('page', 1);
        $perPage = 20;
        $products = new \Illuminate\Pagination\LengthAwarePaginator(
            $products->forPage($page, $perPage),
            $products->count(),
            $perPage,
            $page,
            ['path' => request()->url()]
        );

        $categories = Category::all();

        return view('branch-manager.inventory', compact('products', 'categories'));
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
     * Get report details
     */
    public function getReportDetails($id)
    {
        $report = DailySale::with(['staff', 'items.product'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'report' => $report
        ]);
    }

    /**
     * Verify report - approves the transaction and changes status to completed
     */
    public function verifyReport($id)
    {
        $report = DailySale::findOrFail($id);
        $report->verified_by = auth()->id();
        $report->verified_at = now();
        $report->status = 'completed';
        $report->completed_at = now();
        $report->save();

        return response()->json([
            'success' => true,
            'message' => 'Report verified and approved successfully'
        ]);
    }

    /**
     * Update report details
     */
    public function updateReport(Request $request, $id)
    {
        try {
            $report = DailySale::with('items')->findOrFail($id);
            
            // Update basic fields
            if ($request->has('sale_date')) {
                $report->sale_date = $request->sale_date;
            }
            if ($request->has('payment_method')) {
                $report->payment_method = $request->payment_method;
            }
            if ($request->has('notes')) {
                $report->notes = $request->notes;
            }
            
            // Update item quantities if provided
            if ($request->has('items')) {
                $totalAmount = 0;
                $itemsCount = 0;
                
                foreach ($request->items as $itemData) {
                    $item = \App\Models\DailySalesItem::find($itemData['id']);
                    if ($item) {
                        $item->quantity = $itemData['quantity'];
                        $item->subtotal = $itemData['quantity'] * $item->unit_price;
                        $item->total = $item->subtotal - ($item->discount ?? 0);
                        $item->save();
                        
                        $totalAmount += $item->total;
                        $itemsCount += $item->quantity;
                    }
                }
                
                $report->total_amount = $totalAmount;
                $report->items_count = $itemsCount;
            }
            
            // Reset approval status - requires re-approval after edit
            $report->status = 'pending';
            $report->verified_by = null;
            $report->verified_at = null;
            $report->completed_at = null;
            
            $report->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Report updated successfully. Please re-approve the transaction.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get staff performance details
     */
    public function getStaffPerformance($staffId)
    {
        $staff = User::findOrFail($staffId);
        $currentMonth = Carbon::now();

        $totalSales = DailySale::where('staff_id', $staffId)
            ->whereMonth('sale_date', $currentMonth->month)
            ->whereYear('sale_date', $currentMonth->year)
            ->where('status', 'completed')
            ->sum('total_amount');

        $totalTransactions = DailySale::where('staff_id', $staffId)
            ->whereMonth('sale_date', $currentMonth->month)
            ->whereYear('sale_date', $currentMonth->year)
            ->where('status', 'completed')
            ->count();

        $avgTransaction = $totalTransactions > 0 ? $totalSales / $totalTransactions : 0;

        // Get last 7 days data
        $chartLabels = [];
        $chartValues = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $chartLabels[] = $date->format('M d');
            
            $sales = DailySale::where('staff_id', $staffId)
                ->whereDate('sale_date', $date)
                ->where('status', 'completed')
                ->sum('total_amount');
            
            $chartValues[] = $sales;
        }

        return response()->json([
            'success' => true,
            'staff' => $staff,
            'totalSales' => $totalSales,
            'totalTransactions' => $totalTransactions,
            'avgTransaction' => $avgTransaction,
            'chartData' => [
                'labels' => $chartLabels,
                'values' => $chartValues
            ]
        ]);
    }

    /**
     * Display stock overview page (view only) for this branch
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
        
        // Get recent stock logs for THIS branch only
        $stockLogs = \App\Models\StockLog::with(['product', 'user'])
            ->where('branch_id', $branchId)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('branch-manager.stock', compact('products', 'categories', 'stockLogs'));
    }

    /**
     * Display alerts page
     */
    public function alerts()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        // Get regular notifications for this user
        $notifications = Notification::where('user_id', $user->id)
            ->whereIn('type', ['kpi_target_not_met', 'low_stock_alert'])
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

        // Get pending reports count
        $pendingReports = DailySale::where('branch_id', $branchId)
            ->whereNull('verified_by')
            ->count();

        return view('branch-manager.alerts', compact('notifications', 'lowStockProducts', 'pendingReports'));
    }

    /**
     * Mark notification as read
     */
    public function markNotificationAsRead($id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();
        
        $notification->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }
}