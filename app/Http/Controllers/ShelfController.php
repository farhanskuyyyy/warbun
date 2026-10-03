<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Product;
use App\Models\Shelf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ShelfController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate(['search' => 'nullable|string|max:100', 'shelf' => ['nullable', 'regex:/^(unassigned|[1-9][0-9]*)$/']]);
        $shelves = Shelf::withCount(['products' => fn ($q) => $q->withTrashed()])->orderBy('number')->get();
        $query = Product::withTrashed()->with('shelf', 'category')->orderBy('name')->orderBy('id');
        if ($search = $filters['search'] ?? null) {
            $query->where(fn ($q) => $q->where('name', 'like', "%$search%")->orWhere('sku', 'like', "%$search%")->orWhere('barcode', 'like', "%$search%"));
        }
        if ($shelf = $filters['shelf'] ?? null) {
            $shelf === 'unassigned' ? $query->whereNull('shelf_id') : $query->where('shelf_id', $shelf);
        }
        $products = $query->paginate(20)->withQueryString();
        $unassigned = Product::withTrashed()->whereNull('shelf_id')->count();

        return view('shelves.index', compact('shelves', 'products', 'unassigned'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['number' => 'required|integer|min:1|max:9999|unique:shelves,number', 'name' => 'required|string|max:100']);
        DB::transaction(function () use ($data) {
            $shelf = Shelf::create($data);
            AuditLog::log('shelf.created', $shelf, null, $shelf->toArray());
        });

        return back()->with('success', __('Shelf created. Assign products below.'));
    }

    public function update(Request $request, Shelf $shelf)
    {
        $data = $request->validate(['number' => ['required', 'integer', 'min:1', 'max:9999', Rule::unique('shelves', 'number')->ignore($shelf)], 'name' => 'required|string|max:100']);
        DB::transaction(function () use ($shelf, $data) {
            $locked = Shelf::lockForUpdate()->findOrFail($shelf->id);
            $old = $locked->toArray();
            $locked->update($data);
            AuditLog::log('shelf.updated', $locked, $old, $locked->toArray());
        });

        return back()->with('success', __('Shelf updated.'));
    }

    public function destroy(Shelf $shelf)
    {
        DB::transaction(function () use ($shelf) {
            $locked = Shelf::lockForUpdate()->findOrFail($shelf->id);
            if ($locked->products()->withTrashed()->exists()) {
                throw ValidationException::withMessages(['shelf' => __('Move all products, including archived products, before deleting this shelf.')]);
            }
            AuditLog::log('shelf.deleted', $locked, $locked->toArray(), null);
            $locked->delete();
        });

        return back()->with('success', __('Empty shelf deleted.'));
    }

    public function place(Request $request, Product $product)
    {
        $data = $request->validate(['shelf_id' => 'nullable|integer|exists:shelves,id', 'shelf_position' => 'nullable|string|max:100']);
        DB::transaction(function () use ($product, $data) {
            if ($data['shelf_id'] ?? null) {
                Shelf::lockForUpdate()->findOrFail($data['shelf_id']);
            }
            $locked = Product::withTrashed()->lockForUpdate()->findOrFail($product->id);
            $old = $locked->only('shelf_id', 'shelf_position');
            $locked->update(['shelf_id' => $data['shelf_id'] ?? null, 'shelf_position' => ($data['shelf_id'] ?? null) ? ($data['shelf_position'] ?? null) : null]);
            AuditLog::log('product.placed', $locked, $old, $locked->only('shelf_id', 'shelf_position'));
        });

        return back()->with('success', __('Product location saved.'));
    }
}
