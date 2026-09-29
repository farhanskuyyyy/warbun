<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\Order;
use App\Models\Customer;
use App\Models\AuditLog;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with('customer', 'user');
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->method) {
            $query->where('method', $request->method);
        }
        
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $payments = $query->latest()->paginate(20);
        
        $totalPaid = Payment::where('status', 'paid')->sum('amount');
        $totalPending = Payment::where('status', 'pending')->sum('amount');
        
        return view('payments.index', compact('payments', 'totalPaid', 'totalPending'));
    }

    public function show(Payment $payment)
    {
        $payment->load('customer', 'user', 'payable');
        return view('payments.show', compact('payment'));
    }

    public function confirm(Request $request, Payment $payment)
    {
        if ($payment->status !== 'pending') {
            return back()->withErrors(['error' => 'Payment is not pending']);
        }

        $payment->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        // Update related payable status
        if ($payment->payable_type === Order::class) {
            $order = Order::find($payment->payable_id);
            if ($order) {
                $order->update(['payment_status' => 'paid']);
            }
        }

        AuditLog::log('payment.confirmed', $payment, ['status' => 'pending'], ['status' => 'paid']);

        return redirect()->route('payments.show', $payment)->with('success', 'Payment confirmed');
    }

    public function refund(Request $request, Payment $payment)
    {
        if ($payment->status !== 'paid') {
            return back()->withErrors(['error' => 'Can only refund paid payments']);
        }

        $payment->update([
            'status' => 'refunded',
            'notes' => ($payment->notes ?? '') . "\nRefunded: " . ($request->reason ?? 'No reason'),
        ]);

        AuditLog::log('payment.refunded', $payment, ['status' => 'paid'], ['status' => 'refunded']);

        return redirect()->route('payments.show', $payment)->with('success', 'Payment refunded');
    }

    public function createManual()
    {
        $sales = Sale::where('status', 'completed')->where('payment_method', 'debt')->get();
        $customers = Customer::where('outstanding_balance', '>', 0)->get();
        return view('payments.create', compact('sales', 'customers'));
    }

    public function storeManual(Request $request)
    {
        $validated = $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'amount' => 'required|numeric|min:1',
            'method' => 'required|in:cash,transfer,ewallet,qr,other',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $sale = Sale::findOrFail($validated['sale_id']);
        $validated['customer_id'] = $sale->customer_id;
        $validated['payable_type'] = Sale::class;
        $validated['payable_id'] = $sale->id;
        $validated['user_id'] = auth()->id();
        $validated['status'] = 'paid';
        $validated['paid_at'] = now();

        $payment = Payment::create([
            'payment_number' => 'PAY-' . date('Ymd') . '-' . str_pad(Payment::whereDate('created_at', today())->count() + 1, 5, '0', STR_PAD_LEFT),
            'payable_type' => $validated['payable_type'],
            'payable_id' => $validated['payable_id'],
            'customer_id' => $validated['customer_id'] ?? null,
            'amount' => $validated['amount'],
            'method' => $validated['method'],
            'status' => 'paid',
            'reference_number' => $validated['reference_number'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'user_id' => auth()->id(),
            'paid_at' => now(),
        ]);

        // Update debt if applicable
        if ($sale->customer_id && $sale->debt_amount > 0) {
            $customer = Customer::find($sale->customer_id);
            if ($customer) {
                $customer->update(['outstanding_balance' => max(0, $customer->outstanding_balance - $validated['amount'])]);
                if ($customer->debtAccount) {
                    $customer->debtAccount->update([
                        'total_paid' => $customer->debtAccount->total_paid + $validated['amount'],
                        'outstanding_balance' => max(0, $customer->debtAccount->outstanding_balance - $validated['amount']),
                    ]);
                }
            }
        }

        AuditLog::log('payment.created', $payment, null, $payment->toArray());

        return redirect()->route('payments.show', $payment)->with('success', 'Payment recorded');
    }
}
