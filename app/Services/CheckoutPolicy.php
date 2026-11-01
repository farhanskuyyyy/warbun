<?php

namespace App\Services;

use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutPolicy
{
    public function calculate(int $subtotal, array $data): array
    {
        $settings = DB::table('store_settings')->pluck('value', 'key');
        $shipping = 0;
        $distance = null;
        if (($data['delivery_type'] ?? 'pickup') === 'delivery') {
            if ($settings->get('radius_enabled') === '1') {
                foreach (['store_latitude', 'store_longitude'] as $key) {
                    if (! is_numeric($settings->get($key))) {
                        $this->invalid('Delivery is unavailable until the store location is configured.');
                    }
                }
                if (! is_numeric($data['shipping_latitude'] ?? null) || ! is_numeric($data['shipping_longitude'] ?? null)) {
                    $this->invalid('Choose an address with a valid map location.');
                }
                $distance = $this->distance((float) $settings['store_latitude'], (float) $settings['store_longitude'], (float) $data['shipping_latitude'], (float) $data['shipping_longitude']);
                $shipping = $this->shippingForDistance($distance);
            } else {
                $shipping = Money::cents($settings->get('shipping_cost', '10000'));
            }
        }
        $method = $data['payment_method'] ?? 'cash';
        $channel = $data['payment_channel'] ?? null;
        if ($channel !== null && ! array_key_exists($channel, config('payments.channels.'.$method, []))) {
            $this->invalid('Choose a supported payment channel.');
        }
        $promoRate = (int) $settings->get('payment_promo_bps', 0);
        $discount = min($subtotal, Money::cents($settings->get('payment_promo_flat', '0')) + intdiv($subtotal * $promoRate + 500000, 1000000) * 100);
        $cap = Money::cents($settings->get('payment_promo_cap', '0'));
        if ($cap > 0) {
            $discount = min($discount, $cap);
        }
        $net = $subtotal - $discount;
        $rate = $method === 'qr' ? (int) $settings->get('qris_fee_bps', 0) : 0;
        $fee = $method === 'ewallet' ? Money::cents($settings->get('ewallet_fee', '0')) : intdiv(($net + $shipping) * $rate + 500000, 1000000) * 100;
        $policy = ['radius' => $settings->get('radius_enabled', '0'), 'store_latitude' => $settings->get('store_latitude'), 'store_longitude' => $settings->get('store_longitude'), 'shipping_cost' => $settings->get('shipping_cost', '10000'), 'qris_fee_bps' => $settings->get('qris_fee_bps', '0'), 'ewallet_fee' => $settings->get('ewallet_fee', '0'), 'promo_bps' => $promoRate, 'promo_flat' => $settings->get('payment_promo_flat', '0'), 'promo_cap' => $settings->get('payment_promo_cap', '0')];

        return ['shipping' => $shipping, 'fee' => $fee, 'discount' => $discount, 'total' => $net + $shipping + $fee, 'distance_meters' => $distance, 'method' => $method, 'channel' => $channel, 'fee_basis' => $net + $shipping, 'fee_rate_bps' => $rate, 'policy_version' => hash('sha256', json_encode($policy))];
    }

    public function shippingForDistance(float $meters): int
    {
        if (! is_finite($meters) || $meters < 0 || $meters >= 20000) {
            $this->invalid('This address is outside the delivery area. Choose pickup or another address.');
        }

        return $meters < 5000 ? 0 : Money::cents('10000');
    }

    public function distance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $a = sin(deg2rad($lat2 - $lat1) / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin(deg2rad($lon2 - $lon1) / 2) ** 2;

        return 6371000 * 2 * atan2(sqrt(min(1, $a)), sqrt(max(0, 1 - $a)));
    }

    public function fingerprint(array $items, array $charges, array $data): string
    {
        $lines = collect($items)->map(fn ($item) => [(int) $item['product_id'], (int) $item['quantity']])->sortBy(0)->values()->all();

        return hash('sha256', json_encode([$lines, $charges, $data['delivery_type'], ($data['delivery_type'] === 'delivery' && isset($data['address_id'])) ? (int) $data['address_id'] : null]));
    }

    private function invalid(string $message): never
    {
        throw ValidationException::withMessages(['delivery_type' => __($message)]);
    }
}
