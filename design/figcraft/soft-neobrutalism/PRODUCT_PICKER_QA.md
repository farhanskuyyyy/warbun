# Add-to-cart product picker

> Only [selected previews](../PREVIEWS.md) are retained in Git. Export filenames below describe the original review run; bulk PNG/PDF evidence can be regenerated from the final native document.

31 October 2026. Updated the same native file; new overlay designs are on page 11. Product picker flows start at the existing mobile/desktop catalog frames in each theme.

Product cards on catalog/landing and existing product add buttons now open product-specific sheets/modals instead of navigating directly to the cart. Products and price/stock examples come from WarungCatalogSeeder: rice 1 kg Rp15.000/40, sugar 500 g Rp10.000/36, Indomie Ayam Bawang Rp3.500/96 and Bimoli 1 L Rp22.000/24. Packaging is fixed SKU information; no unsupported variants or notes fields were invented.

Each picker shows image, product, unit price, stock, package, quantity, subtotal and primary add action. Prototype quantities 1–3 have corresponding subtotal states, linked through swapOverlay; minus at one has no action. Plus at three is not connected further: this is a demonstration limit, not the real stock limit. An implementation should accept integer quantities up to available stock and validate on the server.

The add action swaps to product/quantity-specific visual feedback, then returns to browsing when dismissed. It does not persist cart data or update the existing global cart dock/count. Four unavailable-stock references have an inactive CTA. Real stock error/loading, existing-item merging, synchronized cart totals/counts, focus trap/restore, keyboard and screen-reader behavior remain implementation specifications.

Created 48 picker states, 48 feedback states and four unavailable states across mobile/desktop and light/dark. In-canvas handoff records boundaries. Reviewed native renders for rice qty 1/2, dark Indomie, desktop Bimoli, unavailable rice and add feedback. Some offset-frame screenshots have renderer crop artifacts; no complete full-resolution visual audit of all 100 states is claimed. Structural inspection checked node bounds and prototype destinations. Saved four main picker PNG previews and the native document. No Laravel/database changes or live payment/cart behavior are claimed.
