<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CheckoutPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CheckoutPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivery_boundaries_and_fee_rounding(): void
    {
        $policy = app(CheckoutPolicy::class);
        foreach ([0, 4999, 4999.999] as $distance) {
            $this->assertSame(0, $policy->shippingForDistance($distance));
        }
        foreach ([5000, 19999.999] as $distance) {
            $this->assertSame(1000000, $policy->shippingForDistance($distance));
        }
        DB::table('store_settings')->insert(['key' => 'qris_fee_bps', 'value' => '70', 'created_at' => now(), 'updated_at' => now()]);
        $quote = $policy->calculate(2500000, ['delivery_type' => 'pickup', 'payment_method' => 'qr']);
        $this->assertSame(17500, $quote['fee']);
        $this->assertSame(2517500, $quote['total']);
        $delivery = $policy->calculate(2500000, ['delivery_type' => 'delivery', 'payment_method' => 'qr']);
        $this->assertSame(24500, $delivery['fee']);
        $this->assertSame(3524500, $delivery['total']);
        DB::table('store_settings')->insert(['key' => 'ewallet_fee', 'value' => '2000', 'created_at' => now(), 'updated_at' => now()]);
        $wallet = $policy->calculate(2500000, ['delivery_type' => 'delivery', 'payment_method' => 'ewallet']);
        $this->assertSame(200000, $wallet['fee']);
        $this->assertSame(3700000, $wallet['total']);
        $rounded = $policy->calculate(350025, ['delivery_type' => 'pickup', 'payment_method' => 'qr']);
        $this->assertSame(2500, $rounded['fee']);
        DB::table('store_settings')->insert(['key' => 'payment_promo_methods', 'value' => 'qr', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('store_settings')->insert(['key' => 'payment_promo_flat', 'value' => '1000', 'created_at' => now(), 'updated_at' => now()]);
        $this->assertSame(0, $policy->calculate(2500000, ['delivery_type' => 'pickup', 'payment_method' => 'cash'])['discount']);
        $this->assertSame(100000, $policy->calculate(2500000, ['delivery_type' => 'pickup', 'payment_method' => 'qr'])['discount']);
        $this->expectException(ValidationException::class);
        $policy->shippingForDistance(20000);
    }

    public function test_enabled_radius_cannot_default_to_free_without_pin(): void
    {
        DB::table('store_settings')->insert(['key' => 'radius_enabled', 'value' => '1', 'created_at' => now(), 'updated_at' => now()]);
        $this->expectException(ValidationException::class);
        app(CheckoutPolicy::class)->calculate(10000, ['delivery_type' => 'delivery']);
    }

    public function test_stale_quote_is_rejected_and_successful_order_keeps_price_name_and_fee_snapshots(): void
    {
        $this->seed();
        $this->actingAs(User::where('email', 'customer@warbun.local')->firstOrFail());
        $product = Product::where('sku', 'IND-001')->firstOrFail();
        $payload = ['items' => [['product_id' => $product->id, 'quantity' => 1]], 'delivery_type' => 'pickup', 'payment_method' => 'qr'];
        DB::table('store_settings')->updateOrInsert(['key' => 'qris_fee_bps'], ['value' => '70', 'created_at' => now(), 'updated_at' => now()]);
        $this->assertSame(app(CheckoutPolicy::class)->fingerprint($payload['items'], [], $payload), app(CheckoutPolicy::class)->fingerprint($payload['items'], [], $payload + ['address_id' => 999]));
        $quote = $this->postJson(route('customer.cart.quote'), $payload)->assertOk()->json();
        $product->update(['selling_price' => '25000']);
        $this->postJson(route('customer.checkout'), $payload + ['request_key' => 'stale-quote-test', 'quote_token' => $quote['quote_token']])->assertUnprocessable();
        $quote = $this->postJson(route('customer.cart.quote'), $payload)->assertOk()->json();
        $this->post(route('customer.checkout'), $payload + ['request_key' => 'snapshot-test', 'quote_token' => $quote['quote_token']])->assertRedirect();
        $order = Order::where('request_key', 'snapshot-test')->firstOrFail();
        $this->assertSame('175.00', $order->service_fee);
        $this->assertSame('25175.00', $order->total);
        $name = $order->items->first()->product_name;
        $product->update(['name' => 'Renamed later', 'selling_price' => '1']);
        $this->assertSame($name, $order->fresh()->items->first()->product_name);
        $this->assertSame('25175.00', $order->fresh()->total);
        $this->travel(11)->minutes();
        $this->postJson(route('customer.checkout'), $payload + ['request_key' => 'expired-test', 'quote_token' => $quote['quote_token']])->assertUnprocessable();
    }
}
