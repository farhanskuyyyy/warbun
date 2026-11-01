# Landing refinement and entrance motion

> Only [selected previews](../PREVIEWS.md) are retained in Git. Export filenames below describe the original review run; bulk PNG/PDF evidence can be regenerated from the final native document.

31 October 2026. Same native file: `final.figcraft`. Updated existing mobile light/dark frames 2/655 and desktop light/dark frames 423/1076, preserving incoming navigation.

Compared two simple structures on page 09: story-led storefront hero versus search-first catalog. Chose story-led: the existing Shop already owns search, while home should introduce Warbun and lead clearly to shopping. Both alternatives remain in the exploration area.

Main changes: stronger typography/CTA, editable vector storefront illustration, need-based category cards, sample-product preview, and pickup/delivery information. Existing cart icons and rounded active navigation remain. Product pictures are placehold.co samples and prices are repository demo data; no real stock/promotions/customer claims added.

Hero/card sections use vertical Auto Layout for the content stack. Content viewport is configured for vertical overflow; the mobile stack and desktop two-column hero are distinct reference layouts. Runtime browser scrolling and intermediate widths are not verified by these fixed reference frames.

Four separate Landing entrance flows start at frames 4849, 4923, 4997 and 5073. The intro has a 180 ms after-delay navigation with Smart Animate, 550 ms easeOut. Content container opacity transitions 0→1 and its position 12 px→final; headers/navigation are not intentionally animated. Ordinary Customer shopping flows still start at final frames, providing a no-animation route. Reduced-motion OS handling, hover/focus interactions and navigation-back replay rules are documented implementation requirements, not working browser code.

Figcraft accepted proto.preview, but the MCP backend reports headless; actual playback timing/scroll interaction has not been visually validated in a presenter. Motion triggers/properties are stored, not evidence of runtime animation testing.

Visually reviewed mobile light/dark and desktop light viewport renders, plus full mobile light and desktop dark content. This exposed a fulfillment panel whose text was laid out horizontally and clipped. Changed all eight final/intro panels to vertical Auto Layout with fill text and re-rendered mobile/desktop panels to verify the correction. Content extending beyond its configured scroll viewport is intentional; actual panel-child overflow was fixed.

Four landing PNG previews and four main theme PDFs refreshed. No Laravel/frontend implementation or database changes. Suggested user checks: identify the primary action, scroll to products/category links, distinguish landing from Shop, and compare motion versus direct-entry flows in the Figcraft presenter.
