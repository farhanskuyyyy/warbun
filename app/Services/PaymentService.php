<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\CashierShift;
use App\Models\Customer;
use App\Models\DebtAccount;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\User;
use App\Notifications\OperationalNotification;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function receive(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            User::lockForUpdate()->findOrFail(auth()->id());
            $old = Payment::where('request_key', $data['request_key'])->first();
            if ($old) {
                abort_unless($old->user_id === auth()->id(), 403);

                return $old;
            }
            $sale = ! empty($data['sale_id']) ? Sale::findOrFail($data['sale_id']) : null;
            if ($sale && ! $sale->customer_id) {
                $this->invalid('Registered eligible customer required.');
            }
            $customerId = $sale?->customer_id ?? ($data['customer_id'] ?? null);
            if (! $customerId) {
                $this->invalid('Registered eligible customer required.');
            }
            $customer = Customer::lockForUpdate()->findOrFail($customerId);
            if ($sale) {
                $sale = Sale::lockForUpdate()->findOrFail($sale->id);
            }
            $amount = Money::cents($data['amount']);
            if ($sale && ($sale->status !== 'completed' || $amount > Money::cents($sale->debt_amount))) {
                $this->invalid('Payment exceeds outstanding balance.');
            }
            $account = DebtAccount::where('customer_id', $customer->id)->lockForUpdate()->firstOrFail();
            $shift = CashierShift::where('active_user_id', auth()->id())->lockForUpdate()->first();
            if ($data['method'] === 'cash' && ! $shift) {
                $this->invalid('No active cashier shift.');
            }
            $payment = Payment::create(['payment_number' => 'PAY-'.Str::uuid(), 'request_key' => $data['request_key'], 'payable_type' => $sale ? Sale::class : DebtAccount::class, 'payable_id' => $sale?->id ?? $account->id, 'customer_id' => $customer->id, 'amount' => Money::decimal($amount), 'method' => $data['method'], 'status' => 'paid', 'user_id' => auth()->id(), 'cashier_shift_id' => $shift?->id, 'paid_at' => now(), 'reference_number' => $data['reference_number'] ?? null, 'notes' => $data['notes'] ?? null]);
            app(DebtService::class)->credit($customer->id, $amount, 'Debt payment', 'payment', $payment, $sale?->id);
            AuditLog::log('payment.created', $payment, null, $payment->toArray());

            return $payment;
        }, 3);
    }

    public function confirm(Payment $payment, bool $verifiedGateway = false): Payment
    {
        return DB::transaction(function () use ($payment, $verifiedGateway) {
            if (! $verifiedGateway) {
                User::lockForUpdate()->findOrFail(auth()->id());
            }
            if ($payment->payable_type !== Order::class) {
                $this->invalid('Invalid payment.');
            }
            $order = Order::lockForUpdate()->findOrFail($payment->payable_id);
            $payment = Payment::lockForUpdate()->findOrFail($payment->id);
            if ($payment->status === 'paid') {
                return $payment;
            }
            if ($payment->status !== 'pending' || in_array($order->status, ['cancelled', 'refunded']) || $payment->expires_at?->isPast()) {
                $this->invalid('Invalid payment.');
            }
            if ($payment->method === 'online' && ! $verifiedGateway) {
                $this->invalid('Online payment requires a verified webhook.');
            }
            $paid = $order->payments()->where('status', 'paid')->get()->sum(fn ($p) => Money::cents($p->amount));
            $amount = Money::cents($payment->amount);
            $total = Money::cents($order->total);
            if ($amount <= 0 || $paid + $amount > $total) {
                $this->invalid('Invalid payment.');
            }
            $shift = CashierShift::where('active_user_id', auth()->id())->first();
            if ($payment->method === 'cash' && ! $shift) {
                $this->invalid('No active cashier shift.');
            }
            $payment->update(['status' => 'paid', 'paid_at' => now(), 'user_id' => auth()->id(), 'cashier_shift_id' => $payment->method === 'cash' ? $shift->id : null]);
            $order->update(['payment_status' => $paid + $amount === $total ? 'paid' : 'partial']);
            AuditLog::log('payment.confirmed', $payment, null, $payment->toArray());
            $order->customer?->user?->notify(new OperationalNotification('payment.confirmed', $payment->id));

            return $payment;
        }, 3);
    }

    public function forOrder(Order $order, array $data): Payment
    {
        return DB::transaction(function () use ($order, $data) {
            User::lockForUpdate()->findOrFail(auth()->id());
            $order = Order::lockForUpdate()->findOrFail($order->id);
            $existing = Payment::where('request_key', $data['request_key'])->first();
            if ($existing) {
                abort_unless($existing->user_id === auth()->id() && $existing->payable_id === $order->id && $existing->payable_type === Order::class, 403);

                return $existing;
            }
            if (in_array($order->status, ['cancelled', 'refunded', 'completed'])) {
                $this->invalid('Invalid payment.');
            }
            $amount = Money::cents($data['amount']);
            $paid = $order->payments()->where('status', 'paid')->get()->sum(fn ($p) => Money::cents($p->amount));
            if ($amount <= 0 || $amount + $paid > Money::cents($order->total)) {
                $this->invalid('Invalid payment.');
            }
            $payment = Payment::create(['payment_number' => 'PAY-'.Str::uuid(), 'request_key' => $data['request_key'], 'payable_type' => Order::class, 'payable_id' => $order->id, 'customer_id' => $order->customer_id, 'amount' => Money::decimal($amount), 'method' => $data['method'], 'status' => 'pending', 'user_id' => auth()->id()]);
            $payment = $this->confirm($payment);
            // Manual settlement supersedes unused full-order payment intents.
            $order->payments()->where('id', '!=', $payment->id)->where('status', 'pending')->update(['status' => 'cancelled']);

            return $payment;
        }, 3);
    }

    private function invalid(string $message): never
    {
        throw ValidationException::withMessages(['amount' => __($message)]);
    }
}
