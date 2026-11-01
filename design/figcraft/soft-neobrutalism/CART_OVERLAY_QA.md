# Cart overlay and populated cart dock

> Only [selected previews](../PREVIEWS.md) are retained in Git. Export filenames below describe the original review run; bulk PNG/PDF evidence can be regenerated from the final native document.

31 October 2026. Same native document; reference is the GoFood screenshot supplied by the user. Retained Warbun identity rather than copying GoFood branding, ads, merchant prices or delivery claims.

Mobile cart controls now open a bottom-aligned overlay; desktop opens a centered modal. The sheet has product rows/photos, quantity/remove affordances, a clearly labelled product subtotal, close control and checkout action. Open/close/checkout prototype actions are connected. Opening cart does not navigate away; checkout intentionally closes the overlay and navigates to the existing checkout reference. Header cart controls, including landing intro controls, use the overlay. Product add-to-cart actions open the populated overlay rather than the legacy cart page.

Populated-cart docks appear on 12 home/catalog/product reference frames across mobile/desktop and light/dark, showing two demo items and Rp25.000 product subtotal. Mobile docks sit at y672, above navigation y752, with 16 px separation. Browse viewports provide clearance below content; desktop landing viewport was also shortened to avoid the dock. The demo subtotal excludes shipping.

Page 10 includes populated and empty overlays, empty browse variants without cart docks/count badges, behavior annotations and a static visual reference showing the sheet above a dimmed catalog. That composite is a review illustration, not a runtime overlay screenshot. Empty state flows are named Cart empty.

Reviewed native screenshots: populated mobile catalog, mobile sheet light/dark, empty mobile catalog, desktop modal and the open-sheet context composition. Structural checks inspect bounds except intentional scroll content and verify referenced prototype destinations. Native file saved and main theme PDFs/cart PNG previews refreshed.

Quantity +/- and remove controls remain visual specifications. Empty/populated examples are separate reference states, not live conditional cart data. Runtime subtotal/count synchronization, stock checks, focus trapping/restoration, Escape, background inertness, swipe dismissal, safe areas and screen-reader behavior still require implementation/testing. MCP is headless: actual presenter gesture/overlay playback is not claimed verified. No Laravel or database changes.
