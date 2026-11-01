<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('service_fee', 15, 2)->default(0);
            $table->json('checkout_snapshot')->nullable();
        });
        Schema::table('order_items', fn (Blueprint $table) => $table->string('product_name')->nullable());
    }

    public function down(): void
    {
        if (DB::table('orders')->whereNotNull('checkout_snapshot')->exists()) {
            throw new RuntimeException('Keep checkout snapshot columns when rolling back application code with recorded orders.');
        }
        Schema::table('order_items', fn (Blueprint $table) => $table->dropColumn('product_name'));
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn(['service_fee', 'checkout_snapshot']));
    }
};
