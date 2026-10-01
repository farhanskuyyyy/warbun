<?php

namespace App\Http\Controllers;

use App\Models\CashierShift;
use App\Models\Customer;
use App\Models\DebtTransaction;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Services\ReportingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $stats = [
            'sales_today' => app(ReportingService::class)->sales($today, $today->copy()->endOfDay())['totalSales'],
            'transactions_today' => Sale::whereDate('created_at', $today)->whereIn('status', ['completed', 'refunded'])->count(),
            'orders_today' => Order::whereDate('created_at', $today)->count(),
            'outstanding_debt' => Customer::sum('outstanding_balance'),
            'overdue_debt' => DebtTransaction::where('due_date', '<', $today)->sum('remaining_amount'),
            'due_today' => DebtTransaction::whereDate('due_date', $today)->sum('remaining_amount'),
            'active_shifts' => CashierShift::whereNotNull('active_user_id')->count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'failed_payments' => Payment::where('status', 'failed')->count(),
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
