@extends('layouts.app')
@section('title', __('POS / Cashier'))
@section('header', __('Point of Sale'))
@section('content')
@if(!$activeShift)
<div class="panel mb-4"><p>{{ __('Open a shift before making sales.') }}</p><form action="{{ route('pos.open-shift') }}" method="POST" class="flex flex-wrap gap-3 mt-3">@csrf<label class="field">{{ __('Opening cash') }}<input name="opening_cash" type="number" min="0" step="0.01" required></label><button class="button-primary">{{ __('Open Shift') }}</button></form></div>
@else
<div class="panel mb-4"><p>{{ __('Active Shift') }} · {{ $activeShift->opened_at->translatedFormat('d M Y H:i') }}</p><form action="{{ route('pos.close-shift') }}" method="POST" class="flex flex-wrap gap-3 mt-3">@csrf<label class="field">{{ __('Closing cash') }}<input name="closing_cash" type="number" min="0" step="0.01" required></label><button class="button-primary">{{ __('Close Shift') }}</button></form></div>
@endif
<div class="pos-workbench">
    <section class="panel pos-products">
        <form id="barcodeForm" class="pos-scanner">
            <label class="field" for="barcodeInput">{{ __('Scan barcode') }}<input id="barcodeInput" type="text" maxlength="50" autocomplete="off" spellcheck="false" aria-describedby="scannerHelp" @disabled(!$activeShift) @if($activeShift) autofocus @endif></label>
            <button id="scanBtn" type="submit" class="button-primary" @disabled(!$activeShift)>{{ __('Add scanned item') }}</button>
            <p id="scannerHelp">{{ __('Use a USB or Bluetooth keyboard scanner with an Enter suffix. Click this field before scanning.') }}</p>
        </form>
        <p id="scanState" class="pos-scan-state" role="status" aria-live="polite"></p>
        <label class="field">{{ __('Search product or barcode') }}<input id="productSearch" type="search" autocomplete="off"></label>
        <div class="pos-category-filter">
            <p>{{ __('Category') }}</p>
            <div class="pos-categories" role="group" aria-label="{{ __('Filter by category') }}">
                <button type="button" class="pos-category-button" data-pos-category="" aria-pressed="true" aria-controls="productList">{{ __('All categories') }}</button>
                @foreach($categories as $category)<button type="button" class="pos-category-button" data-pos-category="{{ $category->id }}" aria-pressed="false" aria-controls="productList">{{ $category->name }}</button>@endforeach
            </div>
        </div>
        <p id="productState" role="status" class="my-3"></p>
        <div id="productList" class="max-h-[60vh] overflow-y-auto grid sm:grid-cols-2 xl:grid-cols-3 gap-3 mt-3"></div>
    </section>
    <section class="panel">
        <h2 class="text-xl font-semibold mb-3">{{ __('Cart') }}</h2><div id="cartItems" class="space-y-3"></div>
        <form id="saleForm" class="space-y-3 mt-4">
            <label class="field">{{ __('Customer') }}<select id="customerId"><option value="">{{ __('Walk-in customer') }}</option>@foreach(\App\Models\Customer::where('is_active',true)->orderBy('name')->get() as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></label>
            <label class="field">{{ __('Payment Method') }}<select id="paymentMethod">@foreach(['cash','transfer','ewallet','qr','debt'] as $method)<option value="{{ $method }}">{{ __('status.'.$method) }}</option>@endforeach</select></label>
            <label class="field">{{ __('Paid amount') }}<input id="paidAmount" type="number" min="0" step="0.01" value="0" required></label>
            @can('sales.override-discount')<label class="field">{{ __('Discount') }}<input id="discount" type="number" min="0" step="0.01" value="0"></label>@endcan
            <p class="flex justify-between font-semibold text-xl"><span>{{ __('Total') }}</span><span id="total">Rp 0</span></p>
            <p class="flex justify-between"><span>{{ __('Change') }}</span><span id="changeTotal">Rp 0</span></p>
            <label class="field">{{ __('Receipt paper') }}<select id="receiptPaper"><option value="80">80 mm</option><option value="58">58 mm</option></select></label>
            <label class="pos-print-choice"><input id="printReceipt" type="checkbox" checked><span>{{ __('Open print dialog after payment') }}</span></label>
            <p id="saleError" role="alert" class="text-red-700"></p>
            <button id="payBtn" class="button-primary w-full" disabled>{{ __('Complete sale') }}</button>
        </form>
    </section>
</div>
<script id="pos-config" type="application/json">{!! json_encode([
    'productsUrl' => route('pos.products'), 'barcodeUrl' => route('pos.barcode'), 'saleUrl' => route('pos.process-sale'),
    'csrf' => csrf_token(), 'activeShift' => (bool) $activeShift, 'locale' => app()->getLocale() === 'id' ? 'id-ID' : 'en-US',
    'text' => ['loading' => __('Loading products...'), 'empty' => __('No products found'), 'error' => __('Unable to load products. Try searching again.'),
        'emptyCart' => __('No items in cart'), 'stock' => __('Stock'), 'remove' => __('Remove'), 'processing' => __('Processing...'),
        'pay' => __('Complete sale'), 'failed' => __('Unable to process sale. Retry with the same cart.'),
        'increase' => __('Increase quantity'), 'decrease' => __('Decrease quantity'), 'scanReady' => __('Ready to scan.'),
        'scanning' => __('Looking up barcode...'), 'scanFailed' => __('Unable to read this barcode. Try scanning again.'),
        'added' => __('Added to cart'), 'stockLimit' => __('Quantity exceeds available stock.'),
        'tooMany' => __('The cart can contain up to 100 different products.'), 'shift' => __('Open a shift before making sales.')],
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endsection
