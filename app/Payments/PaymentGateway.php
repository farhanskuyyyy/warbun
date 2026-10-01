<?php

namespace App\Payments;

use Illuminate\Http\Request;

interface PaymentGateway
{
    public function verifiedPayload(Request $request): array;
}
