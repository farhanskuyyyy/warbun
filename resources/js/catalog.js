const catalog = document.getElementById('shop-products');
if (catalog) {
    const controls = document.querySelectorAll('[data-catalog-view]');
    function selectView(view) {
        const selected = view === 'list' ? 'list' : 'grid';
        catalog.dataset.view = selected;
        controls.forEach(button => button.setAttribute('aria-pressed', String(button.dataset.catalogView === selected)));
    }
    try { selectView(localStorage.getItem('warbun_catalog_view')); }
    catch { selectView('grid'); }
    controls.forEach(button => button.addEventListener('click', () => {
        selectView(button.dataset.catalogView);
        try { localStorage.setItem('warbun_catalog_view', catalog.dataset.view); }
        catch { /* The selected layout still works when browser storage is unavailable. */ }
    }));
}
