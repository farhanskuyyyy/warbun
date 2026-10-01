<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Product;
use App\Models\StockOpname;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OpnameService
{
    public function create(array $data): StockOpname
    {
        return DB::transaction(function () use ($data) {
            $opname = StockOpname::create(['reference_number' => 'COUNT-'.Str::uuid(), 'user_id' => auth()->id(), 'reason' => $data['reason']]);
            foreach (collect($data['items'])->sortBy('product_id') as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                $opname->items()->create(['product_id' => $product->id, 'expected_stock' => $product->current_stock, 'physical_stock' => $item['physical_stock']]);
            }
            AuditLog::log('opname.created', $opname, null, $opname->toArray());

            return $opname;
        });
    }

    public function approve(StockOpname $opname): StockOpname
    {
        return DB::transaction(function () use ($opname) {
            $opname = StockOpname::lockForUpdate()->findOrFail($opname->id);
            if ($opname->status === 'approved') {
                return $opname;
            }
            foreach ($opname->items()->orderBy('product_id')->get() as $item) {
                $product = Product::lockForUpdate()->findOrFail($item->product_id);
                if ($product->current_stock !== $item->expected_stock) {
                    throw ValidationException::withMessages(['items' => __('Stock changed. Recount before approval.')]);
                }
                app(StockService::class)->change($product->id, $item->physical_stock - $item->expected_stock, $opname->reason, 'opname', $opname->id);
            }
            $opname->update(['status' => 'approved', 'approved_by' => auth()->id()]);
            AuditLog::log('opname.approved', $opname, null, $opname->toArray());

            return $opname;
        }, 3);
    }
}
