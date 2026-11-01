<?php

namespace Tests\Feature;

use App\Models\Shelf;
use App\Models\User;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ShelfLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_layout_is_atomic_versioned_and_rejects_overlap_and_stale_edits(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, MasterDataSeeder::class]);
        $this->actingAs(User::where('email', 'owner@warbun.local')->firstOrFail());
        $a = Shelf::create(['number' => 1, 'name' => 'Food']);
        $b = Shelf::create(['number' => 2, 'name' => 'Drinks']);
        $racks = [['id' => $a->id, 'x' => 1, 'y' => 1, 'width' => 3, 'height' => 2], ['id' => $b->id, 'x' => 6, 'y' => 1, 'width' => 3, 'height' => 2]];
        $this->putJson(route('shelves.layout'), ['revision' => 0, 'racks' => $racks])->assertOk()->assertJsonPath('revision', 1);
        $this->putJson(route('shelves.layout'), ['revision' => 0, 'racks' => $racks])->assertConflict();
        $racks[1]['x'] = 2;
        $this->putJson(route('shelves.layout'), ['revision' => 1, 'racks' => $racks])->assertUnprocessable();
        $this->assertSame(1, DB::table('shelf_layouts')->value('revision'));
        $racks[1]['x'] = 15;
        $this->putJson(route('shelves.layout'), ['revision' => 1, 'racks' => $racks])->assertUnprocessable();
        $this->actingAs(User::where('email', 'kasir@warbun.local')->firstOrFail());
        $this->putJson(route('shelves.layout'), ['revision' => 1, 'racks' => []])->assertForbidden();
    }
}
