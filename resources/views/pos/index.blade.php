@extends('layouts.app')
@section('title', 'POS / Cashier')
@section('header', 'Point of Sale')

@section('content')
<div class="space-y-4">
    <!-- Shift Status -->
    @if(!$activeShift)
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
            <p class="text-yellow-700 font-medium">⚠️ No Active Shift</p>
            <p class="text-sm text-yellow-600 mb-3">You need to open a shift before making sales.</p>
            <form action="{{ route('pos.open-shift') }}" method="POST" class="flex gap-2">
                @csrf
                <input type="number" name="opening_cash" placeholder="Opening cash (Rp)" min="0" required class="px-4 py-2 border border-yellow-300 rounded-lg text-sm">
                <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded-lg text-sm font-medium hover:bg-yellow-700">Open Shift</button>
            </form>
        </div>
    @else
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 flex items-center justify-between">
            <div>
                <p class="text-green-700 font-medium">✅ Active Shift</p>
                <p class="text-sm text-green-600">Opened: {{ $activeShift->opened_at->format('d M Y H:i') }} | Opening Cash: Rp {{ number_format($activeShift->opening_cash, 0, ',', '.') }}</p>
            </div>
            <form action="{{ route('pos.close-shift') }}" method="POST" onsubmit="return confirm('Close shift?')">
                @csrf
                <input type="number" name="closing_cash" placeholder="Closing cash" min="0" required class="px-3 py-2 border border-green-300 rounded-lg text-sm w-32">
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Close Shift</button>
            </form>
        </div>
    @endif

    <!-- POS Interface -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Products -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100">
            <div class="p-4 border-b border-gray-200">
                <input type="text" id="productSearch" placeholder="Search products..." class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div id="productList" class="p-4 grid grid-cols-2 md:grid-cols-3 gap-2 max-h-[500px] overflow-y-auto">
                <!-- Products loaded via JS -->
            </div>
        </div>

        <!-- Cart -->
        <div class="bg-white rounded-xl border border-gray-100">
            <div class="p-4 border-b border-gray-200">
                <h3 class="font-semibold">🛒 Cart</h3>
            </div>
            <div id="cartItems" class="p-4 space-y-2 max-h-[300px] overflow-y-auto">
                <p class="text-gray-500 text-sm text-center">No items in cart</p>
            </div>
            <div class="p-4 border-t border-gray-200 space-y-3">
                <div class="flex justify-between text-sm">
                    <span>Subtotal</span>
                    <span id="subtotal">Rp 0</span>
                </div>
                <div class="flex justify-between text-lg font-bold">
                    <span>Total</span>
                    <span id="total" class="text-primary">Rp 0</span>
                </div>
                
                <!-- Payment -->
                <div class="space-y-2">
                    <select id="paymentMethod" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="cash">Cash</option>
                        <option value="transfer">Transfer</option>
                        <option value="ewallet">E-Wallet</option>
                        <option value="debt">Debt</option>
                    </select>
                    <input type="number" id="paidAmount" placeholder="Paid amount" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <button onclick="processSale()" class="w-full bg-primary text-white py-3 rounded-lg font-medium hover:bg-primary-dark disabled:opacity-50" id="payBtn" disabled>
                        💳 Pay
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let cart = [];

async function loadProducts(search = '') {
    const response = await fetch(`/pos/products?search=${search}`);
    const products = await response.json();
    const container = document.getElementById('productList');
    container.innerHTML = products.map(p => `
        <div onclick="addToCart(${JSON.stringify(p).replace(/"/g, '&quot;')})" 
             class="p-3 border border-gray-200 rounded-lg cursor-pointer hover:border-primary hover:bg-primary/5 transition">
            <p class="font-medium text-sm">${p.name}</p>
            <p class="text-xs text-gray-500">${p.sku}</p>
            <p class="text-primary font-bold mt-1">Rp ${new Intl.NumberFormat('id-ID').format(p.selling_price)}</p>
            <p class="text-xs ${p.current_stock <= 0 ? 'text-red-500' : 'text-green-500'}">Stock: ${p.current_stock}</p>
        </div>
    `).join('');
}

function addToCart(product) {
    const existing = cart.find(item => item.product_id === product.id);
    if (existing) {
        if (existing.quantity < product.current_stock) {
            existing.quantity++;
        }
    } else {
        cart.push({
            product_id: product.id,
            name: product.name,
            unit_price: product.selling_price,
            quantity: 1,
            max_stock: product.current_stock
        });
    }
    updateCart();
}

function removeFromCart(index) {
    cart.splice(index, 1);
    updateCart();
}

function updateQuantity(index, delta) {
    const item = cart[index];
    const newQty = item.quantity + delta;
    if (newQty > 0 && newQty <= item.max_stock) {
        item.quantity = newQty;
        updateCart();
    }
}

function updateCart() {
    const container = document.getElementById('cartItems');
    const subtotalEl = document.getElementById('subtotal');
    const totalEl = document.getElementById('total');
    const payBtn = document.getElementById('payBtn');
    
    if (cart.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-sm text-center">No items in cart</p>';
        subtotalEl.textContent = 'Rp 0';
        totalEl.textContent = 'Rp 0';
        payBtn.disabled = true;
        return;
    }
    
    let subtotal = 0;
    container.innerHTML = cart.map((item, i) => {
        const itemTotal = item.unit_price * item.quantity;
        subtotal += itemTotal;
        return `
            <div class="flex items-center gap-2 text-sm">
                <div class="flex-1">
                    <p class="font-medium">${item.name}</p>
                    <p class="text-xs text-gray-500">Rp ${new Intl.NumberFormat('id-ID').format(item.unit_price)}</p>
                </div>
                <div class="flex items-center gap-1">
                    <button onclick="updateQuantity(${i}, -1)" class="w-6 h-6 bg-gray-100 rounded text-xs">-</button>
                    <span class="w-8 text-center">${item.quantity}</span>
                    <button onclick="updateQuantity(${i}, 1)" class="w-6 h-6 bg-gray-100 rounded text-xs">+</button>
                </div>
                <p class="w-24 text-right font-medium">Rp ${new Intl.NumberFormat('id-ID').format(itemTotal)}</p>
                <button onclick="removeFromCart(${i})" class="text-red-500 text-xs">✕</button>
            </div>
        `;
    }).join('');
    
    subtotalEl.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(subtotal);
    totalEl.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(subtotal);
    payBtn.disabled = false;
}

async function processSale() {
    if (cart.length === 0) return;
    
    const subtotal = cart.reduce((sum, item) => sum + (item.unit_price * item.quantity), 0);
    const paymentMethod = document.getElementById('paymentMethod').value;
    const paidAmount = parseFloat(document.getElementById('paidAmount').value) || 0;
    
    if (paymentMethod === 'cash' && paidAmount < subtotal) {
        alert('Paid amount must be equal or greater than total');
        return;
    }
    
    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    cart.forEach((item, i) => {
        formData.append(`items[${i}][product_id]`, item.product_id);
        formData.append(`items[${i}][quantity]`, item.quantity);
        formData.append(`items[${i}][unit_price]`, item.unit_price);
    });
    formData.append('payment_method', paymentMethod);
    formData.append('paid_amount', paidAmount || subtotal);
    formData.append('discount', 0);
    
    try {
        const response = await fetch('{{ route("pos.process-sale") }}', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        
        if (response.redirected) {
            window.location.href = response.url;
        } else {
            const result = await response.json();
            if (result.errors) {
                alert(Object.values(result.errors).join('\n'));
            }
        }
    } catch (error) {
        alert('Error processing sale');
    }
}

document.getElementById('productSearch').addEventListener('input', (e) => {
    loadProducts(e.target.value);
});

// Load products on page load
loadProducts();
</script>
@endpush
