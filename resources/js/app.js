

import Alpine from 'alpinejs';
import './cart';
import './checkout';
import './catalog';

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
