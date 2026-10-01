const configElement = document.getElementById('checkout-config');
if (configElement) {
    const config = JSON.parse(configElement.textContent);
    const text = config.messages;
    const {read, write, currency} = window.WarbunCart;
    const form = document.getElementById('checkoutForm');
    const list = document.getElementById('cartItems');
    const status = document.getElementById('quote-status');
    const errors = document.getElementById('quote-errors');
    const retry = document.getElementById('quote-retry');
    const submit = document.getElementById('checkoutBtn');
    const guestAction = document.querySelector('[data-checkout-action]');
    let items = read(); let controller; let ready = false;
    if (!config.hasOldInput) {
        try {
            const draft = JSON.parse(sessionStorage.getItem('warbun_checkout_draft') || '{}');
            if (['pickup','delivery'].includes(draft.delivery_type)) form.delivery_type.value = draft.delivery_type;
            if ([...form.payment_method.options].some(option => option.value === draft.payment_method)) form.payment_method.value = draft.payment_method;
            for (const name of ['address','notes']) if (typeof draft[name] === 'string') form.elements[name].value = draft[name].slice(0,2000);
        } catch { /* A damaged draft should not block checkout. */ }
    }
    const saveDraft = () => sessionStorage.setItem('warbun_checkout_draft', JSON.stringify({delivery_type:form.delivery_type.value, payment_method:form.payment_method.value, address:form.address.value, notes:form.notes.value}));
    form.addEventListener('input', saveDraft);
    form.addEventListener('change', saveDraft);
    const element = (tag, className, value) => {const node = document.createElement(tag); node.className = className; if (value !== undefined) node.textContent = value; return node;};
    function canSubmit(value) {ready = value; if (submit) submit.disabled = !value; guestAction?.setAttribute('aria-disabled', String(!value));}
    function render(focus) {
        list.replaceChildren(); form.hidden = items.length === 0;
        if (!items.length) {
            const empty = element('div', 'cart-empty'); empty.append(element('h2', '', text.empty), element('p', '', text.emptyHelp));
            const link = element('a', 'button-primary', text.shop); link.href = text.shopUrl; empty.append(link); list.append(empty); return;
        }
        for (const item of items) {
            const row = element('article', 'cart-item');
            const details = element('div', 'cart-item-details'); details.append(element('h2', '', item.name || text.unavailable), element('p', 'checkout-help', currency(item.price) + (item.unit ? ' / ' + item.unit : '')));
            if (item.available === false) details.append(element('p', 'field-error', text.unavailable));
            else if (item.stock !== undefined) details.append(element('p', 'checkout-help', text.available.replace(':count', item.stock)));
            const total = element('strong', 'cart-line-total', currency(item.price * item.quantity));
            const controls = element('div', 'quantity-controls');
            const change = quantity => { item.quantity = Math.max(1, Math.min(100000, quantity)); write(items); render({id:item.id, action:'quantity'}); check(); };
            const minus = element('button', '', '−'); minus.type = 'button'; minus.disabled = item.quantity <= 1; minus.setAttribute('aria-label', text.decrease + ' ' + item.name); minus.onclick = () => change(item.quantity - 1);
            const input = element('input', ''); input.type = 'number'; input.min = '1'; input.max = String(item.stock ?? 100000); input.value = item.quantity; input.setAttribute('aria-label', text.quantity + ' ' + item.name); input.dataset.quantityId = item.id; input.onchange = () => change(Number.parseInt(input.value) || 1);
            const plus = element('button', '', '+'); plus.type = 'button'; plus.disabled = item.available === false || item.quantity >= (item.stock ?? 100000); plus.setAttribute('aria-label', text.increase + ' ' + item.name); plus.onclick = () => change(item.quantity + 1);
            controls.append(minus, input, plus);
            const remove = element('button', 'cart-remove', text.remove); remove.type = 'button'; remove.setAttribute('aria-label', text.remove + ' ' + item.name); remove.onclick = () => {items = items.filter(other => other.id !== item.id); write(items); render(); check();};
            row.append(details, total, controls, remove); list.append(row);
        }
        if (focus) list.querySelector(`[data-quantity-id="${focus.id}"]`)?.focus();
    }
    function fulfillment() {
        const delivery = form.delivery_type.value === 'delivery';
        document.getElementById('shipping-address-field').hidden = !delivery;
        form.address.required = delivery; form.address.disabled = !delivery;
        document.getElementById('fulfillment-help').textContent = delivery ? text.delivery : text.pickup;
        document.getElementById('payment-help').textContent = form.payment_method.value === 'cash' ? text.cash : (form.payment_method.value === 'online' ? text.online : text.manual);
    }
    async function check() {
        controller?.abort(); canSubmit(false); errors.textContent = ''; retry.hidden = true;
        if (!items.length) { status.textContent = ''; return; }
        controller = new AbortController(); const signal = controller.signal;
        status.textContent = text.checking;
        try {
            const response = await fetch(config.quoteUrl, {method:'POST', headers:{'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN':config.csrf}, body:JSON.stringify({items:items.map(item => ({product_id:item.id, quantity:item.quantity})), delivery_type:form.delivery_type.value}), signal});
            if (!response.ok) throw new Error('Cart quote unavailable');
            const quote = await response.json(); if (signal.aborted) return;
            const focusedId = Number(document.activeElement?.dataset.quantityId);
            items = items.map(item => ({...item, ...quote.items.find(line => line.id === item.id)}));
            localStorage.setItem('warbun_cart', JSON.stringify(items)); window.dispatchEvent(new Event('warbun:cartchange'));
            render(focusedId ? {id:focusedId} : null);
            for (const [id, value] of [['cartTotal',quote.subtotal],['shippingTotal',quote.shipping],['orderTotal',quote.total]]) document.getElementById(id).textContent = currency(value);
            errors.textContent = quote.errors.join(' '); status.textContent = quote.errors.length ? '' : text.ready; canSubmit(quote.errors.length === 0);
        } catch (error) {
            if (error.name === 'AbortError') return;
            status.textContent = ''; errors.textContent = text.error; retry.hidden = false;
        }
    }
    retry.addEventListener('click', check);
    form.delivery_type.addEventListener('change', () => {fulfillment(); check();});
    form.payment_method.addEventListener('change', fulfillment);
    form.addEventListener('change', () => localStorage.setItem('warbun_checkout_key', crypto.randomUUID()));
    guestAction?.addEventListener('click', event => {if (!ready) event.preventDefault();});
    form.addEventListener('submit', event => {
        if (!ready || !items.length) {event.preventDefault(); return;}
        form.querySelectorAll('[data-cart]').forEach(input => input.remove());
        const hidden = (name, value) => {const input = element('input',''); input.type='hidden'; input.name=name; input.value=value; input.dataset.cart='true'; form.append(input);};
        const key = localStorage.getItem('warbun_checkout_key') || crypto.randomUUID(); localStorage.setItem('warbun_checkout_key', key); hidden('request_key', key);
        items.forEach((item, i) => {hidden(`items[${i}][product_id]`, item.id); hidden(`items[${i}][quantity]`, item.quantity);});
        canSubmit(false); if (submit) submit.textContent = text.placing;
    });
    window.addEventListener('storage', event => {if (event.key === 'warbun_cart') {items = read(); render(); check();}});
    fulfillment(); render(); check(); saveDraft();
}
