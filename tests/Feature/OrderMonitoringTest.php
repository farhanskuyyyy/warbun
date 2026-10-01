<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\DebtTransaction;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Services\PaymentService;
use App\Services\RefundService;
use App\Services\ShiftService;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderMonitoringTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolesAndPermissionsSeeder::class, MasterDataSeeder::class]);
        $this->actingAs(User::where('email', 'owner@warbun.local')->firstOrFail());
        app(ShiftService::class)->open('0');
        $this->product = Product::where('sku', 'IND-001')->firstOrFail();
        $this->product->update(['selling_price' => '3500.25']);
        $this->customer = Customer::create(['name' => 'Delivery customer', 'user_id' => User::factory()->create()->id, 'phone' => '081234567890', 'address' => 'Old address', 'is_active' => true, 'can_use_debt' => true, 'debt_status' => 'eligible', 'credit_limit' => '50000']);
    }

    private function payload(array $replace = []): array
    {
        return array_replace(['request_key' => (string) Str::uuid(), 'items' => [['product_id' => $this->product->id, 'quantity' => 2]], 'customer_id' => $this->customer->id, 'payment_method' => 'cash', 'paid_amount' => '20000', 'fulfillment_type' => 'delivery', 'shipping_address' => 'Delivery snapshot'], $replace);
    }

    public function test_delivery_is_charged_and_stock_deducted_once_including_retries_and_fulfillment(): void
    {
        $stock = $this->product->current_stock;
        $payload = $this->payload();
        $this->postJson(route('pos.process-sale'), $payload)->assertOk();
        $sale = Sale::where('request_key', $payload['request_key'])->firstOrFail();
        $this->assertSame('17000.50', $sale->total);
        $this->assertSame('10000.00', $sale->shipping_cost);
        $this->assertSame('2999.50', $sale->change_amount);
        $this->customer->update(['address' => 'Changed address']);
        $this->assertSame('Delivery snapshot', $sale->fresh()->shipping_address);
        $this->postJson(route('pos.process-sale'), $payload)->assertOk();
        foreach (['preparing', 'ready', 'completed'] as $stage) {
            $this->put(route('orders.delivery-status', $sale), ['status' => $stage])->assertRedirect();
            $this->assertSame($stage, $sale->fresh()->fulfillment_status);
        }
        $this->assertSame(1, Sale::count());
        $this->assertSame(0, Order::count());
        $this->assertSame(1, Payment::count());
        $this->assertSame($stock - 2, $this->product->fresh()->current_stock);
        $this->get(route('orders.monitor'))->assertOk()->assertDontSee('data-monitor-id="'.$sale->id.'"', false);
        $this->get(route('orders.monitor', ['status' => 'completed']))->assertOk()->assertSee('data-monitor-source="pos"', false);
    }

    public function test_direct_sale_has_no_delivery_charge_or_active_queue_entry(): void
    {
        $this->postJson(route('pos.process-sale'), $this->payload(['fulfillment_type' => 'in_store']))->assertOk();
        $sale = Sale::latest('id')->firstOrFail();
        $this->assertSame('7000.50', $sale->total);
        $this->assertNull($sale->fulfillment_status);
        $this->assertSame('0.00', $sale->shipping_cost);
        $this->putJson(route('orders.delivery-status', $sale), ['status' => 'preparing'])->assertUnprocessable();
    }

    public function test_delivery_requires_customer_phone_address_and_active_profile(): void
    {
        foreach ([['customer_id' => null], ['shipping_address' => ''], ['shipping_address' => str_repeat('a', 2001)], ['fulfillment_type' => 'other']] as $replace) {
            $this->postJson(route('pos.process-sale'), $this->payload($replace))->assertUnprocessable();
        }
        $this->customer->update(['phone' => null]);
        $this->postJson(route('pos.process-sale'), $this->payload())->assertUnprocessable();
        $this->customer->update(['phone' => '081234567890', 'is_active' => false]);
        $this->postJson(route('pos.process-sale'), $this->payload())->assertUnprocessable();
        $this->assertSame(0, Sale::count());
    }

    public function test_delivery_debt_includes_shipping_deposit_and_repayment_without_duplicate_ledger_entries(): void
    {
        $payload = $this->payload(['payment_method' => 'debt', 'paid_amount' => '2000']);
        $this->postJson(route('pos.process-sale'), $payload)->assertOk();
        $this->postJson(route('pos.process-sale'), $payload)->assertOk();
        $sale = Sale::latest('id')->firstOrFail();
        $this->assertSame('15000.50', $sale->debt_amount);
        $this->assertSame('15000.50', $this->customer->fresh()->outstanding_balance);
        $this->assertSame(1, DebtTransaction::where('type', 'new_debt')->count());
        foreach (['preparing', 'ready', 'completed'] as $stage) {
            $this->put(route('orders.delivery-status', $sale), ['status' => $stage])->assertRedirect();
        }
        $this->assertSame('15000.50', $sale->fresh()->debt_amount);
        $repay = ['request_key' => 'repay-delivery', 'customer_id' => $this->customer->id, 'amount' => '15000.50', 'method' => 'cash'];
        app(PaymentService::class)->receive($repay);
        app(PaymentService::class)->receive($repay);
        $this->assertSame('0.00', $sale->fresh()->debt_amount);
        $this->assertSame('17000.50', $sale->fresh()->paid_amount);
        $this->assertSame('0.00', $this->customer->fresh()->outstanding_balance);
        $this->assertSame(2, Payment::count());
    }

    public function test_credit_limit_inactive_login_and_restricted_accounts_roll_back_delivery_completely(): void
    {
        $stock = $this->product->current_stock;
        $movements = InventoryTransaction::count();
        $this->customer->update(['credit_limit' => '10000']);
        $this->postJson(route('pos.process-sale'), $this->payload(['payment_method' => 'debt', 'paid_amount' => 0]))->assertUnprocessable();
        $this->customer->update(['credit_limit' => '50000']);
        $this->customer->user->update(['is_active' => false]);
        $this->postJson(route('pos.process-sale'), $this->payload(['payment_method' => 'debt', 'paid_amount' => 0]))->assertUnprocessable();
        $this->customer->user->update(['is_active' => true]);
        $this->customer->update(['debt_status' => 'restricted']);
        $this->postJson(route('pos.process-sale'), $this->payload(['payment_method' => 'debt', 'paid_amount' => 0]))->assertUnprocessable();
        $this->assertSame(0, Sale::count());
        $this->assertSame(0, Payment::count());
        $this->assertSame(0, DebtTransaction::count());
        $this->assertSame($stock, $this->product->fresh()->current_stock);
        $this->assertSame($movements, InventoryTransaction::count());
    }

    public function test_monitor_combines_online_and_pos_search_and_enforces_status_permissions(): void
    {
        $this->postJson(route('pos.process-sale'), $this->payload())->assertOk();
        $sale = Sale::latest('id')->firstOrFail();
        $order = Order::create(['order_number' => 'ORD-monitor', 'customer_id' => $this->customer->id, 'subtotal' => 100, 'shipping_cost' => 0, 'total' => 100, 'fulfillment_type' => 'pickup', 'status' => 'pending', 'payment_status' => 'pending']);
        $this->get(route('orders.monitor'))->assertOk()->assertSee('ORD-monitor')->assertSee($sale->sale_number);
        $this->get(route('orders.monitor', ['status' => 'pending']))->assertOk()->assertSee('ORD-monitor')->assertDontSee($sale->sale_number);
        $this->get(route('orders.monitor', ['search' => 'Delivery customer']))->assertOk()->assertSee('ORD-monitor');
        $this->get(route('orders.monitor', ['search' => 'no-match']))->assertOk()->assertDontSee('ORD-monitor');
        $this->putJson(route('orders.delivery-status', $sale), ['status' => 'completed'])->assertUnprocessable();
        $this->actingAs(User::where('email', 'kasir@warbun.local')->firstOrFail());
        $this->put(route('orders.delivery-status', $sale), ['status' => 'preparing'])->assertRedirect();
        $this->put(route('orders.delivery-status', $sale), ['status' => 'ready'])->assertRedirect();
        $this->putJson(route('orders.delivery-status', $sale), ['status' => 'completed'])->assertForbidden();
        $this->actingAs($this->customer->user);
        $this->get(route('orders.monitor'))->assertForbidden();
        $this->putJson(route('orders.delivery-status', $sale), ['status' => 'completed'])->assertForbidden();
        $this->get(route('orders.show', $order))->assertForbidden();
    }

    public function test_new_customer_modal_endpoint_creates_profile_and_optional_customer_only_login_without_credit(): void
    {
        $payload = ['name' => 'New customer', 'phone' => '081111111111', 'address' => 'Bandung', 'can_use_debt' => true, 'credit_limit' => 999999, 'role' => 'owner'];
        $response = $this->postJson(route('pos.customers'), $payload)->assertCreated();
        $customer = Customer::findOrFail($response->json('id'));
        $this->assertNull($customer->user_id);
        $this->assertFalse($customer->can_use_debt);
        $this->assertSame('0.00', $customer->credit_limit);
        $this->postJson(route('pos.customers'), $payload)->assertUnprocessable()->assertJsonValidationErrors('phone');
        $response = $this->postJson(route('pos.customers'), array_replace($payload, ['phone' => '082222222222', 'create_account' => true, 'email' => 'new@example.com', 'password' => 'password123', 'password_confirmation' => 'password123']))->assertCreated();
        $user = Customer::findOrFail($response->json('id'))->user;
        $this->assertTrue($user->hasRole('customer'));
        $this->assertFalse($user->hasRole('owner'));
        $this->postJson(route('pos.customers'), ['name' => 'X', 'phone' => '083333333333', 'address' => 'X', 'create_account' => true])->assertUnprocessable()->assertJsonValidationErrors(['email', 'password']);
        $this->actingAs(User::factory()->create());
        $this->postJson(route('pos.customers'), $payload)->assertForbidden();
    }

    public function test_paid_online_order_cannot_skip_stages_and_unpaid_ready_order_cannot_finish(): void
    {
        $order = Order::create(['order_number' => 'ORD-unpaid', 'customer_id' => $this->customer->id, 'subtotal' => 100, 'shipping_cost' => 0, 'total' => 100, 'fulfillment_type' => 'pickup', 'status' => 'ready', 'payment_status' => 'pending']);
        $this->get(route('orders.monitor'))->assertOk()->assertDontSee('name="status" value="completed"', false);
        $this->putJson(route('orders.update-status', $order), ['status' => 'completed'])->assertUnprocessable();
        $this->get(route('orders.show', $order))->assertOk();
    }

    public function test_delivery_refund_restores_shipping_credit_and_stock_and_removes_active_entry(): void
    {
        $stock = $this->product->current_stock;
        $this->postJson(route('pos.process-sale'), $this->payload(['payment_method' => 'debt', 'paid_amount' => '2000']))->assertOk();
        $sale = Sale::latest('id')->firstOrFail();
        $refund = app(RefundService::class)->all($sale, 'Delivery returned', 'delivery-refund');
        $this->assertSame('17000.50', $refund->amount);
        $this->assertSame('0.00', $this->customer->fresh()->outstanding_balance);
        $this->assertSame($stock, $this->product->fresh()->current_stock);
        $this->assertSame('refunded', $sale->fresh()->status);
        $this->putJson(route('orders.delivery-status', $sale), ['status' => 'preparing'])->assertUnprocessable();
        $this->get(route('orders.monitor'))->assertOk()->assertDontSee('data-monitor-source="pos"', false);
        $this->get(route('orders.monitor', ['status' => 'refunded']))->assertOk()->assertSee($sale->sale_number);
    }

    public function test_monitor_paginates_combined_queue_without_losing_entries_or_soft_deleted_sources(): void
    {
        for ($i = 0; $i < 14; $i++) {
            Order::create(['order_number' => 'ORD-page-'.$i, 'customer_id' => $this->customer->id, 'subtotal' => 100, 'shipping_cost' => 0, 'total' => 100, 'fulfillment_type' => 'pickup', 'status' => 'pending', 'payment_status' => 'pending']);
        }
        $this->get(route('orders.monitor'))->assertOk()->assertSee('ORD-page-0')->assertDontSee('ORD-page-13');
        $this->get(route('orders.monitor', ['page' => 2]))->assertOk()->assertSee('ORD-page-13');
        Order::where('order_number', 'ORD-page-13')->firstOrFail()->delete();
        $this->get(route('orders.monitor', ['page' => 2]))->assertOk()->assertDontSee('ORD-page-13');
    }
}
