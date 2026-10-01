<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\WarungCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ShoppingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_quote_uses_current_prices_and_shipping_without_reserving_stock(): void
    {
        $this->seed(WarungCatalogSeeder::class);
        $product = Product::where('sku', 'IND-001')->firstOrFail();
        $product->update(['selling_price' => '3500.25']);
        $stock = $product->current_stock;
        $movements = DB::table('inventory_transactions')->count();
        $this->postJson(route('customer.cart.quote'), ['items' => [['product_id' => $product->id, 'quantity' => 2, 'price' => 1]], 'delivery_type' => 'delivery'])
            ->assertOk()->assertJsonPath('subtotal', '7000.50')->assertJsonPath('shipping', '10000.00')->assertJsonPath('total', '17000.50')->assertJsonPath('errors', []);
        $this->assertSame($stock, $product->fresh()->current_stock);
        $this->assertSame($movements, DB::table('inventory_transactions')->count());
    }

    public function test_quote_reports_unavailable_and_insufficient_stock(): void
    {
        $this->seed(WarungCatalogSeeder::class);
        $product = Product::where('sku', 'IND-001')->firstOrFail();
        $this->postJson(route('customer.cart.quote'), ['items' => [['product_id' => $product->id, 'quantity' => $product->current_stock + 1]], 'delivery_type' => 'pickup'])->assertOk()->assertJsonCount(1, 'errors');
        $product->update(['is_available_online' => false]);
        $this->postJson(route('customer.cart.quote'), ['items' => [['product_id' => $product->id, 'quantity' => 1]], 'delivery_type' => 'pickup'])->assertOk()->assertJsonPath('items.0.available', false)->assertJsonCount(1, 'errors');
    }

    public function test_quote_rejects_invalid_quantities_and_duplicate_products(): void
    {
        $this->postJson(route('customer.cart.quote'), ['items' => [['product_id' => 1, 'quantity' => 0]], 'delivery_type' => 'pickup'])->assertUnprocessable();
        $this->postJson(route('customer.cart.quote'), ['items' => [['product_id' => 1, 'quantity' => 1], ['product_id' => 1, 'quantity' => 1]], 'delivery_type' => 'pickup'])->assertUnprocessable();
    }

    public function test_customer_registration_returns_to_the_checkout_started_as_guest(): void
    {
        $this->get(route('customer.checkout.continue'))->assertRedirect(route('login'));
        $this->post(route('register'), ['name' => 'Shopping Customer', 'email' => 'shopping@example.test', 'password' => 'password1234', 'password_confirmation' => 'password1234'])->assertRedirect(route('customer.checkout.continue'));
        $this->get(route('customer.checkout.continue'))->assertRedirect(route('customer.cart').'#checkout');
    }
}
