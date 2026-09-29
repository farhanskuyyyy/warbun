<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Brand;
use App\Models\AuditLog;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $query = Brand::query();
        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        $brands = $query->latest()->paginate(15);
        return view('brands.index', compact('brands'));
    }

    public function create() { return view('brands.create'); }

    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|max:255|unique:brands,name', 'code' => 'nullable|string|max:50', 'description' => 'nullable|string', 'is_active' => 'boolean']);
        $validated['is_active'] = $request->boolean('is_active');
        $brand = Brand::create($validated);
        AuditLog::log('brand.created', $brand, null, $brand->toArray());
        return redirect()->route('brands.index')->with('success', 'Brand created');
    }

    public function edit(Brand $brand) { return view('brands.edit', compact('brand')); }

    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate(['name' => 'required|string|max:255|unique:brands,name,' . $brand->id, 'code' => 'nullable|string|max:50', 'description' => 'nullable|string', 'is_active' => 'boolean']);
        $old = $brand->toArray();
        $validated['is_active'] = $request->boolean('is_active');
        $brand->update($validated);
        AuditLog::log('brand.updated', $brand, $old, $brand->toArray());
        return redirect()->route('brands.index')->with('success', 'Brand updated');
    }

    public function destroy(Brand $brand)
    {
        $old = $brand->toArray();
        $brand->delete();
        AuditLog::log('brand.deleted', null, $old, null);
        return redirect()->route('brands.index')->with('success', 'Brand deleted');
    }
}
