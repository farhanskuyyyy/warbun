@extends('layouts.app')

@section('title', 'Inventory')
@section('header', 'Inventory Management')

@section('content')
<div class="space-y-4">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl p-4 border border-gray-100">
            <p class="text-xs text-gray-500 uppercase">Total Products</p>
            <p class="text-2xl font-bold text-primary">{{ $stockSummary->count() }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100">
            <p class="text-xs text-gray-500 uppercase">Low Stock</p>
            <p class="text-2xl font-bold text-orange-500">{{ $lowStockCount }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100">
            <p class="text-xs text-gray-500 uppercase">Out of Stock</p>
            <p class="text-2xl font-bold text-red-500">{{ $outOfStockCount }}</p>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex gap-2">
        <button onclick="document.getElementById('stockInModal').classList.remove('hidden')" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">
            + Stock In
        </button>
        <button onclick="document.getElementById('stockOutModal').classList.remove('hidden')" class="px-4 py-2 bg-orange-500 text-white rounded-lg text-sm font-medium hover:bg-orange-600">
            - Stock Out
        </button>
        <button onclick="document.getElementById('adjustModal').classList.remove('hidden')" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
            Adjust Stock
        </button>
    </div>

    <!-- Stock Summary -->
    <div class="bg-white rounded-xl border border-gray-100 overflow-x-auto">
        <div class="px-4 py-3 border-b border-gray-200">
            <h3 class="font-semibold text-gray-800">Stock Summary</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Product</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">SKU</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">Min Stock</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">Current Stock</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
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
                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-700">Out of Stock</span>
                        @elseif($product->current_stock <= $product->minimum_stock)
                            <span class="px-2 py-1 text-xs rounded-full bg-orange-100 text-orange-700">Low Stock</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">In Stock</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No products found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Transaction History -->
    <div class="bg-white rounded-xl border border-gray-100 overflow-x-auto">
        <div class="px-4 py-3 border-b border-gray-200">
            <h3 class="font-semibold text-gray-800">Transaction History</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Reference</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Product</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Type</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">Qty</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">Stock</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Reason</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">By</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $txn)
                <tr class="border-t border-gray-100">
                    <td class="px-4 py-3 font-mono text-xs">{{ $txn->reference_number }}</td>
                    <td class="px-4 py-3">{{ $txn->product->name }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded-full {{ match($txn->type) { 'stock_in' => 'bg-green-100 text-green-700', 'stock_out' => 'bg-orange-100 text-orange-700', 'adjustment' => 'bg-blue-100 text-blue-700', default => 'bg-gray-100 text-gray-700' } }}">
                            {{ $txn->type }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right {{ $txn->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $txn->quantity > 0 ? '+' : '' }}{{ $txn->quantity }}
                    </td>
                    <td class="px-4 py-3 text-right">{{ $txn->previous_stock }} → {{ $txn->new_stock }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $txn->reason }}</td>
                    <td class="px-4 py-3">{{ $txn->user->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $txn->created_at->format('d M Y H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">No transactions found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="flex justify-center">{{ $transactions->withQueryString()->links() }}</div>
</div>

<!-- Stock In Modal -->
<div id="stockInModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-semibold mb-4">Stock In</h3>
        <form action="{{ route('inventory.stock-in') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product *</label>
                    <select name="product_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="">Select Product</option>
                        @foreach(\App\Models\Product::where('is_active', true)->orderBy('name')->get() as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity *</label>
                        <input type="number" name="quantity" min="1" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit Cost (Rp)</label>
                        <input type="number" name="unit_cost" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reason *</label>
                    <input type="text" name="reason" required class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="e.g., Purchase, Return">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                </div>
            </div>
            <div class="flex gap-2 mt-6">
                <button type="button" onclick="document.getElementById('stockInModal').classList.add('hidden')" class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg font-medium">Submit</button>
            </div>
        </form>
    </div>
</div>

<!-- Stock Out Modal -->
<div id="stockOutModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-semibold mb-4">Stock Out</h3>
        <form action="{{ route('inventory.stock-out') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product *</label>
                    <select name="product_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="">Select Product</option>
                        @foreach(\App\Models\Product::where('is_active', true)->where('current_stock', '>', 0)->orderBy('name')->get() as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }}) - Stock: {{ $p->current_stock }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantity *</label>
                    <input type="number" name="quantity" min="1" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reason *</label>
                    <input type="text" name="reason" required class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="e.g., Damaged, Expired">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                </div>
            </div>
            <div class="flex gap-2 mt-6">
                <button type="button" onclick="document.getElementById('stockOutModal').classList.add('hidden')" class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-orange-500 text-white rounded-lg font-medium">Submit</button>
            </div>
        </form>
    </div>
</div>

<!-- Adjust Modal -->
<div id="adjustModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-semibold mb-4">Adjust Stock</h3>
        <form action="{{ route('inventory.adjust') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product *</label>
                    <select name="product_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="">Select Product</option>
                        @foreach(\App\Models\Product::where('is_active', true)->orderBy('name')->get() as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }}) - Current: {{ $p->current_stock }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">New Quantity *</label>
                    <input type="number" name="new_quantity" min="0" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reason *</label>
                    <input type="text" name="reason" required class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="e.g., Stock opname, Correction">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                </div>
            </div>
            <div class="flex gap-2 mt-6">
                <button type="button" onclick="document.getElementById('adjustModal').classList.add('hidden')" class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg font-medium">Submit</button>
            </div>
        </form>
    </div>
</div>

@endsection
