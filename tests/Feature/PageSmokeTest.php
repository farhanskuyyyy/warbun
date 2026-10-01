<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_operational_pages_render_in_both_languages(): void
    {
        $this->seed();
        $this->actingAs(User::where('email', 'owner@warbun.local')->first());
        $paths = ['/dashboard', '/products', '/products/create', '/categories', '/categories/create', '/product-types', '/product-types/create', '/brands', '/brands/create', '/units', '/units/create', '/suppliers', '/suppliers/create', '/inventory', '/stock-opnames', '/pos', '/pos/history', '/debt', '/debt/create', '/debt/dashboard', '/customers', '/customers/create', '/orders', '/payments', '/payments/create', '/refunds', '/shifts', '/users', '/settings', '/reports', '/reports/sales', '/reports/inventory', '/reports/debt', '/reports/payments', '/reports/staff', '/audit', '/profile', '/shop', '/cart'];
        $product = Product::first();
        $paths = array_merge($paths, ['/products/'.$product->id, '/products/'.$product->id.'/edit', '/categories/'.$product->category_id.'/edit', '/product-types/'.$product->product_type_id.'/edit', '/units/'.$product->unit_id.'/edit', '/brands/'.Brand::first()->id.'/edit', '/suppliers/'.Supplier::first()->id.'/edit']);
        foreach (['id', 'en'] as $locale) {
            $this->withSession(['locale' => $locale]);
            foreach ($paths as $path) {
                $this->get($path)->assertOk();
            }
        }
    }

    public function test_catalog_hides_inactive_and_offline_products(): void
    {
        $this->seed();
        $product = Product::first();
        $product->update(['is_available_online' => false]);
        $this->get('/shop/'.$product->id)->assertNotFound();
        $product->update(['is_available_online' => true]);
        $this->get('/shop/'.$product->id)->assertOk();
        $product->update(['is_active' => false]);
        $this->get('/shop/'.$product->id)->assertNotFound();
    }
}
