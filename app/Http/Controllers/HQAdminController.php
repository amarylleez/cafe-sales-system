<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use App\Models\DailySale;
use App\Models\KPI;
use App\Models\Benchmark;
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
    public function analytics()
    {
        $currentMonth = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();

        // Top 3 performing branches
        $topBranches = $this->getTopBranches($currentMonth);

        // Branch comparison data (last 6 months)
        $comparisonData = $this->getBranchComparisonData();

        // Detailed branch analysis
        $branchAnalysis = $this->getDetailedBranchAnalysis($currentMonth, $lastMonth);

        return view('hq-admin.analytics', compact(
            'topBranches',
            'comparisonData',
            'branchAnalysis'
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
            'branch_id' => 'required_unless:role,hq_admin|exists:branches,id'
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
        $transactionBenchmark = $benchmarks->transaction_target ?? 100;
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
            'transactionBenchmark',
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
            'transaction_target' => 0, // No longer used
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
     * Export reports as CSV
     */
    public function exportCSV(Request $request)
    {
        $query = DailySale::with(['branch', 'staff']);

        // Apply same filters as reports page
        $this->applyReportFilters($query, $request);

        $reports = $query->orderBy('sale_date', 'desc')->get();
        
        // If no reports found with filter, get all reports
        if ($reports->isEmpty()) {
            $reports = DailySale::with(['branch', 'staff'])->orderBy('sale_date', 'desc')->get();
        }

        $filename = 'sales-reports-' . Carbon::now()->format('Y-m-d') . '.csv';
        
        // Build CSV content with BOM for Excel compatibility
        $csvContent = "\xEF\xBB\xBF"; // UTF-8 BOM
        $csvContent .= "Report ID,Date,Branch,Staff,Total Amount,Transactions,Status\n";
        
        foreach ($reports as $report) {
            $branchName = $report->branch ? $report->branch->name : 'N/A';
            $staffName = $report->staff ? $report->staff->name : 'N/A';
            
            $csvContent .= implode(',', [
                $report->id,
                '"' . $report->sale_date->format('d-M-Y') . '"',
                '"' . str_replace('"', '""', $branchName) . '"',
                '"' . str_replace('"', '""', $staffName) . '"',
                $report->total_amount,
                $report->items_count ?? 0,
                $report->status
            ]) . "\n";
        }

        return response($csvContent)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Export reports as PDF
     */
    public function exportPDF(Request $request)
    {
        $query = DailySale::with(['branch', 'staff']);
        $this->applyReportFilters($query, $request);
        $reports = $query->get();

        $pdf = PDF::loadView('hq-admin.pdf.reports', compact('reports'));
        
        return $pdf->download('sales-reports-' . Carbon::now()->format('Y-m-d') . '.pdf');
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
}