<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Sale;
use App\Services\PaymentService;
use App\Services\RefundService;
use Illuminate\Http\Request;

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
        app(PaymentService::class)->confirm($payment);

        return back()->with('success', __('Payment confirmed'));
    }

    public function refund(Request $request, Payment $payment)
    {
        $v = $request->validate(['reason' => 'required|string|max:255', 'request_key' => 'required|string|max:80']);
        abort_unless(in_array($payment->payable_type, [Sale::class, Order::class]), 422);
        app(RefundService::class)->all($payment->payable, $v['reason'], $v['request_key']);

        return back()->with('success', __('Refund recorded'));
    }

    public function createManual()
    {
        $sales = Sale::where('status', 'completed')->where('payment_method', 'debt')->get();
        $customers = Customer::where('outstanding_balance', '>', 0)->get();

        return view('payments.create', compact('sales', 'customers'));
    }

    public function storeManual(Request $request)
    {
        $v = $request->validate(['request_key' => 'required|string|max:80', 'sale_id' => 'required|integer|exists:sales,id', 'amount' => 'required|decimal:0,2|min:0.01', 'method' => 'required|in:cash,transfer,ewallet,qr,other', 'reference_number' => 'nullable|string|max:255', 'notes' => 'nullable|string|max:2000']);
        $payment = app(PaymentService::class)->receive($v);

        return redirect()->route('payments.show', $payment)->with('success', __('Payment recorded'));
    }
}
