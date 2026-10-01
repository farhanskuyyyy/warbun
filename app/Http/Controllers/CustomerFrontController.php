<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Http\Request;

class CustomerFrontController extends Controller
{
    public function landing()
    {
        return view('landing');
    }

    public function shop(Request $request)
    {
        $query = Product::where('is_active', true)->where('is_available_online', true);

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->category) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
        }

        $products = $query->paginate(12);

        return view('customer.shop', compact('products'));
    }

    public function product(Product $product)
    {
        abort_unless($product->is_active && $product->is_available_online, 404);
        $product->load('category', 'brand', 'unit');

        return view('customer.product', compact('product'));
    }

    public function cart()
    {
        return view('customer.cart');
    }

    public function checkout(Request $request)
    {
        $v = $request->validate(['request_key' => 'required|string|max:80', 'items' => 'required|array|min:1|max:100', 'items.*.product_id' => 'required|integer|exists:products,id', 'items.*.quantity' => 'required|integer|min:1|max:100000', 'delivery_type' => 'required|in:pickup,delivery', 'address' => 'required_if:delivery_type,delivery|nullable|string|max:2000', 'notes' => 'nullable|string|max:2000', 'payment_method' => 'required|in:cash,transfer,ewallet,qr,online']);
        $order = app(OrderService::class)->checkout($v);

        return redirect()->route('customer.order.success', $order);
    }

    public function orderSuccess(Order $order)
    {
        abort_unless($order->customer?->user_id === auth()->id(), 403);
        $order->load('items.product', 'payments');

        return view('customer.order-success', compact('order'));
    }

    public function history()
    {
        $customer = Customer::where('user_id', auth()->id())->firstOrFail();
        $orders = $customer->orders()->latest()->paginate(20);
        $transactions = $customer->debtTransactions()->latest()->paginate(20, ['*'], 'debt_page');

        $sales = $customer->sales()->with('items.product')->latest()->paginate(20, ['*'], 'sales_page');

        return view('customer.history', compact('customer', 'orders', 'transactions', 'sales'));
    }
}
