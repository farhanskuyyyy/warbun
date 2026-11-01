

import Alpine from 'alpinejs';
import './cart';
import './checkout';
import './catalog';
import './pos';
import './receipt';
import './barcode-camera';
import './delivery-map';
import './address-book';

document.querySelectorAll('[data-barcode-field]').forEach(input => input.addEventListener('keydown', event => {
    if (event.key === 'Enter') event.preventDefault();
}));

window.Alpine = Alpine;

Alpine.start();

document.querySelectorAll('[data-password-toggle]').forEach(button => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.passwordToggle);
        const visible = input.type === 'password';
        input.type = visible ? 'text' : 'password';
        button.setAttribute('aria-pressed', String(visible));
        button.setAttribute('aria-label', visible ? button.dataset.hide : button.dataset.show);
        button.textContent = visible ? button.dataset.hideText : button.dataset.showText;
    });
});

const drawer = document.getElementById('workspace-menu');
document.querySelector('[data-open-menu]')?.addEventListener('click', () => drawer.showModal());
document.querySelector('[data-close-menu]')?.addEventListener('click', () => drawer.close());
drawer?.addEventListener('close', () => document.querySelector('[data-open-menu]')?.focus());
drawer?.addEventListener('click', event => {
    if (event.target === drawer && event.clientX > drawer.getBoundingClientRect().right) drawer.close();
});
window.matchMedia('(min-width: 1024px)').addEventListener('change', event => {
    if (event.matches && drawer?.open) drawer.close();
});
document.addEventListener('click', event => {
    document.querySelectorAll('.account-menu[open]').forEach(menu => {
        if (!menu.contains(event.target)) menu.open = false;
    });
});
document.addEventListener('keydown', event => {
    if (event.key === 'Escape') document.querySelectorAll('.account-menu[open]').forEach(menu => {
        menu.open = false;
        menu.querySelector('summary').focus();
    });
});
function updateWarbunCartCount() {
    let count = 0;
    try {
        const items = JSON.parse(localStorage.getItem('warbun_cart') || '[]');
        if (Array.isArray(items)) count = items.reduce((sum, item) => sum + Math.max(0, Number(item.quantity) || 0), 0);
    } catch { /* A damaged browser cart must not prevent navigation. */ }
    document.querySelectorAll('[data-cart-count]').forEach(badge => { badge.textContent = count; });
}
updateWarbunCartCount();
window.addEventListener('storage', updateWarbunCartCount);
window.addEventListener('warbun:cartchange', updateWarbunCartCount);

const themeMedia = window.matchMedia('(prefers-color-scheme: dark)');
function applyTheme(dark) {
    document.documentElement.dataset.theme = dark ? 'dark' : 'light';
    document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
    document.querySelectorAll('[data-theme-toggle]').forEach(button => button.setAttribute('aria-pressed', String(dark)));
}
applyTheme(document.documentElement.dataset.theme === 'dark');
document.querySelectorAll('[data-theme-toggle]').forEach(button => button.addEventListener('click', () => {
    const dark = document.documentElement.dataset.theme !== 'dark';
    try { localStorage.setItem('warbun_theme', dark ? 'dark' : 'light'); } catch {}
    applyTheme(dark);
}));
themeMedia.addEventListener('change', event => {
    let preference;
    try { preference = localStorage.getItem('warbun_theme'); } catch {}
    if (!['light', 'dark'].includes(preference)) applyTheme(event.matches);
});
window.addEventListener('storage', event => {
    if (event.key === 'warbun_theme') applyTheme(event.newValue === 'dark' || (event.newValue !== 'light' && themeMedia.matches));
});

function closeNamedModal(name) { document.querySelectorAll('[data-modal-name]').forEach(d => { if (d.dataset.modalName === name) d.close(); }); }
window.addEventListener('open-modal', event => { document.querySelectorAll('[data-modal-name]').forEach(d => { if (d.dataset.modalName === event.detail && !d.open) d.showModal(); }); });
window.addEventListener('close-modal', event => closeNamedModal(event.detail));
document.querySelectorAll('[data-modal-name]').forEach(dialog => {
    if (dialog.hasAttribute('data-initial-open')) dialog.showModal();
    dialog.addEventListener('close', event => { if (event.target !== dialog) dialog.close(); });
    dialog.addEventListener('click', event => { const b=dialog.getBoundingClientRect(); if(event.target===dialog && (event.clientX<b.left||event.clientX>b.right||event.clientY<b.top||event.clientY>b.bottom)) dialog.close(); });
});

document.addEventListener('click', event => { const b=event.target.closest('[data-open-named-modal]'); if(b) { event.preventDefault(); window.dispatchEvent(new CustomEvent('open-modal',{detail:b.dataset.openNamedModal})); } });

import './store-overlays';

import './shelf-grid';

const posCartToggle=document.querySelector('[data-pos-cart-toggle]');
posCartToggle?.addEventListener('click',()=>{const expanded=posCartToggle.getAttribute('aria-expanded')!=='true';posCartToggle.setAttribute('aria-expanded',String(expanded));document.getElementById('pos-cart-panel').classList.toggle('is-mobile-open',expanded);if(expanded)document.getElementById('pos-cart-panel').scrollIntoView({block:'start'});});
const monitorDialog=document.getElementById('monitor-detail');
function selectMonitorOrder(article,open=false){const copy=article.cloneNode(true);copy.querySelector('[data-monitor-select]')?.remove();copy.classList.add('is-detail');const selected=document.querySelector('[data-monitor-selected]');selected?.replaceChildren(copy.cloneNode(true));if(open&&innerWidth<1200){document.querySelector('[data-monitor-dialog-body]').replaceChildren(copy);monitorDialog.showModal();}}
document.querySelectorAll('[data-monitor-select]').forEach(button=>button.addEventListener('click',()=>selectMonitorOrder(button.closest('article'),true)));
const firstMonitorOrder=document.querySelector('.monitor-order');if(firstMonitorOrder)selectMonitorOrder(firstMonitorOrder);
document.querySelector('[data-monitor-close]')?.addEventListener('click',()=>monitorDialog.close());

document.addEventListener('keydown',event=>{if(event.key==='Escape'){const dialogs=[...document.querySelectorAll('dialog[open]')];dialogs.at(-1)?.close();}});

const posCheckout=document.getElementById('pos-checkout-dialog');
document.querySelector('[data-pos-checkout-open]')?.addEventListener('click',()=>posCheckout.showModal());
document.querySelector('[data-pos-checkout-close]')?.addEventListener('click',()=>posCheckout.close());
if(!location.pathname.startsWith('/reports'))document.querySelectorAll('.cms-shell table').forEach(table=>{const labels=[...table.querySelectorAll('thead th')].map(h=>h.textContent.trim());if(!labels.length)return;table.classList.add('responsive-records');table.querySelectorAll('tbody tr').forEach(row=>[...row.children].forEach((cell,i)=>{if(cell.colSpan===1)cell.dataset.columnLabel=labels[i]||'';}));});
