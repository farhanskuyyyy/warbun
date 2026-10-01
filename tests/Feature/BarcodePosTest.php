<?php

namespace Tests\Feature;

use App\Models\InventoryTransaction;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Services\ShiftService;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarcodePosTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolesAndPermissionsSeeder::class, MasterDataSeeder::class]);
        $this->actingAs(User::where('email', 'kasir@warbun.local')->firstOrFail());
        $this->product = Product::where('sku', 'IND-001')->firstOrFail();
        $this->product->update(['barcode' => '0012345678905', 'selling_price' => '3500.25']);
    }

    public function test_barcode_lookup_preserves_zeroes_and_requires_the_whole_code_without_stock_writes(): void
    {
        $stock = $this->product->current_stock;
        $movements = InventoryTransaction::count();
        $this->getJson(route('pos.barcode', ['barcode' => '0012345678905']))->assertOk()
            ->assertJsonPath('id', $this->product->id)->assertJsonPath('barcode', '0012345678905')
            ->assertJsonPath('selling_price', '3500.25');
        foreach (['001234567890', '12345678905', $this->product->name, $this->product->sku] as $code) {
            $this->getJson(route('pos.barcode', ['barcode' => $code]))->assertNotFound();
        }
        $this->assertSame($stock, $this->product->fresh()->current_stock);
        $this->assertSame($movements, InventoryTransaction::count());
    }

    public function test_scan_reports_missing_stock_and_rejects_inactive_or_archived_products(): void
    {
        $this->product->update(['current_stock' => 0]);
        $this->getJson(route('pos.barcode', ['barcode' => $this->product->barcode]))->assertStatus(409)->assertJsonStructure(['message']);
        $this->product->update(['is_active' => false]);
        $this->getJson(route('pos.barcode', ['barcode' => $this->product->barcode]))->assertNotFound();
        $this->product->update(['is_active' => true, 'current_stock' => 10]);
        $this->product->delete();
        $this->getJson(route('pos.barcode', ['barcode' => $this->product->barcode]))->assertNotFound();
    }

    public function test_lookup_validates_barcode_and_requires_cashier_permission(): void
    {
        $this->getJson(route('pos.barcode'))->assertUnprocessable()->assertJsonValidationErrors('barcode');
        $this->getJson(route('pos.barcode', ['barcode' => str_repeat('1', 51)]))->assertUnprocessable();
        $this->actingAs(User::factory()->create());
        $this->getJson(route('pos.barcode', ['barcode' => $this->product->barcode]))->assertForbidden();
    }

    public function test_product_forms_store_unique_barcode_strings_and_allow_updating_the_same_product(): void
    {
        $this->actingAs(User::where('email', 'owner@warbun.local')->firstOrFail());
        $data = [
            'name' => 'Barcode product', 'sku' => 'BARCODE-NEW', 'barcode' => '0009876543210',
            'category_id' => $this->product->category_id, 'product_type_id' => $this->product->product_type_id,
            'unit_id' => $this->product->unit_id, 'cost_price' => '1000', 'selling_price' => '2000',
            'minimum_stock' => 1, 'current_stock' => 10, 'is_active' => true,
        ];
        $this->post(route('products.store'), $data)->assertRedirect(route('products.index'));
        $new = Product::where('sku', 'BARCODE-NEW')->firstOrFail();
        $this->assertSame('0009876543210', $new->barcode);
        $this->postJson(route('products.store'), array_replace($data, ['sku' => 'BARCODE-DUPLICATE']))
            ->assertUnprocessable()->assertJsonValidationErrors('barcode');
        unset($data['current_stock']);
        $this->put(route('products.update', $new), $data)->assertRedirect(route('products.index'));
        $this->putJson(route('products.update', $new), array_replace($data, ['barcode' => $this->product->barcode]))
            ->assertUnprocessable()->assertJsonValidationErrors('barcode');
        $this->get(route('products.show', $new))->assertOk()->assertSee('0009876543210');
    }

    public function test_scanned_quantity_checkout_retries_do_not_duplicate_sale_or_stock_and_receipt_keeps_access_control(): void
    {
        app(ShiftService::class)->open('100000');
        $stock = $this->product->current_stock;
        $payload = ['request_key' => 'barcode-retry', 'items' => [['product_id' => $this->product->id, 'quantity' => 2]],
            'payment_method' => 'cash', 'paid_amount' => '8000', 'print_receipt' => true, 'receipt_paper' => '58'];
        $response = $this->postJson(route('pos.process-sale'), $payload)->assertOk();
        $sale = Sale::where('request_key', 'barcode-retry')->firstOrFail();
        $this->assertSame('7000.50', $sale->total);
        $this->assertSame('999.50', $sale->change_amount);
        $this->assertSame($stock - 2, $this->product->fresh()->current_stock);
        $this->postJson(route('pos.process-sale'), $payload)->assertOk()->assertJsonPath('redirect', $response->json('redirect'));
        $this->assertSame(1, Sale::count());
        $this->assertSame(1, Payment::count());
        $this->assertSame($stock - 2, $this->product->fresh()->current_stock);
        $this->get($response->json('redirect'))->assertOk()->assertSee('data-auto-print="true"', false)->assertSee('data-paper="58"', false);
        $this->actingAs(User::factory()->create()->assignRole('cashier'));
        $this->get(route('pos.receipt', $sale))->assertForbidden();
    }

    public function test_print_options_validate_before_processing_and_stock_is_rechecked_at_payment(): void
    {
        app(ShiftService::class)->open('0');
        $payload = ['request_key' => 'barcode-stock', 'items' => [['product_id' => $this->product->id, 'quantity' => 1]], 'payment_method' => 'cash', 'paid_amount' => '4000'];
        $this->postJson(route('pos.process-sale'), $payload + ['receipt_paper' => '123'])->assertUnprocessable()->assertJsonValidationErrors('receipt_paper');
        $this->postJson(route('pos.process-sale'), $payload + ['print_receipt' => 'yes'])->assertUnprocessable()->assertJsonValidationErrors('print_receipt');
        $this->product->update(['current_stock' => 0]);
        $this->postJson(route('pos.process-sale'), $payload)->assertUnprocessable();
        $this->assertSame(0, Sale::count());
        $this->assertSame(0, Payment::count());
    }
}
