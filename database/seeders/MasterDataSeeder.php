<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Makanan', 'slug' => 'makanan', 'description' => 'Produk makanan'],
            ['name' => 'Minuman', 'slug' => 'minuman', 'description' => 'Produk minuman'],
            ['name' => 'Sembako', 'slug' => 'sembako', 'description' => 'Sembilan bahan pokok'],
            ['name' => 'Rokok', 'slug' => 'rokok', 'description' => 'Produk rokok'],
            ['name' => 'Kebutuhan Rumah', 'slug' => 'kebutuhan-rumah', 'description' => 'Produk rumah tangga'],
            ['name' => 'Personal Care', 'slug' => 'personal-care', 'description' => 'Perawatan pribadi'],
        ];
        foreach ($categories as $cat) {
            Category::firstOrCreate(['slug' => $cat['slug']], $cat);
        }

        $types = [
            ['name' => 'Food', 'slug' => 'food'],
            ['name' => 'Beverage', 'slug' => 'beverage'],
            ['name' => 'Household', 'slug' => 'household'],
            ['name' => 'Accessories', 'slug' => 'accessories'],
            ['name' => 'Other', 'slug' => 'other'],
        ];
        foreach ($types as $type) {
            ProductType::firstOrCreate(['slug' => $type['slug']], $type);
        }

        $brands = [
            ['name' => 'Indofood', 'code' => 'INDO'],
            ['name' => 'Unilever', 'code' => 'UNI'],
            ['name' => 'Nestle', 'code' => 'NEST'],
            ['name' => 'Coca-Cola', 'code' => 'COKE'],
            ['name' => 'Surya', 'code' => 'SURYA'],
        ];
        foreach ($brands as $brand) {
            Brand::firstOrCreate(['code' => $brand['code']], $brand);
        }

        $units = [
            ['name' => 'Piece', 'symbol' => 'pcs'],
            ['name' => 'Box', 'symbol' => 'box'],
            ['name' => 'Bottle', 'symbol' => 'botol'],
            ['name' => 'Pack', 'symbol' => 'pack'],
            ['name' => 'Kilogram', 'symbol' => 'kg'],
            ['name' => 'Gram', 'symbol' => 'g'],
            ['name' => 'Liter', 'symbol' => 'L'],
            ['name' => 'Sachet', 'symbol' => 'sachet'],
        ];
        foreach ($units as $unit) {
            Unit::firstOrCreate(['symbol' => $unit['symbol']], $unit);
        }

        $suppliers = [
            ['name' => 'PT Sumber Rejeki', 'contact_person' => 'Budi', 'phone' => '081234567890'],
            ['name' => 'CV Maju Bersama', 'contact_person' => 'Andi', 'phone' => '081234567891'],
            ['name' => 'Toko Grosir Sejahtera', 'contact_person' => 'Dewi', 'phone' => '081234567892'],
        ];
        foreach ($suppliers as $supplier) {
            Supplier::firstOrCreate(['name' => $supplier['name']], $supplier);
        }

        $products = [
            ['name' => 'Indomie Goreng', 'sku' => 'IND-001', 'category_id' => 1, 'product_type_id' => 1, 'unit_id' => 8, 'cost_price' => 2500, 'selling_price' => 3500, 'current_stock' => 100, 'minimum_stock' => 20],
            ['name' => 'Indomie Kuah Soto', 'sku' => 'IND-002', 'category_id' => 1, 'product_type_id' => 1, 'unit_id' => 8, 'cost_price' => 2500, 'selling_price' => 3500, 'current_stock' => 80, 'minimum_stock' => 20],
            ['name' => 'Teh Pucuk 350ml', 'sku' => 'TEH-001', 'category_id' => 2, 'product_type_id' => 2, 'unit_id' => 3, 'cost_price' => 3000, 'selling_price' => 4000, 'current_stock' => 50, 'minimum_stock' => 10],
            ['name' => 'Aqua 600ml', 'sku' => 'AQU-001', 'category_id' => 2, 'product_type_id' => 2, 'unit_id' => 3, 'cost_price' => 3500, 'selling_price' => 5000, 'current_stock' => 48, 'minimum_stock' => 10],
            ['name' => 'Beras Premium 5kg', 'sku' => 'BER-001', 'category_id' => 3, 'product_type_id' => 1, 'unit_id' => 1, 'cost_price' => 55000, 'selling_price' => 65000, 'current_stock' => 25, 'minimum_stock' => 5],
            ['name' => 'Gula Pasir 1kg', 'sku' => 'GUL-001', 'category_id' => 3, 'product_type_id' => 1, 'unit_id' => 1, 'cost_price' => 12000, 'selling_price' => 15000, 'current_stock' => 30, 'minimum_stock' => 10],
            ['name' => 'Minyak Goreng 1L', 'sku' => 'MIN-001', 'category_id' => 3, 'product_type_id' => 1, 'unit_id' => 7, 'cost_price' => 14000, 'selling_price' => 18000, 'current_stock' => 40, 'minimum_stock' => 10],
            ['name' => 'Rokok Surya 12', 'sku' => 'ROK-001', 'category_id' => 4, 'product_type_id' => 1, 'unit_id' => 1, 'cost_price' => 18000, 'selling_price' => 20000, 'current_stock' => 60, 'minimum_stock' => 10],
            ['name' => 'Rinso 800g', 'sku' => 'RIN-001', 'category_id' => 5, 'product_type_id' => 3, 'unit_id' => 1, 'cost_price' => 12000, 'selling_price' => 15000, 'current_stock' => 20, 'minimum_stock' => 5],
            ['name' => 'Pantene Shampoo 160ml', 'sku' => 'PAN-001', 'category_id' => 6, 'product_type_id' => 3, 'unit_id' => 3, 'cost_price' => 18000, 'selling_price' => 22000, 'current_stock' => 15, 'minimum_stock' => 5],
        ];
        $actor = User::where('email', 'owner@warbun.local')->first();
        foreach ($products as $data) {
            if (Product::where('sku', $data['sku'])->exists()) {
                continue;
            }
            $data['category_id'] = Category::where('slug', $categories[$data['category_id'] - 1]['slug'])->value('id');
            $data['product_type_id'] = ProductType::where('slug', $types[$data['product_type_id'] - 1]['slug'])->value('id');
            $data['unit_id'] = Unit::where('symbol', $units[$data['unit_id'] - 1]['symbol'])->value('id');
            $quantity = $data['current_stock'];
            $data['current_stock'] = 0;
            \DB::transaction(function () use ($data, $quantity, $actor) {
                $product = Product::create($data);
                $product->update(['current_stock' => $quantity]);
                InventoryTransaction::create(['reference_number' => 'SEED-'.Str::uuid(), 'product_id' => $product->id, 'quantity' => $quantity, 'previous_stock' => 0, 'new_stock' => $quantity, 'type' => 'stock_in', 'source_type' => 'initial', 'reason' => 'Development initial stock', 'user_id' => $actor->id]);
            });
        }
    }
}
