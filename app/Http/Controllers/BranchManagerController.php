<?php

namespace App\Http\Controllers;

use App\Models\DailySale;
use App\Models\DailySalesItem;
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
            ->whereNull('verified_by')
            ->where('status', '!=', 'rejected')
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
     * Get daily profit trend for the branch
     */
    private function getDailyProfitTrend($branchId, $startDate, $endDate)
    {
        $labels = [];
        $revenueData = [];
        $profitData = [];

        // Limit to 30 days
        $days = min($startDate->diffInDays($endDate), 30);
        
        for ($i = $days; $i >= 0; $i--) {
            $date = $endDate->copy()->subDays($i);
            $labels[] = $date->format('M d');
            
            $daySales = DailySalesItem::with('product')
                ->whereHas('dailySale', function($q) use ($date, $branchId) {
                    $q->whereDate('sale_date', $date)
                      ->where('status', 'completed')
                      ->where('branch_id', $branchId);
                })
                ->get();
            
            $revenue = $daySales->sum('total');
            $cost = $daySales->sum(function($item) {
                return $item->quantity * ($item->product->cost_price ?? 0);
            });
            
            $revenueData[] = $revenue;
            $profitData[] = $revenue - $cost;
        }

        return [
            'labels' => $labels,
            'revenue' => $revenueData,
            'profit' => $profitData
        ];
    }

    /**
     * Get category profit breakdown
     */
    private function getCategoryProfitData($branchId, $startDate, $endDate)
    {
        $categoryData = DailySalesItem::with(['product.category'])
            ->whereHas('dailySale', function($q) use ($startDate, $endDate, $branchId) {
                $q->whereBetween('sale_date', [$startDate, $endDate])
                  ->where('status', 'completed')
                  ->where('branch_id', $branchId);
            })
            ->get()
            ->groupBy('product.category.name');

        $result = [];
        foreach ($categoryData as $categoryName => $items) {
            $revenue = $items->sum('total');
            $cost = $items->sum(function($item) {
                return $item->quantity * ($item->product->cost_price ?? 0);
            });
            
            $result[] = [
                'name' => $categoryName ?: 'Uncategorized',
                'revenue' => $revenue,
                'cost' => $cost,
                'profit' => $revenue - $cost,
                'margin' => $revenue > 0 ? (($revenue - $cost) / $revenue) * 100 : 0
            ];
        }

        return collect($result)->sortByDesc('profit')->values();
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
                $query->whereNotNull('verified_by')->where('status', '!=', 'rejected');
            } elseif ($status === 'pending') {
                $query->whereNull('verified_by')->where('status', '!=', 'rejected');
            } elseif ($status === 'rejected') {
                $query->where('status', 'rejected');
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
     * Export sales report to PDF
     */
    public function exportSalesReport(Request $request)
    {
        $user = auth()->user();
        $branchId = $user->branch_id;
        $branch = $user->branch;

        // Build query with filters
        $query = DailySale::where('branch_id', $branchId)
            ->with(['staff', 'items.product']);
        
        // Apply date range filter
        $dateRange = $request->input('date_range', '');
        $dateRangeLabel = 'All Time';
        
        if ($dateRange && $dateRange !== '') {
            switch ($dateRange) {
                case 'today':
                    $query->whereDate('sale_date', Carbon::today());
                    $dateRangeLabel = 'Today (' . Carbon::today()->format('d M Y') . ')';
                    break;
                case 'week':
                    $query->whereBetween('sale_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    $dateRangeLabel = 'This Week (' . Carbon::now()->startOfWeek()->format('d M') . ' - ' . Carbon::now()->endOfWeek()->format('d M Y') . ')';
                    break;
                case 'month':
                    $query->whereMonth('sale_date', Carbon::now()->month)
                          ->whereYear('sale_date', Carbon::now()->year);
                    $dateRangeLabel = 'This Month (' . Carbon::now()->format('F Y') . ')';
                    break;
                case 'custom':
                    $startDate = $request->input('start_date');
                    $endDate = $request->input('end_date');
                    if ($startDate && $endDate) {
                        $query->whereBetween('sale_date', [$startDate, $endDate]);
                        $dateRangeLabel = Carbon::parse($startDate)->format('d M Y') . ' - ' . Carbon::parse($endDate)->format('d M Y');
                    }
                    break;
            }
        }
        
        // Apply status filter
        $status = $request->input('status', '');
        if ($status && $status !== '') {
            $query->where('status', $status);
        }
        
        $sales = $query->orderBy('sale_date', 'desc')->get();

        // Calculate summary
        $summary = [
            'totalSales' => $sales->sum('total_amount'),
            'totalTransactions' => $sales->count(),
            'completedCount' => $sales->where('status', 'completed')->count(),
            'pendingCount' => $sales->where('status', 'pending')->count(),
            'rejectedCount' => $sales->where('status', 'rejected')->count(),
            'dateRangeLabel' => $dateRangeLabel,
        ];

        $pdf = \PDF::loadView('branch-manager.pdf.sales-report', compact('sales', 'branch', 'summary'));
        
        return $pdf->download('sales_report_' . $branch->name . '_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Show Performance page (combines KPI Benchmark + Profit/Loss)
     */
    public function performance(Request $request)
    {
        $user = auth()->user();
        $branchId = $user->branch_id;
        $branch = $user->branch;
        $currentMonth = Carbon::now();

        // Date range handling for profit/loss
        $dateRange = $request->get('range', 'this_month');
        
        switch ($dateRange) {
            case 'today':
                $startDate = Carbon::today();
                $endDate = Carbon::today();
                break;
            case 'this_week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                break;
            case 'last_week':
                $startDate = Carbon::now()->subWeek()->startOfWeek();
                $endDate = Carbon::now()->subWeek()->endOfWeek();
                break;
            case 'this_month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'last_month':
                $startDate = Carbon::now()->subMonth()->startOfMonth();
                $endDate = Carbon::now()->subMonth()->endOfMonth();
                break;
            default:
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
        }

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

        // === PROFIT/LOSS CALCULATIONS ===
        
        // Get all sales items for profit calculation
        $salesItems = DailySalesItem::with(['product', 'dailySale'])
            ->whereHas('dailySale', function($q) use ($startDate, $endDate, $branchId) {
                $q->whereBetween('sale_date', [$startDate, $endDate])
                  ->where('status', 'completed')
                  ->where('branch_id', $branchId);
            })
            ->get();

        // Calculate totals
        $totalRevenue = $salesItems->sum('total');
        $totalCost = $salesItems->sum(function($item) {
            return $item->quantity * ($item->product->cost_price ?? 0);
        });

        // Product-wise profit breakdown
        $productProfitData = [];
        foreach ($salesItems as $item) {
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
            $revenue = $item->total;
            $cost = $item->quantity * ($item->product->cost_price ?? 0);
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

        // Calculate stock loss from unsold items
        $unsoldStock = BranchStock::with('product')
            ->where('branch_id', $branchId)
            ->where('stock_quantity', '>', 0)
            ->whereNotNull('received_date')
            ->where('received_date', '<', Carbon::today())
            ->get();
            
        $unsoldStockLoss = $unsoldStock->sum(function($stock) {
            return $stock->stock_quantity * ($stock->cost_at_purchase ?? $stock->product->cost_price ?? 0);
        });

        // Calculate loss from rejected transactions
        $rejectedItems = DailySalesItem::with(['product', 'dailySale'])
            ->whereHas('dailySale', function($q) use ($startDate, $endDate, $branchId) {
                $q->whereBetween('sale_date', [$startDate, $endDate])
                  ->where('status', 'rejected')
                  ->where('branch_id', $branchId);
            })
            ->get();
        
        $rejectedSalesLoss = $rejectedItems->sum(function($item) {
            return $item->quantity * ($item->product->cost_price ?? 0);
        });

        // Total stock loss
        $stockLoss = $unsoldStockLoss + $rejectedSalesLoss;

        // Get potential loss stock for warning display
        $potentialLossStock = BranchStock::with(['product'])
            ->where('branch_id', $branchId)
            ->where('stock_quantity', '>', 0)
            ->whereNotNull('received_date')
            ->where('received_date', '<', Carbon::today())
            ->orderBy('received_date', 'asc')
            ->get();

        // Get rejected sales for display
        $rejectedSales = DailySale::with(['items.product', 'staff'])
            ->where('branch_id', $branchId)
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->where('status', 'rejected')
            ->orderBy('sale_date', 'desc')
            ->get();

        // Net profit (after stock loss)
        $netProfit = $grossProfit - $stockLoss;

        // Daily profit trend
        $dailyProfitTrend = $this->getDailyProfitTrend($branchId, $startDate, $endDate);

        // Detailed daily profit/loss breakdown
        $dailyBreakdown = $this->getDailyProfitBreakdown($branchId, $startDate, $endDate);
        
        // Transform to expected structure for the view
        $dailyProfitLossBreakdown = [
            'days' => $dailyBreakdown['days'],
            'monthly_totals' => [
                'totalRevenue' => $dailyBreakdown['totals']['revenue'],
                'totalCost' => $dailyBreakdown['totals']['cost'],
                'totalGrossProfit' => $dailyBreakdown['totals']['gross_profit'],
                'totalRejectedLoss' => $dailyBreakdown['totals']['rejected_loss'],
                'totalNetProfit' => $dailyBreakdown['totals']['net_profit'],
            ]
        ];

        return view('branch-manager.performance', compact(
            // Benchmark data
            'kpis', 
            'staffKpis', 
            'monthlySalesData', 
            'benchmark',
            'branch',
            'branchMonthlySales',
            'branchTransactionCount',
            // Profit/Loss data
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
            'dailyProfitTrend',
            'dailyBreakdown',
            'dailyProfitLossBreakdown',
            'startDate',
            'endDate',
            'dateRange'
        ));
    }

    /**
     * Get detailed daily profit/loss breakdown
     */
    private function getDailyProfitBreakdown($branchId, $startDate, $endDate)
    {
        $breakdown = [];
        $totalRevenue = 0;
        $totalCost = 0;
        $totalProfit = 0;
        $totalRejectedLoss = 0;

        // Iterate through each day in the range
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $date = $currentDate->copy();
            
            // Get completed sales for this day
            $daySales = DailySalesItem::with('product')
                ->whereHas('dailySale', function($q) use ($date, $branchId) {
                    $q->whereDate('sale_date', $date)
                      ->where('status', 'completed')
                      ->where('branch_id', $branchId);
                })
                ->get();
            
            $dayRevenue = $daySales->sum('total');
            $dayCost = $daySales->sum(function($item) {
                return $item->quantity * ($item->product->cost_price ?? 0);
            });
            $dayProfit = $dayRevenue - $dayCost;
            
            // Get rejected sales loss for this day
            $rejectedItems = DailySalesItem::with('product')
                ->whereHas('dailySale', function($q) use ($date, $branchId) {
                    $q->whereDate('sale_date', $date)
                      ->where('status', 'rejected')
                      ->where('branch_id', $branchId);
                })
                ->get();
            
            $dayRejectedLoss = $rejectedItems->sum(function($item) {
                return $item->quantity * ($item->product->cost_price ?? 0);
            });
            
            // Get transaction count
            $transactionCount = DailySale::where('branch_id', $branchId)
                ->whereDate('sale_date', $date)
                ->where('status', 'completed')
                ->count();
            
            $dayNetProfit = $dayProfit - $dayRejectedLoss;
            
            // Only add days with activity (completed sales or rejected transactions)
            if ($dayRevenue > 0 || $dayRejectedLoss > 0) {
                // Use date string as key for the view to iterate properly
                $dateKey = $date->format('Y-m-d');
                $breakdown[$dateKey] = [
                    'date' => $dateKey,
                    'dateFormatted' => $date->format('D, M d'),
                    'revenue' => $dayRevenue,
                    'cost' => $dayCost,
                    'grossProfit' => $dayProfit,
                    'rejectedLoss' => $dayRejectedLoss,
                    'netProfit' => $dayNetProfit,
                    'transactions' => $transactionCount,
                    'margin' => $dayRevenue > 0 ? ($dayProfit / $dayRevenue) * 100 : 0
                ];
            }
            
            $totalRevenue += $dayRevenue;
            $totalCost += $dayCost;
            $totalProfit += $dayProfit;
            $totalRejectedLoss += $dayRejectedLoss;
            
            $currentDate->addDay();
        }

        // Sort by date descending and keep as associative array
        krsort($breakdown);

        return [
            'days' => $breakdown,
            'totals' => [
                'revenue' => $totalRevenue,
                'cost' => $totalCost,
                'gross_profit' => $totalProfit,
                'rejected_loss' => $totalRejectedLoss,
                'net_profit' => $totalProfit - $totalRejectedLoss
            ]
        ];
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
    public function teamOverview(Request $request)
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
    public function inventory(Request $request)
    {
        $user = auth()->user();
        $branchId = $user->branch_id;
        
        // Build query with optional category filter and search
        $query = Product::with('category')->orderBy('name');
        
        $selectedCategory = $request->input('category');
        if ($selectedCategory && $selectedCategory !== 'all') {
            $query->where('category_id', $selectedCategory);
        }
        
        // Add search filter
        $search = $request->input('search');
        if ($search) {
            $query->where('name', 'ilike', '%' . $search . '%');
        }
        
        // Get paginated products
        $paginatedProducts = $query->paginate(20)->withQueryString();
        
        // Map branch-specific stock to each product
        $paginatedProducts->getCollection()->transform(function ($product) use ($branchId) {
            $branchStock = BranchStock::where('branch_id', $branchId)
                ->where('product_id', $product->id)
                ->first();
            
            $product->stock_quantity = $branchStock ? $branchStock->stock_quantity : 0;
            $product->is_available = $branchStock ? $branchStock->is_available : true;
            
            return $product;
        });

        $categories = Category::orderByRaw("CASE WHEN name = 'Lain-lain' THEN 1 ELSE 0 END")
            ->orderBy('name')
            ->get();

        return view('branch-manager.inventory', compact('paginatedProducts', 'categories', 'selectedCategory'));
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
     * Add a new product to the branch inventory
     */
    public function addProduct(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'category_id' => 'required|exists:categories,id',
                'price' => 'required|numeric|min:0',
                'cost_price' => 'nullable|numeric|min:0',
                'description' => 'nullable|string|max:1000',
                'initial_stock' => 'nullable|integer|min:0',
            ]);

            $user = auth()->user();
            $branchId = $user->branch_id;

            // Create the product
            $product = Product::create([
                'name' => $request->name,
                'category_id' => $request->category_id,
                'price' => $request->price,
                'cost_price' => $request->cost_price ?? ($request->price * 0.6), // Default 40% margin
                'description' => $request->description,
                'is_available' => true,
            ]);

            // Create branch stock entry
            $initialStock = $request->initial_stock ?? 0;
            BranchStock::create([
                'branch_id' => $branchId,
                'product_id' => $product->id,
                'stock_quantity' => $initialStock,
                'is_available' => true,
                'received_date' => $initialStock > 0 ? now() : null,
            ]);

            // Log the stock addition if there's initial stock
            if ($initialStock > 0) {
                \App\Models\StockLog::create([
                    'product_id' => $product->id,
                    'branch_id' => $branchId,
                    'user_id' => auth()->id(),
                    'quantity' => $initialStock,
                    'type' => 'add',
                    'notes' => 'Initial stock when adding new product',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Product added successfully',
                'product' => $product,
                'product_name' => $product->name,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all()),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add product: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove a product from the branch inventory
     */
    public function removeProduct($id)
    {
        try {
            $user = auth()->user();
            $branchId = $user->branch_id;

            $product = Product::findOrFail($id);

            // Check if product has any sales history
            $hasSales = \App\Models\DailySalesItem::where('product_id', $id)->exists();
            
            if ($hasSales) {
                // If product has sales history, just remove from branch stock (soft removal)
                BranchStock::where('branch_id', $branchId)
                    ->where('product_id', $id)
                    ->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Product removed from your branch inventory. Product data preserved for sales history.',
                ]);
            } else {
                // If no sales history, delete the product entirely
                // First delete branch stocks
                BranchStock::where('product_id', $id)->delete();
                
                // Delete stock logs
                \App\Models\StockLog::where('product_id', $id)->delete();
                
                // Delete the product
                $product->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Product completely removed from the system.',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove product: ' . $e->getMessage(),
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
     * Finalize and submit all pending reports to HQ - approves all pending transactions
     */
    public function finalizeAndSubmit()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        // Get all pending (unverified) sales for this branch
        $pendingSales = DailySale::where('branch_id', $branchId)
            ->whereNull('verified_by')
            ->get();

        $count = $pendingSales->count();

        if ($count === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No pending reports to finalize.'
            ]);
        }

        // Approve all pending sales
        foreach ($pendingSales as $sale) {
            $sale->verified_by = auth()->id();
            $sale->verified_at = now();
            $sale->status = 'completed';
            $sale->completed_at = now();
            $sale->save();
        }

        return response()->json([
            'success' => true,
            'message' => $count . ' report(s) finalized and submitted to HQ successfully.',
            'count' => $count
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
     * Reject report - rejects the transaction with a reason
     */
    public function rejectReport(Request $request, $id)
    {
        try {
            $request->validate([
                'reason' => 'required|string|max:500'
            ]);

            $report = DailySale::findOrFail($id);
            $report->status = 'rejected';
            $report->notes = ($report->notes ? $report->notes . "\n\n" : '') . 
                             "[REJECTED by " . auth()->user()->name . " on " . now()->format('d M Y H:i') . "]\n" .
                             "Reason: " . $request->reason;
            $report->save();

            return response()->json([
                'success' => true,
                'message' => 'Transaction rejected successfully'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject transaction: ' . $e->getMessage()
            ], 500);
        }
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
            if ($request->has('payment_details')) {
                $report->payment_details = $request->payment_details;
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

        // Get pending reports count
        $pendingReports = DailySale::where('branch_id', $branchId)
            ->whereNull('verified_by')
            ->where('status', '!=', 'rejected')
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