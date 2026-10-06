<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment(['local', 'testing'])) {
            $this->call(DemoOperationsSeeder::class);
            $this->call(ShelfSeeder::class);
            $this->call(CustomerAddressSeeder::class);

            return;
        }
        $this->call([
            RolesAndPermissionsSeeder::class,
            MasterDataSeeder::class,
            CustomerAddressSeeder::class,
        ]);
    }
}
