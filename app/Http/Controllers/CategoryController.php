<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\AuditLog;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::latest()->paginate(15);
        
        if ($request->search) {
            $categories = Category::where('name', 'like', "%{$request->search}%")->latest()->paginate(15);
        }
        
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = \Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');

        $category = Category::create($validated);

        AuditLog::log('category.created', $category, null, $category->toArray());

        return redirect()->route('categories.index')->with('success', 'Category created successfully');
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $oldValues = $category->toArray();
        $validated['slug'] = \Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');

        $category->update($validated);

        AuditLog::log('category.updated', $category, $oldValues, $category->toArray());

        return redirect()->route('categories.index')->with('success', 'Category updated successfully');
    }

    public function destroy(Category $category)
    {
        $oldValues = $category->toArray();
        $category->delete();
        
        AuditLog::log('category.deleted', null, $oldValues, null);

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully');
    }
}
