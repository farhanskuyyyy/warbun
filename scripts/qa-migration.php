<?php

use App\Models\Customer;
use App\Models\DebtTransaction;
use App\Models\User;
use App\Services\DebtService;
use App\Services\PaymentService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Str;

if (getenv('APP_ENV') !== 'testing' || getenv('DB_CONNECTION') !== 'mysql' || ! str_starts_with(getenv('DB_DATABASE') ?: '', 'warbun_qa_')) {
    fwrite(STDERR, "Use a disposable warbun_qa_* MySQL database in testing.\n");
    exit(2);
}
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
auth()->login(User::where('email', 'owner@warbun.local')->firstOrFail());
$user = User::create(['name' => 'Legacy QA', 'email' => 'legacy-'.Str::uuid().'@warbun.local', 'password' => 'password']);
$user->assignRole('customer');
$c = Customer::create(['user_id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'can_use_debt' => true, 'credit_limit' => '2000', 'debt_status' => 'eligible']);
$d = app(DebtService::class)->debit($c->id, 100000, 'legacy principal');
app(PaymentService::class)->receive(['request_key' => (string) Str::uuid(), 'customer_id' => $c->id, 'amount' => '500', 'method' => 'transfer']);
$original = DB::table('debt_transactions')->where('customer_id', $c->id)->orderBy('id')->get(['debit_amount', 'credit_amount', 'balance_after'])->toJson();
$migration = require __DIR__.'/../database/migrations/2026_10_01_000001_add_operational_integrity.php';
$migration->down();
$migration->up();
$principal = DebtTransaction::findOrFail($d->id);
$after = DB::table('debt_transactions')->where('customer_id', $c->id)->orderBy('id')->get(['debit_amount', 'credit_amount', 'balance_after'])->toJson();
if ($original !== $after || $principal->remaining_amount !== '500.00' || $c->fresh()->outstanding_balance !== '500.00') {
    throw new RuntimeException('Legacy balance reconstruction failed.');
}
app(PaymentService::class)->receive(['request_key' => (string) Str::uuid(), 'customer_id' => $c->id, 'amount' => '250', 'method' => 'transfer']);
if ($principal->fresh()->remaining_amount !== '250.00' || $c->fresh()->outstanding_balance !== '250.00') {
    throw new RuntimeException('Migrated principal cannot be settled.');
}
echo json_encode(['status' => 'PASS', 'checks' => ['Legacy debit/credit amounts preserved', 'Remaining principal backfilled', 'Repayment after migration reconciles']])."\n";
