<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('customer');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        $orders = $query->latest()->paginate(20);

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load('items.product', 'customer', 'payments');

        return view('orders.show', compact('order'));
    }

    public function payment(Request $request, Order $order)
    {
        $v = $request->validate(['request_key' => 'required|string|max:80', 'amount' => 'required|decimal:0,2|min:0.01', 'method' => 'required|in:cash,transfer,ewallet,qr,other']);
        app(PaymentService::class)->forOrder($order, $v);

        return back()->with('success', __('Payment recorded'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:confirmed,preparing,ready,completed,cancelled',
        ]);

        app(OrderService::class)->transition($order, $validated['status']);

        return redirect()->back()->with('success', __('Order status updated'));
    }
}
