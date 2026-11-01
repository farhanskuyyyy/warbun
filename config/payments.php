<?php

return [
    'gateway' => env('PAYMENT_GATEWAY', 'manual'),
    'webhook_secret' => env('PAYMENT_WEBHOOK_SECRET'),
    // These are manual instructions, not automatically activated provider integrations.
    'channels' => ['cash' => ['cash' => 'Cash'], 'transfer' => ['bri' => 'BRI', 'bca' => 'BCA', 'mandiri' => 'Mandiri'], 'ewallet' => ['gopay' => 'GoPay', 'ovo' => 'OVO', 'dana' => 'DANA'], 'qr' => ['qris' => 'QRIS']],
];
