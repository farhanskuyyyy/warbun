<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Shelf;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShelfSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Sample shelf layout requires APP_ENV=local or testing.');
        }
        DB::transaction(function () {
            if (DB::table('store_settings')->where('key', 'demo_shelves_v1')->exists()) {
                return;
            }
            if (! Product::exists()) {
                return;
            }
            $groups = ['makanan' => 'Makanan kemasan', 'minuman' => 'Minuman', 'sembako' => 'Sembako', 'rokok' => 'Rokok di area kasir', 'personal-care' => 'Perawatan tubuh', 'kopi-teh' => 'Kopi dan teh', 'bayi' => 'Kebutuhan bayi', 'camilan' => 'Camilan', 'kebutuhan-rumah' => 'Kebutuhan rumah'];
            foreach ($groups as $slug => $name) {
                $products = Product::whereNull('shelf_id')->whereHas('category', fn ($q) => $q->where('slug', $slug))->orderBy('sku')->get();
                if ($products->isEmpty()) {
                    continue;
                }
                $shelf = Shelf::firstOrCreate(['name' => 'Contoh: '.$name], ['number' => (Shelf::max('number') ?? 0) + 1]);
                foreach ($products as $index => $product) {
                    $product->update(['shelf_id' => $shelf->id, 'shelf_position' => 'Contoh: tingkat '.(intdiv($index, 5) + 1).', urutan '.($index % 5 + 1)]);
                }
            }
            DB::table('store_settings')->insert(['key' => 'demo_shelves_v1', 'value' => 'sample-layout', 'created_at' => now(), 'updated_at' => now()]);
        });
    }
}
