<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CashController;
use App\Http\Controllers\HrmController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\BomController;
use App\Http\Controllers\ProductionDashboardController;
use App\Http\Controllers\MarketingDashboardController;
use App\Http\Controllers\DigitalMarketingDashboardController;
use App\Http\Controllers\FinanceDashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\HrmDashboardController;
use App\Http\Controllers\TailorController; // Added TailorController import
use App\Http\Controllers\ProductionTransferController; // Added ProductionTransferController

use App\Http\Controllers\RedeemCodeController;

// Auth routes
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/chart-data', [\App\Http\Controllers\DashboardController::class, 'chartDataApi'])->name('dashboard.chart-data');

    // Redeem Codes
    Route::prefix('redeem-codes')->name('redeem-codes.')->group(function () {
        Route::get('/', [RedeemCodeController::class, 'index'])->name('index');
        Route::post('/', [RedeemCodeController::class, 'store'])->name('store');
        Route::put('/{redeemCode}', [RedeemCodeController::class, 'update'])->name('update');
        Route::delete('/{redeemCode}', [RedeemCodeController::class, 'destroy'])->name('destroy');
        Route::get('/{redeemCode}/barcode', [RedeemCodeController::class, 'barcode'])->name('barcode');
    });

    // Inventory (resource-style)
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::get('/create', [InventoryController::class, 'create'])->name('create');
        Route::post('/', [InventoryController::class, 'store'])->name('store');
        Route::get('/{product}/edit', [InventoryController::class, 'edit'])->name('edit');
        Route::put('/{product}', [InventoryController::class, 'update'])->name('update');
        Route::delete('/{product}', [InventoryController::class, 'destroy'])->name('destroy');
        Route::get('/{product}/barcode', [InventoryController::class, 'barcode'])->name('barcode');
        Route::post('/stock-in', [InventoryController::class, 'stockIn'])->name('stock-in');
        Route::post('/stock-out', [InventoryController::class, 'stockOut'])->name('stock-out');
        Route::post('/stock-opname', [InventoryController::class, 'stockOpname'])->name('stock-opname');

        // Bill of Materials (BoM)
        Route::post('/{product}/bom', [BomController::class, 'store'])->name('bom.store');
        Route::delete('/bom/{bom}', [BomController::class, 'destroy'])->name('bom.destroy');
    });

    // Suppliers
    Route::prefix('suppliers')->name('suppliers.')->group(function () {
        Route::get('/', [\App\Http\Controllers\SupplierController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\SupplierController::class, 'store'])->name('store');
        Route::put('/{supplier}', [\App\Http\Controllers\SupplierController::class, 'update'])->name('update');
        Route::delete('/{supplier}', [\App\Http\Controllers\SupplierController::class, 'destroy'])->name('destroy');
    });

    // Purchase Orders
    Route::prefix('purchase-orders')->name('purchase-orders.')->group(function () {
        Route::get('/', [\App\Http\Controllers\PurchaseOrderController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\PurchaseOrderController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\PurchaseOrderController::class, 'store'])->name('store');
        Route::get('/{purchaseOrder}', [\App\Http\Controllers\PurchaseOrderController::class, 'show'])->name('show');
        Route::put('/{purchaseOrder}/status', [\App\Http\Controllers\PurchaseOrderController::class, 'updateStatus'])->name('update-status');
    });

    // Production
    Route::prefix('production')->name('production.')->group(function () {
        Route::get('/dashboard', [ProductionDashboardController::class, 'index'])->name('dashboard');
        Route::get('/', [ProductionController::class, 'index'])->name('index');
        Route::get('/create', [ProductionController::class, 'create'])->name('create');
        Route::post('/', [ProductionController::class, 'store'])->name('store');
        Route::put('/{production}/update-status', [ProductionController::class, 'updateStatus'])->name('update-status');
        Route::put('/{production}/advance-stage', [ProductionController::class, 'advanceStage'])->name('advance-stage');
        Route::put('/{production}/assign-tailor', [ProductionController::class, 'assignTailor'])->name('assign-tailor');

        // Stock Transfers
        Route::resource('transfers', ProductionTransferController::class)->except(['show', 'edit', 'update', 'destroy']);
        Route::put('/transfers/{transfer}/status', [ProductionTransferController::class, 'updateStatus'])->name('transfers.update-status');
    });

    // M. Tailors
    Route::get('/tailors/monitoring', [TailorController::class, 'monitoring'])->name('tailors.monitoring');
    Route::resource('tailors', TailorController::class);

    // Users
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    // Categories
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
    });

    // Other pages (still JS-rendered for now)
    // Finance / Cash Book
    Route::prefix('finance')->name('finance.')->group(function () {
        Route::get('/dashboard', [FinanceDashboardController::class, 'index'])->name('dashboard');
        Route::get('/', [CashController::class, 'index'])->name('index');
        Route::post('/', [CashController::class, 'store'])->name('store');
        Route::put('/{finance}', [CashController::class, 'update'])->name('update');
        Route::delete('/{finance}', [CashController::class, 'destroy'])->name('destroy');
        Route::get('/reports', [\App\Http\Controllers\FinanceReportController::class, 'index'])->name('reports');
    });

    // HRM & Payroll
    Route::prefix('hrm')->name('hrm.')->group(function () {
        Route::get('/dashboard', [HrmDashboardController::class, 'index'])->name('dashboard');
        Route::get('/', [HrmController::class, 'index'])->name('index');
        Route::post('/employees', [HrmController::class, 'storeEmployee'])->name('employees.store');
        Route::put('/employees/{employee}', [HrmController::class, 'updateEmployee'])->name('employees.update');
        Route::delete('/employees/{employee}', [HrmController::class, 'destroyEmployee'])->name('employees.destroy');
        Route::post('/attendance', [HrmController::class, 'storeAttendance'])->name('attendance.store');
        Route::post('/leave', [HrmController::class, 'storeLeave'])->name('leave.store');
        
        // Kiosk
        Route::get('/kiosk', [HrmController::class, 'kiosk'])->name('kiosk');
        Route::post('/kiosk/scan', [HrmController::class, 'kioskScan'])->name('kiosk.scan');

        Route::post('/payroll/generate', [HrmController::class, 'generatePayroll'])->name('payroll.generate');
        Route::put('/payroll/{payroll}', [HrmController::class, 'updatePayroll'])->name('payroll.update');
        Route::post('/settings', [HrmController::class, 'updateSettings'])->name('settings.update');
    });

    // Orders (E-Commerce)
    Route::prefix('orders')->name('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::get('/{order}', [OrderController::class, 'show'])->name('.show');
        Route::post('/sync', [OrderController::class, 'syncApi'])->name('.sync');
        Route::put('/{order}/status', [OrderController::class, 'updateStatus'])->name('.update-status');
    });

    // POS System
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::post('/checkout', [PosController::class, 'checkout'])->name('checkout');
        Route::get('/checkout/invoice/{sale}', [PosController::class, 'invoice'])->name('invoice');
        Route::post('/customer', [SalesController::class, 'storeQuick'])->name('customer.store');
        Route::post('/apply-redeem-code', [RedeemCodeController::class, 'apply'])->name('apply-redeem-code');
    });

    // Marketing
    Route::prefix('marketing')->name('marketing.')->group(function () {
        Route::get('/dashboard', [MarketingDashboardController::class, 'index'])->name('dashboard');
    });

    // Digital Marketing
    Route::prefix('digital-marketing')->name('digital-marketing.')->group(function () {
        Route::get('/dashboard', [DigitalMarketingDashboardController::class, 'index'])->name('dashboard');
    });

    // Branches
    Route::resource('branches', \App\Http\Controllers\BranchController::class)->except(['show']);

    // Sales & CRM
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('/', [SalesController::class, 'index'])->name('index');
        Route::post('/customers', [SalesController::class, 'storeCustomer'])->name('customers.store');
        Route::put('/customers/{customer}', [SalesController::class, 'updateCustomer'])->name('customers.update');
        Route::delete('/customers/{customer}', [SalesController::class, 'destroyCustomer'])->name('customers.destroy');
    });

    Route::get('/settings', [\App\Http\Controllers\SettingController::class, 'index'])->name('settings');
    Route::post('/settings', [\App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');
});
