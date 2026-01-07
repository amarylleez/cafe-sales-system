<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use App\Models\DailySale;
use App\Models\DailySalesItem;
use App\Models\Product;
use App\Models\BranchStock;
use App\Models\KPI;
use App\Models\Benchmark;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use PDF; // Assuming you'll use barryvdh/laravel-dompdf
use Illuminate\Support\Facades\Response;

class HQAdminController extends Controller
{
    /**
     * Display HQ Admin dashboard
     */
    public function dashboard()
    {
        $currentMonth = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();

        // Total branches
        $totalBranches = Branch::count();
        $activeBranches = Branch::where('is_active', true)->count();

        // Total sales this month (all branches)
        $totalSales = DailySale::whereMonth('sale_date', $currentMonth->month)
            ->whereYear('sale_date', $currentMonth->year)
            ->where('status', 'completed')
            ->sum('total_amount');

        // Last month sales
        $lastMonthSales = DailySale::whereMonth('sale_date', $lastMonth->month)
            ->whereYear('sale_date', $lastMonth->year)
            ->where('status', 'completed')
            ->sum('total_amount');

        // Sales variance calculation
        $salesVarianceAmount = $totalSales - $lastMonthSales;
        $salesVariance = $lastMonthSales > 0 
            ? (($totalSales - $lastMonthSales) / $lastMonthSales) * 100 
            : 0;

        // Total transactions
        $totalTransactions = DailySale::whereMonth('sale_date', $currentMonth->month)
            ->whereYear('sale_date', $currentMonth->year)
            ->where('status', 'completed')
            ->count();

        // Total staff across all branches
        $totalStaff = User::whereNotNull('branch_id')->count();

        // Branch sales data for chart
        $branchSalesData = $this->getBranchSalesData($currentMonth);

        // Top performing branch
        $topBranch = $this->getTopPerformingBranch($currentMonth);

        // All branches performance
        $branchesPerformance = $this->getAllBranchesPerformance($currentMonth);

        return view('hq-admin.dashboard', compact(
            'totalBranches',
            'activeBranches',
            'totalSales',
            'lastMonthSales',
            'salesVariance',
            'salesVarianceAmount',
            'totalTransactions',
            'totalStaff',
            'branchSalesData',
            'topBranch',
            'branchesPerformance'
        ));
    }

    /**
     * Get branch sales data for chart
     */
    private function getBranchSalesData($month)
    {
        $branches = Branch::all();
        $labels = [];
        $values = [];

        foreach ($branches as $branch) {
            $labels[] = $branch->name;
            
            $sales = DailySale::where('branch_id', $branch->id)
                ->whereMonth('sale_date', $month->month)
                ->whereYear('sale_date', $month->year)
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
     * Get top performing branch
     */
    private function getTopPerformingBranch($month)
    {
        $branches = Branch::all();
        $topBranch = null;
        $maxSales = 0;

        foreach ($branches as $branch) {
            $sales = DailySale::where('branch_id', $branch->id)
                ->whereMonth('sale_date', $month->month)
                ->whereYear('sale_date', $month->year)
                ->where('status', 'completed')
                ->sum('total_amount');

            $transactions = DailySale::where('branch_id', $branch->id)
                ->whereMonth('sale_date', $month->month)
                ->whereYear('sale_date', $month->year)
                ->where('status', 'completed')
                ->count();

            if ($sales > $maxSales) {
                $maxSales = $sales;
                $topBranch = $branch;
                $topBranch->sales = $sales;
                $topBranch->transactions = $transactions;
                
                // Get branch manager
                $topBranch->manager = User::where('branch_id', $branch->id)
                    ->where('role', 'branch_manager')
                    ->first();
            }
        }

        return $topBranch;
    }

    /**
     * Get all branches performance
     */
    private function getAllBranchesPerformance($month)
    {
        $branches = Branch::all()->map(function($branch) use ($month) {
            $sales = DailySale::where('branch_id', $branch->id)
                ->whereMonth('sale_date', $month->month)
                ->whereYear('sale_date', $month->year)
                ->where('status', 'completed')
                ->sum('total_amount');

            $transactions = DailySale::where('branch_id', $branch->id)
                ->whereMonth('sale_date', $month->month)
                ->whereYear('sale_date', $month->year)
                ->where('status', 'completed')
                ->count();

            $branch->sales = $sales;
            $branch->transactions = $transactions;
            
            // Get branch manager
            $branch->manager = User::where('branch_id', $branch->id)
                ->where('role', 'branch_manager')
                ->first();

            return $branch;
        })->sortByDesc('sales');

        return $branches;
    }

    /**
     * Show analytics page
     */
    public function analytics(Request $request)
    {
        $currentMonth = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();

        // Top 3 performing branches
        $topBranches = $this->getTopBranches($currentMonth);

        // Branch comparison data (last 6 months)
        $comparisonData = $this->getBranchComparisonData();

        // Detailed branch analysis
        $branchAnalysis = $this->getDetailedBranchAnalysis($currentMonth, $lastMonth);

        // ===== PROFIT/LOSS DATA =====
        // Date range filter
        $dateRange = $request->input('date_range', 'month');
        $branchFilter = $request->input('branch');
        
        // Determine date boundaries
        switch ($dateRange) {
            case 'today':
                $startDate = Carbon::today();
                $endDate = Carbon::today();
                break;
            case 'week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'quarter':
                $startDate = Carbon::now()->startOfQuarter();
                $endDate = Carbon::now()->endOfQuarter();
                break;
            case 'year':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
                break;
            default:
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
        }

        // Get all branches for filter
        $branches = Branch::all();

        // Build query for sales items with product cost
        $salesQuery = DailySalesItem::with(['product', 'dailySale.branch'])
            ->whereHas('dailySale', function($q) use ($startDate, $endDate, $branchFilter) {
                $q->whereBetween('sale_date', [$startDate, $endDate])
                  ->where('status', 'completed');
                  
                if ($branchFilter) {
                    $q->where('branch_id', $branchFilter);
                }
            });

        // Calculate totals
        $salesItems = $salesQuery->get();
        
        $totalRevenue = 0;
        $totalCost = 0;
        $productProfitData = [];

        foreach ($salesItems as $item) {
            $revenue = $item->total;
            $cost = $item->quantity * ($item->product->cost_price ?? 0);
            
            $totalRevenue += $revenue;
            $totalCost += $cost;
            
            // Aggregate by product
            $productId = $item->product_id;
            if (!isset($productProfitData[$productId])) {
                $productProfitData[$productId] = [
                    'product' => $item->product,
                    'quantity_sold' => 0,
                    'revenue' => 0,
                    'cost' => 0,
                    'profit' => 0
                ];
            }
            $productProfitData[$productId]['quantity_sold'] += $item->quantity;
            $productProfitData[$productId]['revenue'] += $revenue;
            $productProfitData[$productId]['cost'] += $cost;
            $productProfitData[$productId]['profit'] += ($revenue - $cost);
        }

        $grossProfit = $totalRevenue - $totalCost;
        $profitMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;

        // Sort products by profit (descending)
        $productProfitData = collect($productProfitData)->sortByDesc('profit')->values();
        
        // Top 5 profitable products
        $topProfitableProducts = $productProfitData->take(5);

        // Calculate stock loss from unsold items (items stocked more than 1 day ago that haven't been sold)
        $stockLossQuery = BranchStock::with('product')
            ->where('stock_quantity', '>', 0)
            ->where(function($q) {
                // Stock that was received yesterday or earlier (unsold for at least 1 day)
                $q->whereNotNull('received_date')
                  ->where('received_date', '<', Carbon::today());
            });
            
        if ($branchFilter) {
            $stockLossQuery->where('branch_id', $branchFilter);
        }
        
        $unsoldStock = $stockLossQuery->get();
        $unsoldStockLoss = $unsoldStock->sum(function($stock) {
            return $stock->stock_quantity * ($stock->cost_at_purchase ?? $stock->product->cost_price ?? 0);
        });

        // Calculate loss from rejected transactions (stock already deducted but sale not counted)
        $rejectedSalesQuery = DailySalesItem::with(['product', 'dailySale'])
            ->whereHas('dailySale', function($q) use ($startDate, $endDate, $branchFilter) {
                $q->whereBetween('sale_date', [$startDate, $endDate])
                  ->where('status', 'rejected');
                  
                if ($branchFilter) {
                    $q->where('branch_id', $branchFilter);
                }
            });
        
        $rejectedItems = $rejectedSalesQuery->get();
        $rejectedSalesLoss = $rejectedItems->sum(function($item) {
            return $item->quantity * ($item->product->cost_price ?? 0);
        });

        // Total stock loss = unsold stock + rejected sales
        $stockLoss = $unsoldStockLoss + $rejectedSalesLoss;

        // Get potential loss stock (items unsold for more than 1 day - for warning display)
        $potentialLossQuery = BranchStock::with(['product', 'branch'])
            ->where('stock_quantity', '>', 0)
            ->whereNotNull('received_date')
            ->where('received_date', '<', Carbon::today());
            
        if ($branchFilter) {
            $potentialLossQuery->where('branch_id', $branchFilter);
        }
        
        $potentialLossStock = $potentialLossQuery->orderBy('received_date', 'asc')->get();

        // Get rejected sales for display
        $rejectedSales = DailySale::with(['items.product', 'branch', 'staff'])
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->where('status', 'rejected');
            
        if ($branchFilter) {
            $rejectedSales->where('branch_id', $branchFilter);
        }
        
        $rejectedSales = $rejectedSales->orderBy('sale_date', 'desc')->get();

        // Net profit (after stock loss)
        $netProfit = $grossProfit - $stockLoss;

        // Branch-wise profit breakdown
        $branchProfitData = [];
        $salesByBranch = DailySalesItem::with(['product', 'dailySale.branch'])
            ->whereHas('dailySale', function($q) use ($startDate, $endDate) {
                $q->whereBetween('sale_date', [$startDate, $endDate])
                  ->where('status', 'completed');
            })
            ->get()
            ->groupBy('dailySale.branch_id');

        foreach ($salesByBranch as $branchId => $items) {
            $branch = Branch::find($branchId);
            if (!$branch) continue;
            
            $branchRevenue = $items->sum('total');
            $branchCost = $items->sum(function($item) {
                return $item->quantity * ($item->product->cost_price ?? 0);
            });
            
            $branchProfitData[] = [
                'branch' => $branch,
                'revenue' => $branchRevenue,
                'cost' => $branchCost,
                'gross_profit' => $branchRevenue - $branchCost,
                'margin' => $branchRevenue > 0 ? (($branchRevenue - $branchCost) / $branchRevenue) * 100 : 0
            ];
        }
        
        $branchProfitData = collect($branchProfitData)->sortByDesc('gross_profit')->values();

        // Monthly profit trend data (last 6 months)
        $monthlyProfitTrend = $this->getMonthlyProfitTrend($branchFilter);

        // Stock Loss Trend (last 30 days)
        $stockLossTrend = $this->getStockLossTrend($branchFilter);
        
        // Stock Loss by Category
        $stockLossByCategory = $this->getStockLossByCategory($branchFilter, $startDate, $endDate);
        
        // Stock Loss by Branch
        $stockLossByBranch = $this->getStockLossByBranch($startDate, $endDate);

        return view('hq-admin.analytics', compact(
            'topBranches',
            'comparisonData',
            'branchAnalysis',
            // Profit/Loss data
            'branches',
            'totalRevenue',
            'totalCost',
            'grossProfit',
            'netProfit',
            'profitMargin',
            'stockLoss',
            'unsoldStockLoss',
            'rejectedSalesLoss',
            'potentialLossStock',
            'rejectedSales',
            'topProfitableProducts',
            'branchProfitData',
            'monthlyProfitTrend',
            'stockLossTrend',
            'stockLossByCategory',
            'stockLossByBranch',
            'startDate',
            'endDate',
            'dateRange'
        ));
    }

    /**
     * Show branch-specific analytics (like Branch Manager dashboard)
     */
    public function branchAnalytics($id)
    {
        $branch = Branch::findOrFail($id);
        $currentMonth = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();

        // Get branch manager
        $branchManager = User::where('branch_id', $branch->id)
            ->where('role', 'branch_manager')
            ->first();

        // Week sales
        $weekSales = DailySale::where('branch_id', $branch->id)
            ->whereBetween('sale_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->where('status', 'completed')
            ->sum('total_amount');

        $lastWeekSales = DailySale::where('branch_id', $branch->id)
            ->whereBetween('sale_date', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()])
            ->where('status', 'completed')
            ->sum('total_amount');

        $weekGrowth = $lastWeekSales > 0 
            ? (($weekSales - $lastWeekSales) / $lastWeekSales) * 100 
            : 0;

        // Month sales
        $monthSales = DailySale::where('branch_id', $branch->id)
            ->whereMonth('sale_date', $currentMonth->month)
            ->whereYear('sale_date', $currentMonth->year)
            ->where('status', 'completed')
            ->sum('total_amount');

        // Get benchmark target
        $benchmark = Benchmark::where('is_active', true)->first();
        $monthlyTarget = $benchmark->monthly_sales_target ?? 500;

        // Total transactions this month
        $totalTransactions = DailySale::where('branch_id', $branch->id)
            ->whereMonth('sale_date', $currentMonth->month)
            ->whereYear('sale_date', $currentMonth->year)
            ->where('status', 'completed')
            ->count();

        // Active staff count
        $activeStaff = User::where('branch_id', $branch->id)->count();

        // Sales trend (last 7 days)
        $salesTrendData = $this->getBranchSalesTrend($branch->id);

        // Sales by category
        $categoryData = $this->getBranchCategoryData($branch->id, $currentMonth);

        // Monthly sales data (last 6 months)
        $monthlySalesData = $this->getBranchMonthlySalesData($branch->id);

        return view('hq-admin.branch-analytics', compact(
            'branch',
            'branchManager',
            'weekSales',
            'weekGrowth',
            'monthSales',
            'monthlyTarget',
            'totalTransactions',
            'activeStaff',
            'salesTrendData',
            'categoryData',
            'monthlySalesData',
            'benchmark'
        ));
    }

    /**
     * Get branch sales trend (last 7 days)
     */
    private function getBranchSalesTrend($branchId)
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
     * Get branch category data
     */
    private function getBranchCategoryData($branchId, $month)
    {
        $categories = \App\Models\Category::all();
        $labels = [];
        $values = [];

        foreach ($categories as $category) {
            $sales = DB::table('daily_sales_items')
                ->join('daily_sales', 'daily_sales_items.daily_sale_id', '=', 'daily_sales.id')
                ->join('products', 'daily_sales_items.product_id', '=', 'products.id')
                ->where('daily_sales.branch_id', $branchId)
                ->where('products.category_id', $category->id)
                ->whereMonth('daily_sales.sale_date', $month->month)
                ->whereYear('daily_sales.sale_date', $month->year)
                ->where('daily_sales.status', 'completed')
                ->sum('daily_sales_items.quantity');

            if ($sales > 0) {
                $labels[] = $category->name;
                $values[] = $sales;
            }
        }

        // If no category data, show default
        if (empty($labels)) {
            $labels = ['No Sales'];
            $values = [0];
        }

        return [
            'labels' => $labels,
            'values' => $values
        ];
    }

    /**
     * Get branch monthly sales data (last 6 months)
     */
    private function getBranchMonthlySalesData($branchId)
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
     * Get top 3 branches
     */
    private function getTopBranches($month)
    {
        $branches = Branch::all();
        $totalSales = 0;
        $branchData = [];

        foreach ($branches as $branch) {
            $sales = DailySale::where('branch_id', $branch->id)
                ->whereMonth('sale_date', $month->month)
                ->whereYear('sale_date', $month->year)
                ->where('status', 'completed')
                ->sum('total_amount');

            $transactions = DailySale::where('branch_id', $branch->id)
                ->whereMonth('sale_date', $month->month)
                ->whereYear('sale_date', $month->year)
                ->where('status', 'completed')
                ->count();

            $avgTransaction = $transactions > 0 ? $sales / $transactions : 0;

            $branch->sales = $sales;
            $branch->transactions = $transactions;
            $branch->avgTransaction = $avgTransaction;
            $branch->manager = User::where('branch_id', $branch->id)
                ->where('role', 'branch_manager')
                ->first();

            $totalSales += $sales;
            $branchData[] = $branch;
        }

        // Calculate percentage and sort
        $branchData = collect($branchData)->map(function($branch) use ($totalSales) {
            $branch->percentage = $totalSales > 0 ? ($branch->sales / $totalSales) * 100 : 0;
            return $branch;
        })->sortByDesc('sales')->take(3)->values();

        return $branchData;
    }

    /**
     * Get branch comparison data (6 months)
     */
    private function getBranchComparisonData()
    {
        $branches = Branch::all();
        $labels = [];
        $datasets = [];

        // Get last 6 months labels
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $labels[] = $date->format('M Y');
        }

        // Get data for each branch
        foreach ($branches as $branch) {
            $values = [];
            
            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                
                $sales = DailySale::where('branch_id', $branch->id)
                    ->whereMonth('sale_date', $date->month)
                    ->whereYear('sale_date', $date->year)
                    ->where('status', 'completed')
                    ->sum('total_amount');
                
                $values[] = $sales;
            }

            $datasets[] = [
                'name' => $branch->name,
                'values' => $values
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets
        ];
    }

    /**
     * Get detailed branch analysis
     */
    private function getDetailedBranchAnalysis($currentMonth, $lastMonth)
    {
        $branches = Branch::all();

        return $branches->map(function($branch) use ($currentMonth, $lastMonth) {
            $currentMonthSales = DailySale::where('branch_id', $branch->id)
                ->whereMonth('sale_date', $currentMonth->month)
                ->whereYear('sale_date', $currentMonth->year)
                ->where('status', 'completed')
                ->sum('total_amount');

            $lastMonthSales = DailySale::where('branch_id', $branch->id)
                ->whereMonth('sale_date', $lastMonth->month)
                ->whereYear('sale_date', $lastMonth->year)
                ->where('status', 'completed')
                ->sum('total_amount');

            $growth = $lastMonthSales > 0 
                ? (($currentMonthSales - $lastMonthSales) / $lastMonthSales) * 100 
                : 0;

            $transactions = DailySale::where('branch_id', $branch->id)
                ->whereMonth('sale_date', $currentMonth->month)
                ->whereYear('sale_date', $currentMonth->year)
                ->where('status', 'completed')
                ->count();

            $staffCount = User::where('branch_id', $branch->id)->count();
            $avgSalesPerStaff = $staffCount > 0 ? $currentMonthSales / $staffCount : 0;

            $branch->currentMonth = $currentMonthSales;
            $branch->lastMonth = $lastMonthSales;
            $branch->growth = $growth;
            $branch->transactions = $transactions;
            $branch->staffCount = $staffCount;
            $branch->avgSalesPerStaff = $avgSalesPerStaff;

            return $branch;
        })->sortByDesc('currentMonth');
    }

    /**
     * Show manage staff page
     */
    public function manage()
    {
        $branches = Branch::all();
        
        // Get all staff, sorted by branch and role (HQ Admins first, then by branch with managers before staff)
        $allStaff = User::with('branch')
            ->get()
            ->sortBy([
                // First: HQ Admins (no branch) at the top
                fn($a, $b) => ($a->role === 'hq_admin' ? 0 : 1) <=> ($b->role === 'hq_admin' ? 0 : 1),
                // Then: Group by branch name
                fn($a, $b) => ($a->branch->name ?? 'ZZZ') <=> ($b->branch->name ?? 'ZZZ'),
                // Within each branch: Branch managers first, then staff
                fn($a, $b) => ($a->role === 'branch_manager' ? 0 : 1) <=> ($b->role === 'branch_manager' ? 0 : 1),
                // Finally: Sort by name within the same role
                fn($a, $b) => $a->name <=> $b->name,
            ]);
        
        // Count by role
        $totalStaff = User::count();
        $branchManagers = User::where('role', 'branch_manager')->count();
        $staffMembers = User::where('role', 'staff')->count();
        $hqAdmins = User::where('role', 'hq_admin')->count();

        return view('hq-admin.manage', compact(
            'branches',
            'allStaff',
            'totalStaff',
            'branchManagers',
            'staffMembers',
            'hqAdmins'
        ));
    }

    /**
     * Store new staff
     */
    public function storeStaff(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role' => 'required|in:hq_admin,branch_manager,staff',
            'branch_id' => 'required_unless:role,hq_admin|exists:branches,id'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'branch_id' => $request->role === 'hq_admin' ? null : $request->branch_id,
        ]);

        return redirect()->route('hq-admin.manage')->with('success', 'Staff added successfully!');
    }

    /**
     * Get staff details
     */
    public function getStaff($id)
    {
        $staff = User::with('branch')->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'staff' => $staff
        ]);
    }

    /**
     * Update staff
     */
    public function updateStaff(Request $request, $id)
    {
        $staff = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|in:hq_admin,branch_manager,staff',
            'branch_id' => 'nullable|required_unless:role,hq_admin|exists:branches,id'
        ]);

        $staff->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'branch_id' => $request->role === 'hq_admin' ? null : $request->branch_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Staff updated successfully'
        ]);
    }

    /**
     * Delete staff
     */
    public function deleteStaff($id)
    {
        $staff = User::findOrFail($id);
        $staff->delete();

        return response()->json([
            'success' => true,
            'message' => 'Staff deleted successfully'
        ]);
    }

    /**
     * Show KPI & Benchmark page
     */
    public function kpiBenchmark()
    {
        $currentMonth = Carbon::now();

        // Get current benchmarks
        $benchmarks = Benchmark::where('is_active', true)->first();
        
        $monthlyBenchmark = $benchmarks->monthly_sales_target ?? 50000;
        $staffBenchmark = $benchmarks->staff_sales_target ?? 10000;

        // Get branch KPIs
        $branchKPIs = Branch::all()->map(function($branch) use ($currentMonth) {
            $sales = DailySale::where('branch_id', $branch->id)
                ->whereMonth('sale_date', $currentMonth->month)
                ->whereYear('sale_date', $currentMonth->year)
                ->where('status', 'completed')
                ->sum('total_amount');

            $transactions = DailySale::where('branch_id', $branch->id)
                ->whereMonth('sale_date', $currentMonth->month)
                ->whereYear('sale_date', $currentMonth->year)
                ->where('status', 'completed')
                ->count();

            $branch->currentSales = $sales;
            $branch->transactions = $transactions;

            return $branch;
        });

        // Get staff performance
        $staffPerformance = User::where('role', 'staff')
            ->with('branch')
            ->get()
            ->map(function($staff) use ($currentMonth) {
                $sales = DailySale::where('staff_id', $staff->id)
                    ->whereMonth('sale_date', $currentMonth->month)
                    ->whereYear('sale_date', $currentMonth->year)
                    ->where('status', 'completed')
                    ->sum('total_amount');

                $staff->currentSales = $sales;
                return $staff;
            })
            ->sortByDesc('currentSales');

        // Staff KPI chart data
        $staffKPIChartData = [
            'labels' => $staffPerformance->pluck('name')->toArray(),
            'sales' => $staffPerformance->pluck('currentSales')->toArray(),
            'targets' => array_fill(0, $staffPerformance->count(), $staffBenchmark)
        ];

        return view('hq-admin.kpi-benchmark', compact(
            'monthlyBenchmark',
            'staffBenchmark',
            'branchKPIs',
            'staffPerformance',
            'staffKPIChartData'
        ));
    }

    /**
     * Store new benchmarks
     */
    public function storeBenchmark(Request $request)
    {
        $request->validate([
            'monthly_benchmark' => 'required|numeric|min:0',
            'staff_benchmark' => 'required|numeric|min:0'
        ]);

        // Deactivate old benchmarks
        Benchmark::where('is_active', true)->update(['is_active' => false]);

        // Create new benchmark - applied immediately
        Benchmark::create([
            'monthly_sales_target' => $request->monthly_benchmark,
            'transaction_target' => 0, // Deprecated field, kept for backwards compatibility
            'staff_sales_target' => $request->staff_benchmark,
            'is_active' => true,
            'effective_from' => Carbon::now()
        ]);

        return redirect()->route('hq-admin.kpi-benchmark')->with('success', 'Benchmarks updated successfully and applied immediately!');
    }

    /**
     * Show reports page
     */
    public function reports(Request $request)
    {
        $branches = Branch::all();
        
        // Build query
        $query = DailySale::with(['branch', 'staff']);

        // Apply filters - only filter by date if date_range has a value and is not empty
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
            // If date_range is provided but no valid filter matched (like custom without dates), don't apply any date filter
        }
        // When no date_range is provided (first page load or reset), show ALL data without date filter

        if ($request->has('branch') && $request->branch !== '') {
            $query->where('branch_id', $request->branch);
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Clone the query before pagination to calculate summary correctly
        $summaryQuery = clone $query;
        
        $salesReports = $query->orderBy('sale_date', 'desc')->paginate(15);

        // Calculate summary using fresh queries based on same filters
        $reportSummary = [
            'totalSales' => (clone $summaryQuery)->sum('total_amount'),
            'totalReports' => (clone $summaryQuery)->count(),
            'verifiedReports' => (clone $summaryQuery)->where('status', 'completed')->count(),
            'pendingReports' => (clone $summaryQuery)->where('status', 'pending')->count()
        ];

        return view('hq-admin.reports', compact('branches', 'salesReports', 'reportSummary'));
    }

    /**
     * Get report details
     */
    public function getReport($id)
    {
        $report = DailySale::with(['branch', 'staff'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'report' => $report
        ]);
    }

    /**
     * Download single report as PDF
     */
    public function downloadReportPDF($id)
    {
        $report = DailySale::with(['branch', 'staff'])->findOrFail($id);
        
        $pdf = PDF::loadView('hq-admin.pdf.report', compact('report'));
        
        return $pdf->download('sales-report-' . $report->id . '.pdf');
    }

    /**
     * Export reports as PDF
     */
    public function exportPDF(Request $request)
    {
        $dateRange = $request->input('date_range', '');
        $branchFilter = $request->input('branch', '');
        $statusFilter = $request->input('status', '');
        
        // Determine date boundaries
        switch ($dateRange) {
            case 'today':
                $startDate = Carbon::today();
                $endDate = Carbon::today();
                $dateRangeLabel = 'Today (' . $startDate->format('M d, Y') . ')';
                break;
            case 'week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                $dateRangeLabel = 'This Week (' . $startDate->format('M d') . ' - ' . $endDate->format('M d, Y') . ')';
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                $dateRangeLabel = 'This Month (' . $startDate->format('M Y') . ')';
                break;
            case 'custom':
                $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->startOfMonth();
                $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now();
                $dateRangeLabel = 'Custom (' . $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y') . ')';
                break;
            default:
                $startDate = null;
                $endDate = null;
                $dateRangeLabel = 'All Time';
        }
        
        // Get branches for report
        $branches = Branch::all();
        $selectedBranch = $branchFilter ? Branch::find($branchFilter) : null;
        
        // Build profit/loss data per branch
        $branchProfitData = [];
        $overallTotals = [
            'revenue' => 0,
            'cost' => 0,
            'grossProfit' => 0,
            'rejectedLoss' => 0,
            'netProfit' => 0,
            'transactions' => 0
        ];
        
        $branchesToProcess = $branchFilter ? [$selectedBranch] : $branches;
        
        foreach ($branchesToProcess as $branch) {
            if (!$branch) continue;
            
            // Get completed sales for this branch
            $salesQuery = DailySalesItem::with(['product', 'dailySale'])
                ->whereHas('dailySale', function($q) use ($startDate, $endDate, $branch) {
                    $q->where('branch_id', $branch->id)
                      ->where('status', 'completed');
                    if ($startDate && $endDate) {
                        $q->whereBetween('sale_date', [$startDate, $endDate]);
                    }
                });
            
            $salesItems = $salesQuery->get();
            
            $branchRevenue = 0;
            $branchCost = 0;
            
            foreach ($salesItems as $item) {
                $revenue = $item->total;
                $cost = $item->quantity * ($item->product->cost_price ?? 0);
                $branchRevenue += $revenue;
                $branchCost += $cost;
            }
            
            // Get rejected sales loss
            $rejectedQuery = DailySale::where('branch_id', $branch->id)
                ->where('status', 'rejected');
            if ($startDate && $endDate) {
                $rejectedQuery->whereBetween('sale_date', [$startDate, $endDate]);
            }
            $rejectedLoss = $rejectedQuery->sum('total_amount');
            
            // Transaction count
            $transactionQuery = DailySale::where('branch_id', $branch->id)
                ->where('status', 'completed');
            if ($startDate && $endDate) {
                $transactionQuery->whereBetween('sale_date', [$startDate, $endDate]);
            }
            $transactionCount = $transactionQuery->count();
            
            $grossProfit = $branchRevenue - $branchCost;
            $netProfit = $grossProfit - $rejectedLoss;
            
            $branchProfitData[] = [
                'branch' => $branch,
                'revenue' => $branchRevenue,
                'cost' => $branchCost,
                'grossProfit' => $grossProfit,
                'rejectedLoss' => $rejectedLoss,
                'netProfit' => $netProfit,
                'transactions' => $transactionCount,
                'profitMargin' => $branchRevenue > 0 ? ($grossProfit / $branchRevenue) * 100 : 0
            ];
            
            // Add to overall totals
            $overallTotals['revenue'] += $branchRevenue;
            $overallTotals['cost'] += $branchCost;
            $overallTotals['grossProfit'] += $grossProfit;
            $overallTotals['rejectedLoss'] += $rejectedLoss;
            $overallTotals['netProfit'] += $netProfit;
            $overallTotals['transactions'] += $transactionCount;
        }
        
        $overallTotals['profitMargin'] = $overallTotals['revenue'] > 0 
            ? ($overallTotals['grossProfit'] / $overallTotals['revenue']) * 100 
            : 0;
        
        // Sort branches by net profit (descending)
        usort($branchProfitData, function($a, $b) {
            return $b['netProfit'] <=> $a['netProfit'];
        });
        
        // Get top products
        $topProductsQuery = DailySalesItem::with(['product'])
            ->select('product_id')
            ->selectRaw('SUM(quantity) as total_qty')
            ->selectRaw('SUM(total) as total_revenue');
        
        if ($branchFilter) {
            $topProductsQuery->whereHas('dailySale', function($q) use ($branchFilter, $startDate, $endDate) {
                $q->where('branch_id', $branchFilter)->where('status', 'completed');
                if ($startDate && $endDate) {
                    $q->whereBetween('sale_date', [$startDate, $endDate]);
                }
            });
        } else {
            $topProductsQuery->whereHas('dailySale', function($q) use ($startDate, $endDate) {
                $q->where('status', 'completed');
                if ($startDate && $endDate) {
                    $q->whereBetween('sale_date', [$startDate, $endDate]);
                }
            });
        }
        
        $topProducts = $topProductsQuery->groupBy('product_id')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get()
            ->map(function($item) {
                $product = $item->product;
                $cost = $item->total_qty * ($product->cost_price ?? 0);
                return [
                    'name' => $product->name ?? 'Unknown',
                    'quantity' => $item->total_qty,
                    'revenue' => $item->total_revenue,
                    'cost' => $cost,
                    'profit' => $item->total_revenue - $cost
                ];
            });
        
        $pdf = PDF::loadView('hq-admin.pdf.reports', compact(
            'branchProfitData', 
            'overallTotals', 
            'dateRangeLabel', 
            'selectedBranch',
            'topProducts'
        ));
        
        return $pdf->download('profit-loss-report-' . Carbon::now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Apply filters to report query
     */
    private function applyReportFilters($query, $request)
    {
        $dateRange = $request->input('date_range', 'month');
        
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
                if ($startDate && $endDate) {
                    $query->whereBetween('sale_date', [$startDate, $endDate]);
                }
                break;
        }

        $branch = $request->input('branch');
        if ($branch && $branch !== '') {
            $query->where('branch_id', $branch);
        }

        $status = $request->input('status');
        if ($status && $status !== '') {
            $query->where('status', $status);
        }
    }

    // ========== PROFIT & LOSS METHODS ==========

    /**
     * Show Profit & Loss page
     */
    public function profitLoss(Request $request)
    {
        $currentMonth = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();
        
        // Date range filter
        $dateRange = $request->input('date_range', 'month');
        $branchFilter = $request->input('branch');
        
        // Determine date boundaries
        switch ($dateRange) {
            case 'today':
                $startDate = Carbon::today();
                $endDate = Carbon::today();
                break;
            case 'week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'quarter':
                $startDate = Carbon::now()->startOfQuarter();
                $endDate = Carbon::now()->endOfQuarter();
                break;
            case 'year':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
                break;
            case 'custom':
                $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->startOfMonth();
                $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now();
                break;
            default:
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
        }

        // Get all branches for filter
        $branches = Branch::all();

        // Build query for sales items with product cost
        $salesQuery = DailySalesItem::with(['product', 'dailySale.branch'])
            ->whereHas('dailySale', function($q) use ($startDate, $endDate, $branchFilter) {
                $q->whereBetween('sale_date', [$startDate, $endDate])
                  ->where('status', 'completed');
                  
                if ($branchFilter) {
                    $q->where('branch_id', $branchFilter);
                }
            });

        // Calculate totals
        $salesItems = $salesQuery->get();
        
        $totalRevenue = 0;
        $totalCost = 0;
        $productProfitData = [];

        foreach ($salesItems as $item) {
            $revenue = $item->total;
            $cost = $item->quantity * ($item->product->cost_price ?? 0);
            
            $totalRevenue += $revenue;
            $totalCost += $cost;
            
            // Aggregate by product
            $productId = $item->product_id;
            if (!isset($productProfitData[$productId])) {
                $productProfitData[$productId] = [
                    'product' => $item->product,
                    'quantity_sold' => 0,
                    'revenue' => 0,
                    'cost' => 0,
                    'profit' => 0
                ];
            }
            $productProfitData[$productId]['quantity_sold'] += $item->quantity;
            $productProfitData[$productId]['revenue'] += $revenue;
            $productProfitData[$productId]['cost'] += $cost;
            $productProfitData[$productId]['profit'] += ($revenue - $cost);
        }

        $grossProfit = $totalRevenue - $totalCost;
        $profitMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;

        // Sort products by profit (descending)
        $productProfitData = collect($productProfitData)->sortByDesc('profit')->values();
        
        // Top 5 profitable products
        $topProfitableProducts = $productProfitData->take(5);
        
        // Bottom 5 (least profitable/loss-making)
        $leastProfitableProducts = $productProfitData->sortBy('profit')->take(5);

        // Calculate expired stock loss
        $expiredStockQuery = BranchStock::with('product')
            ->expired()
            ->where('stock_quantity', '>', 0);
            
        if ($branchFilter) {
            $expiredStockQuery->where('branch_id', $branchFilter);
        }
        
        $expiredStock = $expiredStockQuery->get();
        $expiredStockLoss = $expiredStock->sum(function($stock) {
            return $stock->stock_quantity * ($stock->cost_at_purchase ?? $stock->product->cost_price ?? 0);
        });

        // Calculate expiring soon stock (within 7 days)
        $expiringSoonQuery = BranchStock::with(['product', 'branch'])
            ->expiringSoon(7)
            ->where('stock_quantity', '>', 0);
            
        if ($branchFilter) {
            $expiringSoonQuery->where('branch_id', $branchFilter);
        }
        
        $expiringSoonStock = $expiringSoonQuery->get();
        $potentialLoss = $expiringSoonStock->sum(function($stock) {
            return $stock->stock_quantity * ($stock->cost_at_purchase ?? $stock->product->cost_price ?? 0);
        });

        // Net profit (after expired stock loss)
        $netProfit = $grossProfit - $expiredStockLoss;

        // Branch-wise profit breakdown
        $branchProfitData = [];
        $salesByBranch = DailySalesItem::with(['product', 'dailySale.branch'])
            ->whereHas('dailySale', function($q) use ($startDate, $endDate) {
                $q->whereBetween('sale_date', [$startDate, $endDate])
                  ->where('status', 'completed');
            })
            ->get()
            ->groupBy('dailySale.branch_id');

        foreach ($salesByBranch as $branchId => $items) {
            $branch = Branch::find($branchId);
            if (!$branch) continue;
            
            $branchRevenue = $items->sum('total');
            $branchCost = $items->sum(function($item) {
                return $item->quantity * ($item->product->cost_price ?? 0);
            });
            
            // Get expired stock loss for this branch
            $branchExpiredLoss = BranchStock::where('branch_id', $branchId)
                ->expired()
                ->where('stock_quantity', '>', 0)
                ->get()
                ->sum(function($stock) {
                    return $stock->stock_quantity * ($stock->cost_at_purchase ?? $stock->product->cost_price ?? 0);
                });
            
            $branchProfitData[] = [
                'branch' => $branch,
                'revenue' => $branchRevenue,
                'cost' => $branchCost,
                'gross_profit' => $branchRevenue - $branchCost,
                'expired_loss' => $branchExpiredLoss,
                'net_profit' => ($branchRevenue - $branchCost) - $branchExpiredLoss,
                'margin' => $branchRevenue > 0 ? (($branchRevenue - $branchCost) / $branchRevenue) * 100 : 0
            ];
        }
        
        $branchProfitData = collect($branchProfitData)->sortByDesc('net_profit')->values();

        // Monthly trend data (last 6 months)
        $monthlyTrendData = $this->getMonthlyProfitTrend($branchFilter);

        // Category breakdown
        $categoryProfitData = $this->getCategoryProfitBreakdown($startDate, $endDate, $branchFilter);

        return view('hq-admin.profit-loss', compact(
            'branches',
            'totalRevenue',
            'totalCost',
            'grossProfit',
            'netProfit',
            'profitMargin',
            'expiredStockLoss',
            'potentialLoss',
            'expiredStock',
            'expiringSoonStock',
            'productProfitData',
            'topProfitableProducts',
            'leastProfitableProducts',
            'branchProfitData',
            'monthlyTrendData',
            'categoryProfitData',
            'startDate',
            'endDate',
            'dateRange'
        ));
    }

    /**
     * Get monthly profit trend for last 6 months
     */
    private function getMonthlyProfitTrend($branchFilter = null)
    {
        $labels = [];
        $revenueData = [];
        $costData = [];
        $profitData = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $labels[] = $date->format('M Y');
            
            $query = DailySalesItem::with('product')
                ->whereHas('dailySale', function($q) use ($date, $branchFilter) {
                    $q->whereMonth('sale_date', $date->month)
                      ->whereYear('sale_date', $date->year)
                      ->where('status', 'completed');
                      
                    if ($branchFilter) {
                        $q->where('branch_id', $branchFilter);
                    }
                });

            $items = $query->get();
            $revenue = $items->sum('total');
            $cost = $items->sum(function($item) {
                return $item->quantity * ($item->product->cost_price ?? 0);
            });
            
            $revenueData[] = round($revenue, 2);
            $costData[] = round($cost, 2);
            $profitData[] = round($revenue - $cost, 2);
        }

        return [
            'labels' => $labels,
            'revenue' => $revenueData,
            'cost' => $costData,
            'profit' => $profitData
        ];
    }

    /**
     * Get stock loss trend over the last 30 days
     */
    private function getStockLossTrend($branchFilter = null)
    {
        $labels = [];
        $unsoldData = [];
        $rejectedData = [];
        $totalData = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('M d');
            
            // Unsold stock loss for this day
            $unsoldQuery = BranchStock::with('product')
                ->where('stock_quantity', '>', 0)
                ->whereNotNull('received_date')
                ->whereDate('received_date', $date);
                
            if ($branchFilter) {
                $unsoldQuery->where('branch_id', $branchFilter);
            }
            
            $unsoldLoss = $unsoldQuery->get()->sum(function($stock) {
                return $stock->stock_quantity * ($stock->cost_at_purchase ?? $stock->product->cost_price ?? 0);
            });
            
            // Rejected sales loss for this day
            $rejectedQuery = DailySalesItem::with('product')
                ->whereHas('dailySale', function($q) use ($date, $branchFilter) {
                    $q->whereDate('sale_date', $date)
                      ->where('status', 'rejected');
                      
                    if ($branchFilter) {
                        $q->where('branch_id', $branchFilter);
                    }
                });
            
            $rejectedLoss = $rejectedQuery->get()->sum(function($item) {
                return $item->quantity * ($item->product->cost_price ?? 0);
            });
            
            $unsoldData[] = round($unsoldLoss, 2);
            $rejectedData[] = round($rejectedLoss, 2);
            $totalData[] = round($unsoldLoss + $rejectedLoss, 2);
        }

        return [
            'labels' => $labels,
            'unsold' => $unsoldData,
            'rejected' => $rejectedData,
            'total' => $totalData
        ];
    }

    /**
     * Get stock loss breakdown by category
     */
    private function getStockLossByCategory($branchFilter, $startDate, $endDate)
    {
        $categories = \App\Models\Category::all();
        $data = [];

        foreach ($categories as $category) {
            // Unsold stock loss
            $unsoldQuery = BranchStock::with('product')
                ->whereHas('product', function($q) use ($category) {
                    $q->where('category_id', $category->id);
                })
                ->where('stock_quantity', '>', 0)
                ->whereNotNull('received_date')
                ->where('received_date', '<', Carbon::today());
                
            if ($branchFilter) {
                $unsoldQuery->where('branch_id', $branchFilter);
            }
            
            $unsoldLoss = $unsoldQuery->get()->sum(function($stock) {
                return $stock->stock_quantity * ($stock->cost_at_purchase ?? $stock->product->cost_price ?? 0);
            });
            
            // Rejected sales loss
            $rejectedQuery = DailySalesItem::with('product')
                ->whereHas('product', function($q) use ($category) {
                    $q->where('category_id', $category->id);
                })
                ->whereHas('dailySale', function($q) use ($startDate, $endDate, $branchFilter) {
                    $q->whereBetween('sale_date', [$startDate, $endDate])
                      ->where('status', 'rejected');
                      
                    if ($branchFilter) {
                        $q->where('branch_id', $branchFilter);
                    }
                });
            
            $rejectedLoss = $rejectedQuery->get()->sum(function($item) {
                return $item->quantity * ($item->product->cost_price ?? 0);
            });
            
            $totalLoss = $unsoldLoss + $rejectedLoss;
            
            if ($totalLoss > 0) {
                $data[] = [
                    'category' => $category->name,
                    'unsold' => $unsoldLoss,
                    'rejected' => $rejectedLoss,
                    'total' => $totalLoss
                ];
            }
        }

        return collect($data)->sortByDesc('total')->values();
    }

    /**
     * Get stock loss breakdown by branch
     */
    private function getStockLossByBranch($startDate, $endDate)
    {
        $branches = Branch::all();
        $data = [];

        foreach ($branches as $branch) {
            // Unsold stock loss
            $unsoldLoss = BranchStock::with('product')
                ->where('branch_id', $branch->id)
                ->where('stock_quantity', '>', 0)
                ->whereNotNull('received_date')
                ->where('received_date', '<', Carbon::today())
                ->get()
                ->sum(function($stock) {
                    return $stock->stock_quantity * ($stock->cost_at_purchase ?? $stock->product->cost_price ?? 0);
                });
            
            // Rejected sales loss
            $rejectedLoss = DailySalesItem::with('product')
                ->whereHas('dailySale', function($q) use ($startDate, $endDate, $branch) {
                    $q->whereBetween('sale_date', [$startDate, $endDate])
                      ->where('status', 'rejected')
                      ->where('branch_id', $branch->id);
                })
                ->get()
                ->sum(function($item) {
                    return $item->quantity * ($item->product->cost_price ?? 0);
                });
            
            $totalLoss = $unsoldLoss + $rejectedLoss;
            
            if ($totalLoss > 0) {
                $data[] = [
                    'branch' => $branch->name,
                    'unsold' => $unsoldLoss,
                    'rejected' => $rejectedLoss,
                    'total' => $totalLoss
                ];
            }
        }

        return collect($data)->sortByDesc('total')->values();
    }

    /**
     * Get profit breakdown by category
     */
    private function getCategoryProfitBreakdown($startDate, $endDate, $branchFilter = null)
    {
        $categories = \App\Models\Category::all();
        $data = [];

        foreach ($categories as $category) {
            $query = DailySalesItem::with('product')
                ->whereHas('product', function($q) use ($category) {
                    $q->where('category_id', $category->id);
                })
                ->whereHas('dailySale', function($q) use ($startDate, $endDate, $branchFilter) {
                    $q->whereBetween('sale_date', [$startDate, $endDate])
                      ->where('status', 'completed');
                      
                    if ($branchFilter) {
                        $q->where('branch_id', $branchFilter);
                    }
                });

            $items = $query->get();
            $revenue = $items->sum('total');
            $cost = $items->sum(function($item) {
                return $item->quantity * ($item->product->cost_price ?? 0);
            });
            
            if ($revenue > 0 || $cost > 0) {
                $data[] = [
                    'category' => $category->name,
                    'revenue' => $revenue,
                    'cost' => $cost,
                    'profit' => $revenue - $cost,
                    'margin' => $revenue > 0 ? (($revenue - $cost) / $revenue) * 100 : 0
                ];
            }
        }

        return collect($data)->sortByDesc('profit')->values();
    }

    /**
     * Export Profit & Loss report
     */
    public function exportProfitLoss(Request $request)
    {
        $dateRange = $request->input('date_range', 'month');
        
        // Determine date boundaries
        switch ($dateRange) {
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'quarter':
                $startDate = Carbon::now()->startOfQuarter();
                $endDate = Carbon::now()->endOfQuarter();
                break;
            case 'year':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
                break;
            default:
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
        }

        $products = Product::all();
        
        $filename = 'profit-loss-report-' . Carbon::now()->format('Y-m-d') . '.csv';
        
        $csvContent = "\xEF\xBB\xBF"; // UTF-8 BOM
        $csvContent .= "Product,Category,Quantity Sold,Revenue (RM),Cost (RM),Profit (RM),Margin (%)\n";
        
        foreach ($products as $product) {
            $items = DailySalesItem::where('product_id', $product->id)
                ->whereHas('dailySale', function($q) use ($startDate, $endDate) {
                    $q->whereBetween('sale_date', [$startDate, $endDate])
                      ->where('status', 'completed');
                })
                ->get();

            $quantitySold = $items->sum('quantity');
            $revenue = $items->sum('total');
            $cost = $quantitySold * $product->cost_price;
            $profit = $revenue - $cost;
            $margin = $revenue > 0 ? (($revenue - $cost) / $revenue) * 100 : 0;

            if ($quantitySold > 0) {
                $csvContent .= implode(',', [
                    '"' . str_replace('"', '""', $product->name) . '"',
                    '"' . str_replace('"', '""', $product->category->name ?? 'N/A') . '"',
                    $quantitySold,
                    number_format($revenue, 2),
                    number_format($cost, 2),
                    number_format($profit, 2),
                    number_format($margin, 1) . '%'
                ]) . "\n";
            }
        }

        return response($csvContent)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Show settings page
     */
    public function settings()
    {
        $user = auth()->user();
        return view('hq-admin.settings', compact('user'));
    }

    /**
     * Update profile information
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return redirect()->route('hq-admin.settings')->with('status', 'profile-updated');
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|min:8|confirmed',
        ]);

        auth()->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('hq-admin.settings')->with('status', 'password-updated');
    }

    // ========== NOTIFICATION METHODS ==========

    /**
     * Show notifications center
     */
    public function notifications()
    {
        $currentMonth = Carbon::now();
        $branches = Branch::all();
        $activeBranches = Branch::where('is_active', true)->count();

        // Get broadcasts sent by HQ (system_announcement sent by current user)
        // Show master records OR broadcasts sent by this HQ admin (for backwards compatibility)
        // Eager load sender and branch to prevent N+1 queries
        $broadcasts = Notification::where('type', 'system_announcement')
            ->where('sender_id', auth()->id())
            ->where(function($query) {
                $query->whereJsonContains('data->is_master_record', true)
                      ->orWhereJsonContains('data->is_hq_broadcast', true);
            })
            ->with(['sender:id,name', 'branch:id,name'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique(function ($item) {
                // Group by title + message + approximate time to avoid duplicates
                return $item->title . '|' . $item->message . '|' . $item->created_at->format('Y-m-d H:i');
            })
            ->values();
        
        // Paginate manually after deduplication
        $page = request()->get('page', 1);
        $perPage = 10;
        $broadcastsPaginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $broadcasts->forPage($page, $perPage),
            $broadcasts->count(),
            $perPage,
            $page,
            ['path' => request()->url()]
        );

        // Stats
        $totalBroadcasts = $broadcasts->count();
        $thisMonthBroadcasts = $broadcasts->filter(function($item) use ($currentMonth) {
            return $item->created_at->month === $currentMonth->month 
                && $item->created_at->year === $currentMonth->year;
        })->count();

        // Generate system alerts
        $systemAlerts = $this->generateSystemAlerts($currentMonth);
        
        // Rename for view compatibility
        $broadcasts = $broadcastsPaginated;

        return view('hq-admin.notifications', compact(
            'branches',
            'activeBranches',
            'broadcasts',
            'systemAlerts',
            'totalBroadcasts',
            'thisMonthBroadcasts'
        ));
    }

    /**
     * Generate system alerts based on branch performance
     */
    private function generateSystemAlerts($currentMonth)
    {
        $alerts = collect();
        $benchmark = Benchmark::where('is_active', true)->first();
        $monthlyTarget = $benchmark->monthly_sales_target ?? 50000;

        // Check each branch's performance
        $branches = Branch::all();
        foreach ($branches as $branch) {
            $sales = DailySale::where('branch_id', $branch->id)
                ->whereMonth('sale_date', $currentMonth->month)
                ->whereYear('sale_date', $currentMonth->year)
                ->where('status', 'completed')
                ->sum('total_amount');

            $progress = $monthlyTarget > 0 ? ($sales / $monthlyTarget) * 100 : 0;
            $daysInMonth = $currentMonth->daysInMonth;
            $dayOfMonth = $currentMonth->day;
            $expectedProgress = ($dayOfMonth / $daysInMonth) * 100;

            // Alert if branch is significantly behind
            if ($progress < ($expectedProgress - 20) && $dayOfMonth >= 7) {
                $alerts->push([
                    'type' => 'warning',
                    'icon' => 'graph-down-arrow',
                    'title' => $branch->name . ' - Below Target',
                    'message' => "Currently at " . number_format($progress, 1) . "% of monthly target. Expected: " . number_format($expectedProgress, 1) . "%",
                    'time' => 'Updated just now',
                    'action_url' => route('hq-admin.analytics.branch', $branch->id)
                ]);
            }

            // Check for no sales today
            $todaySales = DailySale::where('branch_id', $branch->id)
                ->whereDate('sale_date', Carbon::today())
                ->where('status', 'completed')
                ->count();

            if ($todaySales === 0 && Carbon::now()->hour >= 12) {
                $alerts->push([
                    'type' => 'info',
                    'icon' => 'exclamation-circle',
                    'title' => $branch->name . ' - No Sales Today',
                    'message' => 'No completed sales recorded for today yet.',
                    'time' => Carbon::now()->format('g:i A'),
                    'action_url' => route('hq-admin.analytics.branch', $branch->id)
                ]);
            }
        }

        // Check for low-performing staff
        $staffBenchmark = $benchmark->staff_sales_target ?? 10000;
        $lowPerformingStaff = User::where('role', 'staff')
            ->with('branch')
            ->get()
            ->filter(function($staff) use ($currentMonth, $staffBenchmark) {
                $sales = DailySale::where('staff_id', $staff->id)
                    ->whereMonth('sale_date', $currentMonth->month)
                    ->whereYear('sale_date', $currentMonth->year)
                    ->where('status', 'completed')
                    ->sum('total_amount');
                
                $progress = $staffBenchmark > 0 ? ($sales / $staffBenchmark) * 100 : 0;
                return $progress < 30 && $currentMonth->day >= 15; // Alert after mid-month
            });

        if ($lowPerformingStaff->count() > 0) {
            $alerts->push([
                'type' => 'warning',
                'icon' => 'people-fill',
                'title' => $lowPerformingStaff->count() . ' Staff Below 30% Target',
                'message' => 'Some staff members are significantly behind their monthly sales target.',
                'time' => 'Mid-month review',
                'action_url' => route('hq-admin.kpi-benchmark')
            ]);
        }

        return $alerts->take(10); // Limit to 10 alerts
    }

    /**
     * Send broadcast notification
     */
    public function sendBroadcast(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'required|in:medium,high,urgent',
            'target' => 'required|in:all,managers,staff,branch',
            'branch_id' => 'required_if:target,branch|nullable|exists:branches,id',
            'action_url' => 'nullable|url'
        ]);

        $sender = auth()->user();
        $recipients = collect();

        // Determine recipients based on target
        switch ($request->target) {
            case 'all':
                $recipients = User::whereNotNull('branch_id')->get();
                break;
            case 'managers':
                $recipients = User::where('role', 'branch_manager')->get();
                break;
            case 'staff':
                $recipients = User::where('role', 'staff')->get();
                break;
            case 'branch':
                $recipients = User::where('branch_id', $request->branch_id)->get();
                break;
        }

        // Get first branch for master record (use first recipient's branch or first branch in system)
        $firstBranch = Branch::first();

        // Create notification for each recipient
        foreach ($recipients as $recipient) {
            Notification::create([
                'branch_id' => $recipient->branch_id,
                'user_id' => $recipient->id,
                'sender_id' => $sender->id,
                'type' => 'system_announcement',
                'title' => $request->title,
                'message' => $request->message,
                'priority' => $request->priority,
                'action_url' => $request->action_url,
                'data' => [
                    'target' => $request->target,
                    'target_branch_id' => $request->branch_id,
                    'is_hq_broadcast' => true
                ]
            ]);
        }

        // Also create a master record for HQ's broadcast history
        // Use first branch's ID since branch_id is required
        Notification::create([
            'branch_id' => $firstBranch->id,
            'user_id' => $sender->id,
            'sender_id' => $sender->id,
            'type' => 'system_announcement',
            'title' => $request->title,
            'message' => $request->message,
            'priority' => $request->priority,
            'action_url' => $request->action_url,
            'data' => [
                'target' => $request->target,
                'target_branch_id' => $request->branch_id,
                'recipients_count' => $recipients->count(),
                'is_master_record' => true,
                'is_hq_broadcast' => true
            ]
        ]);

        return redirect()->route('hq-admin.notifications')
            ->with('success', 'Broadcast sent successfully to ' . $recipients->count() . ' recipients!');
    }

    /**
     * Get broadcast details
     */
    public function getBroadcast($id)
    {
        $broadcast = Notification::with(['sender', 'branch'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'broadcast' => $broadcast
        ]);
    }

    /**
     * Delete broadcast
     */
    public function deleteBroadcast($id)
    {
        $broadcast = Notification::findOrFail($id);
        
        // Only allow deletion of broadcasts sent by current user
        if ($broadcast->sender_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete your own broadcasts'
            ], 403);
        }

        // Delete all related notifications (both master record and recipient copies)
        // Match by sender, type, title, message, and approximate creation time (within 1 minute)
        $deletedCount = Notification::where('sender_id', $broadcast->sender_id)
            ->where('type', 'system_announcement')
            ->where('title', $broadcast->title)
            ->where('message', $broadcast->message)
            ->whereBetween('created_at', [
                $broadcast->created_at->subMinute(),
                $broadcast->created_at->addMinute()
            ])
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Broadcast deleted successfully for all recipients',
            'deleted_count' => $deletedCount
        ]);
    }
}