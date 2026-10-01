<?php

use App\Models\InventoryTransaction;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Services\SaleService;
use App\Services\ShiftService;
use App\Services\StockService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Validation\ValidationException;

// Run only against a dedicated disposable MySQL database; see QA_REPORT.md.
if (getenv('DB_CONNECTION') !== 'mysql' || ! str_starts_with(getenv('DB_DATABASE') ?: '', 'warbun_qa_')) {
    fwrite(STDERR, "Use a disposable warbun_qa_* MySQL database.\n");
    exit(2);
}
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$mode = $argv[1] ?? '';
if ($mode === 'setup') {
    auth()->login(User::where('email', 'owner@warbun.local')->firstOrFail());
    $product = Product::where('sku', 'IND-001')->firstOrFail();
    app(StockService::class)->change($product->id, 1 - $product->current_stock, 'QA last item');
    foreach (['owner@warbun.local', 'kasir@warbun.local'] as $email) {
        auth()->login(User::where('email', $email)->firstOrFail());
        app(ShiftService::class)->open('0');
    }
    echo json_encode(['setup' => 'ready']);
} elseif ($mode === 'worker') {
    auth()->login(User::where('email', $argv[2])->firstOrFail());
    $deadline = microtime(true) + 10;
    while (! is_file($argv[3])) {
        if (microtime(true) > $deadline) {
            exit(3);
        }usleep(10000);
    }
    try {
        $sale = app(SaleService::class)->process(['request_key' => $argv[4], 'items' => [['product_id' => Product::where('sku', 'IND-001')->value('id'), 'quantity' => 1]], 'payment_method' => 'cash', 'paid_amount' => '3500']);
        echo json_encode(['status' => 'sold', 'id' => $sale->id]);
    } catch (ValidationException $e) {
        echo json_encode(['status' => 'rejected', 'errors' => array_keys($e->errors())]);
    }
} elseif ($mode === 'verify') {
    $result = ['stock' => Product::where('sku', 'IND-001')->value('current_stock'), 'sales' => Sale::count(), 'payments' => Payment::count(), 'sale_movements' => InventoryTransaction::where('source_type', 'sale')->count()];
    echo json_encode($result);
    if ($result !== ['stock' => 0, 'sales' => 1, 'payments' => 1, 'sale_movements' => 1]) {
        exit(1);
    }
} else {
    exit(2);
}
