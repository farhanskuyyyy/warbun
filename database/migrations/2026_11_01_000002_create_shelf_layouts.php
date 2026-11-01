<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shelf_layouts', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->unsignedInteger('revision')->default(0);
            $table->json('geometry');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        if (DB::table('shelf_layouts')->where('revision', '>', 0)->exists()) {
            throw new RuntimeException('Export saved shelf layouts before removing their schema.');
        }
        Schema::dropIfExists('shelf_layouts');
    }
};
