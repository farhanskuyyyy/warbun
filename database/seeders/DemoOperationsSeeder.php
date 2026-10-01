<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Services\OrderService;
use App\Services\SaleService;
use App\Services\ShiftService;
use App\Support\Money;
use Illuminate\Database\Seeder;

class DemoOperationsSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new \RuntimeException('Demo data is for isolated development only.');
        }
        $this->call([RolesAndPermissionsSeeder::class, MasterDataSeeder::class]);
        $user = User::firstOrCreate(['email' => 'customer@warbun.local'], ['name' => 'Demo Customer', 'password' => 'password']);
        $user->assignRole('customer');
        $customer = Customer::firstOrCreate(['user_id' => $user->id], ['name' => $user->name, 'email' => $user->email, 'phone' => '0800123456', 'can_use_debt' => true, 'credit_limit' => 100000, 'debt_status' => 'eligible']);
        $cashier = User::where('email', 'kasir@warbun.local')->first();
        auth()->login($cashier);
        if (! Sale::where('request_key', 'demo-cash-sale')->exists()) {
            app(ShiftService::class)->open('10000');
            app(SaleService::class)->process(['request_key' => 'demo-cash-sale', 'items' => [['product_id' => Product::where('sku', 'IND-001')->firstOrFail()->id, 'quantity' => 1]], 'payment_method' => 'cash', 'paid_amount' => '10000']);
            app(SaleService::class)->process(['request_key' => 'demo-debt-sale', 'items' => [['product_id' => Product::where('sku', 'IND-001')->firstOrFail()->id, 'quantity' => 1]], 'payment_method' => 'debt', 'paid_amount' => '0', 'customer_id' => $customer->id]);
            app(ShiftService::class)->close(Money::decimal(1000000 + Money::cents(Product::where('sku', 'IND-001')->firstOrFail()->selling_price)));
        }
        $product = Product::where('sku', 'IND-001')->firstOrFail();
        $product->update(['is_available_online' => true]);
        auth()->login($user);
        app(OrderService::class)->checkout(['request_key' => 'demo-online-order', 'items' => [['product_id' => $product->id, 'quantity' => 1]], 'delivery_type' => 'pickup', 'payment_method' => 'transfer']);
    }
}
