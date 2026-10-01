<?php

namespace App\Http\Controllers;

use App\Models\CashierShift;
use App\Models\Product;
use App\Models\Sale;
use App\Services\SaleService;
use App\Services\ShiftService;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function index()
    {
        $activeShift = CashierShift::where('user_id', auth()->id())->where('status', 'active')->first();
        $recentSales = Sale::where('user_id', auth()->id())->latest()->take(10)->get();

        return view('pos.index', compact('activeShift', 'recentSales'));
    }

    public function products(Request $request)
    {
        $query = Product::where('is_active', true)->where('current_stock', '>', 0);

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

        $products = $query->orderBy('name')->limit(60)->get();

        return response()->json($products->map(fn ($p) => $p->only(['id', 'name', 'sku', 'barcode', 'selling_price', 'current_stock'])));
    }

    public function processSale(Request $request)
    {
        $request->validate(['print_receipt' => 'sometimes|boolean', 'receipt_paper' => 'sometimes|in:58,80']);
        $data = $request->validate(['request_key' => 'required|string|max:80', 'items' => 'required|array|min:1|max:100', 'items.*.product_id' => 'required|integer|exists:products,id', 'items.*.quantity' => 'required|integer|min:1|max:100000', 'items.*.unit_price' => 'nullable|decimal:0,2|min:0', 'customer_id' => 'nullable|integer|exists:customers,id', 'payment_method' => 'required|in:cash,transfer,ewallet,qr,debt', 'discount' => 'nullable|decimal:0,2|min:0', 'paid_amount' => 'required|decimal:0,2|min:0', 'credit_override' => 'sometimes|boolean']);
        $sale = app(SaleService::class)->process($data);
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
