<?php

namespace App\Providers;

use App\Models\Notification;
use App\Models\Product;
use App\Models\BranchStock;
use App\Models\DailySale;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share notification count with staff layout
        View::composer('layouts.staff', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();
                $branchId = $user->branch_id;
                
                // Count regular unread notifications
                $notificationCount = Notification::where('user_id', $user->id)
                    ->where('is_read', false)
                    ->count();
                
                // Get low stock products for THIS branch only
                $lowStockProducts = BranchStock::with(['product.category'])
                    ->where('branch_id', $branchId)
                    ->where('stock_quantity', '<', 10)
                    ->where('is_available', true)
                    ->orderBy('stock_quantity', 'asc')
                    ->limit(5)
                    ->get()
                    ->map(function ($stock) {
                        $product = $stock->product;
                        $product->stock_quantity = $stock->stock_quantity;
                        $product->is_available = $stock->is_available;
                        return $product;
                    });
                
                $lowStockCount = BranchStock::where('branch_id', $branchId)
                    ->where('stock_quantity', '<', 10)
                    ->where('is_available', true)
                    ->count();
                
                $unreadNotifications = $notificationCount + $lowStockCount;
                
                $view->with('unreadNotifications', $unreadNotifications);
                $view->with('lowStockProducts', $lowStockProducts);
                $view->with('lowStockCount', $lowStockCount);
            }
        });

        // Share notification count with branch-manager layout
        View::composer('layouts.branch-manager', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();
                $branchId = $user->branch_id;
                
                // Count regular unread notifications
                $notificationCount = Notification::where('user_id', $user->id)
                    ->where('is_read', false)
                    ->count();
                
                // Get low stock products for THIS branch only
                $lowStockProducts = BranchStock::with(['product.category'])
                    ->where('branch_id', $branchId)
                    ->where('stock_quantity', '<', 10)
                    ->where('is_available', true)
                    ->orderBy('stock_quantity', 'asc')
                    ->limit(5)
                    ->get()
                    ->map(function ($stock) {
                        $product = $stock->product;
                        $product->stock_quantity = $stock->stock_quantity;
                        $product->is_available = $stock->is_available;
                        return $product;
                    });
                
                $lowStockCount = BranchStock::where('branch_id', $branchId)
                    ->where('stock_quantity', '<', 10)
                    ->where('is_available', true)
                    ->count();

                // Get pending reports count
                $pendingReportsCount = DailySale::where('branch_id', $branchId)
                    ->whereNull('verified_by')
                    ->count();
                
                // Total alerts count
                $alertsCount = $notificationCount + $lowStockCount + $pendingReportsCount;
                
                $view->with('alertsCount', $alertsCount);
                $view->with('lowStockProducts', $lowStockProducts);
                $view->with('lowStockCount', $lowStockCount);
                $view->with('pendingReportsCount', $pendingReportsCount);
            }
        });
    }
}