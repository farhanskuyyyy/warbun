@extends('layouts.app')
@section('title', __('POS / Cashier'))
@section('header', __('Point of Sale'))
@section('content')
<x-barcode-camera />
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
        <x-barcode-scan-actions target="barcodeInput" submit="barcodeForm" :disabled="!$activeShift" />
        <section class="shelf-overview" aria-labelledby="posShelvesHeading">
            <div class="shelf-heading"><h2 id="posShelvesHeading">{{ __('Shelf minimap') }}</h2>@can('products.view')<a href="{{ route('shelves.index') }}">{{ __('Product placement') }}</a>@endcan</div>
            <p>{{ __('Choose a shelf, then a product to see details and add it. Numbers identify shelves, not floor positions.') }}</p>
            <div class="shelf-map" role="group" aria-label="{{ __('Filter by shelf') }}">
                <button type="button" class="shelf-tile" data-pos-shelf="" aria-pressed="true" aria-controls="productList"><x-icon name="boxes-stacked" /><strong>{{ __('All shelves') }}</strong><span>{{ __('Browse all') }}</span></button>
                @foreach($shelves as $shelf)<button type="button" class="shelf-tile" data-pos-shelf="{{ $shelf->id }}" aria-pressed="false" aria-controls="productList"><span class="shelf-number">{{ $shelf->number }}</span><strong>{{ $shelf->name }}</strong><span>{{ $shelf->products_count }} {{ __('available products') }}</span></button>@endforeach
                <button type="button" class="shelf-tile" data-pos-shelf="unassigned" aria-pressed="false" aria-controls="productList"><x-icon name="box" /><strong>{{ __('Unassigned') }}</strong><span>{{ __('Without location') }}</span></button>
            </div>
            @if($shelves->isEmpty())<p class="mt-3">{{ __('No shelves yet. Create shelves in Product placement; products remain available below.') }}</p>@endif
        </section>
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
        <button id="moreProductsBtn" type="button" class="button-secondary mt-3" hidden>{{ __('Load more products') }}</button>
    </section>
    <section class="panel">
        <h2 class="text-xl font-semibold mb-3">{{ __('Cart') }}</h2><div id="cartItems" class="space-y-3"></div>
        <form id="saleForm" class="space-y-3 mt-4">
            <fieldset class="pos-fulfillment"><legend>{{ __('Order type') }}</legend><label><input type="radio" name="fulfillment_type" value="in_store" checked><span>{{ __('Direct sale') }}</span></label><label><input type="radio" name="fulfillment_type" value="delivery"><span><x-icon name="truck" />{{ __('Delivery') }}</span></label></fieldset>
            <div class="pos-customer-heading"><label for="customerId">{{ __('Customer') }}</label>@can('customers.create')<button id="newCustomerBtn" type="button" class="button-secondary"><x-icon name="user-plus" />{{ __('Add customer') }}</button>@endcan</div>
            <label class="field"><span class="sr-only">{{ __('Customer') }}</span><select id="customerId"><option value="">{{ __('Walk-in customer') }}</option>@foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}{{ $c->phone ? ' · '.$c->phone : '' }}</option>@endforeach</select></label>
            <div id="deliveryFields" class="pos-delivery-fields" hidden><label class="field">{{ __('Delivery address') }}<textarea id="shippingAddress" rows="3" maxlength="2000"></textarea></label><p>{{ __('Shipping fee') }}: <strong id="shippingFee"></strong></p><p>{{ __('Choose a customer with a phone number. Verify the address before payment.') }}</p></div>
            <label class="field">{{ __('Order notes') }}<input id="saleNotes" maxlength="2000"></label>
            <label class="field">{{ __('Payment Method') }}<select id="paymentMethod">@foreach(['cash','transfer','ewallet','qr','debt'] as $method)<option value="{{ $method }}">{{ __('status.'.$method) }}</option>@endforeach</select></label>
            <p id="creditInfo" class="pos-credit-info" role="status"></p>
            <label class="field"><span id="paidAmountLabel">{{ __('Paid amount') }}</span><input id="paidAmount" type="number" min="0" step="0.01" value="0" required aria-describedby="depositHelp"></label>
            <p id="depositHelp" class="pos-credit-info" hidden>{{ __('For credit, enter the cash deposit or 0. The remainder becomes debt. For full payment, select another payment method.') }}</p>
            @can('sales.override-discount')<label class="field">{{ __('Discount') }}<input id="discount" type="number" min="0" step="0.01" value="0"></label>@endcan
            <p class="flex justify-between font-semibold text-xl"><span>{{ __('Total') }}</span><span id="total">Rp 0</span></p>
            <p class="flex justify-between"><span>{{ __('Change') }}</span><span id="changeTotal">Rp 0</span></p>
            <p id="debtPreview" class="pos-credit-info" hidden></p>
            <label class="field">{{ __('Receipt paper') }}<select id="receiptPaper"><option value="80">80 mm</option><option value="58">58 mm</option></select></label>
            <label class="pos-print-choice"><input id="printReceipt" type="checkbox" checked><span>{{ __('Open print dialog after payment') }}</span></label>
            <p id="saleError" role="alert" class="text-red-700"></p>
            <button id="payBtn" class="button-primary w-full" disabled>{{ __('Complete sale') }}</button>
        </form>
    </section>
</div>
@can('customers.create')
<dialog id="customerDialog" class="pos-customer-dialog" aria-labelledby="customerDialogTitle">
    <div class="pos-customer-heading"><h2 id="customerDialogTitle">{{ __('New customer') }}</h2><button id="closeCustomerBtn" type="button" class="button-secondary" aria-label="{{ __('Close') }}"><x-icon name="xmark" /></button></div>
    <p>{{ __('Save customer details and return to this sale. Credit requires separate approval.') }}</p>
    <form id="customerForm" class="space-y-4 mt-5">
        <label class="field">{{ __('Name') }}<input name="name" required maxlength="255" autocomplete="name"></label>
        <label class="field">{{ __('Phone') }}<input name="phone" type="tel" required maxlength="20" autocomplete="tel"></label>
        <label class="field">{{ __('Address') }}<textarea name="address" required maxlength="2000" rows="3" autocomplete="street-address"></textarea></label>
        <label class="pos-print-choice"><input id="createCustomerAccount" type="checkbox" name="create_account"><span>{{ __('Create customer login account') }}</span></label>
        <div id="customerAccountFields" class="space-y-4" hidden><label class="field">{{ __('Email') }}<input name="email" type="email" maxlength="255" autocomplete="email"></label><label class="field">{{ __('Password') }}<input name="password" type="password" minlength="8" maxlength="255" autocomplete="new-password"></label><label class="field">{{ __('Confirm Password') }}<input name="password_confirmation" type="password" minlength="8" maxlength="255" autocomplete="new-password"></label></div>
        <p id="customerError" role="alert" class="text-red-700"></p><button id="saveCustomerBtn" class="button-primary w-full">{{ __('Save and select customer') }}</button>
    </form>
</dialog>
@endcan
<dialog id="productDialog" class="pos-customer-dialog pos-product-dialog" aria-labelledby="productDialogTitle">
    <div class="pos-customer-heading"><h2 id="productDialogTitle"></h2><button id="closeProductBtn" type="button" class="button-secondary" aria-label="{{ __('Close') }}"><x-icon name="xmark" /></button></div>
    <p id="productDialogPrice" class="product-dialog-price"></p><p id="productDialogDescription"></p>
    <dl class="product-dialog-facts"><div><dt>{{ __('SKU') }}</dt><dd id="productDialogSku"></dd></div><div><dt>{{ __('Barcode') }}</dt><dd id="productDialogBarcode"></dd></div><div><dt>{{ __('Category') }}</dt><dd id="productDialogCategory"></dd></div><div><dt>{{ __('Stock') }}</dt><dd id="productDialogStock"></dd></div><div><dt>{{ __('Location') }}</dt><dd id="productDialogLocation"></dd></div></dl>
    <p id="productDialogError" role="alert" class="text-red-700"></p><button id="addProductBtn" type="button" class="button-primary w-full">{{ __('Add to cart') }}</button>
</dialog>
<script id="pos-config" type="application/json">{!! json_encode([
    'customerUrl' => route('pos.customers'), 'shippingCost' => \Illuminate\Support\Facades\DB::table('store_settings')->where('key','shipping_cost')->value('value') ?? '10000',
    'customers' => $customers->map(fn($c) => ['id'=>$c->id,'name'=>$c->name,'phone'=>$c->phone,'address'=>$c->address,'eligible'=>(bool)($c->user?->is_active && $c->can_use_debt && $c->debt_status === 'eligible' && (!$c->debtAccount || $c->debtAccount->status === 'active')), 'available'=>\App\Support\Money::decimal(max(0,\App\Support\Money::cents($c->credit_limit)-\App\Support\Money::cents($c->debtAccount?->outstanding_balance ?? $c->outstanding_balance)))]),
    'productsUrl' => route('pos.products'), 'barcodeUrl' => route('pos.barcode'), 'saleUrl' => route('pos.process-sale'),
    'csrf' => csrf_token(), 'activeShift' => (bool) $activeShift, 'locale' => app()->getLocale() === 'id' ? 'id-ID' : 'en-US',
    'text' => ['loading' => __('Loading products...'), 'empty' => __('No products found'), 'error' => __('Unable to load products. Try searching again.'),
        'availableCredit'=>__('Available credit'), 'notEligible'=>__('Credit is unavailable. Select a registered customer with approved credit.'), 'debtRemaining'=>__('Debt remaining'), 'creditExceeded'=>__('Debt exceeds available credit.'), 'savedCustomer'=>__('Customer saved and selected.'), 'saveCustomer'=>__('Save and select customer'), 'customerFailed'=>__('Unable to save customer. Check the details and retry.'),
        'deposit'=>__('Cash deposit'), 'paidAmount'=>__('Paid amount'), 'unassigned'=>__('Location not assigned'), 'viewDetails'=>__('View product details'), 'unavailable'=>__('Not provided'),
        'emptyCart' => __('No items in cart'), 'stock' => __('Stock'), 'remove' => __('Remove'), 'processing' => __('Processing...'),
        'pay' => __('Complete sale'), 'failed' => __('Unable to process sale. Retry with the same cart.'),
        'increase' => __('Increase quantity'), 'decrease' => __('Decrease quantity'), 'scanReady' => __('Ready to scan.'),
        'scanning' => __('Looking up barcode...'), 'scanFailed' => __('Unable to read this barcode. Try scanning again.'),
        'added' => __('Added to cart'), 'stockLimit' => __('Quantity exceeds available stock.'),
        'tooMany' => __('The cart can contain up to 100 different products.'), 'shift' => __('Open a shift before making sales.')],
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endsection
