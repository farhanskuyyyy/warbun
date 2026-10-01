<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\CashierShift;
use App\Models\Payment;
use App\Models\User;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShiftService
{
    public function open(string $amount): CashierShift
    {
        return DB::transaction(function () use ($amount) {
            User::lockForUpdate()->findOrFail(auth()->id());
            if (CashierShift::where('active_user_id', auth()->id())->exists()) {
                throw ValidationException::withMessages(['opening_cash' => __('Shift already active.')]);
            }
            $shift = CashierShift::create(['user_id' => auth()->id(), 'active_user_id' => auth()->id(), 'opened_at' => now(), 'opening_cash' => Money::decimal(Money::cents($amount)), 'status' => 'active']);
            AuditLog::log('shift.opened', $shift, null, $shift->toArray());

            return $shift;
        }, 3);
    }

    public function close(string $amount): CashierShift
    {
        return DB::transaction(function () use ($amount) {
            User::lockForUpdate()->findOrFail(auth()->id());
            $shift = CashierShift::where('user_id', auth()->id())->where('status', 'active')->lockForUpdate()->firstOrFail();
            $cash = Payment::where('cashier_shift_id', $shift->id)->where('method', 'cash')->whereIn('status', ['paid', 'refunded'])->get()->sum(fn ($p) => Money::cents($p->amount));
            $refund = DB::table('refunds')->where('cashier_shift_id', $shift->id)->get()->sum(fn ($r) => Money::cents($r->cash_amount));
            $expected = Money::cents($shift->opening_cash) + $cash - $refund;
            $closing = Money::cents($amount);
            $shift->update(['active_user_id' => null, 'closed_at' => now(), 'closing_cash' => Money::decimal($closing), 'expected_cash' => Money::decimal($expected), 'cash_variance' => Money::decimal($closing - $expected), 'status' => 'closed']);
            AuditLog::log('shift.closed', $shift, null, $shift->toArray());

            return $shift;
        }, 3);
    }
}
