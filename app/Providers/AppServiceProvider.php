<?php

namespace App\Providers;

use App\Models\Notification;
use App\Models\Product;
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
                
                // Count regular unread notifications
                $notificationCount = Notification::where('user_id', $user->id)
                    ->where('is_read', false)
                    ->count();
                
                // Get low stock products (stock < 10) - products are global
                $lowStockProducts = Product::with('category')
                    ->where('stock_quantity', '<', 10)
                    ->where('is_available', true)
                    ->orderBy('stock_quantity', 'asc')
                    ->limit(5)
                    ->get();
                
                $lowStockCount = Product::where('stock_quantity', '<', 10)
                    ->where('is_available', true)
                    ->count();
                
                $unreadNotifications = $notificationCount + $lowStockCount;
                
                $view->with('unreadNotifications', $unreadNotifications);
                $view->with('lowStockProducts', $lowStockProducts);
                $view->with('lowStockCount', $lowStockCount);
            }
        });
    }
}
