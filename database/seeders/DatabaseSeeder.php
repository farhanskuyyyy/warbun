<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment(['local', 'testing'])) {
            $this->call(DemoOperationsSeeder::class);

            return;
        }
        $this->call([
            RolesAndPermissionsSeeder::class,
            MasterDataSeeder::class,
        ]);
    }
}
