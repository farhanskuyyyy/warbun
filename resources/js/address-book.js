const configElement = document.getElementById('address-book-config');
if (configElement) {
    const config = JSON.parse(configElement.textContent);
    const text = config.text;
    const dialog = document.getElementById('addressDialog');
    const form = document.getElementById('addressForm');
    const select = document.querySelector('[data-address-select]');
    const status = document.querySelector('[data-address-status]');
    const retry = document.querySelector('[data-address-retry]');
    const cards = document.getElementById('addressCards');
    const customerSelect = document.getElementById('customerId');
    let addresses = config.addresses;
    let customerId = config.customerId;
    let opener;
    let editing;
    let saving = false;
    let listController;
    let listVersion = 0;
    let applying = false;
    const customers = new Map(config.mode === 'pos' ? JSON.parse(document.getElementById('pos-config').textContent).customers.map(customer => [String(customer.id), customer]) : []);
    const url = () => config.url.replace('__CUSTOMER__', encodeURIComponent(customerId));
    const notify = () => window.dispatchEvent(new Event('addressbook:change'));
    const headers = {'Content-Type':'application/json', Accept:'application/json', 'X-CSRF-TOKEN':config.csrf};

    async function mutate(destination, method, payload) {
        const controller = new AbortController();
        const timer = setTimeout(() => controller.abort(), 15000);
        try {
            return await fetch(destination, {method, headers, signal:controller.signal, body:payload ? JSON.stringify(payload) : undefined});
        } finally { clearTimeout(timer); }
    }

    function chosen() { return addresses.find(address => String(address.id) === select?.value); }
    function apply() {
        if (!select) return;
        applying = true;
        const address = chosen();
        const pos = config.mode === 'pos';
        const summary = document.querySelector('[data-address-summary]');
        const value = address ? address.recipient_name + (address.phone ? ' · ' + address.phone : '') + '\n' + address.address : '';
        summary.textContent = value; summary.hidden = !address;
        const inputs = [
            [pos ? 'shippingAddress' : 'shipping-address', address?.address ?? ''],
            [(pos ? 'posDelivery' : 'cartDelivery') + '-latitude', address?.latitude ?? ''],
            [(pos ? 'posDelivery' : 'cartDelivery') + '-longitude', address?.longitude ?? ''],
        ];
        for (const [id, value] of inputs) document.getElementById(id).value = value;
        for (const [id] of inputs) document.getElementById(id).dispatchEvent(new Event('input', {bubbles:true}));
        select.dispatchEvent(new Event('change', {bubbles:true}));
        applying = false;
        notify();
    }
    function render(preferred) {
        if (select) {
            const previous = preferred ?? select.value;
            select.replaceChildren(new Option(text.choose, ''));
            addresses.forEach(address => select.append(new Option(address.label + ' · ' + address.recipient_name + (address.is_default ? ' · ' + text.main : ''), address.id)));
            select.value = addresses.some(address => String(address.id) === String(previous)) ? String(previous) : String(addresses.find(address => address.is_default)?.id ?? '');
            apply();
        }
        status.textContent = !config.available ? text.signin : (config.mode === 'pos' && !customerId ? text.customer : (addresses.length ? '' : text.empty));
        retry.hidden = true;
        if (!cards) return;
        cards.replaceChildren();
        for (const address of addresses) {
            const card = document.getElementById('addressCardTemplate').content.firstElementChild.cloneNode(true);
            card.querySelector('[data-label]').textContent = address.label;
            card.querySelector('[data-default]').hidden = !address.is_default;
            card.querySelector('[data-recipient]').textContent = address.recipient_name;
            card.querySelector('[data-phone]').textContent = address.phone || '';
            card.querySelector('[data-street]').textContent = address.address;
            const link = card.querySelector('[data-map]');
            const lat = Number(address.latitude); const lng = Number(address.longitude);
            if (address.latitude !== null && address.longitude !== null && Number.isFinite(lat) && Number.isFinite(lng) && Math.abs(lat) <= 90 && Math.abs(lng) <= 180) {
                link.href = 'https://www.openstreetmap.org/?mlat=' + lat + '&mlon=' + lng + '#map=18/' + lat + '/' + lng; link.hidden = false;
            }
            card.querySelector('[data-edit]').addEventListener('click', event => open(event.currentTarget, address));
            for (const [action,label] of [['edit',text.edit],['delete',text.delete]]) {
                const button = card.querySelector('[data-' + action + ']');
                button.setAttribute('aria-label', label + ': ' + address.label);
                button.title = label + ': ' + address.label;
            }
            const main = card.querySelector('[data-set-default]');
            main.hidden = address.is_default;
            main.addEventListener('click', () => saveRecord({...address, is_default:true}, address.id));
            card.querySelector('[data-delete]').addEventListener('click', async () => {
                if (saving || !window.confirm(text.confirmDelete)) return;
                setSaving(true);
                try {
                    const response = await mutate(url() + '/' + address.id, 'DELETE');
                    if (!response.ok) throw new Error('Delete failed');
                    await load();
                } catch {status.textContent = text.deleteFailed;}
                finally {setSaving(false);}
            });
            cards.append(card);
        }
    }
    function setSaving(value) {
        saving = value;
        form?.querySelectorAll('input,textarea,button').forEach(input => { input.disabled = value; });
        if (form && !value) form.is_default.disabled = !addresses.length || Boolean(addresses.find(address => address.id === editing)?.is_default);
        document.getElementById('closeAddressDialog').disabled = value;
        document.querySelectorAll('[data-address-add], #addressCards button').forEach(button => { button.disabled = value; });
        if (select) select.dataset.busy = String(value);
        notify();
    }
    async function load() {
        listController?.abort();
        const version = ++listVersion;
        listController = new AbortController();
        const controller = listController;
        const signal = controller.signal;
        if (select) { addresses = []; select.dataset.busy = 'true'; render(); }
        if (config.mode === 'pos' && !customerId) {
            if (select) select.dataset.busy = 'false';
            notify(); return;
        }
        status.textContent = text.loading; retry.hidden = true; notify();
        let timedOut = false;
        const timer = setTimeout(() => {timedOut = true; controller.abort();}, 15000);
        try {
            const response = await fetch(config.mode === 'pos' ? url() : config.listUrl, {headers:{Accept:'application/json'}, signal});
            if (!response.ok) throw new Error('Load failed');
            const records = await response.json();
            if (signal.aborted || version !== listVersion) return;
            addresses = records; render();
        } catch (error) {
            if ((error.name === 'AbortError' && !timedOut) || version !== listVersion) return;
            status.textContent = text.failed; retry.hidden = false;
        } finally {
            clearTimeout(timer);
            if (version === listVersion) {
                if (select) select.dataset.busy = 'false';
                notify();
            }
        }
    }
    function open(button, address = null) {
        if (saving || button.disabled || (config.mode === 'pos' && !customerId)) return;
        opener = button; editing = address?.id;
        document.getElementById('addressDialogTitle').textContent = address ? text.edit : text.add;
        if (form) {
            form.reset();
            const customer = customers.get(String(customerId));
            for (const name of ['label','recipient_name','phone','address','latitude','longitude']) {
                form.elements[name].value = address?.[name] ?? (name === 'recipient_name' ? (customer?.name ?? config.recipient ?? '') : (name === 'phone' ? (customer?.phone ?? config.phone ?? '') : ''));
            }
            form.is_default.checked = Boolean(address?.is_default || !addresses.length);
            form.is_default.disabled = Boolean(address?.is_default || !addresses.length);
            form.querySelector('[data-default-choice-label]').textContent = form.is_default.disabled ? text.main : text.makeMain;
            document.getElementById('addressFormError').textContent = '';
            document.getElementById('addressBookPoint-latitude').dispatchEvent(new Event('change', {bubbles:true}));
        }
        dialog.showModal();
    }
    async function saveRecord(payload, id = null) {
        if (saving) return;
        const savingFor = customerId;
        setSaving(true);
        if (form) document.getElementById('addressFormError').textContent = '';
        const saveButton = document.getElementById('saveAddressBtn');
        if (saveButton) saveButton.textContent = text.saving;
        try {
            const response = await mutate(url() + (id ? '/' + id : ''), id ? 'PUT' : 'POST', payload);
            const record = await response.json();
            if (!response.ok) {
                const error = record.errors ? Object.values(record.errors).flat().join(' ') : text.saveFailed;
                if (dialog.open) document.getElementById('addressFormError').textContent = error;
                else status.textContent = error;
                return;
            }
            if (savingFor === customerId) {
                addresses = addresses.filter(address => address.id !== record.id).map(address => record.is_default ? {...address, is_default:false} : address);
                addresses.push(record); addresses.sort((a,b) => Number(b.is_default) - Number(a.is_default) || a.id - b.id);
                render(String(record.id)); status.textContent = text.saved;
            }
            if (dialog.open) dialog.close();
        } catch {
            if (dialog.open) document.getElementById('addressFormError').textContent = text.saveFailed;
            else status.textContent = text.saveFailed;
        } finally {
            setSaving(false);
            if (saveButton) saveButton.textContent = text.save;
        }
    }
    select?.addEventListener('change', () => { if (!applying) apply(); });
    window.addEventListener('pos:customer-created', event => customers.set(String(event.detail.id), event.detail));
    customerSelect?.addEventListener('change', () => {customerId = customerSelect.value || null; load();});
    retry?.addEventListener('click', load);
    document.querySelectorAll('[data-address-add]').forEach(button => button.addEventListener('click', () => open(button)));
    for (const id of ['closeAddressDialog','cancelAddressDialog']) document.getElementById(id)?.addEventListener('click', () => { if (!saving) dialog.close(); });
    dialog.addEventListener('cancel', event => {if (saving) event.preventDefault();});
    dialog.addEventListener('close', () => opener?.focus());
    form?.addEventListener('submit', event => {
        event.preventDefault();
        const payload = Object.fromEntries(new FormData(form));
        payload.is_default = form.is_default.checked;
        saveRecord(payload, editing);
    });
    render(select?.dataset.initial || select?.dataset.draft);
}
