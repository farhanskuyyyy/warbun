<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\DebtAccount;
use App\Models\DebtTransaction;
use App\Models\Payment;
use App\Models\Sale;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DebtService
{
    public function debit(int $customerId, int $amount, string $reason, ?int $saleId = null, ?string $due = null, bool $override = false): DebtTransaction
    {
        return DB::transaction(function () use ($customerId, $amount, $reason, $saleId, $due, $override) {
            $customer = Customer::lockForUpdate()->findOrFail($customerId);
            if (! $customer->user_id || ! $customer->is_active || ! $customer->can_use_debt || $customer->debt_status !== 'eligible') {
                $this->invalid('Registered eligible customer required.');
            }
            $account = DebtAccount::firstOrCreate(['customer_id' => $customer->id], ['credit_limit' => $customer->credit_limit]);
            $account = DebtAccount::lockForUpdate()->findOrFail($account->id);
            $balance = Money::cents($account->outstanding_balance) + $amount;
            if ($amount <= 0 || $account->status !== 'active') {
                $this->invalid('Invalid debt account or amount.');
            }
            if ($balance > Money::cents($customer->credit_limit)) {
                if (! $override || ! auth()->user()->can('debt.override-credit-limit')) {
                    $this->invalid('Credit limit exceeded.');
                }
                AuditLog::log('debt.credit_override', $customer, null, ['reason' => $reason, 'amount' => Money::decimal($amount)]);
            }
            $entry = DebtTransaction::create([
                'reference_number' => 'DEBT-'.Str::uuid(), 'debt_account_id' => $account->id, 'customer_id' => $customer->id,
                'sale_id' => $saleId, 'type' => 'new_debt', 'debit_amount' => Money::decimal($amount), 'credit_amount' => 0,
                'remaining_amount' => Money::decimal($amount), 'balance_after' => Money::decimal($balance),
                'due_date' => $due ?? today()->addDays((int) DB::table('store_settings')->where('key', 'debt_terms')->value('value') ?: 14)->toDateString(),
                'status' => 'pending', 'user_id' => auth()->id(), 'description' => $reason,
            ]);
            $account->update(['total_debt' => Money::decimal(Money::cents($account->total_debt) + $amount), 'outstanding_balance' => Money::decimal($balance)]);
            $customer->update(['outstanding_balance' => Money::decimal($balance)]);
            AuditLog::log('debt.created', $entry, null, $entry->toArray());

            return $entry;
        });
    }

    public function credit(int $customerId, int $amount, string $reason, string $type = 'payment', ?Payment $payment = null, ?int $saleId = null): DebtTransaction
    {
        return DB::transaction(function () use ($customerId, $amount, $reason, $type, $payment, $saleId) {
            $customer = Customer::lockForUpdate()->findOrFail($customerId);
            $account = DebtAccount::where('customer_id', $customerId)->lockForUpdate()->firstOrFail();
            $balance = Money::cents($account->outstanding_balance);
            if ($amount <= 0 || $amount > $balance) {
                $this->invalid('Payment exceeds outstanding balance.');
            }
            $query = DebtTransaction::where('debt_account_id', $account->id)->where('remaining_amount', '>', 0);
            if ($saleId) {
                $query->where('sale_id', $saleId);
            }
            $debits = $query->orderBy('due_date')->orderBy('id')->lockForUpdate()->get();
            if ($amount > $debits->sum(fn ($d) => Money::cents($d->remaining_amount))) {
                $this->invalid('Payment exceeds outstanding balance.');
            }
            $entry = DebtTransaction::create([
                'reference_number' => 'CREDIT-'.Str::uuid(), 'debt_account_id' => $account->id, 'customer_id' => $customerId,
                'sale_id' => $saleId, 'payment_id' => $payment?->id, 'type' => $type, 'debit_amount' => 0,
                'credit_amount' => Money::decimal($amount), 'balance_after' => Money::decimal($balance - $amount),
                'status' => 'paid', 'user_id' => auth()->id(), 'description' => $reason,
            ]);
            $remaining = $amount;
            foreach ($debits as $debit) {
                if (! $remaining) {
                    break;
                }
                $old = Money::cents($debit->remaining_amount);
                $used = min($remaining, $old);
                $remaining -= $used;
                DB::table('debt_allocations')->insert(['debt_transaction_id' => $debit->id, 'credit_transaction_id' => $entry->id, 'amount' => Money::decimal($used), 'created_at' => now(), 'updated_at' => now()]);
                $debit->update(['remaining_amount' => Money::decimal($old - $used), 'status' => $old === $used ? 'paid' : 'pending']);
                if ($debit->sale_id) {
                    $sale = Sale::lockForUpdate()->findOrFail($debit->sale_id);
                    $sale->update(['debt_amount' => Money::decimal(Money::cents($sale->debt_amount) - $used), 'paid_amount' => Money::decimal(Money::cents($sale->paid_amount) + ($type === 'payment' ? $used : 0))]);
                }
            }
            $account->update(['outstanding_balance' => Money::decimal($balance - $amount), 'total_paid' => Money::decimal(Money::cents($account->total_paid) + ($type === 'payment' ? $amount : 0))]);
            $customer->update(['outstanding_balance' => Money::decimal($balance - $amount)]);
            AuditLog::log('debt.'.$type, $entry, null, $entry->toArray());

            return $entry;
        });
    }

    private function invalid(string $message): never
    {
        throw ValidationException::withMessages(['amount' => __($message)]);
    }
}
