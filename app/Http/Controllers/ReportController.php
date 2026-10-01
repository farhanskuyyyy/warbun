<?php

namespace App\Http\Controllers;

use App\Models\CashierShift;
use App\Models\Customer;
use App\Models\DebtTransaction;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Refund;
use App\Models\SaleItem;
use App\Models\User;
use App\Services\ReportingService;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function sales(Request $request)
    {
        $request->validate(['date_from' => 'nullable|date', 'date_to' => 'nullable|date|after_or_equal:date_from']);
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : Carbon::now()->startOfMonth();
        $dateTo = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : Carbon::now();

        $summary = app(ReportingService::class)->sales($dateFrom, $dateTo);

        return view('reports.sales', $summary + compact('dateFrom', 'dateTo'));
    }

    public function inventory()
    {
        $products = Product::where('is_active', true)->with('category')->orderBy('current_stock')->get();
        $lowStock = $products->filter(fn ($p) => $p->current_stock <= $p->minimum_stock);
        $outOfStock = $products->filter(fn ($p) => $p->current_stock <= 0);

        $quantities = SaleItem::whereHas('sale', fn ($q) => $q->whereIn('status', ['completed', 'refunded']))->selectRaw('product_id, SUM(quantity) AS quantity')->groupBy('product_id')->pluck('quantity', 'product_id');
        $returns = \DB::table('refund_items')->join('refunds', 'refunds.id', '=', 'refund_items.refund_id')->where('refunds.refundable_type', Sale::class)->selectRaw('product_id, SUM(quantity) AS quantity')->groupBy('product_id')->pluck('quantity', 'product_id');
        foreach ($products as $product) {
            $product->net_quantity = max(0, ($quantities[$product->id] ?? 0) - ($returns[$product->id] ?? 0));
        }
        $bestSelling = $products->sortByDesc('net_quantity')->filter(fn ($p) => $p->net_quantity > 0)->take(5);
        $slowMoving = $products->filter(fn ($p) => $p->net_quantity === 0);

        return view('reports.inventory', compact('products', 'lowStock', 'outOfStock', 'bestSelling', 'slowMoving'));
    }

    public function debt()
    {
        $totalOutstanding = Customer::sum('outstanding_balance');
        $totalPaid = DebtTransaction::where('type', 'payment')->sum('credit_amount');
        $overdueAmount = DebtTransaction::where('debt_transactions.due_date', '<', today())->sum('remaining_amount');

        $customersWithDebt = Customer::where('outstanding_balance', '>', 0)
            ->with('debtAccount')
            ->orderBy('outstanding_balance', 'desc')
            ->get();

        $recentTransactions = DebtTransaction::with('customer')->latest()->take(20)->get();

        $aging = array_fill_keys(['Not yet overdue', '1–30 days overdue', '31–60 days overdue', '61–90 days overdue', 'Over 90 days overdue'], 0);
        foreach (DebtTransaction::where('remaining_amount', '>', 0)->get() as $entry) {
            $days = $entry->due_date ? (int) $entry->due_date->startOfDay()->diffInDays(today(), false) : 0;
            $bucket = $days <= 0 ? 'Not yet overdue' : ($days <= 30 ? '1–30 days overdue' : ($days <= 60 ? '31–60 days overdue' : ($days <= 90 ? '61–90 days overdue' : 'Over 90 days overdue')));
            $aging[$bucket] += Money::cents($entry->remaining_amount);
        }

        return view('reports.debt', compact('totalOutstanding', 'totalPaid', 'overdueAmount', 'customersWithDebt', 'recentTransactions', 'aging'));
    }

    public function payments(Request $request)
    {
        $request->validate(['date_from' => 'nullable|date', 'date_to' => 'nullable|date|after_or_equal:date_from']);
        $from = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : now()->startOfMonth();
        $to = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : now();
        $query = Payment::whereIn('status', ['paid', 'refunded'])->whereBetween('paid_at', [$from, $to]);
        $receipts = (clone $query)->get();
        $returns = \DB::table('refund_payments')->join('payments', 'payments.id', '=', 'refund_payments.payment_id')->whereBetween('refund_payments.created_at', [$from, $to])->select('payments.method', 'refund_payments.amount')->get();
        $totals = $receipts->pluck('method')->merge($returns->pluck('method'))->unique()->map(fn ($method) => (object) ['method' => $method, 'amount' => Money::decimal($receipts->where('method', $method)->sum(fn ($p) => Money::cents($p->amount)) - $returns->where('method', $method)->sum(fn ($r) => Money::cents($r->amount)))]);
        $payments = $query->with('user', 'customer')->latest()->paginate(30);

        return view('reports.payments', compact('totals', 'payments'));
    }

    public function staff()
    {
        $staff = User::whereHas('roles', fn ($q) => $q->where('name', '!=', 'customer'))->with('sales')->get();
        foreach ($staff as $user) {
            $user->sales_count = $user->sales->count();
            $user->sales_total = Money::decimal($user->sales->sum(fn ($s) => Money::cents($s->total)));
            $user->payments_total = Money::decimal(Payment::where('user_id', $user->id)->whereIn('status', ['paid', 'refunded'])->get()->sum(fn ($v) => Money::cents($v->amount)));
            $user->debt_total = Money::decimal(DebtTransaction::where('user_id', $user->id)->where('type', 'new_debt')->get()->sum(fn ($v) => Money::cents($v->debit_amount)));
            $user->refund_total = Money::decimal(Refund::where('user_id', $user->id)->get()->sum(fn ($v) => Money::cents($v->amount)));
        }
        $shifts = CashierShift::with('user')->latest()->paginate(30);

        return view('reports.staff', compact('staff', 'shifts'));
    }
}
