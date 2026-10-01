<?php

namespace Database\Seeders;

use App\Models\CashierShift;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\DebtService;
use App\Services\OpnameService;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\RefundService;
use App\Services\SaleService;
use App\Services\ShiftService;
use App\Support\Money;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DemoOperationsSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Demo data requires APP_ENV=local or testing.');
        }
        $previousUser = auth()->user();
        $previousTime = Carbon::getTestNow();
        try {
            DB::transaction(function () {
                if (DB::table('store_settings')->where('key', 'demo_dataset_v2')->exists()) {
                    return;
                }
                $this->call(WarungCatalogSeeder::class);
                foreach (['store_name' => 'Warbun Demo', 'debt_terms' => '7', 'shipping_cost' => '10000', 'payment_instructions' => 'Data demo: bayar tunai saat ambil di toko. Untuk transfer, dompet digital, atau QR, minta petunjuk dari kasir. Data demo tidak memiliki rekening atau QR pembayaran aktif.'] as $key => $value) {
                    DB::table('store_settings')->insertOrIgnore(['key' => $key, 'value' => $value, 'created_at' => now(), 'updated_at' => now()]);
                }
                $customers = [];
                foreach (['customer' => 'Demo Pelanggan', 'sari' => 'Demo Bu Sari', 'budi' => 'Demo Pak Budi', 'rani' => 'Demo Mbak Rani', 'agus' => 'Demo Pak Agus', 'dewi' => 'Demo Bu Dewi'] as $key => $name) {
                    $user = User::firstOrCreate(['email' => $key.'@warbun.local'], ['name' => $name, 'password' => 'password']);
                    $user->assignRole('customer');
                    $customers[] = Customer::firstOrCreate(['user_id' => $user->id], ['name' => $user->name, 'email' => $user->email, 'phone' => '080012345'.count($customers), 'address' => 'Alamat demo '.$name.', bukan alamat pelanggan nyata', 'can_use_debt' => $key !== 'dewi', 'credit_limit' => $key !== 'dewi' ? 300000 : 0, 'debt_status' => $key !== 'dewi' ? 'eligible' : 'restricted']);
                }
                $cashier = User::firstOrCreate(['email' => 'demo.cashier@warbun.local'], ['name' => 'Demo Kasir', 'password' => 'password']);
                $cashier->assignRole('cashier');
                $clerk = User::firstOrCreate(['email' => 'demo.stock@warbun.local'], ['name' => 'Demo Petugas Stok', 'password' => 'password']);
                $clerk->assignRole('manager');
                $clerk->givePermissionTo('inventory.approve-adjustment');
                $today = now()->startOfDay();
                for ($day = 6; $day >= 0; $day--) {
                    Carbon::setTestNow($today->copy()->subDays($day)->setTime(9, 0));
                    auth()->setUser($cashier);
                    app(ShiftService::class)->open('150000');
                    $cash = app(SaleService::class)->process(['request_key' => 'demo-v2-cash-'.$day, 'items' => $this->items(['IND-001' => 2, 'MIN-002' => 1]), 'payment_method' => 'cash', 'paid_amount' => '50000']);
                    app(SaleService::class)->process(['request_key' => 'demo-v2-transfer-'.$day, 'items' => $this->items(['ROM-001' => 1, 'BEN-001' => 2]), 'payment_method' => 'transfer', 'paid_amount' => '50000']);
                    $customer = $customers[$day % 5];
                    app(SaleService::class)->process(['request_key' => 'demo-v2-debt-'.$day, 'items' => $this->items(['BER-002' => 1, 'GUL-002' => 1]), 'payment_method' => 'debt', 'paid_amount' => '0', 'customer_id' => $customer->id]);
                    app(PaymentService::class)->receive(['request_key' => 'demo-v2-repayment-'.$day, 'customer_id' => $customer->id, 'method' => 'cash', 'amount' => '5000', 'notes' => 'Demo pembayaran sebagian utang']);
                    if ($day === 2) {
                        app(RefundService::class)->process($cash, [['product_id' => Product::where('sku', 'IND-001')->firstOrFail()->id, 'quantity' => 1]], 'Demo kemasan rusak, retur sebagian', 'demo-v2-refund');
                    }
                    $shift = CashierShift::where('active_user_id', $cashier->id)->firstOrFail();
                    $received = Payment::where('cashier_shift_id', $shift->id)->where('method', 'cash')->whereIn('status', ['paid', 'refunded'])->get()->sum(fn ($payment) => Money::cents($payment->amount));
                    $returned = DB::table('refunds')->where('cashier_shift_id', $shift->id)->get()->sum(fn ($refund) => Money::cents($refund->cash_amount));
                    app(ShiftService::class)->close(Money::decimal(Money::cents($shift->opening_cash) + $received - $returned));
                }
                Carbon::setTestNow($today->copy()->setTime(11, 0));
                auth()->setUser(User::where('email', 'owner@warbun.local')->firstOrFail());
                app(DebtService::class)->debit($customers[1]->id, Money::cents('20000'), 'Demo utang dengan jatuh tempo lewat', null, $today->copy()->subDays(3)->toDateString());
                foreach (['pending', 'confirmed', 'preparing', 'ready', 'completed', 'cancelled'] as $index => $status) {
                    $customer = $customers[$index];
                    auth()->setUser($customer->user);
                    $order = app(OrderService::class)->checkout(['request_key' => 'demo-v2-order-'.$status, 'items' => $this->items(['AQU-001' => 2, 'KOP-001' => 3]), 'delivery_type' => $index % 2 ? 'delivery' : 'pickup', 'address' => $index % 2 ? $customer->address : null, 'payment_method' => 'transfer', 'notes' => 'Pesanan contoh development']);
                    auth()->setUser(User::where('email', 'owner@warbun.local')->firstOrFail());
                    if ($status === 'cancelled') {
                        app(OrderService::class)->transition($order, 'cancelled');

                        continue;
                    }
                    if ($status === 'pending') {
                        continue;
                    }
                    app(OrderService::class)->transition($order, 'confirmed');
                    if (in_array($status, ['ready', 'completed'])) {
                        app(PaymentService::class)->confirm($order->payments()->firstOrFail());
                    }
                    foreach (['preparing', 'ready', 'completed'] as $next) {
                        if ($order->fresh()->status === $status) {
                            break;
                        }
                        app(OrderService::class)->transition($order, $next);
                    }
                }
                auth()->setUser($clerk);
                $product = Product::where('sku', 'TIS-001')->firstOrFail();
                $opname = app(OpnameService::class)->create(['reason' => 'Demo hitung rak, selisih satu pack tisu', 'items' => [['product_id' => $product->id, 'physical_stock' => $product->current_stock - 1]]]);
                app(OpnameService::class)->approve($opname);
                app(OpnameService::class)->create(['reason' => 'Demo opname menunggu persetujuan', 'items' => [['product_id' => $product->id, 'physical_stock' => $product->fresh()->current_stock]]]);
                auth()->setUser($cashier);
                app(ShiftService::class)->open('150000');
                DB::table('store_settings')->insert(['key' => 'demo_dataset_v2', 'value' => 'complete', 'created_at' => now(), 'updated_at' => now()]);
            });
        } finally {
            Carbon::setTestNow($previousTime);
            $previousUser ? auth()->setUser($previousUser) : auth()->forgetUser();
        }
    }

    private function items(array $quantities): array
    {
        return collect($quantities)->map(fn ($quantity, $sku) => ['product_id' => Product::where('sku', $sku)->firstOrFail()->id, 'quantity' => $quantity])->values()->all();
    }
}
