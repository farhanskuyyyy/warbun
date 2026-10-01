<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StockService
{
    public function change(int $id, int $quantity, string $reason, string $source = 'manual', ?int $sourceId = null, ?string $cost = null, ?string $notes = null): InventoryTransaction
    {
        return DB::transaction(function () use ($id, $quantity, $reason, $source, $sourceId, $cost, $notes) {
            $product = Product::withTrashed()->lockForUpdate()->findOrFail($id);
            if (trim($reason) === '' || $product->current_stock + $quantity < 0) {
                throw ValidationException::withMessages(['quantity' => __('Insufficient stock or missing reason.')]);
            }
            $before = $product->current_stock;
            $product->update(['current_stock' => $before + $quantity]);
            $movement = InventoryTransaction::create([
                'reference_number' => 'INV-'.Str::uuid(), 'product_id' => $id,
                'type' => $source === 'manual_adjustment' || $source === 'opname' ? 'adjustment' : ($quantity >= 0 ? 'stock_in' : 'stock_out'),
                'source_type' => $source, 'source_id' => $sourceId, 'quantity' => $quantity,
                'previous_stock' => $before, 'new_stock' => $before + $quantity,
                'unit_cost' => $cost, 'notes' => $notes, 'reason' => $reason, 'user_id' => auth()->id(),
            ]);
            AuditLog::log('stock.changed', $movement, ['stock' => $before], ['stock' => $before + $quantity]);

            return $movement;
        });
    }
}
