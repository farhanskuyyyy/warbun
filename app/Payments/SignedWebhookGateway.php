<?php

namespace App\Payments;

use Illuminate\Http\Request;

class SignedWebhookGateway implements PaymentGateway
{
    public function verifiedPayload(Request $request): array
    {
        $secret = config('payments.webhook_secret');
        $stamp = $request->header('X-Payment-Timestamp', '');
        abort_unless(is_string($secret) && strlen($secret) >= 32 && ctype_digit($stamp) && abs(time() - (int) $stamp) <= 300, 401);
        $signature = hash_hmac('sha256', $stamp.'.'.$request->getContent(), $secret);
        abort_unless(hash_equals($signature, $request->header('X-Payment-Signature', '')), 401);

        return $request->validate(['payment_number' => 'required|string', 'amount' => 'required|decimal:0,2|min:0.01', 'status' => 'required|in:paid,failed,cancelled', 'reference' => 'required|string|max:255']);
    }
}
