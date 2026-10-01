<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryTransaction::with('product', 'user');

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->product_id) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->latest()->paginate(20);

        $stockSummary = Product::where('is_active', true)
            ->selectRaw('*, (current_stock - minimum_stock) as stock_diff')
            ->orderBy('current_stock')
            ->get();

        $lowStockCount = Product::where('current_stock', '<=', DB::raw('minimum_stock'))->where('is_active', true)->count();
        $outOfStockCount = Product::where('current_stock', '<=', 0)->where('is_active', true)->count();

        return view('inventory.index', compact('transactions', 'stockSummary', 'lowStockCount', 'outOfStockCount'));
    }

    public function stockIn(Request $request)
    {
        return $this->move($request, 1);
    }

    public function stockOut(Request $request)
    {
        return $this->move($request, -1);
    }

    private function move(Request $request, int $sign)
    {
        $v = $request->validate(['product_id' => 'required|integer|exists:products,id', 'quantity' => 'required|integer|min:1|max:1000000', 'reason' => 'required|string|max:255', 'unit_cost' => 'nullable|decimal:0,2|min:0', 'notes' => 'nullable|string|max:2000']);
        app(StockService::class)->change($v['product_id'], $sign * $v['quantity'], $v['reason'], 'manual', null, $v['unit_cost'] ?? null, $v['notes'] ?? null);

        return back()->with('success', __('Stock updated successfully'));
    }

    public function adjust(Request $request)
    {
        $v = $request->validate(['product_id' => 'required|integer|exists:products,id', 'new_quantity' => 'required|integer|min:0|max:1000000', 'reason' => 'required|string|max:255']);
        \DB::transaction(function () use ($v) {
            $product = Product::lockForUpdate()->findOrFail($v['product_id']);
            app(StockService::class)->change($product->id, $v['new_quantity'] - $product->current_stock, $v['reason'], 'manual_adjustment');
        });

        return back()->with('success', __('Stock updated successfully'));
    }
}
