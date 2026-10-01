<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\CashierShift;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\Sale;
use App\Models\User;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RefundService
{
    public function all(Sale|Order $source, string $reason, string $key): Refund
    {
        return DB::transaction(function () use ($source, $reason, $key) {
            User::lockForUpdate()->findOrFail(auth()->id());
            if ($source->customer_id) {
                Customer::lockForUpdate()->findOrFail($source->customer_id);
            }
            $source = $source::lockForUpdate()->findOrFail($source->id);
            $returned = Refund::where('refundable_type', $source::class)->where('refundable_id', $source->id)->with('items')->get()->flatMap->items;
            $items = $source->items->map(fn ($item) => ['product_id' => $item->product_id, 'quantity' => $item->quantity - $returned->where('product_id', $item->product_id)->sum('quantity')])->filter(fn ($item) => $item['quantity'] > 0)->values()->all();

            return $this->process($source, $items, $reason, $key);
        }, 3);
    }

    public function process(Sale|Order $source, array $items, string $reason, string $key): Refund
    {
        return DB::transaction(function () use ($source, $items, $reason, $key) {
            User::lockForUpdate()->findOrFail(auth()->id());
            $old = Refund::where('request_key', $key)->first();
            if ($old) {
                abort_unless($old->user_id === auth()->id(), 403);

                return $old;
            }
            if ($source->customer_id) {
                Customer::lockForUpdate()->findOrFail($source->customer_id);
            }
            $source = $source::lockForUpdate()->findOrFail($source->id);
            if (! in_array($source->status, $source instanceof Sale ? ['completed'] : ['confirmed', 'preparing', 'ready', 'completed']) || trim($reason) === '') {
                $this->invalid();
            }
            $previous = Refund::where('refundable_type', $source::class)->where('refundable_id', $source->id)->with('items')->get();
            $amount = 0;
            $lines = [];
            $subtotal = Money::cents($source->subtotal);
            $discount = Money::cents($source->discount);
            foreach (collect($items)->groupBy('product_id')->sortKeys() as $id => $rows) {
                $item = $source->items()->where('product_id', $id)->first();
                $qty = $rows->sum('quantity');
                $returned = $previous->flatMap->items->where('product_id', (int) $id)->sum('quantity');
                if (! $item || $qty <= 0 || $qty > $item->quantity - $returned) {
                    $this->invalid();
                }
                // Cumulative proportional allocation keeps rounding exact over multiple partial returns.
                $lineCents = Money::cents($item->subtotal);
                $allocated = $discount ? Money::proportion($discount, $lineCents, max(1, $subtotal)) : 0;
                $netLine = $lineCents - $allocated;
                $line = Money::proportion($netLine, $returned + $qty, $item->quantity) - Money::proportion($netLine, $returned, $item->quantity);
                $amount += $line;
                $lines[] = ['product_id' => (int) $id, 'quantity' => $qty, 'amount' => Money::decimal($line)];
            }
            $total = Money::cents($source->total);
            $already = $previous->sum(fn ($r) => Money::cents($r->amount));
            $allReturned = true;
            foreach ($source->items as $item) {
                $now = collect($lines)->where('product_id', $item->product_id)->sum('quantity');
                if ($previous->flatMap->items->where('product_id', $item->product_id)->sum('quantity') + $now < $item->quantity) {
                    $allReturned = false;
                }
            }
            if ($allReturned) {
                $amount = $total - $already;
            }
            if ($amount <= 0 || $amount + $already > $total) {
                $this->invalid();
            }
            $unpaid = $source instanceof Sale ? Money::cents($source->debt_amount) : 0;
            $debt = min($unpaid, $amount);
            if ($debt) {
                app(DebtService::class)->credit($source->customer_id, $debt, $reason, 'reversal', null, $source->id);
                $source->update(['debt_amount' => Money::decimal($unpaid - $debt)]);
            }
            $receiptLimits = [];
            if ($source instanceof Sale) {
                $repayments = DB::table('debt_allocations as allocation')
                    ->join('debt_transactions as debit', 'debit.id', '=', 'allocation.debt_transaction_id')
                    ->join('debt_transactions as credit', 'credit.id', '=', 'allocation.credit_transaction_id')
                    ->where('debit.sale_id', $source->id)->whereNotNull('credit.payment_id')
                    ->select('credit.payment_id', 'allocation.amount')->get();
                foreach ($repayments as $repayment) {
                    $receiptLimits[$repayment->payment_id] = ($receiptLimits[$repayment->payment_id] ?? 0) + Money::cents($repayment->amount);
                }
            }
            $paid = Payment::whereIn('status', ['paid', 'refunded'])->where(function ($q) use ($source, $receiptLimits) {
                $q->where(function ($direct) use ($source) {
                    $direct->where('payable_type', $source::class)->where('payable_id', $source->id);
                });
                if ($receiptLimits) {
                    $q->orWhereIn('id', array_keys($receiptLimits));
                }
            })->orderBy('id')->lockForUpdate()->get();
            $toReturn = $amount - $debt;
            $cash = 0;
            $allocations = [];
            foreach ($paid as $receipt) {
                $used = Money::cents((string) DB::table('refund_payments')->where('payment_id', $receipt->id)->sum('amount'));
                $sourceUsed = Money::cents((string) DB::table('refund_payments')->where('payment_id', $receipt->id)->whereIn('refund_id', $previous->pluck('id'))->sum('amount'));
                $limit = $receiptLimits[$receipt->id] ?? Money::cents($receipt->amount);
                $part = min($toReturn, Money::cents($receipt->amount) - $used, $limit - $sourceUsed);
                if ($part > 0) {
                    $allocations[] = ['payment_id' => $receipt->id, 'amount' => Money::decimal($part)];
                    $toReturn -= $part;
                    if ($receipt->method === 'cash') {
                        $cash += $part;
                    }
                }
            }
            if ($toReturn > 0) {
                $this->invalid();
            }
            $shift = CashierShift::where('active_user_id', auth()->id())->lockForUpdate()->first();
            if ($cash && ! $shift) {
                throw ValidationException::withMessages(['reason' => __('No active cashier shift.')]);
            }
            $refund = Refund::create(['reference_number' => 'REF-'.Str::uuid(), 'refundable_type' => $source::class, 'refundable_id' => $source->id, 'amount' => Money::decimal($amount), 'cash_amount' => Money::decimal($cash), 'paid_amount' => Money::decimal($amount - $debt), 'debt_amount' => Money::decimal($debt), 'cashier_shift_id' => $cash ? $shift->id : null, 'user_id' => auth()->id(), 'reason' => $reason, 'request_key' => $key]);
            foreach ($allocations as $allocation) {
                DB::table('refund_payments')->insert($allocation + ['refund_id' => $refund->id, 'created_at' => now(), 'updated_at' => now()]);
            }
            foreach ($lines as $line) {
                $refund->items()->create($line);
                app(StockService::class)->change($line['product_id'], $line['quantity'], $reason, 'refund', $refund->id);
            }
            if ($allReturned) {
                $source->update(['status' => 'refunded']);
                $source->payments()->where('status', 'paid')->update(['status' => 'refunded']);
                if ($source instanceof Order) {
                    $source->update(['payment_status' => 'refunded']);
                }
            }
            AuditLog::log('refund.created', $refund, null, $refund->toArray());

            return $refund;
        }, 3);
    }

    private function invalid(): never
    {
        throw ValidationException::withMessages(['items' => __('Invalid refund or returned quantity.')]);
    }
}
