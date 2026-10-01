<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\Supplier;
use App\Models\Unit;
use App\Services\StockService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand', 'unit']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('sku', 'like', "%{$request->search}%")
                    ->orWhere('barcode', 'like', "%{$request->search}%");
            });
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->status === 'active') {
            $query->where('is_active', true);
        } elseif ($request->status === 'inactive') {
            $query->where('is_active', false);
        }

        $products = $query->latest()->paginate(15);

        return view('products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        $productTypes = ProductType::where('is_active', true)->get();
        $brands = Brand::where('is_active', true)->get();
        $units = Unit::where('is_active', true)->get();
        $suppliers = Supplier::where('is_active', true)->get();

        return view('products.create', compact('categories', 'productTypes', 'brands', 'units', 'suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:50|unique:products,sku',
            'barcode' => 'nullable|string|max:50|unique:products,barcode',
            'category_id' => 'required|exists:categories,id',
            'product_type_id' => 'required|exists:product_types,id',
            'brand_id' => 'nullable|exists:brands,id',
            'unit_id' => 'required|exists:units,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'description' => 'nullable|string',
            'cost_price' => 'required|decimal:0,2|min:0|max:999999999999.99',
            'selling_price' => 'required|decimal:0,2|min:0|max:999999999999.99',
            'minimum_stock' => 'required|integer|min:0',
            'current_stock' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'is_available_online' => 'boolean',
            'is_featured' => 'boolean',
            'weight' => 'nullable|numeric|min:0',
            'image_upload' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image_upload')) {
            $validated['image'] = $request->file('image_upload')->store('products', 'public');
        }
        unset($validated['image_upload']);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_available_online'] = $request->boolean('is_available_online');
        $validated['is_featured'] = $request->boolean('is_featured');

        $product = \DB::transaction(function () use ($validated) {
            $quantity = $validated['current_stock'];
            $validated['current_stock'] = 0;
            $product = Product::create($validated);
            if ($quantity) {
                app(StockService::class)->change($product->id, $quantity, 'Initial stock', 'initial', $product->id);
            }
            AuditLog::log('product.created', $product, null, $product->toArray());

            return $product;
        });

        return redirect()->route('products.index')->with('success', __('Product created successfully'));
    }

    public function show(Product $product)
    {
        $product->load(['category', 'productType', 'brand', 'unit', 'supplier']);

        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::where('is_active', true)->get();
        $productTypes = ProductType::where('is_active', true)->get();
        $brands = Brand::where('is_active', true)->get();
        $units = Unit::where('is_active', true)->get();
        $suppliers = Supplier::where('is_active', true)->get();

        return view('products.edit', compact('product', 'categories', 'productTypes', 'brands', 'units', 'suppliers'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:50|unique:products,sku,'.$product->id,
            'barcode' => 'nullable|string|max:50|unique:products,barcode,'.$product->id,
            'category_id' => 'required|exists:categories,id',
            'product_type_id' => 'required|exists:product_types,id',
            'brand_id' => 'nullable|exists:brands,id',
            'unit_id' => 'required|exists:units,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'description' => 'nullable|string',
            'cost_price' => 'required|decimal:0,2|min:0|max:999999999999.99',
            'selling_price' => 'required|decimal:0,2|min:0|max:999999999999.99',
            'minimum_stock' => 'required|integer|min:0',
            'current_stock' => 'prohibited',
            'is_active' => 'boolean',
            'is_available_online' => 'boolean',
            'is_featured' => 'boolean',
            'weight' => 'nullable|numeric|min:0',
            'image_upload' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image_upload')) {
            $validated['image'] = $request->file('image_upload')->store('products', 'public');
        }
        unset($validated['image_upload']);
        $oldValues = $product->toArray();
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_available_online'] = $request->boolean('is_available_online');
        $validated['is_featured'] = $request->boolean('is_featured');

        \DB::transaction(function () use ($product, $validated) {
            $product = Product::lockForUpdate()->findOrFail($product->id);
            $old = $product->toArray();
            $product->update($validated);
            AuditLog::log('product.updated', $product, $old, $product->toArray());
        });

        return redirect()->route('products.index')->with('success', __('Product updated successfully'));
    }

    public function destroy(Product $product)
    {
        $oldValues = $product->toArray();
        $product->delete();

        AuditLog::log('product.deleted', null, $oldValues, null);

        return redirect()->route('products.index')->with('success', __('Product deleted successfully'));
    }
}
