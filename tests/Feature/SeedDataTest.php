<?php

namespace Tests\Feature;

use App\Models\CashierShift;
use App\Models\Customer;
use App\Models\DebtAccount;
use App\Models\Product;
use App\Models\User;
use App\Support\Money;
use Database\Seeders\DemoOperationsSeeder;
use Database\Seeders\WarungCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SeedDataTest extends TestCase
{
    use RefreshDatabase;

    private const TABLES = ['users', 'roles', 'permissions', 'model_has_roles', 'role_has_permissions', 'model_has_permissions', 'categories', 'product_types', 'brands', 'units', 'suppliers', 'products', 'inventory_transactions', 'customers', 'cashier_shifts', 'sales', 'sale_items', 'orders', 'order_items', 'payments', 'debt_accounts', 'debt_transactions', 'debt_allocations', 'refunds', 'refund_items', 'refund_payments', 'stock_opnames', 'stock_opname_items', 'store_settings', 'notifications', 'audit_logs', 'shelves'];

    public function test_demo_fills_every_business_table_and_preserves_ledger_invariants(): void
    {
        $this->seed();
        foreach (self::TABLES as $table) {
            $this->assertGreaterThan(0, DB::table($table)->count(), $table);
        }
        $this->assertSame(53, Product::count());
        $this->assertSame(9, DB::table('shelves')->count());
        $this->assertSame(0, Product::whereNull('shelf_id')->count());
        $this->assertSame(9, DB::table('categories')->count());
        foreach (Product::all() as $product) {
            $this->assertSame($product->current_stock, (int) DB::table('inventory_transactions')->where('product_id', $product->id)->sum('quantity'), $product->sku);
            $this->assertNotNull($product->brand_id);
            $this->assertNotNull($product->supplier_id);
        }
        foreach (DebtAccount::all() as $account) {
            $entries = DB::table('debt_transactions')->where('debt_account_id', $account->id)->get();
            $balance = $entries->sum(fn ($entry) => Money::cents($entry->debit_amount) - Money::cents($entry->credit_amount));
            $this->assertSame($balance, Money::cents($account->outstanding_balance));
            $this->assertSame($balance, $entries->sum(fn ($entry) => Money::cents($entry->remaining_amount)));
            $this->assertSame($balance, Money::cents(Customer::findOrFail($account->customer_id)->outstanding_balance));
        }
        foreach (CashierShift::where('status', 'closed')->get() as $shift) {
            $this->assertSame('0.00', $shift->cash_variance);
            $this->assertSame($shift->expected_cash, $shift->closing_cash);
        }
        foreach (DB::table('refunds')->get() as $refund) {
            $this->assertSame(Money::cents($refund->paid_amount), Money::cents((string) DB::table('refund_payments')->where('refund_id', $refund->id)->sum('amount')));
        }
        $this->assertSame(1, CashierShift::where('status', 'active')->count());
        $this->assertGuest();
    }

    public function test_full_demo_retry_does_not_duplicate_records_or_reset_stock(): void
    {
        $this->seed();
        $before = collect(self::TABLES)->mapWithKeys(fn ($table) => [$table => DB::table($table)->count()])->all();
        $stock = Product::pluck('current_stock', 'sku')->all();
        $this->seed();
        $this->assertSame($before, collect(self::TABLES)->mapWithKeys(fn ($table) => [$table => DB::table($table)->count()])->all());
        $this->assertSame($stock, Product::pluck('current_stock', 'sku')->all());
    }

    public function test_catalog_retry_preserves_edited_prices_and_stock(): void
    {
        $this->seed(WarungCatalogSeeder::class);
        $product = Product::where('sku', 'KOP-001')->firstOrFail();
        $product->update(['selling_price' => '2750.50']);
        $movements = DB::table('inventory_transactions')->count();
        $this->seed(WarungCatalogSeeder::class);
        $this->assertSame('2750.50', $product->fresh()->selling_price);
        $this->assertSame($movements, DB::table('inventory_transactions')->count());
    }

    public function test_demo_refuses_production_before_writing(): void
    {
        $this->app->instance('env', 'production');
        try {
            app(DemoOperationsSeeder::class)->run();
            $this->fail('Demo fixtures must not run in production');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('local or testing', $exception->getMessage());
            $this->assertSame(0, User::count());
        }
    }
}
