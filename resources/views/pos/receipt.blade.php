<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Receipt') }} · {{ $sale->sale_number }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="receipt-screen" data-receipt-id="{{ $sale->id }}" data-auto-print="{{ request()->boolean('print') ? 'true' : 'false' }}" data-paper="{{ request('paper') === '58' ? '58' : '80' }}">
    <nav class="receipt-toolbar no-print" aria-label="{{ __('Receipt actions') }}">
        <a href="{{ route('pos.index') }}">{{ __('New sale') }}</a>
        <label>{{ __('Receipt paper') }}<select id="receiptWidth"><option value="80" @selected(request('paper') !== '58')>80 mm</option><option value="58" @selected(request('paper') === '58')>58 mm</option></select></label>
        <button type="button" id="printReceiptButton" class="button-primary">{{ __('Print receipt') }}</button>
    </nav>
    <p class="receipt-print-help no-print">{{ __('Choose your printer and matching paper size in the print dialog. Turn off browser headers and footers.') }}</p>
    <main class="receipt-paper">
        <header class="receipt-heading">
            <h1>{{ \Illuminate\Support\Facades\DB::table('store_settings')->where('key','store_name')->value('value') ?: 'Warbun' }}</h1>
            <p>{{ __('Receipt') }}</p>
        </header>
        <dl class="receipt-reference">
            <div><dt>{{ __('Reference') }}</dt><dd>{{ $sale->sale_number }}</dd></div>
            <div><dt>{{ __('Date') }}</dt><dd>{{ $sale->created_at->translatedFormat('d M Y H:i') }}</dd></div>
            <div><dt>{{ __('Cashier') }}</dt><dd>{{ $sale->user->name }}</dd></div>
            @if($sale->customer)<div><dt>{{ __('Customer') }}</dt><dd>{{ $sale->customer->name }}</dd></div>@endif
            @if($sale->fulfillment_type === 'delivery')<div><dt>{{ __('Delivery') }}</dt><dd>{{ __('status.'.$sale->fulfillment_status) }}</dd></div><div><dt>{{ __('Address') }}</dt><dd>{{ $sale->shipping_address }}<x-delivery-map-link :latitude="$sale->shipping_latitude" :longitude="$sale->shipping_longitude" /></dd></div><div><dt>{{ __('Phone') }}</dt><dd>{{ $sale->customer?->phone }}</dd></div>@endif
        </dl>
        <div class="receipt-items">
            @foreach($sale->items as $item)
            <div class="receipt-item"><strong>{{ $item->product->name }}</strong><div><span>{{ $item->quantity }} × {{ \App\Support\Money::format($item->unit_price) }}</span><span>{{ \App\Support\Money::format($item->subtotal) }}</span></div></div>
            @endforeach
        </div>
        <dl class="receipt-totals">
            <div><dt>{{ __('Subtotal') }}</dt><dd>{{ \App\Support\Money::format($sale->subtotal) }}</dd></div>
            @if($sale->shipping_cost > 0)<div><dt>{{ __('Shipping fee') }}</dt><dd>{{ \App\Support\Money::format($sale->shipping_cost) }}</dd></div>@endif
            @if($sale->discount > 0)<div><dt>{{ __('Discount') }}</dt><dd>−{{ \App\Support\Money::format($sale->discount) }}</dd></div>@endif
            <div class="receipt-total"><dt>{{ __('Total') }}</dt><dd>{{ \App\Support\Money::format($sale->total) }}</dd></div>
            <div><dt>{{ __('Payment') }} ({{ __('status.'.$sale->payment_method) }})</dt><dd>{{ \App\Support\Money::format(\App\Support\Money::decimal(\App\Support\Money::cents($sale->paid_amount) + \App\Support\Money::cents($sale->change_amount))) }}</dd></div>
            @if($sale->change_amount > 0)<div><dt>{{ __('Change') }}</dt><dd>{{ \App\Support\Money::format($sale->change_amount) }}</dd></div>@endif
            @if($sale->debt_amount > 0)<div><dt>{{ __('Debt') }}</dt><dd>{{ \App\Support\Money::format($sale->debt_amount) }}</dd></div>@endif
        </dl>
        <footer>{{ __('Terima kasih atas kunjungan Anda!') }}</footer>
    </main>
</body>
</html>
