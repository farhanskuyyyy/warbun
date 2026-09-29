<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use App\Models\DebtTransaction;
use App\Models\InventoryTransaction;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function sales(Request $request)
    {
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : Carbon::now()->startOfMonth();
        $dateTo = $request->date_to ? Carbon::parse($request->date_to) : Carbon::now();
        
        $sales = Sale::whereBetween('created_at', [$dateFrom, $dateTo])->where('status', 'completed');
        
        $totalSales = $sales->sum('total');
        $totalTransactions = $sales->count();
        $avgTransaction = $totalTransactions > 0 ? $totalSales / $totalTransactions : 0;
        
        $dailySales = Sale::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'completed')
            ->selectRaw('DATE(created_at) as date, SUM(total) as total, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        return view('reports.sales', compact('totalSales', 'totalTransactions', 'avgTransaction', 'dailySales', 'dateFrom', 'dateTo'));
    }

    public function inventory()
    {
        $products = Product::where('is_active', true)->with('category')->orderBy('current_stock')->get();
        $lowStock = $products->filter(fn($p) => $p->current_stock <= $p->minimum_stock);
        $outOfStock = $products->filter(fn($p) => $p->current_stock <= 0);
        
        return view('reports.inventory', compact('products', 'lowStock', 'outOfStock'));
    }

    public function debt()
    {
        $totalOutstanding = DebtTransaction::where('status', 'pending')->sum('debit_amount') - DebtTransaction::where('status', 'pending')->sum('credit_amount');
        $totalPaid = DebtTransaction::where('type', 'payment')->sum('credit_amount');
        $overdueAmount = DebtTransaction::where('status', 'pending')->where('debt_transactions.due_date', '<', today())->sum('debit_amount');
        
        $customersWithDebt = Customer::where('outstanding_balance', '>', 0)
            ->with('debtAccount')
            ->orderBy('outstanding_balance', 'desc')
            ->get();
        
        $recentTransactions = DebtTransaction::with('customer')->latest()->take(20)->get();
        
        return view('reports.debt', compact('totalOutstanding', 'totalPaid', 'overdueAmount', 'customersWithDebt', 'recentTransactions'));
    }
}
