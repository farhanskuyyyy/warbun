<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();
        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        $suppliers = $query->latest()->paginate(15);

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:suppliers,name',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        $supplier = Supplier::create($validated);
        AuditLog::log('supplier.created', $supplier, null, $supplier->toArray());

        return redirect()->route('suppliers.index')->with('success', __('Supplier created'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:suppliers,name,'.$supplier->id,
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        $old = $supplier->toArray();
        $validated['is_active'] = $request->boolean('is_active');
        $supplier->update($validated);
        AuditLog::log('supplier.updated', $supplier, $old, $supplier->toArray());

        return redirect()->route('suppliers.index')->with('success', __('Supplier updated'));
    }

    public function destroy(Supplier $supplier)
    {
        $old = $supplier->toArray();
        $supplier->delete();
        AuditLog::log('supplier.deleted', null, $old, null);

        return redirect()->route('suppliers.index')->with('success', __('Supplier deleted'));
    }
}
