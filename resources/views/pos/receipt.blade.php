<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Receipt') }} - {{ $sale->sale_number }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print { .no-print { display: none; } }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white p-6 max-w-sm w-full border border-gray-200 rounded-xl">
        <div class="text-center mb-4">
            <h1 class="text-lg font-bold">🏪 Warbun</h1>
            <p class="text-xs text-gray-500">{{ __('Sistem Manajemen Warung') }}</p>
        </div>
        
        <div class="border-t border-b border-dashed border-gray-300 py-3 mb-4 text-sm break-words">
            <p><strong>{{ __('Reference') }}:</strong> {{ $sale->sale_number }}</p>
            <p><strong>{{ __('Date:') }}</strong> {{ $sale->created_at->translatedFormat('d M Y H:i') }}</p>
            <p><strong>{{ __('Cashier:') }}</strong> {{ $sale->user->name }}</p>
            @if($sale->customer)
                <p><strong>{{ __('Customer:') }}</strong> {{ $sale->customer->name }}</p>
            @endif
        </div>
        
        <div class="space-y-1 mb-4">
            @foreach($sale->items as $item)
            <div class="flex justify-between text-sm">
                <span>{{ $item->product->name }} x{{ $item->quantity }}</span>
                <span>{{ \App\Support\Money::format($item->subtotal) }}</span>
            </div>
            @endforeach
        </div>
        
        <div class="border-t border-dashed border-gray-300 pt-3 space-y-1">
            <div class="flex justify-between text-sm">
                <span>{{ __('Subtotal') }}</span>
                <span>{{ \App\Support\Money::format($sale->subtotal) }}</span>
            </div>
            @if($sale->discount > 0)
            <div class="flex justify-between text-sm text-green-600">
                <span>{{ __('Discount') }}</span>
                <span>-{{ \App\Support\Money::format($sale->discount) }}</span>
            </div>
            @endif
            <div class="flex justify-between font-bold text-lg">
                <span>{{ __('Total') }}</span>
                <span>{{ \App\Support\Money::format($sale->total) }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span>{{ __('Payment') }} ({{ __('status.'.$sale->payment_method) }})</span>
                <span>{{ \App\Support\Money::format($sale->paid_amount) }}</span>
            </div>
            @if($sale->change_amount > 0)
            <div class="flex justify-between text-sm">
                <span>{{ __('Change') }}</span>
                <span>{{ \App\Support\Money::format($sale->change_amount) }}</span>
            </div>
            @endif
        </div>
        
        <div class="text-center mt-4 text-xs text-gray-500">
            <p>{{ __('Terima kasih atas kunjungan Anda!') }}</p>
        </div>
    </div>
    
    <div class="no-print fixed bottom-4 right-4">
        <button onclick="window.print()" class="bg-primary text-white px-4 py-2 rounded-lg text-sm font-medium">{{ __('🖨️ Print') }}</button>
        <a href="{{ route('pos.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">{{ __('Back to POS') }}</a>
    </div>
</body>
</html>
