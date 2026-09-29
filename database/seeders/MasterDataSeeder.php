<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\ProductType;
use App\Models\Brand;
use App\Models\Unit;
use App\Models\Supplier;
use App\Models\Product;

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
        foreach ($categories as $cat) Category::create($cat);

        $types = [
            ['name' => 'Food', 'slug' => 'food'],
            ['name' => 'Beverage', 'slug' => 'beverage'],
            ['name' => 'Household', 'slug' => 'household'],
            ['name' => 'Accessories', 'slug' => 'accessories'],
            ['name' => 'Other', 'slug' => 'other'],
        ];
        foreach ($types as $type) ProductType::create($type);

        $brands = [
            ['name' => 'Indofood', 'code' => 'INDO'],
            ['name' => 'Unilever', 'code' => 'UNI'],
            ['name' => 'Nestle', 'code' => 'NEST'],
            ['name' => 'Coca-Cola', 'code' => 'COKE'],
            ['name' => 'Surya', 'code' => 'SURYA'],
        ];
        foreach ($brands as $brand) Brand::create($brand);

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
        foreach ($units as $unit) Unit::create($unit);

        $suppliers = [
            ['name' => 'PT Sumber Rejeki', 'contact_person' => 'Budi', 'phone' => '081234567890'],
            ['name' => 'CV Maju Bersama', 'contact_person' => 'Andi', 'phone' => '081234567891'],
            ['name' => 'Toko Grosir Sejahtera', 'contact_person' => 'Dewi', 'phone' => '081234567892'],
        ];
        foreach ($suppliers as $supplier) Supplier::create($supplier);

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
        foreach ($products as $product) Product::create($product);
    }
}
