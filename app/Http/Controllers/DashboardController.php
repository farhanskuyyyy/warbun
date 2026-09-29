<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Models\DebtTransaction;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        
        $stats = [
            'sales_today' => Sale::whereDate('created_at', $today)->where('status', 'completed')->sum('total'),
            'transactions_today' => Sale::whereDate('created_at', $today)->where('status', 'completed')->count(),
            'orders_today' => Order::whereDate('created_at', $today)->count(),
            'debt_created_today' => DebtTransaction::whereDate('created_at', $today)->where('type', 'new_debt')->sum('debit_amount'),
            'debt_collected_today' => DebtTransaction::whereDate('created_at', $today)->where('type', 'payment')->sum('credit_amount'),
        ];
        
        $inventory_alerts = [
            'low_stock' => Product::where('current_stock', '<=', DB::raw('minimum_stock'))->where('is_active', true)->count(),
            'out_of_stock' => Product::where('current_stock', '<=', 0)->where('is_active', true)->count(),
        ];
        
        $recent_sales = Sale::with('user')->latest()->take(5)->get();
        
        return view('dashboard', compact('stats', 'inventory_alerts', 'recent_sales'));
    }
}
