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

use App\Http\Controllers\RedeemCodeController;

// Auth routes
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn() => view('pages.dashboard', ['activePage' => 'dashboard']))->name('dashboard');

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
    });

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
        Route::get('/', [CashController::class, 'index'])->name('index');
        Route::post('/', [CashController::class, 'store'])->name('store');
        Route::put('/{finance}', [CashController::class, 'update'])->name('update');
        Route::delete('/{finance}', [CashController::class, 'destroy'])->name('destroy');
        Route::get('/reports', [\App\Http\Controllers\FinanceReportController::class, 'index'])->name('reports');
    });

    // HRM & Payroll
    Route::prefix('hrm')->name('hrm.')->group(function () {
        Route::get('/', [HrmController::class, 'index'])->name('index');
        Route::post('/employees', [HrmController::class, 'storeEmployee'])->name('employees.store');
        Route::put('/employees/{employee}', [HrmController::class, 'updateEmployee'])->name('employees.update');
        Route::delete('/employees/{employee}', [HrmController::class, 'destroyEmployee'])->name('employees.destroy');
        Route::post('/attendance', [HrmController::class, 'storeAttendance'])->name('attendance.store');
        
        // Kiosk
        Route::get('/kiosk', [HrmController::class, 'kiosk'])->name('kiosk');
        Route::post('/kiosk/scan', [HrmController::class, 'kioskScan'])->name('kiosk.scan');

        Route::post('/payroll/generate', [HrmController::class, 'generatePayroll'])->name('payroll.generate');
        Route::put('/payroll/{payroll}', [HrmController::class, 'updatePayroll'])->name('payroll.update');
    });

    // Orders
    Route::get('/orders', fn() => view('pages.orders', ['activePage' => 'orders']))->name('orders');

    // POS System
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::post('/checkout', [PosController::class, 'checkout'])->name('checkout');
        Route::post('/customer', [SalesController::class, 'storeQuick'])->name('customer.store');
        Route::post('/apply-redeem-code', [RedeemCodeController::class, 'apply'])->name('apply-redeem-code');
    });

    // Sales & CRM
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('/', [SalesController::class, 'index'])->name('index');
        Route::post('/customers', [SalesController::class, 'storeCustomer'])->name('customers.store');
        Route::put('/customers/{customer}', [SalesController::class, 'updateCustomer'])->name('customers.update');
        Route::delete('/customers/{customer}', [SalesController::class, 'destroyCustomer'])->name('customers.destroy');
    });

    Route::get('/settings', fn() => view('pages.settings', ['activePage' => 'settings']))->name('settings');
});
