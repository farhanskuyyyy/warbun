<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductType;
use App\Models\AuditLog;

class ProductTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductType::query();
        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        $productTypes = $query->latest()->paginate(15);
        return view('product-types.index', compact('productTypes'));
    }

    public function create() { return view('product-types.create'); }

    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|max:255|unique:product_types,name', 'description' => 'nullable|string', 'is_active' => 'boolean']);
        $validated['slug'] = \Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');
        $productType = ProductType::create($validated);
        AuditLog::log('product_type.created', $productType, null, $productType->toArray());
        return redirect()->route('product-types.index')->with('success', 'Product type created');
    }

    public function edit(ProductType $productType) { return view('product-types.edit', compact('productType')); }

    public function update(Request $request, ProductType $productType)
    {
        $validated = $request->validate(['name' => 'required|string|max:255|unique:product_types,name,' . $productType->id, 'description' => 'nullable|string', 'is_active' => 'boolean']);
        $old = $productType->toArray();
        $validated['slug'] = \Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');
        $productType->update($validated);
        AuditLog::log('product_type.updated', $productType, $old, $productType->toArray());
        return redirect()->route('product-types.index')->with('success', 'Product type updated');
    }

    public function destroy(ProductType $productType)
    {
        $old = $productType->toArray();
        $productType->delete();
        AuditLog::log('product_type.deleted', null, $old, null);
        return redirect()->route('product-types.index')->with('success', 'Product type deleted');
    }
}
