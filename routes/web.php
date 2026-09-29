<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductTypeController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\CustomerFrontController;

// Landing / Customer pages (public)
Route::get('/', [CustomerFrontController::class, 'landing'])->name('landing');
Route::get('/shop', [CustomerFrontController::class, 'shop'])->name('customer.shop');
Route::get('/shop/{product}', [CustomerFrontController::class, 'product'])->name('customer.product');
Route::get('/cart', [CustomerFrontController::class, 'cart'])->name('customer.cart');
Route::post('/checkout', [CustomerFrontController::class, 'checkout'])->name('customer.checkout');
Route::get('/order-success/{order}', [CustomerFrontController::class, 'orderSuccess'])->name('customer.order.success');

// CMS (admin/staff)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Master Data
    Route::resource('categories', CategoryController::class);
    Route::resource('product-types', ProductTypeController::class);
    Route::resource('brands', BrandController::class);
    Route::resource('units', UnitController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::resource('products', ProductController::class);
    
    // Inventory
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory/stock-in', [InventoryController::class, 'stockIn'])->name('inventory.stock-in');
    Route::post('/inventory/stock-out', [InventoryController::class, 'stockOut'])->name('inventory.stock-out');
    Route::post('/inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
    
    // Customers
    Route::resource('customers', CustomerController::class);
    
    // POS
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::get('/pos/products', [PosController::class, 'products'])->name('pos.products');
    Route::post('/pos/process-sale', [PosController::class, 'processSale'])->name('pos.process-sale');
    Route::get('/pos/receipt/{sale}', [PosController::class, 'receipt'])->name('pos.receipt');
    Route::get('/pos/history', [PosController::class, 'history'])->name('pos.history');
    Route::post('/pos/open-shift', [PosController::class, 'openShift'])->name('pos.open-shift');
    Route::post('/pos/close-shift', [PosController::class, 'closeShift'])->name('pos.close-shift');
    
    // Debt
    Route::get('/debt', [DebtController::class, 'index'])->name('debt.index');
    Route::get('/debt/create', [DebtController::class, 'create'])->name('debt.create');
    Route::post('/debt', [DebtController::class, 'store'])->name('debt.store');
    Route::post('/debt/payment', [DebtController::class, 'payment'])->name('debt.payment');
    Route::get('/debt/dashboard', [DebtController::class, 'dashboard'])->name('debt.dashboard');
    
    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    
    // Payments
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/create', [PaymentController::class, 'createManual'])->name('payments.create');
    Route::post('/payments', [PaymentController::class, 'storeManual'])->name('payments.store');
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::post('/payments/{payment}/confirm', [PaymentController::class, 'confirm'])->name('payments.confirm');
    Route::post('/payments/{payment}/refund', [PaymentController::class, 'refund'])->name('payments.refund');
    
    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
    Route::get('/reports/debt', [ReportController::class, 'debt'])->name('reports.debt');
    
    // Audit
    Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
    Route::get('/audit/{auditLog}', [AuditController::class, 'show'])->name('audit.show');
    
    // Profile
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
});
