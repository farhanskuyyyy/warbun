<?php

use App\Models\User;

require __DIR__.'/qa-browser-setup.php';

$customer = User::where('email', 'customer@warbun.local')->firstOrFail()->customer;
$customer->addresses()->delete();
$customer->update(['name' => 'QA Customer', 'address' => null, 'latitude' => null, 'longitude' => null]);
echo "Address QA customer starts with an empty book.\n";
