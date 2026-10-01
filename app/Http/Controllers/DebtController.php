<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DebtTransaction;
use App\Services\DebtService;
use App\Services\PaymentService;
use App\Support\Money;
use Illuminate\Http\Request;

class DebtController extends Controller
{
    public function index(Request $request)
    {
        $query = DebtTransaction::with('customer', 'user');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->latest()->paginate(20);

        // Dashboard stats
        $totalOutstanding = Customer::sum('outstanding_balance');
        $totalOverdue = DebtTransaction::where('due_date', '<', today())->sum('remaining_amount');
        $customersWithDebt = Customer::where('outstanding_balance', '>', 0)->count();

        return view('debt.index', compact('transactions', 'totalOutstanding', 'totalOverdue', 'customersWithDebt'));
    }

    public function create()
    {
        $customers = Customer::where('is_active', true)->where('can_use_debt', true)->get();

        return view('debt.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $v = $request->validate(['customer_id' => 'required|integer|exists:customers,id', 'amount' => 'required|decimal:0,2|min:0.01', 'due_date' => 'required|date|after_or_equal:today', 'description' => 'required|string|max:255', 'credit_override' => 'sometimes|boolean']);
        app(DebtService::class)->debit($v['customer_id'], Money::cents($v['amount']), $v['description'], null, $v['due_date'], (bool) ($v['credit_override'] ?? false));

        return redirect()->route('debt.index')->with('success', __('Debt transaction created'));
    }

    public function payment(Request $request)
    {
        $v = $request->validate(['request_key' => 'required|string|max:80', 'customer_id' => 'required|integer|exists:customers,id', 'amount' => 'required|decimal:0,2|min:0.01', 'payment_method' => 'required|in:cash,transfer,ewallet,qr,other', 'notes' => 'nullable|string|max:2000']);
        $v['method'] = $v['payment_method'];
        app(PaymentService::class)->receive($v);

        return back()->with('success', __('Payment recorded successfully'));
    }

    public function dashboard()
    {
        return $this->index(request());
    }
}
