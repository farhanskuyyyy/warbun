const configElement = document.getElementById('pos-config');
if (configElement) {
    const config = JSON.parse(configElement.textContent);
    const labels = config.text;
    const field = id => document.getElementById(id);
    const money = value => new Intl.NumberFormat(config.locale, {style: 'currency', currency: 'IDR', maximumFractionDigits: 2}).format(value);
    const cart = [];
    let requestKey = crypto.randomUUID();
    let loading = false;
    let pendingScans = 0;
    let scanQueue = Promise.resolve();
    let productsController;
    let searchTimer;
    let selectedCategory = '';

    function text(tag, value, classes = '') {
        const element = document.createElement(tag);
        element.textContent = value;
        element.className = classes;
        return element;
    }

    function scanStatus(message, error = false) {
        field('scanState').textContent = message;
        field('scanState').classList.toggle('is-error', error);
    }

    function add(product) {
        if (loading || !config.activeShift) { scanStatus(labels.shift, true); return false; }
        const existing = cart.find(item => item.product_id === product.id);
        if ((existing?.quantity || 0) >= product.current_stock) { scanStatus(labels.stockLimit + ' ' + product.name, true); return false; }
        if (!existing && cart.length >= 100) { scanStatus(labels.tooMany, true); return false; }
        if (existing) {
            Object.assign(existing, {name: product.name, max: product.current_stock, unit_price: product.selling_price, quantity: existing.quantity + 1});
        } else {
            cart.push({product_id: product.id, name: product.name, quantity: 1, unit_price: product.selling_price, max: product.current_stock});
        }
        requestKey = crypto.randomUUID();
        render();
        scanStatus(labels.added + ': ' + product.name);
        return true;
    }

    function render() {
        const list = field('cartItems');
        list.replaceChildren();
        if (!cart.length) list.append(text('p', labels.emptyCart));
        for (const [index, item] of cart.entries()) {
            const row = text('div', '', 'pos-cart-item');
            row.dataset.productId = item.product_id;
            row.append(text('strong', item.name, 'block'), text('span', money(item.unit_price), 'block text-sm'));
            const controls = text('div', '', 'flex flex-wrap items-center gap-3');
            for (const delta of [-1, 1]) {
                const button = text('button', delta === 1 ? '+' : '−', 'px-4 border rounded');
                button.type = 'button';
                button.disabled = loading;
                button.setAttribute('aria-label', (delta === 1 ? labels.increase : labels.decrease) + ' ' + item.name);
                button.addEventListener('click', () => {
                    if (loading) return;
                    if (item.quantity + delta <= 0 || item.quantity + delta > item.max) return;
                    item.quantity += delta;
                    requestKey = crypto.randomUUID();
                    render();
                });
                controls.append(button);
            }
            const quantity = text('span', item.quantity);
            quantity.dataset.posQuantity = '';
            controls.append(quantity);
            const remove = text('button', labels.remove, 'text-red-700 px-2');
            remove.type = 'button';
            remove.disabled = loading;
            remove.addEventListener('click', () => {
                if (loading) return;
                cart.splice(index, 1);
                requestKey = crypto.randomUUID();
                render();
            });
            controls.append(remove);
            row.append(controls);
            list.append(row);
        }
        const subtotal = cart.reduce((sum, item) => sum + Math.round(Number(item.unit_price) * 100) * item.quantity, 0);
        const total = subtotal - Math.round(Number(field('discount')?.value || 0) * 100);
        field('total').textContent = money(total / 100);
        field('changeTotal').textContent = money(field('paymentMethod').value === 'debt' ? 0 : Math.max(0, Math.round(Number(field('paidAmount').value || 0) * 100) - total) / 100);
        field('payBtn').disabled = loading || pendingScans > 0 || !cart.length || !config.activeShift;
        field('barcodeInput').disabled = loading || !config.activeShift;
        field('scanBtn').disabled = loading || !config.activeShift;
        field('saleForm').querySelectorAll('input, select').forEach(input => { input.disabled = loading; });
    }

    async function loadProducts(search = field('productSearch').value) {
        productsController?.abort();
        const controller = new AbortController();
        productsController = controller;
        field('productState').textContent = labels.loading;
        field('productList').setAttribute('aria-busy', 'true');
        try {
            const query = new URLSearchParams({search, category_id: selectedCategory});
            const response = await fetch(config.productsUrl + '?' + query, {signal: controller.signal, headers: {Accept: 'application/json'}});
            if (!response.ok) throw new Error();
            const products = await response.json();
            const list = field('productList');
            list.replaceChildren();
            for (const product of products) {
                const button = text('button', '', 'panel text-left hover:border-primary');
                button.type = 'button';
                button.append(text('strong', product.name, 'block'), text('span', product.sku, 'block text-sm'), text('span', money(product.selling_price), 'block font-semibold'), text('span', labels.stock + ': ' + product.current_stock, 'block text-sm'));
                button.addEventListener('click', () => add(product));
                list.append(button);
            }
            field('productState').textContent = products.length ? '' : labels.empty;
        } catch (error) {
            if (error.name !== 'AbortError') field('productState').textContent = labels.error;
        } finally {
            if (controller === productsController) field('productList').setAttribute('aria-busy', 'false');
        }
    }

    document.querySelectorAll('[data-pos-category]').forEach(button => button.addEventListener('click', () => {
        clearTimeout(searchTimer);
        selectedCategory = button.dataset.posCategory;
        document.querySelectorAll('[data-pos-category]').forEach(control => control.setAttribute('aria-pressed', String(control === button)));
        button.scrollIntoView({block: 'nearest', inline: 'nearest'});
        loadProducts();
    }));

    field('barcodeForm').addEventListener('submit', event => {
        event.preventDefault();
        const barcode = field('barcodeInput').value.trim();
        if (!barcode || loading || !config.activeShift) return;
        field('barcodeInput').value = '';
        field('barcodeInput').focus();
        pendingScans++;
        render();
        scanQueue = scanQueue.then(async () => {
            scanStatus(labels.scanning);
            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 10000);
            try {
                const response = await fetch(config.barcodeUrl + '?barcode=' + encodeURIComponent(barcode), {signal: controller.signal, headers: {Accept: 'application/json'}});
                const data = await response.json();
                if (!response.ok) { scanStatus(data.message || labels.scanFailed, true); return; }
                add(data);
            } catch { scanStatus(labels.scanFailed, true); }
            finally { clearTimeout(timeout); pendingScans--; render(); }
        });
    });

    field('saleForm').addEventListener('submit', async event => {
        event.preventDefault();
        if (loading || pendingScans || !cart.length || !config.activeShift) return;
        const payload = {
            request_key: requestKey, items: cart.map(({product_id, quantity}) => ({product_id, quantity})),
            customer_id: field('customerId').value || null, payment_method: field('paymentMethod').value,
            paid_amount: field('paidAmount').value, discount: field('discount')?.value || 0,
            print_receipt: field('printReceipt').checked, receipt_paper: field('receiptPaper').value,
        };
        loading = true;
        render();
        field('payBtn').textContent = labels.processing;
        field('saleError').textContent = '';
        try {
            const response = await fetch(config.saleUrl, {method: 'POST', headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrf, Accept: 'application/json'}, body: JSON.stringify(payload)});
            const data = await response.json();
            if (!response.ok) {
                field('saleError').textContent = data.errors ? Object.values(data.errors).flat().join(' ') : labels.failed;
                return;
            }
            window.location.assign(data.redirect);
        } catch { field('saleError').textContent = labels.failed; }
        finally { loading = false; field('payBtn').textContent = labels.pay; render(); }
    });

    field('productSearch').addEventListener('input', event => {
        clearTimeout(searchTimer);
        productsController?.abort();
        searchTimer = setTimeout(() => loadProducts(event.target.value), 200);
    });
    for (const id of ['discount', 'customerId', 'paymentMethod', 'paidAmount']) {
        field(id)?.addEventListener('input', () => { if (!loading) { requestKey = crypto.randomUUID(); render(); } });
    }
    try { field('receiptPaper').value = localStorage.getItem('warbun_receipt_paper') === '58' ? '58' : '80'; } catch { /* Paper selection works without browser storage. */ }
    field('receiptPaper').addEventListener('change', () => {
        try { localStorage.setItem('warbun_receipt_paper', field('receiptPaper').value); } catch { /* The selected width is still included in checkout. */ }
    });
    scanStatus(config.activeShift ? labels.scanReady : labels.shift);
    render();
    loadProducts();
}
