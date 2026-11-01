<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Shelf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShelfLayoutController extends Controller
{
    public function products(Shelf $shelf)
    {
        return response()->json($shelf->products()->withTrashed()->orderBy('name')->paginate(30, ['id', 'name', 'sku', 'current_stock', 'shelf_position']));
    }

    public function update(Request $request)
    {
        $data = $request->validate(['revision' => 'required|integer|min:0', 'racks' => 'required|array|max:192', 'racks.*.id' => 'required|integer|distinct|exists:shelves,id', 'racks.*.x' => 'required|integer|min:0|max:15', 'racks.*.y' => 'required|integer|min:0|max:11', 'racks.*.width' => 'required|integer|min:1|max:16', 'racks.*.height' => 'required|integer|min:1|max:12']);
        $occupied = [];
        foreach ($data['racks'] as $rack) {
            if ($rack['x'] + $rack['width'] > 16 || $rack['y'] + $rack['height'] > 12) {
                throw ValidationException::withMessages(['racks' => __('A shelf is outside the grid.')]);
            }
            for ($x = $rack['x']; $x < $rack['x'] + $rack['width']; $x++) {
                for ($y = $rack['y']; $y < $rack['y'] + $rack['height']; $y++) {
                    $key = $x.':'.$y;
                    if (isset($occupied[$key])) {
                        throw ValidationException::withMessages(['racks' => __('Shelves cannot overlap.')]);
                    }
                    $occupied[$key] = true;
                }
            }
        }

        return DB::transaction(function () use ($data) {
            DB::table('shelf_layouts')->insertOrIgnore(['id' => 1, 'revision' => 0, 'geometry' => '[]', 'created_at' => now(), 'updated_at' => now()]);
            $layout = DB::table('shelf_layouts')->where('id', 1)->lockForUpdate()->first();
            abort_if($layout->revision !== $data['revision'], 409, __('This layout changed. Reload before saving. Your draft is preserved.'));
            $ids = Shelf::orderBy('id')->lockForUpdate()->pluck('id')->all();
            foreach ($data['racks'] as $rack) {
                if (! in_array($rack['id'], $ids)) {
                    throw ValidationException::withMessages(['racks' => __('A shelf was removed. Reload before saving.')]);
                }
            }
            $revision = $layout->revision + 1;
            DB::table('shelf_layouts')->where('id', 1)->update(['revision' => $revision, 'geometry' => json_encode($data['racks']), 'updated_at' => now()]);
            AuditLog::log('shelf.layout_updated', null, ['revision' => $layout->revision], ['revision' => $revision, 'racks' => $data['racks']]);

            return response()->json(['revision' => $revision, 'racks' => $data['racks']]);
        }, 3);
    }
}
