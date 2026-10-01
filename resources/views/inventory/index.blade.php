@extends('layouts.app')

@section('title', __('Inventory'))
@section('header', __('Inventory Management'))

@section('content')
<div class="space-y-4">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl p-4 border border-gray-100">
            <p class="text-xs text-gray-500 uppercase">{{ __('Total Products') }}</p>
            <p class="text-2xl font-bold text-primary">{{ $stockSummary->count() }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100">
            <p class="text-xs text-gray-500 uppercase">{{ __('Low Stock') }}</p>
            <p class="text-2xl font-bold text-orange-500">{{ $lowStockCount }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100">
            <p class="text-xs text-gray-500 uppercase">{{ __('Out of Stock') }}</p>
            <p class="text-2xl font-bold text-red-500">{{ $outOfStockCount }}</p>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex gap-2">
        <button onclick="document.getElementById('stockInModal').showModal()" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">
            {{ __('+ Stock In') }}
        </button>
        <button onclick="document.getElementById('stockOutModal').showModal()" class="px-4 py-2 bg-orange-500 text-white rounded-lg text-sm font-medium hover:bg-orange-600">
            {{ __('- Stock Out') }}
        </button>
        <button onclick="document.getElementById('adjustModal').showModal()" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
            {{ __('Adjust Stock') }}
        </button>
    </div>

    <!-- Stock Summary -->
    <div class="bg-white rounded-xl border border-gray-100 overflow-x-auto">
        <div class="px-4 py-3 border-b border-gray-200">
            <h3 class="font-semibold text-gray-800">{{ __('Stock Summary') }}</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Product') }}</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('SKU') }}</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">{{ __('Min Stock') }}</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">{{ __('Current Stock') }}</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stockSummary as $product)
                <tr class="border-t border-gray-100">
                    <td class="px-4 py-3 font-medium">{{ $product->name }}</td>
                    <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $product->sku }}</td>
                    <td class="px-4 py-3 text-right">{{ $product->minimum_stock }}</td>
                    <td class="px-4 py-3 text-right font-bold {{ $product->current_stock <= 0 ? 'text-red-600' : ($product->current_stock <= $product->minimum_stock ? 'text-orange-500' : '') }}">
                        {{ $product->current_stock }}
                    </td>
                    <td class="px-4 py-3">
                        @if($product->current_stock <= 0)
                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-700">{{ __('Out of Stock') }}</span>
                        @elseif($product->current_stock <= $product->minimum_stock)
                            <span class="px-2 py-1 text-xs rounded-full bg-orange-100 text-orange-700">{{ __('Low Stock') }}</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">{{ __('In Stock') }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">{{ __('No products found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Transaction History -->
    <div class="bg-white rounded-xl border border-gray-100 overflow-x-auto">
        <div class="px-4 py-3 border-b border-gray-200">
            <h3 class="font-semibold text-gray-800">{{ __('Transaction History') }}</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Reference') }}</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Product') }}</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Type') }}</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">{{ __('Qty') }}</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">{{ __('Stock') }}</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Reason') }}</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('By') }}</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Date') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $txn)
                <tr class="border-t border-gray-100">
                    <td class="px-4 py-3 font-mono text-xs">{{ $txn->reference_number }}</td>
                    <td class="px-4 py-3">{{ $txn->product->name }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded-full {{ match($txn->type) { 'stock_in' => 'bg-green-100 text-green-700', 'stock_out' => 'bg-orange-100 text-orange-700', 'adjustment' => 'bg-blue-100 text-blue-700', default => 'bg-gray-100 text-gray-700' } }}">
                            {{ __('status.'.$txn->type) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right {{ $txn->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $txn->quantity > 0 ? '+' : '' }}{{ $txn->quantity }}
                    </td>
                    <td class="px-4 py-3 text-right">{{ $txn->previous_stock }} → {{ $txn->new_stock }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $txn->reason }}</td>
                    <td class="px-4 py-3">{{ $txn->user->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $txn->created_at->translatedFormat('d M Y H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">{{ __('No transactions found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="flex justify-center">{{ $transactions->withQueryString()->links() }}</div>
</div>

<!-- Stock In Modal -->
<dialog id="stockInModal" aria-labelledby="stockInModal-title" class="w-full max-w-md rounded-xl p-0">
    <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h3 id="stockInModal-title" class="text-lg font-semibold mb-4">{{ __('Stock In') }}</h3>
        <form action="{{ route('inventory.stock-in') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="stockInModal-product_id" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Product *') }}</label>
                    <select id="stockInModal-product_id" name="product_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="">{{ __('Select Product') }}</option>
                        @foreach(\App\Models\Product::where('is_active', true)->orderBy('name')->get() as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="stockInModal-quantity" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Quantity *') }}</label>
                        <input id="stockInModal-quantity" type="number" name="quantity" min="1" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label for="stockInModal-unit_cost" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Unit Cost (Rp)') }}</label>
                        <input id="stockInModal-unit_cost" type="number" name="unit_cost" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                </div>
                <div>
                    <label for="stockInModal-reason" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Reason *') }}</label>
                    <input id="stockInModal-reason" type="text" name="reason" required class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="{{ __('e.g., Purchase, Return') }}">
                </div>
                <div>
                    <label for="stockInModal-notes" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Notes') }}</label>
                    <textarea id="stockInModal-notes" name="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                </div>
            </div>
            <div class="flex gap-2 mt-6">
                <button type="button" onclick="document.getElementById('stockInModal').close()" class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg">{{ __('Cancel') }}</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg font-medium">{{ __('Submit') }}</button>
            </div>
        </form>
    </div>
</dialog>

<!-- Stock Out Modal -->
<dialog id="stockOutModal" aria-labelledby="stockOutModal-title" class="w-full max-w-md rounded-xl p-0">
    <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h3 id="stockOutModal-title" class="text-lg font-semibold mb-4">{{ __('Stock Out') }}</h3>
        <form action="{{ route('inventory.stock-out') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="stockOutModal-product_id" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Product *') }}</label>
                    <select id="stockOutModal-product_id" name="product_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="">{{ __('Select Product') }}</option>
                        @foreach(\App\Models\Product::where('is_active', true)->where('current_stock', '>', 0)->orderBy('name')->get() as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }}) - {{ __('Stock') }}: {{ $p->current_stock }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="stockOutModal-quantity" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Quantity *') }}</label>
                    <input id="stockOutModal-quantity" type="number" name="quantity" min="1" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label for="stockOutModal-reason" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Reason *') }}</label>
                    <input id="stockOutModal-reason" type="text" name="reason" required class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="{{ __('e.g., Damaged, Expired') }}">
                </div>
                <div>
                    <label for="stockOutModal-notes" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Notes') }}</label>
                    <textarea id="stockOutModal-notes" name="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                </div>
            </div>
            <div class="flex gap-2 mt-6">
                <button type="button" onclick="document.getElementById('stockOutModal').close()" class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg">{{ __('Cancel') }}</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-orange-500 text-white rounded-lg font-medium">{{ __('Submit') }}</button>
            </div>
        </form>
    </div>
</dialog>

<!-- Adjust Modal -->
<dialog id="adjustModal" aria-labelledby="adjustModal-title" class="w-full max-w-md rounded-xl p-0">
    <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h3 id="adjustModal-title" class="text-lg font-semibold mb-4">{{ __('Adjust Stock') }}</h3>
        <form action="{{ route('inventory.adjust') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="adjustModal-product_id" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Product *') }}</label>
                    <select id="adjustModal-product_id" name="product_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="">{{ __('Select Product') }}</option>
                        @foreach(\App\Models\Product::where('is_active', true)->orderBy('name')->get() as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }}) - {{ __('Current') }}: {{ $p->current_stock }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="adjustModal-new_quantity" class="block text-sm font-medium text-gray-700 mb-1">{{ __('New Quantity *') }}</label>
                    <input id="adjustModal-new_quantity" type="number" name="new_quantity" min="0" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label for="adjustModal-reason" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Reason *') }}</label>
                    <input id="adjustModal-reason" type="text" name="reason" required class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="{{ __('e.g., Stock opname, Correction') }}">
                </div>
                <div>
                    <label for="adjustModal-notes" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Notes') }}</label>
                    <textarea id="adjustModal-notes" name="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                </div>
            </div>
            <div class="flex gap-2 mt-6">
                <button type="button" onclick="document.getElementById('adjustModal').close()" class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg">{{ __('Cancel') }}</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg font-medium">{{ __('Submit') }}</button>
            </div>
        </form>
    </div>
</dialog>

@endsection
