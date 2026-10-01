<?php

namespace Tests\Feature;

use App\Models\CashierShift;
use App\Models\Category;
use App\Models\Customer;
use App\Models\DebtTransaction;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\Sale;
use App\Models\Unit;
use App\Models\User;
use App\Services\DebtService;
use App\Services\OpnameService;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\RefundService;
use App\Services\ReportingService;
use App\Services\SaleService;
use App\Services\ShiftService;
use App\Services\StockService;
use App\Support\Money;
use Database\Seeders\DemoOperationsSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OperationsTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->owner = User::where('email', 'owner@warbun.local')->first();
        $this->actingAs($this->owner);
        $category = Category::create(['name' => 'Food', 'slug' => 'food']);
        $type = ProductType::create(['name' => 'Food', 'slug' => 'food']);
        $unit = Unit::create(['name' => 'Piece', 'symbol' => 'pcs']);
        $this->product = Product::create(['name' => 'Rice', 'sku' => 'RICE', 'category_id' => $category->id, 'product_type_id' => $type->id, 'unit_id' => $unit->id, 'cost_price' => '1000.00', 'selling_price' => '3500.25', 'current_stock' => 0, 'is_active' => true, 'is_available_online' => true]);
        app(StockService::class)->change($this->product->id, 20, 'Initial stock', 'initial');
    }

    private function customer(): Customer
    {
        $user = User::factory()->create();
        $user->assignRole('customer');

        return Customer::create(['user_id' => $user->id, 'name' => $user->name, 'phone' => '0800'.Str::random(10), 'email' => $user->email, 'is_active' => true, 'can_use_debt' => true, 'credit_limit' => '100000', 'debt_status' => 'eligible']);
    }

    private function sale(array $overrides = []): Sale
    {
        if (! CashierShift::where('active_user_id', auth()->id())->exists()) {
            app(ShiftService::class)->open('10000');
        }

        return app(SaleService::class)->process(array_replace(['request_key' => (string) Str::uuid(), 'items' => [['product_id' => $this->product->id, 'quantity' => 2]], 'payment_method' => 'cash', 'paid_amount' => '10000', 'discount' => 0], $overrides));
    }

    private function invalid(callable $call): void
    {
        try {
            $call();
            $this->fail('Expected validation rejection');
        } catch (ValidationException $e) {
            $this->assertNotEmpty($e->errors());
        }
    }

    public function test_exact_money_and_cash_sale_reconcile(): void
    {
        $this->assertSame(1001, Money::cents('10.01'));
        $this->assertSame('10.01', Money::decimal(1001));
        $sale = $this->sale();
        $this->assertSame('7000.50', $sale->total);
        $this->assertSame('2999.50', $sale->change_amount);
        $this->assertEquals(18, $this->product->fresh()->current_stock);
        $this->assertSame('7000.50', $sale->payments()->first()->amount);
        $this->assertDatabaseHas('inventory_transactions', ['source_type' => 'sale', 'source_id' => $sale->id, 'quantity' => -2]);
        $shift = app(ShiftService::class)->close('17000.50');
        $this->assertSame('0.00', $shift->cash_variance);
    }

    public function test_sale_retry_does_not_double_charge_or_reduce_stock(): void
    {
        $key = (string) Str::uuid();
        $first = $this->sale(['request_key' => $key]);
        $second = $this->sale(['request_key' => $key]);
        $this->assertSame($first->id, $second->id);
        $this->assertEquals(18, $this->product->fresh()->current_stock);
        $this->assertEquals(1, Payment::count());
    }

    public function test_duplicate_lines_cannot_oversell_and_transaction_rolls_back(): void
    {
        $this->invalid(fn () => $this->sale(['items' => [['product_id' => $this->product->id, 'quantity' => 11], ['product_id' => $this->product->id, 'quantity' => 11]], 'paid_amount' => '999999']));
        $this->assertEquals(20, $this->product->fresh()->current_stock);
        $this->assertEquals(0, Sale::count());
    }

    public function test_cashier_cannot_override_price_or_discount(): void
    {
        $this->actingAs(User::where('email', 'kasir@warbun.local')->first());
        $this->invalid(fn () => $this->sale(['items' => [['product_id' => $this->product->id, 'quantity' => 1, 'unit_price' => '1']]]));
        $this->invalid(fn () => $this->sale(['discount' => '1']));
        $this->assertEquals(0, Sale::count());
    }

    public function test_anonymous_or_unregistered_debt_rolls_back(): void
    {
        $this->invalid(fn () => $this->sale(['payment_method' => 'debt', 'paid_amount' => '0']));
        $anon = Customer::create(['name' => 'Anonymous', 'can_use_debt' => true, 'credit_limit' => 100000]);
        $this->invalid(fn () => $this->sale(['payment_method' => 'debt', 'paid_amount' => '0', 'customer_id' => $anon->id]));
        $this->assertEquals(20, $this->product->fresh()->current_stock);
        $this->assertEquals(0, Sale::count());
    }

    public function test_credit_limit_is_enforced_inside_sale_transaction(): void
    {
        $c = $this->customer();
        $c->update(['credit_limit' => 100]);
        $this->invalid(fn () => $this->sale(['payment_method' => 'debt', 'paid_amount' => '0', 'customer_id' => $c->id]));
        $this->assertEquals(0, DebtTransaction::count());
        $this->assertEquals(20, $this->product->fresh()->current_stock);
    }

    public function test_partial_and_full_debt_payments_allocate_and_update_sale(): void
    {
        $c = $this->customer();
        $sale = $this->sale(['payment_method' => 'debt', 'paid_amount' => '0', 'customer_id' => $c->id]);
        $entry = DebtTransaction::first();
        $entry->update(['due_date' => today()->subDay()]);
        $p = app(PaymentService::class)->receive(['request_key' => (string) Str::uuid(), 'sale_id' => $sale->id, 'amount' => '2000', 'method' => 'cash']);
        $this->assertSame('5000.50', $entry->fresh()->remaining_amount);
        $this->assertTrue($entry->fresh()->isOverdue());
        app(PaymentService::class)->receive(['request_key' => (string) Str::uuid(), 'sale_id' => $sale->id, 'amount' => '5000.50', 'method' => 'transfer']);
        $this->assertSame('0.00', $c->fresh()->outstanding_balance);
        $this->assertSame('0.00', $sale->fresh()->debt_amount);
        $this->assertSame('7000.50', $sale->fresh()->paid_amount);
        $this->assertFalse($entry->fresh()->isOverdue());
        $this->assertEquals(2, \DB::table('debt_allocations')->count());
    }

    public function test_overpayment_is_rejected_without_payment_or_ledger_write(): void
    {
        $c = $this->customer();
        $sale = $this->sale(['payment_method' => 'debt', 'paid_amount' => 0, 'customer_id' => $c->id]);
        $this->invalid(fn () => app(PaymentService::class)->receive(['request_key' => (string) Str::uuid(), 'sale_id' => $sale->id, 'amount' => 8000, 'method' => 'cash']));
        $this->assertEquals(0, Payment::count());
        $this->assertEquals(1, DebtTransaction::count());
    }

    public function test_due_today_is_not_overdue(): void
    {
        $c = $this->customer();
        $entry = app(DebtService::class)->debit($c->id, 100, 'purchase', null, today()->toDateString());
        $this->assertFalse($entry->isOverdue());
    }

    public function test_stock_out_cannot_go_negative(): void
    {
        $this->invalid(fn () => app(StockService::class)->change($this->product->id, -21, 'damage'));
        $this->assertEquals(20, $this->product->fresh()->current_stock);
    }

    public function test_double_shift_open_is_rejected(): void
    {
        app(ShiftService::class)->open('0');
        $this->invalid(fn () => app(ShiftService::class)->open('0'));
        $this->assertEquals(1, CashierShift::count());
    }

    public function test_debt_refund_reverses_ledger_and_restores_stock_once(): void
    {
        $c = $this->customer();
        $sale = $this->sale(['payment_method' => 'debt', 'paid_amount' => 0, 'customer_id' => $c->id]);
        $key = (string) Str::uuid();
        $r = app(RefundService::class)->all($sale, 'return', $key);
        $again = app(RefundService::class)->all($sale, 'return', $key);
        $this->assertSame($r->id, $again->id);
        $this->assertSame('0.00', $c->fresh()->outstanding_balance);
        $this->assertEquals(20, $this->product->fresh()->current_stock);
        $this->assertDatabaseHas('debt_transactions', ['type' => 'reversal']);
        $this->assertSame('refunded', $sale->fresh()->status);
    }

    public function test_partial_refund_quantity_and_cash_reconcile(): void
    {
        $sale = $this->sale();
        app(RefundService::class)->process($sale, [['product_id' => $this->product->id, 'quantity' => 1]], 'return', (string) Str::uuid());
        $this->assertEquals(19, $this->product->fresh()->current_stock);
        $this->invalid(fn () => app(RefundService::class)->process($sale, [['product_id' => $this->product->id, 'quantity' => 2]], 'return', (string) Str::uuid()));
        $shift = app(ShiftService::class)->close('13500.25');
        $this->assertSame('0.00', $shift->cash_variance);
    }

    public function test_opname_records_difference_once_and_rejects_stale_counts(): void
    {
        $s = app(OpnameService::class);
        $o = $s->create(['reason' => 'count', 'items' => [['product_id' => $this->product->id, 'physical_stock' => 18]]]);
        $s->approve($o);
        $s->approve($o);
        $this->assertEquals(18, $this->product->fresh()->current_stock);
        $this->assertEquals(1, InventoryTransaction::where('source_type', 'opname')->count());
        $stale = $s->create(['reason' => 'count', 'items' => [['product_id' => $this->product->id, 'physical_stock' => 15]]]);
        app(StockService::class)->change($this->product->id, 1, 'restock');
        $this->invalid(fn () => $s->approve($stale));
        $this->assertEquals(19, $this->product->fresh()->current_stock);
    }

    public function test_customer_order_reserves_stock_and_enforces_ownership(): void
    {
        $c = $this->customer();
        $this->actingAs($c->user);
        $key = (string) Str::uuid();
        $data = ['request_key' => $key, 'items' => [['product_id' => $this->product->id, 'quantity' => 1]], 'delivery_type' => 'pickup', 'payment_method' => 'transfer'];
        $order = app(OrderService::class)->checkout($data);
        $again = app(OrderService::class)->checkout($data);
        $this->assertSame($order->id, $again->id);
        $this->assertEquals(19, $this->product->fresh()->current_stock);
        $this->assertSame('pending', $order->payments()->first()->status);
        $this->actingAs($this->customer()->user);
        $this->get(route('customer.order.success', $order))->assertForbidden();
    }

    public function test_order_cancellation_restores_reserved_stock_and_invalid_transition_fails(): void
    {
        $c = $this->customer();
        $this->actingAs($c->user);
        $o = app(OrderService::class)->checkout(['request_key' => (string) Str::uuid(), 'items' => [['product_id' => $this->product->id, 'quantity' => 1]], 'delivery_type' => 'pickup']);
        $this->actingAs($this->owner);
        $this->invalid(fn () => app(OrderService::class)->transition($o, 'completed'));
        app(OrderService::class)->transition($o, 'cancelled');
        $this->assertEquals(20, $this->product->fresh()->current_stock);
        $this->invalid(fn () => app(OrderService::class)->transition($o, 'confirmed'));
    }

    public function test_order_paid_confirmation_and_completion_requires_valid_flow(): void
    {
        $c = $this->customer();
        $this->actingAs($c->user);
        $o = app(OrderService::class)->checkout(['request_key' => (string) Str::uuid(), 'items' => [['product_id' => $this->product->id, 'quantity' => 1]], 'delivery_type' => 'pickup']);
        $this->actingAs($this->owner);
        app(PaymentService::class)->confirm($o->payments()->first());
        $this->assertSame('paid', $o->fresh()->payment_status);
        $this->invalid(fn () => app(OrderService::class)->transition($o, 'cancelled'));
        foreach (['confirmed', 'preparing', 'ready', 'completed'] as $status) {
            $o = app(OrderService::class)->transition($o, $status);
        }
        $this->assertSame('completed', $o->status);
    }

    public function test_customer_and_cashier_cannot_access_sensitive_actions(): void
    {
        $this->actingAs($this->customer()->user);
        $this->get('/products')->assertForbidden();
        $this->get('/debt')->assertForbidden();
        $this->actingAs(User::where('email', 'kasir@warbun.local')->first());
        $this->get('/settings')->assertForbidden();
        $this->get('/users')->assertForbidden();
        $this->post('/inventory/adjust', [])->assertForbidden();
        $this->post('/refunds', [])->assertForbidden();
    }

    public function test_locale_switch_persists_and_invalid_locale_is_rejected(): void
    {
        $this->post('/locale', ['locale' => 'en'])->assertRedirect();
        $this->assertSame('en', $this->owner->fresh()->locale);
        $this->assertSame('en', session('locale'));
        $this->post('/locale', ['locale' => 'xx'])->assertSessionHasErrors('locale');
    }

    public function test_signed_webhook_rejects_tampering_and_replays_safely(): void
    {
        $c = $this->customer();
        $this->actingAs($c->user);
        $o = app(OrderService::class)->checkout(['request_key' => (string) Str::uuid(), 'items' => [['product_id' => $this->product->id, 'quantity' => 1]], 'delivery_type' => 'pickup']);
        $payment = $o->payments()->first();
        $payment->update(['method' => 'online']);
        config(['payments.gateway' => 'signed-webhook', 'payments.webhook_secret' => str_repeat('s', 32)]);
        $payload = ['payment_number' => $payment->payment_number, 'amount' => $payment->amount, 'status' => 'paid', 'reference' => 'gateway-001'];
        $json = json_encode($payload);
        $stamp = (string) time();
        $this->call('POST', '/payments/webhook', [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_X_PAYMENT_TIMESTAMP' => $stamp, 'HTTP_X_PAYMENT_SIGNATURE' => 'bad'], $json)->assertUnauthorized();
        $headers = ['CONTENT_TYPE' => 'application/json', 'HTTP_X_PAYMENT_TIMESTAMP' => $stamp, 'HTTP_X_PAYMENT_SIGNATURE' => hash_hmac('sha256', $stamp.'.'.$json, str_repeat('s', 32))];
        $this->call('POST', '/payments/webhook', [], [], [], $headers, $json)->assertOk();
        $this->call('POST', '/payments/webhook', [], [], [], $headers, $json)->assertOk();
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('paid', $o->fresh()->payment_status);
    }

    public function test_partial_order_payments_are_bounded_and_refund_preserves_receipt_methods(): void
    {
        $c = $this->customer();
        $this->actingAs($c->user);
        $o = app(OrderService::class)->checkout(['request_key' => (string) Str::uuid(), 'items' => [['product_id' => $this->product->id, 'quantity' => 2]], 'delivery_type' => 'pickup']);
        $this->actingAs($this->owner);
        app(ShiftService::class)->open('10000');
        $data = ['request_key' => (string) Str::uuid(), 'amount' => '2000', 'method' => 'cash'];
        $p = app(PaymentService::class)->forOrder($o, $data);
        $again = app(PaymentService::class)->forOrder($o, $data);
        $this->assertSame($p->id, $again->id);
        $this->assertSame('partial', $o->fresh()->payment_status);
        $this->invalid(fn () => app(PaymentService::class)->forOrder($o, ['request_key' => (string) Str::uuid(), 'amount' => '5001', 'method' => 'transfer']));
        app(PaymentService::class)->forOrder($o, ['request_key' => (string) Str::uuid(), 'amount' => '5000.50', 'method' => 'transfer']);
        $this->assertSame('paid', $o->fresh()->payment_status);
        app(OrderService::class)->transition($o, 'confirmed');
        $r = app(RefundService::class)->all($o, 'return', (string) Str::uuid());
        $this->assertSame('7000.50', $r->paid_amount);
        $this->assertSame('2000.00', $r->cash_amount);
        $this->assertEquals(2, \DB::table('refund_payments')->count());
        $this->assertSame('0.00', app(ShiftService::class)->close('10000')->cash_variance);
    }

    public function test_account_fifo_repayments_can_be_refunded_without_using_other_sales_receipts(): void
    {
        $c = $this->customer();
        $a = $this->sale(['payment_method' => 'debt', 'paid_amount' => 0, 'customer_id' => $c->id]);
        $b = $this->sale(['payment_method' => 'debt', 'paid_amount' => 0, 'customer_id' => $c->id]);
        app(PaymentService::class)->receive(['request_key' => (string) Str::uuid(), 'customer_id' => $c->id, 'amount' => '14001', 'method' => 'cash']);
        $r = app(RefundService::class)->all($a, 'first return', (string) Str::uuid());
        $this->assertSame('7000.50', $r->cash_amount);
        $r2 = app(RefundService::class)->all($b, 'second return', (string) Str::uuid());
        $this->assertSame('7000.50', $r2->cash_amount);
        $this->assertSame('0.00', $c->fresh()->outstanding_balance);
        $this->assertSame('0.00', app(ShiftService::class)->close('10000')->cash_variance);
    }

    public function test_discount_refund_rounding_and_sales_report_are_exact(): void
    {
        $sale = $this->sale(['discount' => '0.01']);
        $first = app(RefundService::class)->process($sale, [['product_id' => $this->product->id, 'quantity' => 1]], 'partial', (string) Str::uuid());
        $second = app(RefundService::class)->process($sale, [['product_id' => $this->product->id, 'quantity' => 1]], 'final', (string) Str::uuid());
        $this->assertSame(700049, Money::cents($first->amount) + Money::cents($second->amount));
        $report = app(ReportingService::class)->sales(today(), today()->endOfDay());
        $this->assertSame('7000.50', $report['grossSales']);
        $this->assertSame('0.01', $report['totalDiscounts']);
        $this->assertSame('7000.49', $report['refundTotal']);
        $this->assertSame('0.00', $report['totalSales']);
    }

    public function test_write_off_updates_sale_balance_without_claiming_cash_received(): void
    {
        $c = $this->customer();
        $sale = $this->sale(['payment_method' => 'debt', 'paid_amount' => 0, 'customer_id' => $c->id]);
        app(DebtService::class)->credit($c->id, 700050, 'uncollectible', 'write_off');
        $this->assertSame('0.00', $sale->fresh()->debt_amount);
        $this->assertSame('0.00', $sale->fresh()->paid_amount);
        $this->assertSame('0.00', $c->fresh()->outstanding_balance);
        $this->assertEquals(0, Payment::count());
    }

    public function test_money_formats_fractions_and_allocates_large_amounts_without_float(): void
    {
        app()->setLocale('id');
        $this->assertSame('Rp 3.500,25', Money::format('3500.25'));
        app()->setLocale('en');
        $this->assertSame('-Rp 3,500.25', Money::format('-3500.25'));
        $this->assertSame(33333333333333, Money::proportion(99999999999999, 1, 3));
        $this->invalid(fn () => Money::multiply(99999999999999, 2));
    }

    public function test_demo_seeder_is_idempotent_even_with_preexisting_master_ids(): void
    {
        $this->seed(DemoOperationsSeeder::class);
        $count = [Sale::count(), Order::count(), Payment::count(), InventoryTransaction::count()];
        $this->seed(DemoOperationsSeeder::class);
        $this->assertSame($count, [Sale::count(), Order::count(), Payment::count(), InventoryTransaction::count()]);
        $p = Product::where('sku', 'IND-001')->first();
        $this->assertSame('makanan', $p->category->slug);
    }

    public function test_deleting_account_with_financial_history_deactivates_and_preserves_records(): void
    {
        $sale = $this->sale();
        $this->delete('/profile', ['password' => 'password'])->assertRedirect('/');
        $this->assertDatabaseHas('users', ['id' => $this->owner->id, 'is_active' => false]);
        $this->assertDatabaseHas('sales', ['id' => $sale->id]);
        $this->actingAs($this->owner->fresh());
        $this->get('/dashboard')->assertForbidden();
    }

    public function test_phone_login_and_disabled_login_are_checked_before_authentication(): void
    {
        $c = $this->customer();
        $c->user->update(['password' => 'password']);
        auth()->logout();
        $this->post('/login', ['email' => $c->phone, 'password' => 'password'])->assertRedirect('/my-orders');
        $this->assertAuthenticatedAs($c->user);
        auth()->logout();
        $c->user->update(['is_active' => false]);
        $this->post('/login', ['email' => $c->phone, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_delegated_staff_management_cannot_escalate_to_owner_or_disable_without_permission(): void
    {
        $u = User::factory()->create();
        $u->givePermissionTo(['users.create', 'users.update', 'permissions.assign']);
        $this->actingAs($u);
        $this->post('/users', ['name' => 'Escalation', 'email' => 'escalation@example.com', 'password' => 'long-password', 'role' => 'owner'])->assertForbidden();
        $target = User::factory()->create();
        $target->assignRole('customer');
        $this->put('/users/'.$target->id, ['role' => 'customer', 'is_active' => false])->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $target->id, 'is_active' => true]);
    }

    public function test_full_refund_after_partial_return_only_returns_remaining_items(): void
    {
        $sale = $this->sale();
        app(RefundService::class)->process($sale, [['product_id' => $this->product->id, 'quantity' => 1]], 'partial', (string) Str::uuid());
        $r = app(RefundService::class)->all($sale, 'remaining', (string) Str::uuid());
        $this->assertSame('3500.25', $r->amount);
        $this->assertEquals(20, $this->product->fresh()->current_stock);
        $this->assertSame('refunded', $sale->fresh()->status);
    }

    public function test_signed_failed_payment_and_invalid_amount_do_not_reduce_stock_twice(): void
    {
        $c = $this->customer();
        $this->actingAs($c->user);
        $o = app(OrderService::class)->checkout(['request_key' => (string) Str::uuid(), 'items' => [['product_id' => $this->product->id, 'quantity' => 1]], 'delivery_type' => 'pickup']);
        $payment = $o->payments()->first();
        $payment->update(['method' => 'online']);
        $secret = str_repeat('s', 32);
        config(['payments.gateway' => 'signed-webhook', 'payments.webhook_secret' => $secret]);
        $send = function ($amount, $status, $stamp) use ($payment, $secret) {
            $json = json_encode(['payment_number' => $payment->payment_number, 'amount' => $amount, 'status' => $status, 'reference' => 'gateway-failed']);

            return $this->call('POST', '/payments/webhook', [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_X_PAYMENT_TIMESTAMP' => (string) $stamp, 'HTTP_X_PAYMENT_SIGNATURE' => hash_hmac('sha256', $stamp.'.'.$json, $secret)], $json);
        };
        $send('1', 'paid', time())->assertUnprocessable();
        $this->assertSame('pending', $payment->fresh()->status);
        $send($payment->amount, 'failed', time() - 1000)->assertUnauthorized();
        $send($payment->amount, 'failed', time())->assertOk();
        $send($payment->amount, 'failed', time())->assertOk();
        $this->assertSame('failed', $o->fresh()->payment_status);
        $this->assertEquals(19, $this->product->fresh()->current_stock);
        $this->actingAs($this->owner);
        app(OrderService::class)->transition($o, 'cancelled');
        $this->assertEquals(20, $this->product->fresh()->current_stock);
    }

    public function test_archived_product_remains_on_receipt_and_can_be_returned(): void
    {
        $sale = $this->sale();
        $this->delete('/products/'.$this->product->id)->assertRedirect();
        $this->get('/pos/receipt/'.$sale->id)->assertOk()->assertSee('Rice');
        app(RefundService::class)->all($sale, 'archived item return', (string) Str::uuid());
        $this->assertEquals(20, Product::withTrashed()->find($this->product->id)->current_stock);
        $this->get('/shop/'.$this->product->id)->assertNotFound();
    }

    public function test_product_update_cannot_silently_change_stock_or_round_price(): void
    {
        $data = $this->product->only(['name', 'sku', 'category_id', 'product_type_id', 'unit_id', 'cost_price', 'selling_price', 'minimum_stock']);
        $this->put('/products/'.$this->product->id, $data + ['current_stock' => 99])->assertSessionHasErrors('current_stock');
        $data['selling_price'] = '1000.001';
        $this->put('/products/'.$this->product->id, $data)->assertSessionHasErrors('selling_price');
        $this->assertEquals(20, $this->product->fresh()->current_stock);
    }

    public function test_reports_include_paid_confirmed_online_orders_and_debt_aging(): void
    {
        $c = $this->customer();
        $this->actingAs($c->user);
        $o = app(OrderService::class)->checkout(['request_key' => (string) Str::uuid(), 'items' => [['product_id' => $this->product->id, 'quantity' => 1]], 'delivery_type' => 'pickup']);
        $this->actingAs($this->owner);
        app(PaymentService::class)->confirm($o->payments()->first());
        app(OrderService::class)->transition($o, 'confirmed');
        $report = app(ReportingService::class)->sales(today(), today()->endOfDay());
        $this->assertSame('3500.25', $report['totalSales']);
        app(DebtService::class)->debit($c->id, 100, 'aging', null, today()->subDays(40)->toDateString());
        $this->get('/reports/debt')->assertOk()->assertViewHas('aging', fn ($aging) => $aging['31–60 days overdue'] === 100);
    }

    public function test_profile_updates_registered_identity_without_changing_credit_fields(): void
    {
        $c = $this->customer();
        $this->actingAs($c->user);
        $this->patch('/profile', ['name' => 'Updated customer', 'email' => 'updated@example.com', 'phone' => '080098765432', 'address' => 'Bandung', 'credit_limit' => 999999])->assertRedirect('/profile');
        $this->assertSame('Updated customer', $c->fresh()->name);
        $this->assertSame('updated@example.com', $c->fresh()->email);
        $this->assertSame('080098765432', $c->fresh()->phone);
        $this->assertSame('100000.00', $c->fresh()->credit_limit);
    }

    public function test_cashier_shift_history_is_limited_to_own_shifts(): void
    {
        app(ShiftService::class)->open('12345');
        $other = User::where('email', 'kasir@warbun.local')->first();
        $this->actingAs($other);
        app(ShiftService::class)->open('42');
        $this->get('/shifts')->assertOk()->assertViewHas('shifts', fn ($shifts) => $shifts->count() === 1 && $shifts->first()->user_id === $other->id);
    }

    public function test_invalid_manual_payment_target_returns_validation_without_financial_writes(): void
    {
        $sale = $this->sale();
        $before = Payment::count();
        $this->post('/payments', ['request_key' => (string) Str::uuid(), 'sale_id' => $sale->id, 'amount' => '1', 'method' => 'cash'])->assertSessionHasErrors('amount');
        $this->invalid(fn () => app(PaymentService::class)->confirm($sale->payments()->first()));
        $this->assertEquals($before, Payment::count());
    }
}
