<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Payment;
use App\Payments\PaymentGateway;
use App\Services\PaymentService;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        abort_unless(config('payments.gateway') === 'signed-webhook', 503);
        $data = app(PaymentGateway::class)->verifiedPayload($request);
        DB::transaction(function () use ($data) {
            $payment = Payment::where('payment_number', $data['payment_number'])->firstOrFail();
            $order = Order::lockForUpdate()->findOrFail($payment->payable_id);
            $payment = Payment::lockForUpdate()->findOrFail($payment->id);
            abort_unless($payment->method === 'online' && $payment->payable_type === Order::class && Money::cents($data['amount']) === Money::cents($payment->amount), 422);
            if ($payment->status === $data['status']) {
                abort_unless($payment->gateway_reference === $data['reference'], 409);

                return;
            }
            abort_unless($payment->status === 'pending', 409);
            Auth::onceUsingId($payment->user_id);
            if ($data['status'] === 'paid') {
                app(PaymentService::class)->confirm($payment, true);
            } else {
                $payment->update(['status' => $data['status']]);
                $order->update(['payment_status' => 'failed']);
            }
            $payment->update(['gateway_reference' => $data['reference']]);
            AuditLog::log('payment.webhook', $payment, null, ['status' => $data['status'], 'reference' => $data['reference']]);
        }, 3);

        return response()->json(['status' => 'ok']);
    }
}
