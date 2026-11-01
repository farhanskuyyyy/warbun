const configNode = document.getElementById('store-overlay-config');
if (configNode) {
    const cfg = JSON.parse(configNode.textContent);
    const cart = window.WarbunCart;
    const dialog = document.getElementById('store-overlay');
    const body = dialog.querySelector('[data-store-body]');
    const footer = dialog.querySelector('footer');
    const title = dialog.querySelector('h2');
    const node = (tag, text, cls = '') => { const e = document.createElement(tag); e.textContent = text; e.className = cls; return e; };
    const button = (text, fn, cls = 'button-secondary') => { const b = node('button', text, cls); b.type = 'button'; b.addEventListener('click', fn); return b; };
    let mode = '', controller, pendingHistory=false, queuedOpen=null, generation=0;
    function open(name) { title.textContent = name; if(pendingHistory){queuedOpen=name;return;} if (!dialog.open) { history.pushState({...history.state,warbunOverlay:true},'',location.href); dialog.showModal(); } }
    function updateDock() {
        const items = cart.read(), dock = document.querySelector('[data-store-dock]');
        dock.hidden = !items.length || location.pathname === new URL(cfg.cartUrl).pathname;
        document.querySelector('[data-dock-count]').textContent = items.reduce((n, i) => n + i.quantity, 0) + ' · ' + cfg.cart;
        document.querySelector('[data-dock-total]').textContent = cart.currency(items.reduce((n, i) => n + i.quantity * i.price, 0));
    }
    function renderCart() {
        mode = 'cart'; body.replaceChildren(); footer.replaceChildren();
        const items = cart.read();
        if (!items.length) body.append(node('p', cfg.empty));
        for (const item of items) {
            const row = node('article', '', 'overlay-cart-row');
            row.append(node('h3', item.name), node('strong', cart.currency(item.price * item.quantity)));
            const controls = node('div', '', 'quantity-controls');
            const input = document.createElement('input'); input.type = 'number'; input.min = '1'; input.max = String(item.stock || 100000); input.value = item.quantity; input.setAttribute('aria-label', cfg.quantity + ' ' + item.name);
            function set(q) { const next = cart.read(); const found = next.find(i => i.id === item.id); if (found) { found.quantity = Math.max(1, Math.min(Number(item.stock) || 100000, q)); cart.write(next); renderCart(); } }
            input.onchange = () => set(Number.parseInt(input.value) || 1);
            controls.append(button('−', () => set(item.quantity - 1)), input, button('+', () => set(item.quantity + 1)));
            row.append(controls, button(cfg.remove, () => { cart.write(cart.read().filter(i => i.id !== item.id)); renderCart(); })); body.append(row);
        }
        if (items.length) {
            const total = node('strong', cart.currency(items.reduce((n, i) => n + i.price * i.quantity, 0)));
            const link = node('a', cfg.checkout, 'button-primary'); link.href = cfg.cartUrl; link.dataset.checkoutNav='true'; footer.append(total, link);
        }
        open(cfg.cart);
    }
    async function picker(url) {
        mode = 'product'; controller?.abort(); controller = new AbortController(); const signal = controller.signal;
        body.replaceChildren(node('p', '…')); footer.replaceChildren(); open(cfg.quantity);
        try {
            const response = await fetch(url, { headers: { Accept: 'application/json' }, signal });
            if (!response.ok) throw Error();
            const product = await response.json(); if (signal.aborted) return;
            title.textContent = product.name; body.replaceChildren();
            if (product.image) { const img = document.createElement('img'); img.src = product.image; img.alt = product.name; body.append(img); }
            body.append(node('p', product.description || ''), node('strong', cart.currency(product.price)));
            const label = node('label', cfg.quantity), qty = document.createElement('input'); qty.type = 'number'; qty.min = '1'; qty.max = String(product.stock); qty.value = '1'; label.append(qty); body.append(label);
            const add = button(cfg.add, () => { let n = Math.min(Number(product.stock), Math.max(1, Number.parseInt(qty.value) || 1)); while (n-- > 0) { if (!cart.add(product)) break; } renderCart(); }, 'button-primary');
            add.disabled = product.stock <= 0; footer.append(add); if (!product.stock) body.append(node('p', cfg.unavailable));
        } catch (error) { if (error.name !== 'AbortError') body.replaceChildren(node('p', cfg.error), button(cfg.retry, () => picker(url))); }
    }
    document.addEventListener('click', event => {
        const receipt=event.target.closest('[data-order-receipt]');
        if(receipt){mode='receipt';body.replaceChildren(document.getElementById('order-receipt-'+receipt.dataset.orderReceipt).content.cloneNode(true));footer.replaceChildren();open(receipt.textContent);return;}
        const reorder=event.target.closest('[data-reorder]');
        if(reorder){
            mode='reorder';const currentGeneration=++generation;body.replaceChildren();footer.replaceChildren();open(reorder.textContent);
            const lines=JSON.parse(reorder.dataset.reorder), approved=[];
            Promise.all(lines.map(async line=>{const p=node('p',line.name);body.append(p);try{const response=await fetch(cfg.shopUrl+'/'+line.id,{headers:{Accept:'application/json'}});if(!response.ok)throw Error();const product=await response.json();const qty=document.createElement('input');qty.type='number';qty.min='0';qty.max=product.stock;qty.value=Math.min(line.quantity,product.stock);qty.setAttribute('aria-label',cfg.quantity+' '+product.name);p.textContent=product.name+' · '+cart.currency(product.price);p.append(qty);approved.push({product,qty});}catch{p.textContent=line.name+' · '+cfg.unavailable;}})).then(()=>{if(currentGeneration!==generation||mode!=='reorder')return;footer.append(button(cfg.add,()=>{for(const {product,qty} of approved){let n=Math.min(product.stock,Math.max(0,Number.parseInt(qty.value)||0));while(n-->0)if(!cart.add(product))break;}renderCart();},'button-primary'));});return;
        }
        const link = event.target.closest('a');
        if (event.target.closest('[data-open-cart]') || (link && !link.dataset.checkoutNav && link.href === cfg.cartUrl && location.pathname !== new URL(cfg.cartUrl).pathname)) { event.preventDefault(); renderCart(); return; }
        if (link && new URL(link.href).origin === location.origin && /^\/shop\/\d+$/.test(new URL(link.href).pathname) && !event.ctrlKey && !event.metaKey) { event.preventDefault(); picker(link.href); }
    });
    dialog.querySelector('[data-store-close]').onclick = () => dialog.close();
    dialog.addEventListener('close', () => { mode = ''; generation++; controller?.abort(); if(history.state?.warbunOverlay){pendingHistory=true;history.back();} });
    window.addEventListener('popstate',()=>{if(pendingHistory){pendingHistory=false;if(queuedOpen){const name=queuedOpen;queuedOpen=null;open(name);}return;}if(dialog.open)dialog.close();});
    dialog.addEventListener('click', event => { const b = dialog.getBoundingClientRect(); if (event.target === dialog && (event.clientX < b.left || event.clientX > b.right || event.clientY < b.top || event.clientY > b.bottom)) dialog.close(); });
    window.addEventListener('warbun:cartchange', updateDock);
    window.addEventListener('storage', () => { updateDock(); if (dialog.open && mode === 'cart') renderCart(); });
    updateDock();
}
