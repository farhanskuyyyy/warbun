<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OperationalNotification;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function checkout(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            User::lockForUpdate()->findOrFail(auth()->id());
            $customer = Customer::where('user_id', auth()->id())->where('is_active', true)->lockForUpdate()->firstOrFail();
            $old = Order::where('request_key', $data['request_key'])->first();
            if ($old) {
                abort_unless($old->customer_id === $customer->id, 403);

                return $old;
            }
            $items = [];
            $subtotal = 0;
            if (($data['payment_method'] ?? '') === 'online' && config('payments.gateway') !== 'signed-webhook') {
                $this->invalid('Online gateway is not configured.');
            }
            foreach (collect($data['items'])->groupBy('product_id')->sortKeys() as $id => $rows) {
                $product = Product::lockForUpdate()->findOrFail($id);
                $qty = $rows->sum('quantity');
                if (! $product->is_active || ! $product->is_available_online || $qty > $product->current_stock) {
                    $this->invalid('Insufficient stock or inactive product.');
                }
                $line = Money::multiply(Money::cents($product->selling_price), $qty);
                $subtotal += $line;
                $items[] = ['product_id' => (int) $id, 'quantity' => $qty, 'unit_price' => $product->selling_price, 'subtotal' => Money::decimal($line)];
            }
            $shipping = $data['delivery_type'] === 'delivery' ? Money::cents(DB::table('store_settings')->where('key', 'shipping_cost')->value('value') ?? '10000') : 0;
            $order = Order::create(['order_number' => 'ORD-'.Str::uuid(), 'request_key' => $data['request_key'], 'customer_id' => $customer->id, 'subtotal' => Money::decimal($subtotal), 'shipping_cost' => Money::decimal($shipping), 'total' => Money::decimal($subtotal + $shipping), 'fulfillment_type' => $data['delivery_type'], 'shipping_address' => $data['address'] ?? null, 'notes' => $data['notes'] ?? null, 'status' => 'pending', 'payment_status' => 'pending']);
            foreach ($items as $item) {
                $order->items()->create($item);
                app(StockService::class)->change($item['product_id'], -$item['quantity'], 'Online order reservation', 'order', $order->id);
            }
            Payment::create(['payment_number' => 'PAY-'.Str::uuid(), 'payable_type' => Order::class, 'payable_id' => $order->id, 'customer_id' => $customer->id, 'amount' => $order->total, 'method' => $data['payment_method'] ?? 'transfer', 'status' => 'pending', 'user_id' => auth()->id(), 'expires_at' => now()->addDay()]);
            AuditLog::log('order.created', $order, null, $order->toArray());
            auth()->user()->notify(new OperationalNotification('order.created', $order->id));

            return $order;
        }, 3);
    }

    public function transition(Order $order, string $status): Order
    {
        return DB::transaction(function () use ($order, $status) {
            $order = Order::lockForUpdate()->findOrFail($order->id);
            $permission = match ($status) {
                'confirmed' => 'orders.confirm','completed' => 'orders.complete','cancelled' => 'orders.cancel',default => 'orders.update'
            };
            abort_unless(auth()->user()->can($permission), 403);
            $next = ['pending' => ['confirmed', 'cancelled'], 'confirmed' => ['preparing', 'cancelled'], 'preparing' => ['ready', 'cancelled'], 'ready' => ['completed', 'cancelled']];
            if (! in_array($status, $next[$order->status] ?? [])) {
                $this->invalid('Invalid order transition.');
            }
            if ($status === 'completed' && $order->payment_status !== 'paid') {
                $this->invalid('Order must be paid before completion.');
            }
            if ($status === 'cancelled') {
                if ($order->payments()->whereIn('status', ['paid', 'refunded'])->exists() || in_array($order->payment_status, ['paid', 'partial'])) {
                    $this->invalid('Paid order requires a refund.');
                }
                foreach ($order->items()->orderBy('product_id')->get() as $item) {
                    app(StockService::class)->change($item->product_id, $item->quantity, 'Order cancelled', 'order_cancel', $order->id);
                }
                $order->payments()->where('status', 'pending')->update(['status' => 'cancelled']);
            }
            $old = $order->status;
            $order->update(['status' => $status]);
            AuditLog::log('order.status_updated', $order, ['status' => $old], ['status' => $status]);

            return $order;
        }, 3);
    }

    private function invalid(string $message): never
    {
        throw ValidationException::withMessages(['status' => __($message)]);
    }
}
