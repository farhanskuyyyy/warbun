<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['customers' => '', 'orders' => 'shipping_', 'sales' => 'shipping_'] as $name => $prefix) {
            Schema::table($name, function (Blueprint $table) use ($prefix) {
                $table->decimal($prefix.'latitude', 10, 7)->nullable();
                $table->decimal($prefix.'longitude', 10, 7)->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (['customers' => '', 'orders' => 'shipping_', 'sales' => 'shipping_'] as $name => $prefix) {
            Schema::table($name, fn (Blueprint $table) => $table->dropColumn([$prefix.'latitude', $prefix.'longitude']));
        }
    }
};
