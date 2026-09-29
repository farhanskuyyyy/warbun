@extends('layouts.customer')
@section('title', 'Cart')

@section('content')
<h1 class="text-xl font-bold text-gray-800 mb-4">🛒 Keranjang Belanja</h1>

<div id="cartEmpty" class="text-center py-12 text-gray-500">
    <p class="text-4xl mb-2">🛒</p>
    <p>Keranjang kosong</p>
    <a href="{{ route('customer.shop') }}" class="text-primary mt-2 inline-block">Mulai Belanja →</a>
</div>

<div id="cartContent" class="hidden space-y-4">
    <div id="cartItems" class="space-y-3"></div>
    
    <div class="bg-white rounded-xl border border-gray-100 p-4">
        <div class="flex justify-between mb-2"><span class="text-gray-600">Subtotal</span><span id="subtotal" class="font-medium">Rp 0</span></div>
        <div class="flex justify-between mb-2"><span class="text-gray-600">Ongkir</span><span id="shipping" class="font-medium">Rp 0</span></div>
        <div class="flex justify-between border-t pt-2"><span class="font-bold">Total</span><span id="total" class="font-bold text-primary text-lg">Rp 0</span></div>
    </div>

    <!-- Delivery Options -->
    <div class="bg-white rounded-xl border border-gray-100 p-4">
        <h3 class="font-semibold mb-3">Opsi Pengambilan</h3>
        <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer mb-2">
            <input type="radio" name="delivery" value="pickup" checked class="text-primary" onchange="updateDelivery()">
            <span>🏪 Ambil di Tempat (Gratis)</span>
        </label>
        <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer">
            <input type="radio" name="delivery" value="delivery" class="text-primary" onchange="updateDelivery()">
            <span>🚚 Diantar (+Rp 10.000)</span>
        </label>
        <div id="addressField" class="hidden mt-3">
            <textarea id="address" placeholder="Alamat pengiriman..." class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm"></textarea>
        </div>
    </div>

    <button onclick="checkout()" class="w-full bg-primary text-white py-3 rounded-xl font-semibold hover:bg-primary-dark transition">
        Checkout →
    </button>
</div>
@endsection

@push('scripts')
<script>
let cart = JSON.parse(localStorage.getItem('warbun_cart') || '[]');

function renderCart() {
    const empty = document.getElementById('cartEmpty');
    const content = document.getElementById('cartContent');
    const items = document.getElementById('cartItems');
    
    if (cart.length === 0) {
        empty.classList.remove('hidden');
        content.classList.add('hidden');
        return;
    }
    empty.classList.add('hidden');
    content.classList.remove('hidden');
    
    let subtotal = 0;
    items.innerHTML = cart.map((item, i) => {
        const itemTotal = item.price * item.quantity;
        subtotal += itemTotal;
        return `
            <div class="bg-white rounded-xl border border-gray-100 p-4 flex items-center gap-3">
                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center text-xl">📦</div>
                <div class="flex-1">
                    <p class="font-medium text-sm">${item.name}</p>
                    <p class="text-primary text-sm">Rp ${new Intl.NumberFormat('id-ID').format(item.price)}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="updateQty(${i}, -1)" class="w-8 h-8 bg-gray-100 rounded-lg text-sm">-</button>
                    <span class="w-8 text-center font-medium">${item.quantity}</span>
                    <button onclick="updateQty(${i}, 1)" class="w-8 h-8 bg-gray-100 rounded-lg text-sm">+</button>
                </div>
                <button onclick="removeItem(${i})" class="text-red-400 hover:text-red-600 text-sm">✕</button>
            </div>`;
    }).join('');
    
    const delivery = document.querySelector('input[name="delivery"]:checked').value;
    const shipping = delivery === 'delivery' ? 10000 : 0;
    document.getElementById('subtotal').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(subtotal);
    document.getElementById('shipping').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(shipping);
    document.getElementById('total').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(subtotal + shipping);
}

function updateQty(i, delta) {
    cart[i].quantity += delta;
    if (cart[i].quantity <= 0) cart.splice(i, 1);
    localStorage.setItem('warbun_cart', JSON.stringify(cart));
    renderCart();
}

function removeItem(i) {
    cart.splice(i, 1);
    localStorage.setItem('warbun_cart', JSON.stringify(cart));
    renderCart();
}

function updateDelivery() {
    const delivery = document.querySelector('input[name="delivery"]:checked').value;
    document.getElementById('addressField').classList.toggle('hidden', delivery !== 'delivery');
    renderCart();
}

function checkout() {
    if (cart.length === 0) return alert('Keranjang kosong!');
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("customer.checkout") }}';
    form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">';
    cart.forEach((item, i) => {
        form.innerHTML += `<input type="hidden" name="items[${i}][product_id]" value="${item.id}">`;
        form.innerHTML += `<input type="hidden" name="items[${i}][quantity]" value="${item.quantity}">`;
    });
    form.innerHTML += `<input type="hidden" name="delivery_type" value="${document.querySelector('input[name="delivery"]:checked').value}">`;
    form.innerHTML += `<input type="hidden" name="address" value="${document.getElementById('address')?.value || ''}">`;
    document.body.appendChild(form);
    form.submit();
}

renderCart();
</script>
@endpush
