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
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductionTransferController;
use App\Http\Controllers\TailorController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\GeneralLedgerController;
use App\Http\Controllers\FiscalPeriodController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\AuditTrailController;

use App\Http\Controllers\RedeemCodeController;

// Auth routes
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware('auth')->group(function () {
    // Notifications API
    Route::get('/api/notifications', [NotificationController::class, 'getNotifications'])->name('api.notifications');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'chartDataApi'])->name('dashboard.chart-data');

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
        
        // Supplier Products Catalog
        Route::post('/{supplier}/products', [\App\Http\Controllers\SupplierController::class, 'storeProduct'])->name('products.store');
        Route::put('/{supplier}/products/{supplierProduct}', [\App\Http\Controllers\SupplierController::class, 'updateProduct'])->name('products.update');
        Route::delete('/{supplier}/products/{supplierProduct}', [\App\Http\Controllers\SupplierController::class, 'destroyProduct'])->name('products.destroy');
    });

    // API Routes for AJAX
    Route::get('/api/suppliers/{supplier}/products', [\App\Http\Controllers\SupplierController::class, 'apiProducts'])->name('api.suppliers.products');

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

    // Finance Division
    Route::prefix('finance')->name('finance.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [FinanceDashboardController::class, 'index'])->name('dashboard');

        // Cash Book
        Route::get('/', [CashController::class, 'index'])->name('index');
        Route::post('/', [CashController::class, 'store'])->name('store');
        Route::get('/print', [CashController::class, 'print'])->name('print');
        Route::put('/{finance}', [CashController::class, 'update'])->name('update');
        Route::delete('/{finance}', [CashController::class, 'destroy'])->name('destroy');

        // Chart of Accounts
        Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
        Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
        Route::put('/accounts/{account}', [AccountController::class, 'update'])->name('accounts.update');
        Route::patch('/accounts/{account}/toggle', [AccountController::class, 'toggleActive'])->name('accounts.toggle');

        // General Ledger
        Route::get('/ledger', [GeneralLedgerController::class, 'index'])->name('ledger.index');
        Route::get('/ledger/create', [GeneralLedgerController::class, 'create'])->name('ledger.create');
        Route::post('/ledger', [GeneralLedgerController::class, 'store'])->name('ledger.store');
        Route::get('/ledger/{entry}', [GeneralLedgerController::class, 'show'])->name('ledger.show');
        Route::patch('/ledger/{entry}/post', [GeneralLedgerController::class, 'post'])->name('ledger.post');
        Route::patch('/ledger/{entry}/void', [GeneralLedgerController::class, 'void'])->name('ledger.void');

        // Fiscal Periods
        Route::get('/periods', [FiscalPeriodController::class, 'index'])->name('periods.index');
        Route::post('/periods', [FiscalPeriodController::class, 'store'])->name('periods.store');
        Route::patch('/periods/{period}/close', [FiscalPeriodController::class, 'close'])->name('periods.close');
        Route::patch('/periods/{period}/lock', [FiscalPeriodController::class, 'lock'])->name('periods.lock');
        Route::patch('/periods/{period}/reopen', [FiscalPeriodController::class, 'reopen'])->name('periods.reopen');

        // Budgets
        Route::get('/budgets', [BudgetController::class, 'index'])->name('budgets.index');
        Route::get('/budgets/create', [BudgetController::class, 'create'])->name('budgets.create');
        Route::post('/budgets', [BudgetController::class, 'store'])->name('budgets.store');
        Route::get('/budgets/report', [BudgetController::class, 'report'])->name('budgets.report');

        // Audit Trail
        Route::get('/audit', [AuditTrailController::class, 'index'])->name('audit.index');

        // Reports
        Route::get('/reports', [\App\Http\Controllers\FinanceReportController::class, 'index'])->name('reports');
        Route::get('/reports/print', [\App\Http\Controllers\FinanceReportController::class, 'print'])->name('reports.print');
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
        Route::get('/', [OrderController::class, 'index'])->name('.index');
        Route::get('/{order}', [OrderController::class, 'show'])->name('.show');
        Route::post('/import', [OrderController::class, 'import'])->name('.import');
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
        Route::get('/dashboard/chart-data', [MarketingDashboardController::class, 'chartApi'])->name('dashboard.chart-data');
    });

    // Digital Marketing
    Route::prefix('digital-marketing')->name('digital-marketing.')->group(function () {
        Route::get('/dashboard', [DigitalMarketingDashboardController::class, 'index'])->name('dashboard');
        Route::post('/shopee-report', [DigitalMarketingDashboardController::class, 'generateShopeeReport'])->name('shopee-report');
    });

    // Digital Platforms (E-Commerce)
    Route::resource('digital-platforms', \App\Http\Controllers\DigitalPlatformController::class)->except(['show']);

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
    Route::post('/settings/backup/manual', [\App\Http\Controllers\SettingController::class, 'manualBackup'])->name('settings.backup.manual');
    Route::post('/settings/export/audit', [\App\Http\Controllers\SettingController::class, 'exportAudit'])->name('settings.export.audit');
});
