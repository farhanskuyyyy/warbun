<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\InventoryTransaction;
use App\Models\AuditLog;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryTransaction::with('product', 'user');
        
        if ($request->type) {
            $query->where('type', $request->type);
        }
        
        if ($request->product_id) {
            $query->where('product_id', $request->product_id);
        }
        
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $transactions = $query->latest()->paginate(20);
        
        $stockSummary = Product::where('is_active', true)
            ->selectRaw('*, (current_stock - minimum_stock) as stock_diff')
            ->orderBy('current_stock')
            ->get();
        
        $lowStockCount = Product::where('current_stock', '<=', DB::raw('minimum_stock'))->where('is_active', true)->count();
        $outOfStockCount = Product::where('current_stock', '<=', 0)->where('is_active', true)->count();
        
        return view('inventory.index', compact('transactions', 'stockSummary', 'lowStockCount', 'outOfStockCount'));
    }

    public function stockIn(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'unit_cost' => 'nullable|numeric|min:0',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $previousStock = $product->current_stock;
        $newStock = $previousStock + $validated['quantity'];

        \DB::transaction(function () use ($product, $validated, $previousStock, $newStock) {
            $product->update(['current_stock' => $newStock]);

            InventoryTransaction::create([
                'reference_number' => 'SI-' . date('Ymd') . '-' . str_pad(InventoryTransaction::whereDate('created_at', today())->count() + 1, 5, '0', STR_PAD_LEFT),
                'product_id' => $product->id,
                'type' => 'stock_in',
                'source_type' => 'manual',
                'quantity' => $validated['quantity'],
                'unit_cost' => $validated['unit_cost'] ?? null,
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'reason' => $validated['reason'],
                'notes' => $validated['notes'] ?? null,
                'user_id' => auth()->id(),
            ]);
        });

        AuditLog::log('inventory.stock_in', $product, ['stock' => $previousStock], ['stock' => $newStock, 'quantity' => $validated['quantity']]);

        return redirect()->route('inventory.index')->with('success', 'Stock added successfully');
    }

    public function stockOut(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        
        if ($product->current_stock < $validated['quantity']) {
            return back()->withErrors(['quantity' => 'Insufficient stock. Available: ' . $product->current_stock]);
        }

        $previousStock = $product->current_stock;
        $newStock = $previousStock - $validated['quantity'];

        \DB::transaction(function () use ($product, $validated, $previousStock, $newStock) {
            $product->update(['current_stock' => $newStock]);

            InventoryTransaction::create([
                'reference_number' => 'SO-' . date('Ymd') . '-' . str_pad(InventoryTransaction::whereDate('created_at', today())->count() + 1, 5, '0', STR_PAD_LEFT),
                'product_id' => $product->id,
                'type' => 'stock_out',
                'source_type' => 'manual',
                'quantity' => -$validated['quantity'],
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'reason' => $validated['reason'],
                'notes' => $validated['notes'] ?? null,
                'user_id' => auth()->id(),
            ]);
        });

        AuditLog::log('inventory.stock_out', $product, ['stock' => $previousStock], ['stock' => $newStock, 'quantity' => -$validated['quantity']]);

        return redirect()->route('inventory.index')->with('success', 'Stock removed successfully');
    }

    public function adjust(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'new_quantity' => 'required|integer|min:0',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $previousStock = $product->current_stock;
        $newStock = $validated['new_quantity'];
        $adjustment = $newStock - $previousStock;

        \DB::transaction(function () use ($product, $validated, $previousStock, $newStock, $adjustment) {
            $product->update(['current_stock' => $newStock]);

            InventoryTransaction::create([
                'reference_number' => 'ADJ-' . date('Ymd') . '-' . str_pad(InventoryTransaction::whereDate('created_at', today())->count() + 1, 5, '0', STR_PAD_LEFT),
                'product_id' => $product->id,
                'type' => 'adjustment',
                'source_type' => 'manual_adjustment',
                'quantity' => $adjustment,
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'reason' => $validated['reason'],
                'notes' => $validated['notes'] ?? null,
                'user_id' => auth()->id(),
            ]);
        });

        AuditLog::log('inventory.adjust', $product, ['stock' => $previousStock], ['stock' => $newStock, 'adjustment' => $adjustment]);

        return redirect()->route('inventory.index')->with('success', 'Stock adjusted successfully');
    }
}
