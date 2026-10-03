<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderMonitorController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate(['status' => 'nullable|in:active,pending,confirmed,preparing,ready,completed,cancelled,refunded', 'search' => 'nullable|string|max:100']);
        $status = $filters['status'] ?? 'active';
        $search = $filters['search'] ?? '';
        $counts = [];
        foreach (['pending', 'confirmed', 'preparing', 'ready'] as $stage) {
            $counts[$stage] = Order::where('status', $stage)->count() + Sale::where('fulfillment_type', 'delivery')->where('status', 'completed')->where('fulfillment_status', $stage)->count();
        }
        $online = Order::query()->selectRaw("id, created_at, status as stage, 'online' as source");
        $deliveries = Sale::query()->where('fulfillment_type', 'delivery')->selectRaw("id, created_at, CASE WHEN status = 'refunded' THEN 'refunded' ELSE fulfillment_status END as stage, 'pos' as source");
        foreach ([$online, $deliveries] as $query) {
            if ($search !== '') {
                $reference = $query->getModel() instanceof Order ? 'order_number' : 'sale_number';
                $query->where(function ($q) use ($search, $reference) {
                    $q->where($reference, 'like', '%'.$search.'%')->orWhereHas('customer', fn ($c) => $c->where('name', 'like', '%'.$search.'%')->orWhere('phone', 'like', '%'.$search.'%'));
                });
            }
        }
        $queue = DB::query()->fromSub($online->unionAll($deliveries)->toBase(), 'queue');
        $status === 'active' ? $queue->whereIn('stage', ['pending', 'confirmed', 'preparing', 'ready']) : $queue->where('stage', $status);
        $entries = $queue->orderBy('created_at', $status === 'active' ? 'asc' : 'desc')->orderBy('source')->orderBy('id')->paginate(12)->withQueryString();
        $orders = Order::with('customer', 'items.product.shelf')->whereIn('id', $entries->where('source', 'online')->pluck('id'))->get()->keyBy('id');
        $sales = Sale::with('customer', 'items.product.shelf')->whereIn('id', $entries->where('source', 'pos')->pluck('id'))->get()->keyBy('id');

        return view('orders.monitor', compact('entries', 'orders', 'sales', 'counts', 'status', 'search'));
    }

    public function updateDelivery(Request $request, Sale $sale)
    {
        $data = $request->validate(['status' => 'required|in:preparing,ready,completed']);
        abort_if($data['status'] === 'completed' && ! $request->user()->can('orders.complete'), 403);
        DB::transaction(function () use ($sale, $data) {
            $locked = Sale::lockForUpdate()->findOrFail($sale->id);
            $next = ['confirmed' => 'preparing', 'preparing' => 'ready', 'ready' => 'completed'];
            if ($locked->fulfillment_type !== 'delivery' || $locked->status !== 'completed' || ($next[$locked->fulfillment_status] ?? null) !== $data['status']) {
                throw ValidationException::withMessages(['status' => __('Invalid order transition.')]);
            }
            $before = $locked->fulfillment_status;
            $locked->update(['fulfillment_status' => $data['status']]);
            AuditLog::log('sale.fulfillment_updated', $locked, ['fulfillment_status' => $before], ['fulfillment_status' => $data['status']]);
        }, 3);

        return back()->with('success', __('Order status updated'));
    }
}
