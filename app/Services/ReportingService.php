<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Refund;
use App\Models\Sale;
use App\Support\Money;

class ReportingService
{
    public function sales($from, $to): array
    {
        $sales = Sale::whereBetween('created_at', [$from, $to])->whereIn('status', ['completed', 'refunded'])->get();
        $sales = $sales->concat(Order::whereBetween('created_at', [$from, $to])->whereIn('status', ['confirmed', 'preparing', 'ready', 'completed', 'refunded'])->get());
        $refunds = Refund::whereIn('refundable_type', [Sale::class, Order::class])->whereBetween('created_at', [$from, $to])->get();
        $gross = $sales->sum(fn ($s) => Money::cents($s->subtotal));
        $discounts = $sales->sum(fn ($s) => Money::cents($s->discount));
        $returned = $refunds->sum(fn ($r) => Money::cents($r->amount));
        $net = $sales->sum(fn ($s) => Money::cents($s->total)) - $returned;
        $dates = $sales->map(fn ($s) => $s->created_at->toDateString())->merge($refunds->map(fn ($r) => $r->created_at->toDateString()))->unique()->sort();
        $daily = $dates->map(function ($date) use ($sales, $refunds) {
            $rows = $sales->filter(fn ($s) => $s->created_at->toDateString() === $date);
            $credits = $refunds->filter(fn ($r) => $r->created_at->toDateString() === $date)->sum(fn ($r) => Money::cents($r->amount));

            return (object) ['date' => $date, 'total' => Money::decimal($rows->sum(fn ($s) => Money::cents($s->total)) - $credits), 'count' => $rows->count()];
        });

        return ['grossSales' => Money::decimal($gross), 'totalDiscounts' => Money::decimal($discounts), 'refundTotal' => Money::decimal($returned), 'totalSales' => Money::decimal($net), 'totalTransactions' => $sales->count(), 'avgTransaction' => Money::decimal($sales->count() ? intdiv($net, $sales->count()) : 0), 'dailySales' => $daily];
    }
}
