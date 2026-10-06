<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\CashierShift;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Shelf;
use App\Models\User;
use App\Services\SaleService;
use App\Services\ShiftService;
use App\Support\DeliveryPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PosController extends Controller
{
    public function index()
    {
        $activeShift = CashierShift::where('user_id', auth()->id())->where('status', 'active')->first();
        $recentSales = Sale::where('user_id', auth()->id())->latest()->take(10)->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $customers = Customer::with('user', 'debtAccount')->where('is_active', true)->orderBy('name')->get();

        $shelves = Shelf::withCount(['products' => fn ($q) => $q->where('is_active', true)->where('current_stock', '>', 0)])->orderBy('number')->get();

        return view('pos.index', compact('activeShift', 'recentSales', 'categories', 'customers', 'shelves'));
    }

    public function createCustomer(Request $request)
    {
        $data = $request->validate([
            ...DeliveryPoint::rules(''),
            'name' => 'required|string|max:255', 'phone' => 'required|string|max:20|unique:customers,phone',
            'address' => 'required|string|max:2000', 'create_account' => 'sometimes|boolean',
            'email' => 'nullable|required_if:create_account,true|email|max:255|unique:customers,email|unique:users,email',
            'password' => 'nullable|required_if:create_account,true|string|min:8|max:255|confirmed',
        ]);
        $customer = DB::transaction(function () use ($data, $request) {
            $user = null;
            if ($request->boolean('create_account')) {
                $user = User::create(['name' => $data['name'], 'email' => $data['email'], 'password' => Hash::make($data['password']), 'is_active' => true]);
                $user->assignRole('customer');
            }
            $customer = Customer::create(['name' => $data['name'], 'phone' => $data['phone'], 'address' => $data['address'],
                'latitude' => $data['latitude'] ?? null, 'longitude' => $data['longitude'] ?? null,
                'email' => $data['email'] ?? null, 'user_id' => $user?->id, 'is_active' => true,
                'can_use_debt' => false, 'credit_limit' => 0, 'outstanding_balance' => 0, 'debt_status' => 'restricted']);
            AuditLog::log('customer.created', $customer, null, $customer->toArray());

            return $customer;
        });

        return response()->json($customer->only(['id', 'name', 'phone', 'address', 'latitude', 'longitude']), 201);
    }

    public function products(Request $request)
    {
        $request->validate(['search' => 'nullable|string|max:255', 'category_id' => 'nullable|integer|exists:categories,id', 'shelf_id' => 'nullable|integer|exists:shelves,id', 'unassigned' => 'sometimes|boolean', 'page' => 'sometimes|integer|min:1']);
        $query = Product::with('shelf', 'category', 'unit')->where('is_active', true)->where('current_stock', '>', 0);
        if ($request->filled('shelf_id')) {
            $query->where('shelf_id', $request->shelf_id);
        } elseif ($request->boolean('unassigned')) {
            $query->whereNull('shelf_id');
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('sku', 'like', "%{$request->search}%")
                    ->orWhere('barcode', 'like', "%{$request->search}%");
            });
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->orderBy('name')->orderBy('id')->paginate(60);

        return response()->json($products->getCollection()->map(fn ($p) => array_merge($p->only(['id', 'name', 'sku', 'barcode', 'selling_price', 'current_stock', 'description']), ['location' => $p->location_label, 'category' => $p->category?->name, 'unit' => $p->unit?->name])))->header('X-Has-More', $products->hasMorePages() ? '1' : '0');
    }

    public function processSale(Request $request)
    {
        $point = $request->validate(DeliveryPoint::rules());
        $request->validate(['fulfillment_type' => 'sometimes|in:in_store,delivery', 'shipping_address' => 'nullable|required_if:fulfillment_type,delivery|string|max:2000', 'notes' => 'nullable|string|max:2000']);
        $request->validate(['print_receipt' => 'sometimes|boolean', 'receipt_paper' => 'sometimes|in:58,80']);
        $data = $request->validate(['request_key' => 'required|string|max:80', 'items' => 'required|array|min:1|max:100', 'items.*.product_id' => 'required|integer|exists:products,id', 'items.*.quantity' => 'required|integer|min:1|max:100000', 'items.*.unit_price' => 'nullable|decimal:0,2|min:0', 'customer_id' => 'nullable|integer|exists:customers,id', 'payment_method' => 'required|in:cash,transfer,ewallet,qr,debt', 'discount' => 'nullable|decimal:0,2|min:0', 'paid_amount' => 'required|decimal:0,2|min:0', 'credit_override' => 'sometimes|boolean']);
        $sale = app(SaleService::class)->process(array_merge($data, $point, $request->only('fulfillment_type', 'shipping_address', 'notes')));
        $receiptUrl = route('pos.receipt', ['sale' => $sale, 'print' => $request->boolean('print_receipt') ? 1 : null, 'paper' => $request->input('receipt_paper')]);
        if ($request->expectsJson()) {
            return response()->json(['redirect' => $receiptUrl]);
        }

        return redirect()->to($receiptUrl)->with('success', __('Sale completed!'));
    }

    public function barcode(Request $request)
    {
        $data = $request->validate(['barcode' => 'required|string|max:50']);
        $product = Product::where('barcode', $data['barcode'])->where('is_active', true)->first();
        if (! $product) {
            return response()->json(['message' => __('Barcode not found. Check the product barcode in the catalog.')], 404);
        }
        if ($product->current_stock <= 0) {
            return response()->json(['message' => __('This product is out of stock.')], 409);
        }

        return response()->json($product->only(['id', 'name', 'sku', 'barcode', 'selling_price', 'current_stock']));
    }

    public function receipt(Sale $sale)
    {
        abort_unless($sale->user_id === auth()->id() || auth()->user()->can('reports.staff'), 403);
        $sale->load('items.product', 'customer', 'user');

        return view('pos.receipt', compact('sale'));
    }

    public function history(Request $request)
    {
        $query = Sale::with('user', 'customer');
        if (! auth()->user()->can('reports.staff')) {
            $query->where('user_id', auth()->id());
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $sales = $query->latest()->paginate(20);

        return view('pos.history', compact('sales'));
    }

    public function openShift(Request $request)
    {
        $v = $request->validate(['opening_cash' => 'required|decimal:0,2|min:0']);
        app(ShiftService::class)->open((string) $v['opening_cash']);

        return back()->with('success', __('Shift opened successfully'));
    }

    public function closeShift(Request $request)
    {
        $v = $request->validate(['closing_cash' => 'required|decimal:0,2|min:0']);
        app(ShiftService::class)->close((string) $v['closing_cash']);

        return back()->with('success', __('Shift closed successfully'));
    }
}
