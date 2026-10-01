<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('fulfillment_type', 20)->default('in_store');
            $table->string('fulfillment_status', 20)->nullable()->index();
            $table->text('shipping_address')->nullable();
            $table->decimal('shipping_cost', 15, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['fulfillment_status']);
            $table->dropColumn(['fulfillment_type', 'fulfillment_status', 'shipping_address', 'shipping_cost']);
        });
    }
};
