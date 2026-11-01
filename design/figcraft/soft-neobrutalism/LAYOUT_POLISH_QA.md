# Cart, media, bottom navigation and payment summary

> Only [selected previews](../PREVIEWS.md) are retained in Git. Export filenames below describe the original review run; bulk PNG/PDF evidence can be regenerated from the final native document.

31 October 2026. Updated the same `final.figcraft` document.

- Cart controls now use the existing project Font Awesome Free `cart-shopping` vector with a count badge. Bottom tabs use the same local icon set. Original license/attribution: `public/icons/LICENSE-fontawesome.txt` and `public/icons/fontawesome-solid.svg` in the Warbun project.
- Product media placeholders now contain an actual example PNG fetched from https://placehold.co/600x400/png?text=Foto+Produk and retained at `sample-images/product-600x400.png`. These are placeholders, not real product photography. Images are fitted/cropped to the existing media dimensions.
- Rounded bottom navigation now has a distinct burgundy active tab, white active icon/text, neutral inactive labels and full-tab prototype click targets. The bar is inset from screen edges.
- Mobile subtotal, total, selected channel and confirmation button are grouped in a rounded summary panel. Total label/value use horizontal Auto Layout. Summary position follows the rendered accordion height with a 20 px gap; it is no longer artificially pinned far below a collapsed list. All 32 mobile payment states were updated.
- First screenshot showed clipped/oversized icons from path sizing. Recreated them through native vector creation with proportional height sizing, then re-rendered. Reviewed mobile catalog in light/dark and payment collapsed light/dark plus BRI-expanded light after the summary adjustment.
- Main PDF and PNG previews refreshed. Native document saved. This is a design-only change, not a change to the Laravel app. Browser/assistive-technology behavior, dynamic sticky/navigation behavior and intermediate viewport implementation remain untested.
