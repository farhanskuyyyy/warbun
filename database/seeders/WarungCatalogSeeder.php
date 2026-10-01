<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WarungCatalogSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Warung catalog fixtures require APP_ENV=local or testing.');
        }
        $previous = auth()->user();
        try {
            DB::transaction(function () {
                $this->call([RolesAndPermissionsSeeder::class, MasterDataSeeder::class]);
                auth()->setUser(User::where('email', 'owner@warbun.local')->firstOrFail());
                foreach (['kopi-teh' => 'Kopi & teh', 'camilan' => 'Camilan', 'bayi' => 'Kebutuhan bayi'] as $slug => $name) {
                    Category::firstOrCreate(['slug' => $slug], ['name' => $name, 'description' => 'Kategori katalog demo warung']);
                }
                foreach (['MAYORA' => 'Mayora', 'WINGS' => 'Wings', 'KAPAL' => 'Kapal Api', 'LOCAL' => 'Tanpa merek', 'OTSUKA' => 'Otsuka', 'KINO' => 'Kino', 'SOFTEX' => 'Softex', 'ABC' => 'ABC', 'DANONE' => 'Danone', 'PG' => 'P&G'] as $code => $name) {
                    Brand::firstOrCreate(['code' => $code], ['name' => $name]);
                }
                $suppliers = [];
                foreach (['sembako' => 'Demo Grosir Sembako', 'kemasan' => 'Demo Distributor Kemasan', 'rumah' => 'Demo Grosir Rumah Tangga'] as $key => $name) {
                    $suppliers[$key] = Supplier::firstOrCreate(['name' => $name], ['contact_person' => 'Kontak demo', 'address' => 'Alamat contoh, bukan supplier nyata']);
                }
                foreach ($this->products() as [$sku, $name, $category, $type, $brand, $unit, $cost, $price, $stock, $minimum]) {
                    if (Product::withTrashed()->where('sku', $sku)->exists()) {
                        continue;
                    }
                    $supplier = $type === 'household' ? 'rumah' : ($category === 'sembako' ? 'sembako' : 'kemasan');
                    $product = Product::create([
                        'sku' => $sku, 'name' => $name, 'category_id' => Category::where('slug', $category)->firstOrFail()->id,
                        'product_type_id' => ProductType::where('slug', $type)->firstOrFail()->id, 'brand_id' => Brand::where('code', $brand)->firstOrFail()->id,
                        'unit_id' => Unit::where('symbol', $unit)->firstOrFail()->id, 'supplier_id' => $suppliers[$supplier]->id,
                        'cost_price' => $cost, 'selling_price' => $price, 'current_stock' => 0, 'minimum_stock' => $minimum,
                        'is_available_online' => true, 'is_active' => true, 'description' => 'Produk katalog demo. Harga dan stok merupakan contoh development, bukan penawaran harga resmi.',
                    ]);
                    app(StockService::class)->change($product->id, $stock, 'Demo opening stock', 'initial', null, (string) $cost);
                }
                // Only supply missing master relations; existing prices and stock remain authoritative.
                foreach (['IND-001' => 'INDO', 'IND-002' => 'INDO', 'TEH-001' => 'MAYORA', 'AQU-001' => 'DANONE', 'BER-001' => 'LOCAL', 'GUL-001' => 'LOCAL', 'MIN-001' => 'LOCAL', 'ROK-001' => 'SURYA', 'RIN-001' => 'UNI', 'PAN-001' => 'PG'] as $sku => $brand) {
                    $product = Product::where('sku', $sku)->firstOrFail();
                    if (! $product->brand_id) {
                        $product->update(['brand_id' => Brand::where('code', $brand)->firstOrFail()->id, 'supplier_id' => $suppliers['kemasan']->id, 'is_available_online' => $sku !== 'ROK-001']);
                    }
                }
            });
        } finally {
            $previous ? auth()->setUser($previous) : auth()->forgetUser();
        }
    }

    private function products(): array
    {
        // Research sources and the assumptions behind package sizes/prices are in SEED_DATA.md.
        return [
            ['IND-003', 'Indomie Ayam Bawang', 'makanan', 'food', 'INDO', 'pack', 2700, 3500, 96, 15],
            ['IND-004', 'Indomie Kari Ayam', 'makanan', 'food', 'INDO', 'pack', 2700, 3500, 80, 15],
            ['SED-001', 'Mie Sedaap Goreng', 'makanan', 'food', 'WINGS', 'pack', 2600, 3500, 90, 15],
            ['POP-001', 'Pop Mie Soto Ayam', 'makanan', 'food', 'INDO', 'pcs', 4200, 5500, 36, 8],
            ['SAR-001', 'Sarimi Isi 2 Ayam Kecap', 'makanan', 'food', 'INDO', 'pack', 3500, 4500, 48, 8],
            ['BER-002', 'Beras Medium 1 kg', 'sembako', 'food', 'LOCAL', 'pack', 12500, 15000, 40, 8],
            ['GUL-002', 'Gula Pasir 500 g', 'sembako', 'food', 'LOCAL', 'pack', 8000, 10000, 36, 8],
            ['BIM-001', 'Bimoli Minyak Goreng 1 L', 'sembako', 'food', 'INDO', 'pack', 18000, 22000, 24, 6],
            ['TER-001', 'Segitiga Biru Tepung Terigu 1 kg', 'sembako', 'food', 'INDO', 'pack', 10500, 13000, 20, 5],
            ['GAR-001', 'Garam Beryodium 250 g', 'sembako', 'food', 'LOCAL', 'pack', 2000, 3000, 30, 5],
            ['TEL-001', 'Telur Ayam per Butir', 'sembako', 'food', 'LOCAL', 'pcs', 1800, 2500, 60, 12],
            ['KEC-001', 'ABC Kecap Manis Sachet', 'sembako', 'food', 'ABC', 'sachet', 800, 1500, 80, 12],
            ['SAM-001', 'Indofood Sambal Sachet', 'sembako', 'food', 'INDO', 'sachet', 700, 1000, 60, 10],
            ['BUM-001', 'Royco Kaldu Ayam Sachet', 'sembako', 'food', 'UNI', 'sachet', 400, 1000, 100, 15],
            ['KOP-001', 'Kapal Api Special Mix Sachet', 'kopi-teh', 'beverage', 'KAPAL', 'sachet', 1400, 2000, 100, 20],
            ['KOP-002', 'Good Day Latte Original Sachet', 'kopi-teh', 'beverage', 'KAPAL', 'sachet', 1800, 2500, 80, 15],
            ['TOR-001', 'Torabika Cappuccino Sachet', 'kopi-teh', 'beverage', 'MAYORA', 'sachet', 1800, 2500, 72, 12],
            ['ENE-001', 'Energen Coklat Sachet', 'kopi-teh', 'beverage', 'MAYORA', 'sachet', 1800, 2500, 60, 12],
            ['TEB-001', 'Teh Celup Kotak 25 Sachet', 'kopi-teh', 'beverage', 'LOCAL', 'box', 5000, 7000, 24, 5],
            ['MIN-002', 'Le Minerale 600 ml', 'minuman', 'beverage', 'MAYORA', 'botol', 3000, 4500, 72, 12],
            ['MIN-003', 'Coca-Cola 250 ml', 'minuman', 'beverage', 'COKE', 'botol', 4000, 5500, 30, 8],
            ['SUS-001', 'Indomilk UHT Coklat 190 ml', 'minuman', 'beverage', 'INDO', 'pcs', 4500, 6000, 40, 8],
            ['SUS-002', 'Susu Kental Manis Sachet', 'minuman', 'beverage', 'LOCAL', 'sachet', 1200, 2000, 60, 10],
            ['POC-001', 'Pocari Sweat 350 ml', 'minuman', 'beverage', 'OTSUKA', 'botol', 5000, 7000, 24, 6],
            ['ROM-001', 'Roma Kelapa Biskuit', 'camilan', 'food', 'MAYORA', 'pack', 7000, 9500, 24, 5],
            ['BEN-001', 'Beng-Beng Wafer', 'camilan', 'food', 'MAYORA', 'pcs', 1800, 2500, 60, 12],
            ['KOP-003', 'Kopiko Permen Kopi Pack', 'camilan', 'food', 'MAYORA', 'pack', 3500, 5000, 30, 6],
            ['CHI-001', 'Chitato Sapi Panggang', 'camilan', 'food', 'INDO', 'pack', 7000, 9500, 24, 5],
            ['QTE-001', 'Qtela Singkong Original', 'camilan', 'food', 'INDO', 'pack', 5000, 7000, 24, 5],
            ['AST-001', 'Astor Wafer Roll', 'camilan', 'food', 'MAYORA', 'pack', 4500, 6500, 18, 4],
            ['SUN-001', 'Sunlight Cuci Piring Sachet', 'kebutuhan-rumah', 'household', 'UNI', 'sachet', 1200, 2000, 50, 10],
            ['DAI-001', 'Daia Detergen Sachet', 'kebutuhan-rumah', 'household', 'WINGS', 'sachet', 700, 1500, 60, 10],
            ['SOK-001', 'SoKlin Liquid Sachet', 'kebutuhan-rumah', 'household', 'WINGS', 'sachet', 700, 1500, 60, 10],
            ['DET-001', 'Rinso Detergen Sachet', 'kebutuhan-rumah', 'household', 'UNI', 'sachet', 800, 1500, 60, 10],
            ['MOL-001', 'Molto Pewangi Sachet', 'kebutuhan-rumah', 'household', 'UNI', 'sachet', 700, 1500, 50, 10],
            ['LIF-001', 'Lifebuoy Sabun Batang', 'personal-care', 'household', 'UNI', 'pcs', 3000, 4500, 4, 6],
            ['PEP-001', 'Pepsodent Pasta Gigi', 'personal-care', 'household', 'UNI', 'pcs', 4500, 6500, 24, 5],
            ['SOF-001', 'Softex Pembalut Pack', 'personal-care', 'household', 'SOFTEX', 'pack', 5500, 8000, 18, 4],
            ['MYB-001', 'Minyak Telon Botol', 'bayi', 'household', 'LOCAL', 'botol', 17000, 22000, 12, 3],
            ['POP-002', 'Popok Bayi Pack Kecil', 'bayi', 'household', 'LOCAL', 'pack', 15000, 20000, 0, 3],
            ['TIS-001', 'Tisu Wajah Pack', 'kebutuhan-rumah', 'household', 'LOCAL', 'pack', 4000, 6000, 25, 5],
            ['BAT-001', 'Baterai AA Sepasang', 'kebutuhan-rumah', 'other', 'LOCAL', 'pack', 4000, 6000, 20, 5],
            ['WIP-001', 'Wipol Pembersih Lantai Sachet', 'kebutuhan-rumah', 'household', 'UNI', 'sachet', 1200, 2000, 40, 8],
        ];
    }
}
