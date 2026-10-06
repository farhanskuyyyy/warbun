<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\CashierShift;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Support\DeliveryPoint;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SaleService
{
    public function process(array $data): Sale
    {
        return DB::transaction(function () use ($data) {
            User::lockForUpdate()->findOrFail(auth()->id());
            $existing = Sale::where('request_key', $data['request_key'])->first();
            if ($existing) {
                abort_unless($existing->user_id === auth()->id(), 403);

                return $existing;
            }
            $shift = CashierShift::where('user_id', auth()->id())->where('status', 'active')->lockForUpdate()->first();
            if (! $shift) {
                $this->invalid('No active cashier shift.');
            }
            $customer = ! empty($data['customer_id']) ? Customer::lockForUpdate()->findOrFail($data['customer_id']) : null;
            $delivery = ($data['fulfillment_type'] ?? 'in_store') === 'delivery';
            if ($delivery && ! empty($data['address_id'])) {
                if (! $customer) {
                    $this->invalid('Delivery requires an active customer, phone number, and address.');
                }
                $data = array_merge($data, app(AddressBookService::class)->shipping($customer, $data['address_id']));
            }
            if (($customer && ! $customer->is_active) || ($delivery && (! $customer || (! $customer->phone && empty($data['delivery_phone'])) || empty(trim($data['shipping_address'] ?? ''))))) {
                $this->invalid('Delivery requires an active customer, phone number, and address.');
            }
            $grouped = collect($data['items'])->groupBy('product_id')->sortKeys();
            $items = [];
            $subtotal = 0;
            foreach ($grouped as $id => $rows) {
                $product = Product::lockForUpdate()->findOrFail($id);
                $quantity = $rows->sum('quantity');
                if (! $product->is_active || $quantity > $product->current_stock) {
                    $this->invalid('Insufficient stock or inactive product.');
                }
                $price = Money::cents($product->selling_price);
                $requested = array_unique($rows->pluck('unit_price')->filter(fn ($v) => $v !== null)->all());
                if (count($requested) > 1) {
                    $this->invalid('Conflicting product prices.');
                }
                if ($requested && Money::cents(reset($requested)) !== $price) {
                    if (! auth()->user()->can('sales.override-price')) {
                        $this->invalid('Price override is not allowed.');
                    }
                    $price = Money::cents(reset($requested));
                }
                $line = Money::multiply($price, $quantity);
                $subtotal += $line;
                $items[] = ['product_id' => (int) $id, 'quantity' => $quantity, 'unit_price' => Money::decimal($price), 'subtotal' => Money::decimal($line)];
            }
            $discount = Money::cents($data['discount'] ?? 0);
            if ($discount > $subtotal || ($discount && ! auth()->user()->can('sales.override-discount'))) {
                $this->invalid('Discount is not allowed.');
            }
            $shipping = $delivery ? Money::cents(DB::table('store_settings')->where('key', 'shipping_cost')->value('value') ?? '10000') : 0;
            $total = $subtotal - $discount + $shipping;
            $paid = Money::cents($data['paid_amount']);
            $debt = $data['payment_method'] === 'debt';
            if ($total <= 0 || (! $debt && $paid < $total) || ($debt && ($paid >= $total || empty($data['customer_id'])))) {
                $this->invalid('Invalid payment or customer.');
            }
            $sale = Sale::create([
                ...DeliveryPoint::snapshot($data, $delivery),
                'fulfillment_type' => $delivery ? 'delivery' : 'in_store', 'fulfillment_status' => $delivery ? 'confirmed' : null,
                'shipping_address' => $delivery ? trim($data['shipping_address']) : null, 'shipping_cost' => Money::decimal($shipping), 'notes' => $data['notes'] ?? null,
                'sale_number' => 'SAL-'.Str::uuid(), 'request_key' => $data['request_key'], 'user_id' => auth()->id(), 'cashier_shift_id' => $shift->id,
                'customer_id' => $data['customer_id'] ?? null, 'subtotal' => Money::decimal($subtotal), 'discount' => Money::decimal($discount), 'tax' => 0,
                'total' => Money::decimal($total), 'payment_method' => $data['payment_method'], 'paid_amount' => Money::decimal($debt ? $paid : $total),
                'change_amount' => Money::decimal($debt ? 0 : $paid - $total), 'debt_amount' => Money::decimal($debt ? $total - $paid : 0), 'status' => 'completed',
            ]);
            foreach ($items as $item) {
                $sale->items()->create($item);
                app(StockService::class)->change($item['product_id'], -$item['quantity'], 'POS sale', 'sale', $sale->id);
            }
            if ($debt) {
                app(DebtService::class)->debit($sale->customer_id, $total - $paid, 'POS purchase', $sale->id, null, (bool) ($data['credit_override'] ?? false));
            }
            $received = $debt ? $paid : $total;
            if ($received > 0) {
                Payment::create([
                    'payment_number' => 'PAY-'.Str::uuid(), 'payable_type' => Sale::class, 'payable_id' => $sale->id, 'customer_id' => $sale->customer_id,
                    'amount' => Money::decimal($received), 'method' => $debt ? 'cash' : $data['payment_method'], 'status' => 'paid',
                    'user_id' => auth()->id(), 'cashier_shift_id' => $shift->id, 'paid_at' => now(),
                ]);
            }
            AuditLog::log('sale.created', $sale, null, $sale->toArray());

            return $sale;
        }, 3);
    }

    private function invalid(string $message): never
    {
        throw ValidationException::withMessages(['items' => __($message)]);
    }
}
