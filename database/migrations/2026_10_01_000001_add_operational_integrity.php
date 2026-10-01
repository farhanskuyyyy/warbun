<?php

use App\Support\Money;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['user_id', 'phone', 'email'] as $column) {
            if (DB::table('customers')->select($column)->whereNotNull($column)->groupBy($column)->havingRaw('COUNT(*) > 1')->exists()) {
                throw new RuntimeException('Duplicate customer '.$column.'; reconcile identities before migration.');
            }
        }
        if (DB::table('cashier_shifts')->select('user_id')->where('status', 'active')->groupBy('user_id')->havingRaw('COUNT(*) > 1')->exists()) {
            throw new RuntimeException('Duplicate active shifts; reconcile before migration.');
        }
        Schema::table('customers', function (Blueprint $t) {
            $t->unique('user_id');
            $t->unique('phone');
            $t->unique('email');
        });
        Schema::table('users', function (Blueprint $t) {
            $t->boolean('is_active')->default(true);
            $t->string('locale', 2)->default('id');
        });
        Schema::table('cashier_shifts', function (Blueprint $t) {
            $t->unsignedBigInteger('active_user_id')->nullable()->unique();
        });
        // Fail rather than silently close duplicate legacy active shifts.
        foreach (DB::table('cashier_shifts')->where('status', 'active')->get() as $shift) {
            DB::table('cashier_shifts')->where('id', $shift->id)->update(['active_user_id' => $shift->user_id]);
        }
        foreach (['sales', 'orders', 'payments'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->string('request_key', 80)->nullable()->unique();
            });
        }
        Schema::table('payments', function (Blueprint $t) {
            $t->foreignId('cashier_shift_id')->nullable()->constrained()->restrictOnDelete();
        });
        Schema::table('debt_transactions', function (Blueprint $t) {
            $t->decimal('remaining_amount', 15, 2)->default(0);
            $t->foreignId('payment_id')->nullable()->constrained()->restrictOnDelete();
        });
        Schema::create('debt_allocations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('debt_transaction_id')->constrained()->restrictOnDelete();
            $t->foreignId('credit_transaction_id')->constrained('debt_transactions')->restrictOnDelete();
            $t->decimal('amount', 15, 2);
            $t->timestamps();
        });
        Schema::create('stock_opnames', function (Blueprint $t) {
            $t->id();
            $t->string('reference_number')->unique();
            $t->string('status')->default('pending');
            $t->foreignId('user_id')->constrained()->restrictOnDelete();
            $t->foreignId('approved_by')->nullable()->constrained('users')->restrictOnDelete();
            $t->text('reason');
            $t->timestamps();
        });
        Schema::create('stock_opname_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('stock_opname_id')->constrained()->restrictOnDelete();
            $t->foreignId('product_id')->constrained()->restrictOnDelete();
            $t->integer('expected_stock');
            $t->integer('physical_stock');
            $t->unique(['stock_opname_id', 'product_id']);
            $t->timestamps();
        });
        Schema::create('refunds', function (Blueprint $t) {
            $t->id();
            $t->string('reference_number')->unique();
            $t->string('refundable_type');
            $t->unsignedBigInteger('refundable_id');
            $t->decimal('amount', 15, 2);
            $t->decimal('cash_amount', 15, 2)->default(0);
            $t->decimal('paid_amount', 15, 2)->default(0);
            $t->decimal('debt_amount', 15, 2)->default(0);
            $t->foreignId('cashier_shift_id')->nullable()->constrained()->restrictOnDelete();
            $t->foreignId('user_id')->constrained()->restrictOnDelete();
            $t->text('reason');
            $t->string('request_key', 80)->unique();
            $t->timestamps();
            $t->index(['refundable_type', 'refundable_id']);
        });
        Schema::create('refund_payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('refund_id')->constrained()->restrictOnDelete();
            $t->foreignId('payment_id')->constrained()->restrictOnDelete();
            $t->decimal('amount', 15, 2);
            $t->timestamps();
        });
        Schema::create('refund_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('refund_id')->constrained()->restrictOnDelete();
            $t->foreignId('product_id')->constrained()->restrictOnDelete();
            $t->integer('quantity');
            $t->decimal('amount', 15, 2);
            $t->timestamps();
        });
        Schema::create('store_settings', function (Blueprint $t) {
            $t->string('key')->primary();
            $t->text('value');
            $t->timestamps();
        });
        Schema::create('notifications', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('type');
            $t->morphs('notifiable');
            $t->text('data');
            $t->timestamp('read_at')->nullable();
            $t->timestamps();
        });
        // Reconstruct open principal from chronological legacy credits; ledger values stay unchanged.
        foreach (DB::table('debt_accounts')->get() as $account) {
            $credits = (string) DB::table('debt_transactions')->where('debt_account_id', $account->id)->sum('credit_amount');
            $credit = Money::cents($credits);
            foreach (DB::table('debt_transactions')->where('debt_account_id', $account->id)->where('debit_amount', '>', 0)->orderBy('due_date')->orderBy('id')->get() as $entry) {
                $amount = Money::cents($entry->debit_amount);
                $used = min($credit, $amount);
                $credit -= $used;
                DB::table('debt_transactions')->where('id', $entry->id)->update(['remaining_amount' => Money::decimal($amount - $used)]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $t) {
            // MySQL may replace the implicit FK index with the unique identity index.
            if (! Schema::hasIndex('customers', 'customers_user_id_restore_index')) {
                $t->index('user_id', 'customers_user_id_restore_index');
            }
            $t->dropUnique(['user_id']);
            $t->dropUnique(['phone']);
            $t->dropUnique(['email']);
        });
        foreach (['notifications', 'store_settings', 'refund_items', 'refund_payments', 'refunds', 'stock_opname_items', 'stock_opnames', 'debt_allocations'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('debt_transactions', function (Blueprint $t) {
            $t->dropConstrainedForeignId('payment_id');
            $t->dropColumn('remaining_amount');
        });
        Schema::table('payments', function (Blueprint $t) {
            $t->dropConstrainedForeignId('cashier_shift_id');
        });
        foreach (['sales', 'orders', 'payments'] as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->dropColumn('request_key'));
        }
        Schema::table('cashier_shifts', fn (Blueprint $t) => $t->dropColumn('active_user_id'));
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['is_active', 'locale']));
    }
};
