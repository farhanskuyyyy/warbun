function read() {
    try {
        const items = JSON.parse(localStorage.getItem('warbun_cart') || '[]');
        if (!Array.isArray(items)) return [];
        return items.filter(item => Number.isInteger(Number(item.id)) && Number(item.id) > 0 && Number(item.quantity) > 0).map(item => ({...item, id: Number(item.id), name: String(item.name || ''), price: Number(item.price) || 0, quantity: Math.min(100000, Math.max(1, Math.floor(Number(item.quantity))))}));
    } catch { return []; }
}

function write(items) {
    localStorage.setItem('warbun_cart', JSON.stringify(items));
    localStorage.setItem('warbun_checkout_key', crypto.randomUUID());
    window.dispatchEvent(new Event('warbun:cartchange'));
}

function add(product) {
    const items = read();
    const existing = items.find(item => item.id === Number(product.id));
    if ((existing?.quantity || 0) >= Number(product.stock)) return false;
    if (existing) { Object.assign(existing, product, {quantity: existing.quantity + 1}); }
    else items.push({...product, quantity: 1});
    write(items);
    return true;
}

const messages = JSON.parse(document.getElementById('shopping-messages')?.textContent || '{}');
const currency = value => new Intl.NumberFormat(document.documentElement.lang === 'id' ? 'id-ID' : 'en-US', {style:'currency', currency:'IDR', minimumFractionDigits:0, maximumFractionDigits:2}).format(value);
function updateShoppingControls() {
    const items = read();
    const count = items.reduce((sum, item) => sum + item.quantity, 0);
    document.querySelectorAll('[data-cart-bar]').forEach(bar => {
        bar.hidden = count === 0;
        bar.querySelector('[data-cart-bar-count]').textContent = (messages.items || ':count items').replace(':count', count);
        bar.querySelector('[data-cart-bar-subtotal]').textContent = currency(items.reduce((sum, item) => sum + item.price * item.quantity, 0));
    });
    document.querySelectorAll('[data-product-quantity]').forEach(label => {
        const quantity = items.find(item => item.id === Number(label.dataset.productQuantity))?.quantity || 0;
        label.textContent = quantity ? (messages.inCart || ':count in cart').replace(':count', quantity) : '';
    });
}
let noticeTimer;
document.querySelectorAll('[data-product]').forEach(button => {
    button.addEventListener('click', () => {
        const product = JSON.parse(button.dataset.product);
        const added = add(product);
        const notice = document.getElementById('cart-notice');
        if (notice) {
            notice.textContent = added ? (messages.added || 'Added to cart') + ': ' + product.name : messages.limit;
            clearTimeout(noticeTimer);
            noticeTimer = setTimeout(() => { notice.textContent = ''; }, 4000);
        }
        if (added && button.dataset.goToCart) window.location.assign(button.dataset.goToCart);
    });
});
window.WarbunCart = {read, write, add, currency};
updateShoppingControls();
window.addEventListener('warbun:cartchange', updateShoppingControls);
window.addEventListener('storage', updateShoppingControls);
