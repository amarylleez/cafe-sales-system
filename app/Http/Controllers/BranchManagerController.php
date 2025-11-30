<?php

namespace App\Http\Controllers;

use App\Models\DailySale;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Models\KPI;
use App\Models\Branch;
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

        // Get monthly target from KPIs
        $monthlyTarget = KPI::where('branch_id', $branchId)
            ->where('kpi_type', 'sales_amount')
            ->whereMonth('target_month', Carbon::now()->month)
            ->whereYear('target_month', Carbon::now()->year)
            ->sum('target_value');

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
        $pendingReports = 0; // You can implement this later

        // Low stock items
        $lowStockItems = 0; // You can implement inventory tracking later

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
            'lowStockItems'
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
    public function salesReport()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        // Get monthly sales reports submitted by staff
        $reports = DailySale::where('branch_id', $branchId)
            ->with(['staff', 'items.product'])
            ->whereMonth('sale_date', Carbon::now()->month)
            ->whereYear('sale_date', Carbon::now()->year)
            ->orderBy('sale_date', 'desc')
            ->paginate(20);

        // Calculate summary
        $totalSales = DailySale::where('branch_id', $branchId)
            ->whereMonth('sale_date', Carbon::now()->month)
            ->whereYear('sale_date', Carbon::now()->year)
            ->where('status', 'completed')
            ->sum('total_amount');

        $totalTransactions = $reports->total();

        return view('branch-manager.sales-report', compact('reports', 'totalSales', 'totalTransactions'));
    }

    /**
     * Show KPI & Benchmark page
     */
    public function kpiBenchmark()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;
        $currentMonth = Carbon::now();

        // Get branch KPIs
        $kpis = KPI::where('branch_id', $branchId)
            ->whereMonth('target_month', $currentMonth->month)
            ->whereYear('target_month', $currentMonth->year)
            ->with('progress')
            ->get();

        // Get staff KPIs
        $staffKpis = User::where('branch_id', $branchId)
            ->where('id', '!=', $user->id)
            ->with(['branch'])
            ->get()
            ->map(function($staff) use ($currentMonth) {
                $staffSales = DailySale::where('staff_id', $staff->id)
                    ->whereMonth('sale_date', $currentMonth->month)
                    ->whereYear('sale_date', $currentMonth->year)
                    ->where('status', 'completed')
                    ->sum('total_amount');

                return [
                    'staff' => $staff,
                    'sales' => $staffSales,
                    'transactions' => DailySale::where('staff_id', $staff->id)
                        ->whereMonth('sale_date', $currentMonth->month)
                        ->whereYear('sale_date', $currentMonth->year)
                        ->where('status', 'completed')
                        ->count()
                ];
            });

        // Monthly sales comparison (last 6 months)
        $monthlySalesData = $this->getMonthlySalesComparison($branchId);

        return view('branch-manager.kpi-benchmark', compact('kpis', 'staffKpis', 'monthlySalesData'));
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
     * Show inventory page (same as staff)
     */
    public function inventory()
    {
        $products = Product::with('category')
            ->orderBy('name')
            ->paginate(20);

        $categories = Category::all();

        return view('branch-manager.inventory', compact('products', 'categories'));
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
     * Verify report
     */
    public function verifyReport($id)
    {
        $report = DailySale::findOrFail($id);
        $report->verified_by = auth()->id();
        $report->verified_at = now();
        $report->save();

        return response()->json([
            'success' => true,
            'message' => 'Report verified successfully'
        ]);
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
     * Display stock overview page (view only)
     */
    public function stock()
    {
        $products = Product::with('category')->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        
        // Get recent stock logs
        $stockLogs = \App\Models\StockLog::with(['product', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('branch-manager.stock', compact('products', 'categories', 'stockLogs'));
    }
}