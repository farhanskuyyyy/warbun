<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Services\AddressBookService;
use App\Services\ShiftService;
use Database\Seeders\CustomerAddressSeeder;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AddressBookTest extends TestCase
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
        $this->customer = Customer::create(['user_id' => $user->id, 'name' => 'Address customer', 'phone' => '0811112233', 'is_active' => true]);
        $this->product = Product::where('sku', 'IND-001')->firstOrFail();
        $this->product->update(['is_available_online' => true]);
        $this->actingAs($user);
    }

    private function payload(array $replace = []): array
    {
        return array_replace(['label' => 'Home', 'recipient_name' => 'Recipient', 'phone' => '08123456789',
            'address' => 'House 12, blue gate', 'latitude' => '-6.2000000', 'longitude' => '106.8000000'], $replace);
    }

    private function address(array $replace = []): CustomerAddress
    {
        return app(AddressBookService::class)->save($this->customer, $this->payload($replace));
    }

    public function test_multiple_addresses_are_private_and_first_becomes_default(): void
    {
        $first = $this->postJson(route('customer.addresses.store'), $this->payload())->assertCreated()->json('id');
        $this->postJson(route('customer.addresses.store'), $this->payload(['label' => 'Office', 'address' => 'Office 20']))->assertCreated()->assertJsonPath('is_default', false);
        $this->getJson(route('customer.addresses.index'))->assertOk()->assertJsonCount(2)->assertJsonPath('0.id', $first);
        $this->assertSame('House 12, blue gate', $this->customer->fresh()->address);
        $this->get(route('customer.addresses'))->assertOk()->assertSee(__('My addresses'));
    }

    public function test_updating_default_promoting_deleting_and_deleting_last_preserve_single_default(): void
    {
        $first = $this->address();
        $second = $this->address(['label' => 'Office']);
        $this->putJson(route('customer.addresses.update', $second), $this->payload(['label' => 'Office', 'address' => 'Office new', 'is_default' => true]))->assertOk();
        $this->assertFalse($first->fresh()->is_default);
        $this->assertSame('Office new', $this->customer->fresh()->address);
        $this->putJson(route('customer.addresses.update', $second), $this->payload(['is_default' => false]))->assertOk();
        $this->assertSame(1, $this->customer->addresses()->where('is_default', true)->count());
        $this->deleteJson(route('customer.addresses.destroy', $second))->assertNoContent();
        $this->assertTrue($first->fresh()->is_default);
        $this->deleteJson(route('customer.addresses.destroy', $first))->assertNoContent();
        $this->assertNull($this->customer->fresh()->address);
        $this->assertNull($this->customer->fresh()->latitude);
        $this->assertSame(0, $this->customer->addresses()->count());
    }

    public function test_another_customer_cannot_read_edit_delete_or_use_an_address(): void
    {
        $address = $this->address();
        $other = User::factory()->create();
        Customer::create(['user_id' => $other->id, 'name' => 'Other', 'is_active' => true]);
        $this->actingAs($other);
        $this->getJson(route('customer.addresses.index'))->assertOk()->assertJsonCount(0);
        $this->putJson(route('customer.addresses.update', $address), $this->payload())->assertNotFound();
        $this->deleteJson(route('customer.addresses.destroy', $address))->assertNotFound();
        $stock = $this->product->current_stock;
        $this->postJson(route('customer.checkout'), $this->orderPayload($address->id))->assertUnprocessable()->assertJsonValidationErrors('address_id');
        $this->assertSame($stock, $this->product->fresh()->current_stock);
        $this->assertSame(0, Order::count());
    }

    private function orderPayload(int $id, array $replace = []): array
    {
        return array_replace(['request_key' => (string) Str::uuid(), 'address_id' => $id,
            'address' => 'Forged address', 'shipping_latitude' => 0, 'shipping_longitude' => 0,
            'items' => [['product_id' => $this->product->id, 'quantity' => 1]], 'delivery_type' => 'delivery', 'payment_method' => 'cash'], $replace);
    }

    public function test_online_uses_selected_record_and_snapshot_survives_edit_delete_and_retry(): void
    {
        $address = $this->address();
        $payload = $this->orderPayload($address->id);
        $this->post(route('customer.checkout'), $payload)->assertRedirect();
        $order = Order::where('request_key', $payload['request_key'])->firstOrFail();
        $this->assertSame($address->shippingText(), $order->shipping_address);
        $this->assertSame('-6.2000000', $order->shipping_latitude);
        $this->putJson(route('customer.addresses.update', $address), $this->payload(['address' => 'Moved']))->assertOk();
        $this->deleteJson(route('customer.addresses.destroy', $address))->assertNoContent();
        $this->post(route('customer.checkout'), $payload)->assertRedirect();
        $this->assertSame($address->shippingText(), $order->fresh()->shipping_address);
        $this->assertSame(1, Order::count());
        $this->postJson(route('customer.checkout'), $this->orderPayload($address->id))->assertUnprocessable();
        $this->post(route('customer.checkout'), $this->orderPayload($address->id, ['delivery_type' => 'pickup']))->assertRedirect();
        $this->assertNull(Order::latest('id')->first()->shipping_latitude);
    }

    public function test_address_validation_and_mass_assignment_cannot_change_owner(): void
    {
        foreach ([['label' => ''], ['recipient_name' => ''], ['phone' => ''], ['address' => ''], ['latitude' => 91], ['longitude' => null], ['address' => str_repeat('x', 2001)]] as $bad) {
            $this->postJson(route('customer.addresses.store'), $this->payload($bad))->assertUnprocessable();
        }
        $this->assertSame(0, $this->customer->addresses()->count());
        $this->postJson(route('customer.addresses.store'), $this->payload(['customer_id' => 999999, 'latitude' => 0, 'longitude' => 0]))->assertCreated()->assertJsonPath('customer_id', $this->customer->id);
    }

    public function test_guests_inactive_users_and_backoffice_permission_are_enforced(): void
    {
        auth()->logout();
        $this->getJson(route('customer.addresses.index'))->assertUnauthorized();
        $this->postJson(route('customer.addresses.store'), $this->payload())->assertUnauthorized();
        $this->actingAs($this->customer->user);
        $this->getJson(route('pos.addresses.index', $this->customer))->assertForbidden();
        $this->postJson(route('pos.addresses.store', $this->customer), $this->payload())->assertForbidden();
        $this->customer->user->update(['is_active' => false]);
        $this->getJson(route('customer.addresses.index'))->assertForbidden();
    }

    public function test_pos_can_save_multiple_destinations_and_rejects_cross_customer_selection(): void
    {
        $this->actingAs(User::where('email', 'owner@warbun.local')->firstOrFail());
        app(ShiftService::class)->open('0');
        $addressId = $this->postJson(route('pos.addresses.store', $this->customer), $this->payload())->assertCreated()->json('id');
        $this->postJson(route('pos.addresses.store', $this->customer), $this->payload(['label' => 'Office']))->assertCreated();
        $this->getJson(route('pos.addresses.index', $this->customer))->assertOk()->assertJsonCount(2);
        $payload = ['request_key' => 'book-sale', 'customer_id' => $this->customer->id, 'address_id' => $addressId,
            'fulfillment_type' => 'delivery', 'shipping_address' => 'Forged', 'shipping_latitude' => 0, 'shipping_longitude' => 0,
            'items' => [['product_id' => $this->product->id, 'quantity' => 1]], 'payment_method' => 'cash', 'paid_amount' => 50000];
        $this->postJson(route('pos.process-sale'), $payload)->assertOk();
        $sale = Sale::where('request_key', 'book-sale')->firstOrFail();
        $this->assertSame('-6.2000000', $sale->shipping_latitude);
        $this->assertSame(CustomerAddress::find($addressId)->shippingText(), $sale->shipping_address);
        $other = Customer::create(['name' => 'Other', 'phone' => '089991', 'is_active' => true]);
        $this->postJson(route('pos.process-sale'), array_replace($payload, ['request_key' => 'wrong-book', 'customer_id' => $other->id]))->assertUnprocessable()->assertJsonValidationErrors('address_id');
        $this->postJson(route('pos.process-sale'), $payload)->assertOk();
        $this->assertSame(1, Sale::count());
        $this->postJson(route('pos.process-sale'), array_replace($payload, ['request_key' => 'in-store-book', 'fulfillment_type' => 'in_store']))->assertOk();
        $this->assertNull(Sale::latest('id')->first()->shipping_latitude);
    }

    public function test_legacy_import_and_seeder_are_idempotent(): void
    {
        $this->customer->update(['address' => 'Legacy house', 'latitude' => 0, 'longitude' => 0]);
        $this->seed(CustomerAddressSeeder::class);
        $this->seed(CustomerAddressSeeder::class);
        $this->assertSame(1, $this->customer->addresses()->count());
        $this->assertSame('0.0000000', $this->customer->addresses()->first()->latitude);
        $this->assertTrue($this->customer->addresses()->first()->is_default);
    }

    public function test_migration_backfills_existing_addresses_without_touching_order_snapshots(): void
    {
        $this->customer->update(['address' => 'Legacy house', 'latitude' => 0, 'longitude' => 0]);
        $migration = require database_path('migrations/2026_10_06_230000_create_customer_addresses.php');
        $migration->down();
        $migration->up();
        $address = $this->customer->addresses()->firstOrFail();
        $this->assertSame('Legacy house', $address->address);
        $this->assertSame('0.0000000', $address->latitude);
        $this->assertSame('Alamat utama', $address->label);
    }

    public function test_full_length_address_with_recipient_phone_supports_pos_customer_without_account_phone(): void
    {
        $address = $this->address(['address' => str_repeat('a', 2000), 'recipient_name' => str_repeat('b', 255)]);
        $this->customer->update(['phone' => null]);
        $this->actingAs(User::where('email', 'owner@warbun.local')->firstOrFail());
        app(ShiftService::class)->open('0');
        $this->postJson(route('pos.process-sale'), [
            'request_key' => 'recipient-phone', 'customer_id' => $this->customer->id, 'address_id' => $address->id,
            'fulfillment_type' => 'delivery', 'shipping_address' => $address->address,
            'items' => [['product_id' => $this->product->id, 'quantity' => 1]], 'payment_method' => 'cash', 'paid_amount' => 50000,
        ])->assertOk();
        $sale = Sale::where('request_key', 'recipient-phone')->firstOrFail();
        $this->assertSame($address->shippingText(), $sale->shipping_address);
    }
}
