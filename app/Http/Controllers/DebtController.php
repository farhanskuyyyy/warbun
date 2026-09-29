<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\DebtAccount;
use App\Models\DebtTransaction;
use App\Models\AuditLog;

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
        $totalOutstanding = DebtTransaction::where('status', 'pending')->sum('debit_amount') - DebtTransaction::where('status', 'pending')->sum('credit_amount');
        $totalOverdue = DebtTransaction::where('status', 'pending')->where('due_date', '<', today())->sum('debit_amount');
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
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:1',
            'due_date' => 'required|date|after:today',
            'description' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);
        
        if (!$customer->can_use_debt) {
            return back()->withErrors(['customer_id' => 'Customer cannot use debt']);
        }

        if ($customer->outstanding_balance + $validated['amount'] > $customer->debtAccount->credit_limit) {
            return back()->withErrors(['amount' => 'Exceeds credit limit. Available: Rp ' . number_format($customer->debtAccount->credit_limit - $customer->outstanding_balance, 0, ',', '.')]);
        }

        \DB::transaction(function () use ($validated, $customer) {
            $debtAccount = $customer->debtAccount;
            $previousBalance = $debtAccount->outstanding_balance;
            $newBalance = $previousBalance + $validated['amount'];
            
            $txn = DebtTransaction::create([
                'reference_number' => 'DEB-' . date('Ymd') . '-' . str_pad(DebtTransaction::whereDate('created_at', today())->count() + 1, 5, '0', STR_PAD_LEFT),
                'debt_account_id' => $debtAccount->id,
                'customer_id' => $customer->id,
                'type' => 'new_debt',
                'debit_amount' => $validated['amount'],
                'credit_amount' => 0,
                'balance_after' => $newBalance,
                'due_date' => $validated['due_date'],
                'status' => 'pending',
                'user_id' => auth()->id(),
                'description' => $validated['description'],
                'notes' => $validated['notes'] ?? null,
            ]);
            
            $customer->update(['outstanding_balance' => $newBalance]);
            $debtAccount->update([
                'total_debt' => $debtAccount->total_debt + $validated['amount'],
                'outstanding_balance' => $newBalance,
            ]);
            
            AuditLog::log('debt.created', $txn, null, $txn->toArray());
        });

        return redirect()->route('debt.index')->with('success', 'Debt transaction created');
    }

    public function payment(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:cash,transfer,ewallet,other',
            'notes' => 'nullable|string',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);
        
        if ($customer->outstanding_balance <= 0) {
            return back()->withErrors(['customer_id' => 'Customer has no outstanding debt']);
        }

        if ($validated['amount'] > $customer->outstanding_balance) {
            return back()->withErrors(['amount' => 'Payment exceeds outstanding balance. Balance: Rp ' . number_format($customer->outstanding_balance, 0, ',', '.')]);
        }

        \DB::transaction(function () use ($validated, $customer) {
            $debtAccount = $customer->debtAccount;
            $previousBalance = $debtAccount->outstanding_balance;
            $newBalance = $previousBalance - $validated['amount'];
            
            $txn = DebtTransaction::create([
                'reference_number' => 'DPAY-' . date('Ymd') . '-' . str_pad(DebtTransaction::whereDate('created_at', today())->count() + 1, 5, '0', STR_PAD_LEFT),
                'debt_account_id' => $debtAccount->id,
                'customer_id' => $customer->id,
                'type' => 'payment',
                'debit_amount' => 0,
                'credit_amount' => $validated['amount'],
                'balance_after' => $newBalance,
                'status' => $newBalance <= 0 ? 'paid' : 'pending',
                'user_id' => auth()->id(),
                'description' => 'Payment via ' . $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
            ]);
            
            $customer->update(['outstanding_balance' => $newBalance]);
            $debtAccount->update([
                'total_paid' => $debtAccount->total_paid + $validated['amount'],
                'outstanding_balance' => $newBalance,
            ]);
            
            AuditLog::log('debt.payment', $txn, null, $txn->toArray());
        });

        return redirect()->route('debt.index')->with('success', 'Payment recorded successfully');
    }

    public function dashboard()
    {
        $totalOutstanding = DebtTransaction::where('status', 'pending')->sum('debit_amount') - DebtTransaction::where('status', 'pending')->sum('credit_amount');
        $totalOverdue = DebtTransaction::where('status', 'pending')->where('due_date', '<', today())->sum('debit_amount');
        $customersWithDebt = Customer::where('outstanding_balance', '>', 0)->count();
        $recentTransactions = DebtTransaction::with('customer', 'user')->latest()->take(10)->get();
        
        return view('debt.dashboard', compact('totalOutstanding', 'totalOverdue', 'customersWithDebt', 'recentTransactions'));
    }
}
