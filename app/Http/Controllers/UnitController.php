<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Unit;
use App\Models\AuditLog;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $query = Unit::query();
        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        $units = $query->latest()->paginate(15);
        return view('units.index', compact('units'));
    }

    public function create() { return view('units.create'); }

    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|max:255|unique:units,name', 'symbol' => 'nullable|string|max:20', 'is_active' => 'boolean']);
        $validated['is_active'] = $request->boolean('is_active');
        $unit = Unit::create($validated);
        AuditLog::log('unit.created', $unit, null, $unit->toArray());
        return redirect()->route('units.index')->with('success', 'Unit created');
    }

    public function edit(Unit $unit) { return view('units.create', compact('unit')); }

    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate(['name' => 'required|string|max:255|unique:units,name,' . $unit->id, 'symbol' => 'nullable|string|max:20', 'is_active' => 'boolean']);
        $old = $unit->toArray();
        $validated['is_active'] = $request->boolean('is_active');
        $unit->update($validated);
        AuditLog::log('unit.updated', $unit, $old, $unit->toArray());
        return redirect()->route('units.index')->with('success', 'Unit updated');
    }

    public function destroy(Unit $unit)
    {
        $old = $unit->toArray();
        $unit->delete();
        AuditLog::log('unit.deleted', null, $old, null);
        return redirect()->route('units.index')->with('success', 'Unit deleted');
    }
}
