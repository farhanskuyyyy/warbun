<?php

use App\Http\Controllers\AuditController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerFrontController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ManagementController;
use App\Http\Controllers\OperationsController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductTypeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;
use App\Http\Middleware\BackofficePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Landing / Customer pages (public)
Route::get('/', [CustomerFrontController::class, 'landing'])->name('landing');
Route::get('/shop', [CustomerFrontController::class, 'shop'])->name('customer.shop');
Route::get('/shop/{product}', [CustomerFrontController::class, 'product'])->name('customer.product');
Route::get('/cart', [CustomerFrontController::class, 'cart'])->name('customer.cart');
Route::post('/checkout', [CustomerFrontController::class, 'checkout'])->middleware(['auth', 'throttle:30,1'])->name('customer.checkout');
Route::get('/order-success/{order}', [CustomerFrontController::class, 'orderSuccess'])->middleware('auth')->name('customer.order.success');

// CMS (admin/staff)
Route::middleware(['auth', BackofficePermission::class])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Master Data
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('product-types', ProductTypeController::class)->except(['show']);
    Route::resource('brands', BrandController::class)->except(['show']);
    Route::resource('units', UnitController::class)->except(['show']);
    Route::resource('suppliers', SupplierController::class)->except(['show']);
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
    Route::post('/orders/{order}/payments', [OrderController::class, 'payment'])->name('orders.payment');
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
    Route::get('/reports/payments', [ReportController::class, 'payments'])->name('reports.payments');
    Route::get('/reports/staff', [ReportController::class, 'staff'])->name('reports.staff');
    Route::get('/reports/debt', [ReportController::class, 'debt'])->name('reports.debt');

    // Audit
    Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
    Route::get('/audit/{auditLog}', [AuditController::class, 'show'])->name('audit.show');

});

Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::post('/locale', function (Request $request) {
    $v = $request->validate(['locale' => 'required|in:id,en']);
    session(['locale' => $v['locale']]);
    if ($request->user()) {
        $request->user()->update($v);
    }

    return back();
})->name('locale.update');

Route::middleware(['auth', BackofficePermission::class])->group(function () {
    Route::get('/stock-opnames', [OperationsController::class, 'opnames'])->name('opnames.index');
    Route::post('/stock-opnames', [OperationsController::class, 'createOpname'])->name('opnames.store');
    Route::post('/stock-opnames/{opname}/approve', [OperationsController::class, 'approveOpname'])->name('opnames.approve');
    Route::get('/refunds', [OperationsController::class, 'refunds'])->name('refunds.index');
    Route::post('/refunds', [OperationsController::class, 'refund'])->name('refunds.store');
    Route::get('/shifts', [OperationsController::class, 'shifts'])->name('shifts.index');
    Route::post('/debt/correction', [OperationsController::class, 'correction'])->name('debt.correction');
});
Route::get('/my-orders', [CustomerFrontController::class, 'history'])->middleware('auth')->name('customer.history');

Route::middleware(['auth', BackofficePermission::class])->group(function () {
    Route::get('/users', [ManagementController::class, 'users'])->name('users.index');
    Route::post('/users', [ManagementController::class, 'storeUser'])->name('users.store');
    Route::put('/users/{user}', [ManagementController::class, 'updateUser'])->name('users.update');
    Route::put('/roles/{role}', [ManagementController::class, 'role'])->name('roles.update');
    Route::get('/settings', [ManagementController::class, 'settings'])->name('settings.index');
    Route::put('/settings', [ManagementController::class, 'saveSettings'])->name('settings.update');
});
Route::post('/payments/webhook', PaymentWebhookController::class)->middleware('throttle:60,1')->name('payments.webhook');
