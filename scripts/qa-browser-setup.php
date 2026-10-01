<?php

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;

$database = getenv('DB_DATABASE') ?: '';
if (getenv('APP_ENV') !== 'testing' || getenv('DB_CONNECTION') !== 'sqlite' || ! str_contains($database, 'warbun-qa-') || ! is_file($database)) {
    fwrite(STDERR, "Use APP_ENV=testing, SQLite, and an existing database file inside a disposable warbun-qa-* directory.\n");
    exit(2);
}
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
Artisan::call('migrate:fresh', ['--force' => true]);
Artisan::call('db:seed', ['--force' => true]);
Product::query()->update(['is_available_online' => true]);
$user = User::firstOrCreate(['email' => 'customer@warbun.local'], ['name' => 'QA Customer', 'password' => 'password']);
$user->assignRole('customer');
Customer::firstOrCreate(['user_id' => $user->id], ['name' => $user->name, 'email' => $user->email, 'phone' => '0800123456', 'credit_limit' => 100000, 'can_use_debt' => true, 'debt_status' => 'eligible']);
echo "Disposable browser QA fixtures ready.\n";
