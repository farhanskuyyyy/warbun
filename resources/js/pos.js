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
    let selectedShelf = '';
    let productsPage = 1;
    let dialogProduct;
    const customers = new Map(config.customers.map(customer => [String(customer.id), customer]));
    const isDelivery = () => field('saleForm').querySelector('[name="fulfillment_type"]:checked').value === 'delivery';
    let savingCustomer = false;

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
        const total = subtotal - Math.round(Number(field('discount')?.value || 0) * 100) + (isDelivery() ? Math.round(Number(config.shippingCost) * 100) : 0);
        const customer = customers.get(field('customerId').value);
        const debt = field('paymentMethod').value === 'debt';
        const remaining = Math.max(0, total - Math.round(Number(field('paidAmount').value || 0) * 100));
        field('deliveryFields').hidden = !isDelivery();
        field('shippingAddress').required = isDelivery();
        field('customerId').required = isDelivery() || debt;
        field('shippingFee').textContent = money(config.shippingCost);
        field('creditInfo').textContent = debt ? (customer?.eligible ? labels.availableCredit + ': ' + money(customer.available) : labels.notEligible) : '';
        field('paidAmountLabel').textContent = debt ? labels.deposit : labels.paidAmount;
        field('depositHelp').hidden = !debt;
        field('debtPreview').hidden = !debt;
        field('debtPreview').textContent = labels.debtRemaining + ': ' + money(remaining / 100) + (customer?.eligible && remaining > Math.round(Number(customer.available) * 100) ? ' · ' + labels.creditExceeded : '');
        field('total').textContent = money(total / 100);
        field('changeTotal').textContent = money(field('paymentMethod').value === 'debt' ? 0 : Math.max(0, Math.round(Number(field('paidAmount').value || 0) * 100) - total) / 100);
        field('payBtn').disabled = loading || savingCustomer || pendingScans > 0 || !cart.length || !config.activeShift || (debt && (!customer?.eligible || remaining <= 0 || remaining > Math.round(Number(customer.available) * 100)));
        if (field('newCustomerBtn')) field('newCustomerBtn').disabled = loading || savingCustomer;
        field('barcodeInput').disabled = loading || !config.activeShift;
        field('scanBtn').disabled = loading || !config.activeShift;
        document.querySelectorAll('[data-camera-target="barcodeInput"], [data-barcode-focus="barcodeInput"]').forEach(button => { button.disabled = loading || !config.activeShift; });
        field('saleForm').querySelectorAll('input, select').forEach(input => { input.disabled = loading; });
        field('saleForm').querySelectorAll('[data-location-open], [data-location-clear]').forEach(button => { button.disabled = loading || savingCustomer; });
    }

    function showProduct(product) {
        if (loading) return;
        dialogProduct = product;
        field('productDialogTitle').textContent = product.name;
        field('productDialogPrice').textContent = money(product.selling_price);
        field('productDialogDescription').textContent = product.description || '';
        for (const [suffix, value] of Object.entries({Sku: product.sku, Barcode: product.barcode, Category: product.category, Stock: product.current_stock + (product.unit ? ' ' + product.unit : ''), Location: product.location || labels.unassigned})) {
            field('productDialog' + suffix).textContent = value || labels.unavailable;
        }
        field('productDialogError').textContent = config.activeShift ? '' : labels.shift;
        field('addProductBtn').disabled = !config.activeShift;
        field('productDialog').showModal();
    }

    field('closeProductBtn').addEventListener('click', () => field('productDialog').close());
    field('addProductBtn').addEventListener('click', () => {
        if (dialogProduct && add(dialogProduct)) field('productDialog').close();
        else field('productDialogError').textContent = field('scanState').textContent;
    });

    async function loadProducts(search = field('productSearch').value, append = false) {
        productsController?.abort();
        const controller = new AbortController();
        productsController = controller;
        field('productState').textContent = labels.loading;
        field('productList').setAttribute('aria-busy', 'true');
        field('moreProductsBtn').disabled = true;
        if (!append) { productsPage = 1; field('productList').replaceChildren(); field('moreProductsBtn').hidden = true; }
        try {
            const query = new URLSearchParams({search, category_id: selectedCategory, page: append ? productsPage + 1 : 1});
            if (selectedShelf === 'unassigned') query.set('unassigned', '1');
            else if (selectedShelf) query.set('shelf_id', selectedShelf);
            const response = await fetch(config.productsUrl + '?' + query, {signal: controller.signal, headers: {Accept: 'application/json'}});
            if (!response.ok) throw new Error();
            const products = await response.json();
            const list = field('productList');
            if (controller !== productsController) return;
            if (append) productsPage++;
            for (const product of products) {
                const button = text('button', '', 'panel text-left hover:border-primary');
                button.type = 'button';
                button.append(text('strong', product.name, 'block'), text('span', product.sku, 'block text-sm'), text('span', money(product.selling_price), 'block font-semibold'), text('span', labels.stock + ': ' + product.current_stock, 'block text-sm'));
                button.append(text('span', product.location || labels.unassigned, 'product-location'), text('span', labels.viewDetails, 'product-detail-link'));
                button.addEventListener('click', () => showProduct(product));
                list.append(button);
            }
            field('productState').textContent = list.children.length ? '' : labels.empty;
            field('moreProductsBtn').hidden = response.headers.get('X-Has-More') !== '1';
        } catch (error) {
            if (error.name !== 'AbortError' && controller === productsController) field('productState').textContent = labels.error;
        } finally {
            if (controller === productsController) { field('productList').setAttribute('aria-busy', 'false'); field('moreProductsBtn').disabled = false; }
        }
    }

    field('moreProductsBtn').addEventListener('click', () => loadProducts(field('productSearch').value, true));
    document.querySelectorAll('[data-pos-shelf]').forEach(button => button.addEventListener('click', () => {
        clearTimeout(searchTimer);
        selectedShelf = button.dataset.posShelf;
        field('productSearch').value = '';
        selectedCategory = '';
        document.querySelectorAll('[data-pos-category]').forEach(control => control.setAttribute('aria-pressed', String(control.dataset.posCategory === '')));
        document.querySelectorAll('[data-pos-shelf]').forEach(control => control.setAttribute('aria-pressed', String(control === button)));
        loadProducts();
    }));

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
            shipping_latitude: isDelivery() ? field('posDelivery-latitude').value || null : null,
            shipping_longitude: isDelivery() ? field('posDelivery-longitude').value || null : null,
            fulfillment_type: isDelivery() ? 'delivery' : 'in_store', shipping_address: isDelivery() ? field('shippingAddress').value : null, notes: field('saleNotes').value,
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
    for (const id of ['discount', 'customerId', 'paymentMethod', 'paidAmount', 'shippingAddress', 'saleNotes', 'posDelivery-latitude', 'posDelivery-longitude']) {
        field(id)?.addEventListener('input', () => { if (!loading) { requestKey = crypto.randomUUID(); render(); } });
    }
    field('customerId').addEventListener('change', () => {
        field('shippingAddress').value = customers.get(field('customerId').value)?.address || '';
        field('posDelivery-latitude').value = customers.get(field('customerId').value)?.latitude ?? '';
        field('posDelivery-longitude').value = customers.get(field('customerId').value)?.longitude ?? '';
        field('posDelivery-latitude').dispatchEvent(new Event('change', {bubbles:true}));
        requestKey = crypto.randomUUID();
        render();
    });
    document.querySelectorAll('[name="fulfillment_type"]').forEach(input => input.addEventListener('change', () => {
        requestKey = crypto.randomUUID();
        render();
    }));
    if (field('customerDialog')) {
        const dialog = field('customerDialog');
        field('newCustomerBtn').addEventListener('click', () => { if (!loading) dialog.showModal(); });
        field('closeCustomerBtn').addEventListener('click', () => { if (!savingCustomer) dialog.close(); });
        dialog.addEventListener('cancel', event => { if (savingCustomer) event.preventDefault(); });
        field('createCustomerAccount').addEventListener('change', event => {
            field('customerAccountFields').hidden = !event.target.checked;
            field('customerAccountFields').querySelectorAll('input').forEach(input => { input.required = event.target.checked; input.disabled = !event.target.checked; });
        });
        field('customerAccountFields').querySelectorAll('input').forEach(input => { input.disabled = true; });
        field('customerForm').addEventListener('submit', async event => {
            event.preventDefault();
            if (savingCustomer || loading) return;
            const payload = Object.fromEntries(new FormData(event.target));
            payload.create_account = field('createCustomerAccount').checked;
            savingCustomer = true;
            field('customerError').textContent = '';
            field('saveCustomerBtn').textContent = labels.processing;
            field('customerForm').querySelectorAll('input, textarea, button').forEach(input => { input.disabled = true; });
            field('closeCustomerBtn').disabled = true;
            render();
            try {
                const response = await fetch(config.customerUrl, {method: 'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN':config.csrf,Accept:'application/json'},body:JSON.stringify(payload)});
                const customer = await response.json();
                if (!response.ok) {
                    field('customerError').textContent = customer.errors ? Object.values(customer.errors).flat().join(' ') : labels.customerFailed;
                    return;
                }
                customers.set(String(customer.id), {...customer, eligible:false, available:0});
                field('customerId').append(new Option(customer.name + ' · ' + customer.phone, customer.id));
                field('customerId').value = String(customer.id);
                field('shippingAddress').value = customer.address;
                field('posDelivery-latitude').value = customer.latitude ?? '';
                field('posDelivery-longitude').value = customer.longitude ?? '';
                field('posDelivery-latitude').dispatchEvent(new Event('change', {bubbles:true}));
                requestKey = crypto.randomUUID();
                dialog.close();
                event.target.reset();
                field('newCustomerDelivery-latitude').dispatchEvent(new Event('change', {bubbles:true}));
                field('customerAccountFields').hidden = true;
                scanStatus(labels.savedCustomer);
            } catch { field('customerError').textContent = labels.customerFailed; }
            finally {
                savingCustomer = false;
                field('customerForm').querySelectorAll('input, textarea, button').forEach(input => { input.disabled = false; });
                field('customerAccountFields').querySelectorAll('input').forEach(input => { input.disabled = !field('createCustomerAccount').checked; input.required = field('createCustomerAccount').checked; });
                field('saveCustomerBtn').textContent = labels.saveCustomer;
                field('closeCustomerBtn').disabled = false;
                render();
            }
        });
    }
    try { field('receiptPaper').value = localStorage.getItem('warbun_receipt_paper') === '58' ? '58' : '80'; } catch { /* Paper selection works without browser storage. */ }
    field('receiptPaper').addEventListener('change', () => {
        try { localStorage.setItem('warbun_receipt_paper', field('receiptPaper').value); } catch { /* The selected width is still included in checkout. */ }
    });
    scanStatus(config.activeShift ? labels.scanReady : labels.shift);
    render();
    loadProducts();
}
