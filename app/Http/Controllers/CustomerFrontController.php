<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;

class CustomerFrontController extends Controller
{
    public function landing()
    {
        return view('landing');
    }

    public function shop(Request $request)
    {
        $query = Product::where('is_active', true)->where('current_stock', '>', 0);
        
        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        
        if ($request->category) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }
        
        $products = $query->paginate(12);
        
        return view('customer.shop', compact('products'));
    }

    public function product(Product $product)
    {
        $product->load('category', 'brand', 'unit');
        return view('customer.product', compact('product'));
    }

    public function cart()
    {
        return view('customer.cart');
    }

    public function checkout(Request $request)
    {
        $items = $request->input('items', []);
        
        if (empty($items)) {
            return redirect()->route('customer.cart')->with('error', 'Cart is empty');
        }

        $subtotal = 0;
        $orderItems = [];
        
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            if ($product && $product->current_stock >= $item['quantity']) {
                $itemTotal = $product->selling_price * $item['quantity'];
                $subtotal += $itemTotal;
                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->selling_price,
                    'subtotal' => $itemTotal,
                ];
            }
        }

        $shippingCost = $request->input('delivery_type') === 'delivery' ? 10000 : 0;
        $total = $subtotal + $shippingCost;

        $order = Order::create([
            'order_number' => 'ORD-' . date('Ymd') . '-' . str_pad(Order::count() + 1, 5, '0', STR_PAD_LEFT),
            'customer_id' => auth()->guard('customer')->id() ?? null,
            'subtotal' => $subtotal,
            'shipping_cost' => $shippingCost,
            'total' => $total,
            'status' => 'pending',
            'payment_status' => 'pending',
            'fulfillment_type' => $request->input('delivery_type', 'pickup'),
            'shipping_address' => $request->input('address'),
            'notes' => $request->input('notes'),
        ]);

        foreach ($orderItems as $item) {
            $order->items()->create($item);
        }

        return redirect()->route('customer.order.success', $order);
    }

    public function orderSuccess(Order $order)
    {
        $order->load('items.product');
        return view('customer.order-success', compact('order'));
    }
}
