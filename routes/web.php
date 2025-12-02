<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HQAdminController;
use App\Http\Controllers\BranchManagerController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SettingsController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    
    if ($user->role === 'hq_admin') {
        return redirect()->route('hq-admin.dashboard');
    } elseif ($user->role === 'branch_manager') {
        return redirect()->route('branch-manager.dashboard');
    } elseif ($user->role === 'staff') {
        return redirect()->route('staff.dashboard');
    }
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// HQ Admin Routes
Route::middleware(['auth'])->prefix('hq-admin')->name('hq-admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [HQAdminController::class, 'dashboard'])->name('dashboard');
    
    // Analytics
    Route::get('/analytics', [HQAdminController::class, 'analytics'])->name('analytics');
    Route::get('/analytics/branch/{id}', [HQAdminController::class, 'branchAnalytics'])->name('analytics.branch');
    
    // Manage Staff
    Route::get('/manage', [HQAdminController::class, 'manage'])->name('manage');
    Route::post('/manage', [HQAdminController::class, 'storeStaff'])->name('manage.store');
    Route::get('/manage/{id}', [HQAdminController::class, 'getStaff'])->name('manage.get');
    Route::patch('/manage/{id}', [HQAdminController::class, 'updateStaff'])->name('manage.update');
    Route::delete('/manage/{id}', [HQAdminController::class, 'deleteStaff'])->name('manage.delete');
    
    // KPI & Benchmark
    Route::get('/kpi-benchmark', [HQAdminController::class, 'kpiBenchmark'])->name('kpi-benchmark');
    Route::post('/kpi-benchmark', [HQAdminController::class, 'storeBenchmark'])->name('kpi-benchmark.store');
    
    // Reports
    Route::get('/reports', [HQAdminController::class, 'reports'])->name('reports');
    Route::get('/reports/export/csv', [HQAdminController::class, 'exportCSV'])->name('reports.export.csv');
    Route::get('/reports/export/pdf', [HQAdminController::class, 'exportPDF'])->name('reports.export.pdf');
    Route::get('/reports/{id}', [HQAdminController::class, 'getReport'])->name('reports.get');
    Route::get('/reports/{id}/pdf', [HQAdminController::class, 'downloadReportPDF'])->name('reports.pdf');
    
    // Settings
    Route::get('/settings', [HQAdminController::class, 'settings'])->name('settings');
    Route::patch('/settings/profile', [HQAdminController::class, 'updateProfile'])->name('settings.profile');
    Route::put('/settings/password', [HQAdminController::class, 'updatePassword'])->name('settings.password');
});
   
// Branch Manager Routes
Route::middleware(['auth'])->prefix('branch-manager')->name('branch-manager.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [BranchManagerController::class, 'dashboard'])->name('dashboard');
    
    // Sales Report
    Route::get('/sales-report', [BranchManagerController::class, 'salesReport'])->name('sales-report');
    Route::get('/sales-report/export', [BranchManagerController::class, 'exportSalesReport'])->name('sales-report.export');
    Route::post('/sales-report/finalize', [BranchManagerController::class, 'finalizeAndSubmit'])->name('sales-report.finalize');
    Route::get('/sales-report/{id}', [BranchManagerController::class, 'getReportDetails'])->name('sales-report.details');
    Route::post('/sales-report/{id}/verify', [BranchManagerController::class, 'verifyReport'])->name('sales-report.verify');
    Route::post('/sales-report/{id}/edit', [BranchManagerController::class, 'updateReport'])->name('sales-report.edit');
    
    // KPI & Benchmark
    Route::get('/kpi-benchmark', [BranchManagerController::class, 'kpiBenchmark'])->name('kpi-benchmark');
    
    // Team Overview
    Route::get('/team-overview', [BranchManagerController::class, 'teamOverview'])->name('team-overview');
    Route::get('/staff/{staffId}/performance', [BranchManagerController::class, 'getStaffPerformance'])->name('staff.performance');
    Route::get('/staff/{staffId}/schedule', [BranchManagerController::class, 'getStaffSchedule'])->name('staff.schedule');
    
    // Staff Schedule
    Route::get('/staff-schedule', [BranchManagerController::class, 'staffSchedule'])->name('staff-schedule');
    Route::post('/staff-schedule', [BranchManagerController::class, 'storeSchedule'])->name('staff-schedule.store');
    Route::patch('/staff-schedule/{id}/status', [BranchManagerController::class, 'updateScheduleStatus'])->name('staff-schedule.status');
    Route::delete('/staff-schedule/{id}', [BranchManagerController::class, 'deleteSchedule'])->name('staff-schedule.delete');
    
    // Inventory
    Route::get('/inventory', [BranchManagerController::class, 'inventory'])->name('inventory');
    Route::post('/inventory/{id}/availability', [BranchManagerController::class, 'updateProductAvailability'])->name('inventory.availability');
    
    // Stock Management (View Only)
    Route::get('/stock', [BranchManagerController::class, 'stock'])->name('stock');

    // Alerts/Notifications
    Route::get('/alerts', [BranchManagerController::class, 'alerts'])->name('alerts');
    Route::post('/notifications/{id}/mark-read', [BranchManagerController::class, 'markNotificationAsRead'])->name('notifications.mark-read');
});

// Staff Routes 
Route::middleware(['auth'])->prefix('staff')->name('staff.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [StaffController::class, 'index'])->name('dashboard');
    
    // Sales Routes
    Route::get('/sales/create', [StaffController::class, 'createSales'])->name('sales.create');
    Route::post('/sales/store', [StaffController::class, 'storeSales'])->name('sales.store');
    
    // KPI Routes
    Route::get('/kpi', [StaffController::class, 'kpi'])->name('kpi');
    Route::post('/kpi/{kpiId}/toggle-completion', [StaffController::class, 'toggleKPICompletion'])->name('kpi.toggle');
    
    // My Schedule Routes
    Route::get('/my-schedule', [StaffController::class, 'mySchedule'])->name('my-schedule');
    Route::post('/schedule/{id}/confirm', [StaffController::class, 'confirmSchedule'])->name('schedule.confirm');
    
    // Dashboard Sub-pages
    Route::get('/dashboard/target', [StaffController::class, 'targetOverview'])->name('dashboard.target');
    Route::get('/dashboard/progress', [StaffController::class, 'progressBar'])->name('dashboard.progress');
    
    // Alerts/Notifications Routes
    Route::get('/alerts', [StaffController::class, 'alerts'])->name('alerts');
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    
    // Inventory Routes
    Route::get('/inventory', [StaffController::class, 'inventory'])->name('inventory');
    Route::post('/inventory/{id}/availability', [StaffController::class, 'updateProductAvailability'])->name('inventory.availability');
    Route::get('/inventory/{id}', [InventoryController::class, 'show'])->name('inventory.show');
    Route::post('/inventory/{id}/mark-sold', [InventoryController::class, 'markAsSold'])->name('inventory.mark-sold');
    
    // Stock Management
    Route::get('/stock', [StaffController::class, 'stock'])->name('stock');
    Route::post('/stock/{id}/add', [StaffController::class, 'addStock'])->name('stock.add');
    Route::post('/stock/{id}/adjust', [StaffController::class, 'adjustStock'])->name('stock.adjust');
});


require __DIR__.'/auth.php';