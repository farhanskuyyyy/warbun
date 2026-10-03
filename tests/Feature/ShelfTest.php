<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shelf;
use App\Models\User;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\ShelfSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShelfTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolesAndPermissionsSeeder::class, MasterDataSeeder::class]);
        $this->actingAs(User::where('email', 'owner@warbun.local')->firstOrFail());
        $this->product = Product::where('sku', 'IND-001')->firstOrFail();
    }

    public function test_placement_changes_only_location_and_can_be_cleared(): void
    {
        $shelf = Shelf::create(['number' => 1, 'name' => 'Snack rack']);
        $before = $this->product->only('current_stock', 'cost_price', 'selling_price');
        $movements = InventoryTransaction::count();
        $this->put(route('shelves.place', $this->product), ['shelf_id' => $shelf->id, 'shelf_position' => 'Top left', 'current_stock' => 999])->assertRedirect();
        $this->assertSame($before, $this->product->fresh()->only('current_stock', 'cost_price', 'selling_price'));
        $this->assertSame($movements, InventoryTransaction::count());
        $this->getJson(route('pos.products', ['shelf_id' => $shelf->id]))->assertOk()->assertJsonCount(1)->assertJsonPath('0.location', 'Etalase 1 · Snack rack / Top left');
        $this->put(route('shelves.place', $this->product), ['shelf_id' => null, 'shelf_position' => 'old position'])->assertRedirect();
        $this->assertNull($this->product->fresh()->shelf_position);
        $this->assertNull($this->product->fresh()->shelf_id);
    }

    public function test_cashier_can_read_but_cannot_manage_and_customer_cannot_read(): void
    {
        $shelf = Shelf::create(['number' => 1, 'name' => 'Drinks']);
        $this->actingAs(User::where('email', 'kasir@warbun.local')->firstOrFail());
        $this->get(route('shelves.index'))->assertOk()->assertDontSee('name="shelf_position"', false);
        $this->postJson(route('shelves.store'), ['number' => 2, 'name' => 'Other'])->assertForbidden();
        $this->putJson(route('shelves.update', $shelf), ['number' => 2, 'name' => 'Other'])->assertForbidden();
        $this->putJson(route('shelves.place', $this->product), ['shelf_id' => $shelf->id])->assertForbidden();
        $this->deleteJson(route('shelves.destroy', $shelf))->assertForbidden();
        $this->actingAs(User::factory()->create());
        $this->get(route('shelves.index'))->assertForbidden();
        $this->getJson(route('pos.products', ['shelf_id' => $shelf->id]))->assertForbidden();
    }

    public function test_shelf_validation_and_deletion_protect_assigned_and_archived_products(): void
    {
        $this->post(route('shelves.store'), ['number' => 1, 'name' => 'Food'])->assertRedirect();
        $shelf = Shelf::firstOrFail();
        $this->postJson(route('shelves.store'), ['number' => 1, 'name' => 'Duplicate'])->assertUnprocessable();
        $this->postJson(route('shelves.store'), ['number' => 0, 'name' => ''])->assertUnprocessable();
        $this->putJson(route('shelves.place', $this->product), ['shelf_id' => 99999])->assertUnprocessable();
        $this->putJson(route('shelves.place', $this->product), ['shelf_position' => str_repeat('x', 101)])->assertUnprocessable();
        $this->product->update(['shelf_id' => $shelf->id]);
        $this->deleteJson(route('shelves.destroy', $shelf))->assertUnprocessable();
        $this->product->delete();
        $this->deleteJson(route('shelves.destroy', $shelf))->assertUnprocessable();
        $this->put(route('shelves.place', $this->product), ['shelf_id' => null])->assertRedirect();
        $this->assertNull($this->product->fresh()->shelf_id);
        $this->delete(route('shelves.destroy', $shelf))->assertRedirect();
        $this->assertDatabaseMissing('shelves', ['id' => $shelf->id]);
    }

    public function test_pos_shelf_search_category_and_unassigned_filters_keep_barcode_global(): void
    {
        $shelf = Shelf::create(['number' => 3, 'name' => 'Food']);
        $this->product->update(['shelf_id' => $shelf->id, 'barcode' => '00112233']);
        $this->getJson(route('pos.products', ['shelf_id' => $shelf->id, 'search' => $this->product->sku, 'category_id' => $this->product->category_id]))->assertOk()->assertJsonCount(1);
        $this->getJson(route('pos.products', ['unassigned' => 1, 'search' => $this->product->sku]))->assertOk()->assertJsonCount(0);
        $this->getJson(route('pos.barcode', ['barcode' => '00112233', 'shelf_id' => 999]))->assertOk()->assertJsonPath('id', $this->product->id);
        $this->product->update(['current_stock' => 0]);
        $this->getJson(route('pos.products', ['shelf_id' => $shelf->id]))->assertOk()->assertJsonCount(0);
        $this->getJson(route('pos.products', ['shelf_id' => 9999]))->assertUnprocessable();
    }

    public function test_pos_can_load_more_than_sixty_products_without_duplicates(): void
    {
        $shelf = Shelf::create(['number' => 1, 'name' => 'Large shelf']);
        for ($i = 0; $i < 65; $i++) {
            $product = $this->product->replicate();
            $product->fill(['sku' => 'PAGE-'.$i, 'name' => 'Product '.str_pad((string) $i, 3, '0', STR_PAD_LEFT), 'shelf_id' => $shelf->id])->save();
        }
        $page1 = $this->getJson(route('pos.products', ['shelf_id' => $shelf->id]))->assertOk()->assertJsonCount(60)->assertHeader('X-Has-More', '1');
        $page2 = $this->getJson(route('pos.products', ['shelf_id' => $shelf->id, 'page' => 2]))->assertOk()->assertJsonCount(5)->assertHeader('X-Has-More', '0');
        $this->assertCount(65, array_unique(array_merge(array_column($page1->json(), 'id'), array_column($page2->json(), 'id'))));
    }

    public function test_online_picking_uses_current_location_after_product_moves(): void
    {
        $customer = Customer::create(['name' => 'Picker customer']);
        $order = Order::create(['order_number' => 'ORDER-SHELF', 'customer_id' => $customer->id, 'subtotal' => 3500, 'total' => 3500, 'shipping_cost' => 0, 'fulfillment_type' => 'pickup', 'status' => 'pending', 'payment_status' => 'pending']);
        $order->items()->create(['product_id' => $this->product->id, 'quantity' => 1, 'unit_price' => 3500, 'subtotal' => 3500]);
        $shelf = Shelf::create(['number' => 7, 'name' => 'New location']);
        $this->product->update(['shelf_id' => $shelf->id, 'shelf_position' => 'Bottom right']);
        $this->get(route('orders.monitor'))->assertOk()->assertSee('Etalase 7 · New location / Bottom right');
        $this->get(route('orders.show', $order))->assertOk()->assertSee('Etalase 7 · New location / Bottom right');
    }

    public function test_seeder_is_repeatable_preserves_manual_layout_stock_and_cleared_locations(): void
    {
        $manual = Shelf::create(['number' => 1, 'name' => 'My shelf']);
        $this->product->update(['shelf_id' => $manual->id, 'shelf_position' => 'My position']);
        $stock = Product::pluck('current_stock', 'id')->all();
        $this->seed(ShelfSeeder::class);
        $this->assertSame('My position', $this->product->fresh()->shelf_position);
        $other = Product::whereNotNull('shelf_id')->whereKeyNot($this->product->id)->firstOrFail();
        $other->update(['shelf_id' => null, 'shelf_position' => null]);
        $count = Shelf::count();
        $this->seed(ShelfSeeder::class);
        $this->assertSame($count, Shelf::count());
        $this->assertNull($other->fresh()->shelf_id);
        $this->assertSame($stock, Product::pluck('current_stock', 'id')->all());
        $this->app->instance('env', 'production');
        $this->expectException(\RuntimeException::class);
        app(ShelfSeeder::class)->run();
    }
}
