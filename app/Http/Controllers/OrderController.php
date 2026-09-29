<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\AuditLog;

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

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:confirmed,preparing,ready,completed,cancelled',
        ]);

        $oldStatus = $order->status;
        $order->update(['status' => $validated['status']]);

        AuditLog::log('order.status_updated', $order, ['status' => $oldStatus], ['status' => $validated['status']]);

        return redirect()->back()->with('success', 'Order status updated');
    }
}
