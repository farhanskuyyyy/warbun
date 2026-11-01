# Typography aligned with local Warbun web

> Only [selected previews](../PREVIEWS.md) are retained in Git. Export filenames below describe the original review run; bulk PNG/PDF evidence can be regenerated from the final native document.

Date: 2026-10-31. Scope: existing customer design in `final.figcraft`; mobile/desktop, light/dark. No Laravel frontend changes.

Reference: local `http://127.0.0.1:8000/`, `/shop`, and `resources/css/app.css`. Browser computed styles identify Georgia for hero/section/catalog headings and system-ui for body, controls and prices. The system font actually rendered on this Mac is SF NS, available in Figcraft as `System Font`.

## Applied

- Georgia Regular for page/section headings and product modal titles. Mobile hero 39 px; desktop hero 64 px; line height 108%, tracking -4%. Other heading sizes follow available component space, with 120% line height and -2.5% tracking.
- System Font for body, labels, controls, navigation and numeric prices; body line height 150%. Action labels use weight 600; price emphasis uses 700. The existing wordmark remains weight 800 with its previously verified spacing.
- Typography propagated to customer pages, payment states, landing motion frames, cart overlays, product pickers and signed-in variants. Foundations and UI error/empty states updated. Text styles `Warbun/Typography/Web/Body` and `Warbun/Typography/Web/Heading` registered and linked to matching repeated text roles.
- Mobile hero enlarged to 252 px, description moved to y108 and CTA to y190; landing content uses vertical hug sizing to accommodate the new heading metrics.

## Verification

- Figcraft MCP render review: all 44 main customer screens in light/dark at mobile 390 px and desktop 1440 px, plus 12 representative cart/product/account overlays. Contact sheets and individual renders are under `sop-review/typography/`.
- Direct full-size render checks included mobile landing, mobile/desktop payment, login, cart sheet, long product title and account menu. Login and dark payment were rendered again after the button-weight correction.
- Text bounds checked in the inspected main screens and overlay samples: no text exceeded its immediate parent bounds. Deliberately scrollable viewport content remains clipped by the viewport.
- Main landing, catalog, payment, cart and picker PNGs refreshed, plus four main light/dark mobile/desktop PDFs. Native file save confirmed by MCP.

## Limits

This is a design typography update, not a pixel-for-pixel copy of every web layout. System UI fonts depend on OS; Apple font files were not copied into the repository. Headless renders verify appearance, not browser keyboard interaction, screen-reader behavior or live prototype playback. Variant states share the updated type properties; every possible variant was not individually reviewed at full size.

Skills/tools: product-design-sop, antislop core/UI, Figcraft MCP; Playwright browser inspection of local web typography.
