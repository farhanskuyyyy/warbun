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

Route::get('/', function () {
    return redirect()->route('login');
});

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
});
