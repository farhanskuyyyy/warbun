<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('label', 80);
            $table->string('recipient_name');
            $table->string('phone', 30)->nullable();
            $table->text('address');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->index(['customer_id', 'is_default']);
        });
        DB::table('customers')->whereNotNull('address')->orderBy('id')->chunkById(200, function ($customers) {
            foreach ($customers as $customer) {
                if (trim($customer->address) === '') {
                    continue;
                }
                DB::table('customer_addresses')->insert([
                    'customer_id' => $customer->id, 'label' => 'Alamat utama',
                    'recipient_name' => $customer->name, 'phone' => $customer->phone,
                    'address' => $customer->address, 'latitude' => $customer->latitude,
                    'longitude' => $customer->longitude, 'is_default' => true,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};
