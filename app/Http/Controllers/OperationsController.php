<?php

namespace App\Http\Controllers;

use App\Models\CashierShift;
use App\Models\Order;
use App\Models\Product;
use App\Models\Refund;
use App\Models\Sale;
use App\Models\StockOpname;
use App\Services\DebtService;
use App\Services\OpnameService;
use App\Services\RefundService;
use App\Support\Money;
use Illuminate\Http\Request;

class OperationsController extends Controller
{
    public function opnames()
    {
        $opnames = StockOpname::with('items.product')->latest()->paginate(20);
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('operations.opnames', compact('opnames', 'products'));
    }

    public function createOpname(Request $request)
    {
        $v = $request->validate(['reason' => 'required|string|max:255', 'items' => 'required|array|min:1|max:1000', 'items.*.product_id' => 'required|integer|distinct|exists:products,id', 'items.*.physical_stock' => 'required|integer|min:0|max:1000000']);
        app(OpnameService::class)->create($v);

        return back()->with('success', __('Stock count submitted'));
    }

    public function approveOpname(StockOpname $opname)
    {
        app(OpnameService::class)->approve($opname);

        return back()->with('success', __('Stock count approved'));
    }

    public function refunds()
    {
        $refunds = Refund::with('items')->latest()->paginate(20);

        return view('operations.refunds', compact('refunds'));
    }

    public function refund(Request $request)
    {
        $v = $request->validate(['source' => 'required|in:sale,order', 'source_id' => 'required|integer', 'reason' => 'required|string|max:255', 'request_key' => 'required|string|max:80', 'items' => 'required|array|min:1', 'items.*.product_id' => 'required|integer|distinct', 'items.*.quantity' => 'required|integer|min:1']);
        $source = ($v['source'] === 'sale' ? Sale::class : Order::class)::findOrFail($v['source_id']);
        app(RefundService::class)->process($source, $v['items'], $v['reason'], $v['request_key']);

        return back()->with('success', __('Refund recorded'));
    }

    public function shifts()
    {
        $shifts = CashierShift::with('user')->when(! auth()->user()->can('reports.staff'), fn ($q) => $q->where('user_id', auth()->id()))->latest()->paginate(30);

        return view('operations.shifts', compact('shifts'));
    }

    public function correction(Request $request)
    {
        $v = $request->validate(['customer_id' => 'required|integer|exists:customers,id', 'amount' => 'required|decimal:0,2|min:0.01', 'reason' => 'required|string|max:255', 'type' => 'required|in:adjustment,write_off']);
        abort_unless(auth()->user()->can($v['type'] === 'write_off' ? 'debt.write-off' : 'debt.adjust'), 403);
        app(DebtService::class)->credit($v['customer_id'], Money::cents($v['amount']), $v['reason'], $v['type']);

        return back()->with('success', __('Debt correction recorded'));
    }
}
