<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Services\AddressBookService;
use App\Services\CheckoutPolicy;
use App\Services\OrderService;
use App\Support\DeliveryPoint;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

    public function product(Request $request, Product $product)
    {
        abort_unless($product->is_active && $product->is_available_online, 404);
        $product->load('category', 'brand', 'unit');

        if ($request->expectsJson()) {
            return response()->json(['id' => $product->id, 'name' => $product->name, 'price' => $product->selling_price, 'stock' => $product->current_stock, 'unit' => $product->unit?->symbol, 'description' => $product->description, 'image' => $product->image ? Storage::disk('public')->url($product->image) : null]);
        }

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
        $data = $request->validate(['items' => 'required|array|min:1|max:100', 'items.*.product_id' => 'required|integer|distinct', 'items.*.quantity' => 'required|integer|min:1|max:100000', 'delivery_type' => 'required|in:pickup,delivery', 'payment_method' => 'nullable|in:cash,transfer,ewallet,qr,online', 'payment_channel' => 'nullable|string|max:30', 'address_id' => 'nullable|integer|min:1', 'shipping_latitude' => 'nullable|numeric|between:-90,90', 'shipping_longitude' => 'nullable|numeric|between:-180,180']);
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
        if (! empty($data['address_id'])) {
            abort_unless(auth()->check(), 401);
            $customer = Customer::where('user_id', auth()->id())->firstOrFail();
            $data = array_merge($data, app(AddressBookService::class)->shipping($customer, $data['address_id']));
        }
        $policy = app(CheckoutPolicy::class);
        $charges = $policy->calculate($subtotal, $data);
        $token = Crypt::encryptString(json_encode(['user_id' => auth()->id(), 'expires' => now()->addMinutes(10)->timestamp, 'fingerprint' => $policy->fingerprint($data['items'], $charges, $data)]));

        return response()->json(['items' => $items, 'subtotal' => Money::decimal($subtotal), 'shipping' => Money::decimal($charges['shipping']), 'discount' => Money::decimal($charges['discount']), 'fee' => Money::decimal($charges['fee']), 'total' => Money::decimal($charges['total']), 'distance_meters' => $charges['distance_meters'], 'quote_token' => $token, 'errors' => $errors]);
    }

    public function checkout(Request $request)
    {
        $address = $request->validate(['address_id' => 'nullable|integer|min:1', 'quote_token' => 'nullable|string|max:10000', 'payment_channel' => 'nullable|string|max:30']);
        $point = $request->validate(DeliveryPoint::rules());
        $v = $request->validate(['request_key' => 'required|string|max:80', 'items' => 'required|array|min:1|max:100', 'items.*.product_id' => 'required|integer|exists:products,id', 'items.*.quantity' => 'required|integer|min:1|max:100000', 'delivery_type' => 'required|in:pickup,delivery', 'address' => 'required_if:delivery_type,delivery|nullable|string|max:2000', 'notes' => 'nullable|string|max:2000', 'payment_method' => 'required|in:cash,transfer,ewallet,qr,online']);
        $order = app(OrderService::class)->checkout(array_merge($v, $point, $address));

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
        $orders = $customer->orders()->with('items.product')->latest()->paginate(12);
        $transactions = $customer->debtTransactions()->latest()->paginate(20, ['*'], 'debt_page');

        $sales = $customer->sales()->with('items.product')->latest()->paginate(20, ['*'], 'sales_page');

        return view('customer.history', compact('customer', 'orders', 'transactions', 'sales'));
    }
}
