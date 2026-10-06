<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use App\Support\DeliveryPoint;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerFrontController extends Controller
{
    public function landing()
    {
        $featured = Product::with('category')->where('is_active', true)->where('is_available_online', true)->orderByDesc('current_stock')->orderBy('name')->limit(4)->get();
        $categories = Category::where('is_active', true)->whereHas('products', fn ($query) => $query->where('is_active', true)->where('is_available_online', true))->orderBy('name')->get();

        return view('landing', compact('featured', 'categories'));
    }

    public function shop(Request $request)
    {
        $query = Product::with('category', 'unit')->where('is_active', true)->where('is_available_online', true);

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->category) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
        }

        $products = $query->orderBy('name')->paginate(12);
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('customer.shop', compact('products', 'categories'));
    }

    public function product(Product $product)
    {
        abort_unless($product->is_active && $product->is_available_online, 404);
        $product->load('category', 'brand', 'unit');

        return view('customer.product', compact('product'));
    }

    public function cart()
    {
        $shippingCost = DB::table('store_settings')->where('key', 'shipping_cost')->value('value') ?? '10000';
        $paymentInstructions = DB::table('store_settings')->where('key', 'payment_instructions')->value('value');
        $storeContact = DB::table('store_settings')->where('key', 'store_contact')->value('value');

        return view('customer.cart', compact('shippingCost', 'paymentInstructions', 'storeContact'));
    }

    public function quote(Request $request)
    {
        $data = $request->validate(['items' => 'required|array|min:1|max:100', 'items.*.product_id' => 'required|integer|distinct', 'items.*.quantity' => 'required|integer|min:1|max:100000', 'delivery_type' => 'required|in:pickup,delivery']);
        $products = Product::with('unit')->whereIn('id', array_column($data['items'], 'product_id'))->where('is_active', true)->where('is_available_online', true)->get()->keyBy('id');
        $subtotal = 0;
        $errors = [];
        $items = [];
        foreach ($data['items'] as $item) {
            $product = $products->get($item['product_id']);
            if (! $product) {
                $errors[] = __('A product is no longer available. Remove it to continue.');
                $items[] = ['id' => $item['product_id'], 'available' => false];

                continue;
            }
            if ($item['quantity'] > $product->current_stock) {
                $errors[] = __('Not enough stock for :name. Available: :count.', ['name' => $product->name, 'count' => $product->current_stock]);
            }
            $line = Money::multiply(Money::cents($product->selling_price), $item['quantity']);
            $subtotal += $line;
            $items[] = ['id' => $product->id, 'available' => true, 'name' => $product->name, 'price' => $product->selling_price, 'stock' => $product->current_stock, 'unit' => $product->unit?->symbol];
        }
        $shipping = $data['delivery_type'] === 'delivery' ? Money::cents(DB::table('store_settings')->where('key', 'shipping_cost')->value('value') ?? '10000') : 0;

        return response()->json(['items' => $items, 'subtotal' => Money::decimal($subtotal), 'shipping' => Money::decimal($shipping), 'total' => Money::decimal($subtotal + $shipping), 'errors' => $errors]);
    }

    public function checkout(Request $request)
    {
        $point = $request->validate(DeliveryPoint::rules());
        $v = $request->validate(['request_key' => 'required|string|max:80', 'items' => 'required|array|min:1|max:100', 'items.*.product_id' => 'required|integer|exists:products,id', 'items.*.quantity' => 'required|integer|min:1|max:100000', 'delivery_type' => 'required|in:pickup,delivery', 'address' => 'required_if:delivery_type,delivery|nullable|string|max:2000', 'notes' => 'nullable|string|max:2000', 'payment_method' => 'required|in:cash,transfer,ewallet,qr,online']);
        $order = app(OrderService::class)->checkout(array_merge($v, $point));

        return redirect()->route('customer.order.success', $order);
    }

    public function orderSuccess(Order $order)
    {
        abort_unless($order->customer?->user_id === auth()->id(), 403);
        $order->load('items.product', 'payments');

        $paymentInstructions = DB::table('store_settings')->where('key', 'payment_instructions')->value('value');
        $storeContact = DB::table('store_settings')->where('key', 'store_contact')->value('value');

        return view('customer.order-success', compact('order', 'paymentInstructions', 'storeContact'));
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
