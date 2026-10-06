<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Services\AddressBookService;
use Illuminate\Database\Seeder;

class CustomerAddressSeeder extends Seeder
{
    public function run(): void
    {
        Customer::orderBy('id')->chunkById(200, function ($customers) {
            foreach ($customers as $customer) {
                app(AddressBookService::class)->importLegacy($customer);
            }
        });
    }
}
