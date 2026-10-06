<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Services\ShiftService;
use App\Support\DeliveryPoint;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DeliveryPointTest extends TestCase
{
    use RefreshDatabase;

    private Customer $customer;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolesAndPermissionsSeeder::class, MasterDataSeeder::class]);
        $user = User::factory()->create();
        $user->assignRole('customer');
        $this->customer = Customer::create(['user_id' => $user->id, 'name' => 'Map customer', 'phone' => '081234567890', 'address' => 'House 7', 'latitude' => -6.2, 'longitude' => 106.8, 'is_active' => true]);
        $this->product = Product::where('sku', 'IND-001')->firstOrFail();
        $this->product->update(['is_available_online' => true]);
    }

    private function orderPayload(array $replace = []): array
    {
        return array_replace(['request_key' => (string) Str::uuid(), 'items' => [['product_id' => $this->product->id, 'quantity' => 1]], 'delivery_type' => 'delivery', 'address' => 'House 8', 'payment_method' => 'cash', 'shipping_latitude' => '-6.1234567', 'shipping_longitude' => '106.7654321'], $replace);
    }

    private function cashier(): void
    {
        $this->actingAs(User::where('email', 'owner@warbun.local')->firstOrFail());
        app(ShiftService::class)->open('0');
    }

    public function test_online_delivery_snapshots_coordinates_and_idempotent_retry_does_not_change_stock_or_point(): void
    {
        $this->actingAs($this->customer->user);
        $stock = $this->product->current_stock;
        $payload = $this->orderPayload();
        $this->post(route('customer.checkout'), $payload)->assertRedirect();
        $order = Order::where('request_key', $payload['request_key'])->firstOrFail();
        $this->assertSame('-6.1234567', $order->shipping_latitude);
        $this->assertSame('106.7654321', $order->shipping_longitude);
        $this->customer->update(['address' => 'New home', 'latitude' => 0, 'longitude' => 0]);
        $this->post(route('customer.checkout'), array_replace($payload, ['shipping_latitude' => 0, 'shipping_longitude' => 0]))->assertRedirect();
        $this->assertSame('-6.1234567', $order->fresh()->shipping_latitude);
        $this->assertSame($stock - 1, $this->product->fresh()->current_stock);
        $this->assertSame(1, Order::count());
        $this->get(route('customer.order.success', $order))->assertOk()->assertSee(__('Open delivery point'));
    }

    public function test_invalid_or_incomplete_order_coordinates_reject_without_reserving_stock(): void
    {
        $this->actingAs($this->customer->user);
        $stock = $this->product->current_stock;
        foreach ([['shipping_latitude' => 91], ['shipping_longitude' => -181], ['shipping_latitude' => 'javascript:alert(1)'], ['shipping_latitude' => null], ['shipping_longitude' => null]] as $invalid) {
            $this->postJson(route('customer.checkout'), $this->orderPayload($invalid))->assertUnprocessable();
        }
        $this->assertSame(0, Order::count());
        $this->assertSame($stock, $this->product->fresh()->current_stock);
    }

    public function test_manual_addresses_remain_valid_and_pickup_discards_map_point(): void
    {
        $this->actingAs($this->customer->user);
        $this->post(route('customer.checkout'), $this->orderPayload(['shipping_latitude' => null, 'shipping_longitude' => null]))->assertRedirect();
        $manual = Order::latest('id')->firstOrFail();
        $this->assertNull($manual->shipping_latitude);
        $this->post(route('customer.checkout'), $this->orderPayload(['delivery_type' => 'pickup']))->assertRedirect();
        $pickup = Order::latest('id')->firstOrFail();
        $this->assertNull($pickup->shipping_latitude);
        $this->assertNull($pickup->shipping_longitude);
        $this->assertSame('0.00', $pickup->shipping_cost);
    }

    public function test_cashier_delivery_records_zero_coordinates_and_monitor_and_receipt_expose_snapshot(): void
    {
        $this->cashier();
        $payload = ['request_key' => 'map-sale', 'items' => [['product_id' => $this->product->id, 'quantity' => 1]], 'customer_id' => $this->customer->id, 'fulfillment_type' => 'delivery', 'shipping_address' => 'House 9', 'shipping_latitude' => 0, 'shipping_longitude' => 0, 'payment_method' => 'cash', 'paid_amount' => 50000];
        $this->postJson(route('pos.process-sale'), $payload)->assertOk();
        $sale = Sale::where('request_key', 'map-sale')->firstOrFail();
        $this->assertSame('0.0000000', $sale->shipping_latitude);
        $this->assertSame('0.0000000', $sale->shipping_longitude);
        $this->postJson(route('pos.process-sale'), $payload)->assertOk();
        $this->assertSame(1, Sale::count());
        $this->get(route('orders.monitor'))->assertOk()->assertSee('mlat=0.0000000', false);
        $this->get(route('pos.receipt', $sale))->assertOk()->assertSee(__('Open delivery point'));
        $this->postJson(route('pos.process-sale'), array_replace($payload, ['request_key' => 'invalid-map', 'shipping_longitude' => null]))->assertUnprocessable();
        $this->postJson(route('pos.process-sale'), array_replace($payload, ['request_key' => 'store-map', 'fulfillment_type' => 'in_store']))->assertOk();
        $this->assertNull(Sale::where('request_key', 'store-map')->firstOrFail()->shipping_latitude);
    }

    public function test_new_cashier_customer_can_store_point_but_cannot_store_an_incomplete_pair(): void
    {
        $this->cashier();
        $payload = ['name' => 'Pinned home', 'phone' => '082111111111', 'address' => 'Home', 'latitude' => -6.2, 'longitude' => 106.8];
        $response = $this->postJson(route('pos.customers'), $payload)->assertCreated()->assertJsonPath('latitude', '-6.2000000');
        $customer = Customer::findOrFail($response->json('id'));
        $this->postJson(route('pos.customers'), array_replace($payload, ['phone' => '082222222222', 'longitude' => null]))->assertUnprocessable()->assertJsonValidationErrors('longitude');
        $this->put(route('customers.update', $customer), ['name' => 'Pinned home', 'address' => 'Different home', 'is_active' => 1])->assertRedirect();
        $this->assertNull($customer->fresh()->latitude);
        $this->assertNull($customer->fresh()->longitude);
    }

    public function test_map_links_are_fixed_provider_urls_and_do_not_bypass_order_authorization(): void
    {
        $this->assertNull(DeliveryPoint::url('javascript:alert(1)', 1));
        $this->assertNull(DeliveryPoint::url(null, 1));
        $this->assertNull(DeliveryPoint::url(1, 181));
        $this->assertNull(DeliveryPoint::url(NAN, 1));
        $this->assertStringStartsWith('https://www.openstreetmap.org/', DeliveryPoint::url(0, 0));
        $this->actingAs($this->customer->user);
        $this->post(route('customer.checkout'), $this->orderPayload())->assertRedirect();
        $order = Order::latest('id')->firstOrFail();
        $this->actingAs(User::factory()->create());
        $this->get(route('customer.order.success', $order))->assertForbidden();
        $this->get(route('orders.show', $order))->assertForbidden();
    }
}
