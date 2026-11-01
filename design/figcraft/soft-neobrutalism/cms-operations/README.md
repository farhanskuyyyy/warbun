# CMS operational redesign

> Only [selected previews](../../PREVIEWS.md) are retained in Git. Export filenames below describe the original review run; bulk PNG/PDF evidence can be regenerated from the final native document.

Latest Shelf revision: [grid view and editor](../shelf-grid/README.md) replaces the six Shelf entry layouts with a clickable example floor layout and drag/resize/save prototype states. Earlier numbered product placement remains preserved on page 20 for product assignment; this supersedes the earlier numbered-only entry design described below. No runtime persistence is implemented.

Latest revision: [semantic status colors](STATUS_COLORS.md) are applied to Monitor, order details, Dashboard, POS and Products across light/dark and desktop/tablet/mobile. Reference boards are on page 19.

1 November 2026. Same native document: `../final.figcraft`.
Pages **18 · CMS operations · Dashboard, POS, shelves & monitor** (24044) and **19 · CMS operations · States & handoff** (24045). Design/prototype scope; no Laravel or database implementation.

## Brief and decisions

Users: Warbun owner and cashier. Tasks: see work requiring attention; scan/find items and complete a sale; assign product shelf locations; process oldest orders while respecting payment state. Existing features, access controls and transactional inventory/payment rules remain the implementation contract.

Navbar matches the inspected local web: context on the left; store shortcut, moon/sun theme control and inline account on the right. Account dropdown includes profile, dashboard, logout and language icons. Updated the eight prior Products shell frames as well. All 29 existing sidebar destinations remain; Dashboard, POS, Shelves, Monitor and Products link between native design frames where available.

Dashboard prioritizes sales and actions requiring attention, receivables and recent sales. The development snapshot is labeled; figures are not live business activity. POS uses catalog/cart columns with independent catalog scrolling on desktop/tablet, and a cart dock on mobile. Shelf placement uses numbered racks and product positions, explicitly not a floor map. Monitor preserves oldest-first order with detail beside the queue on desktop and modal detail on narrower screens. Paid status stays visible; an unpaid online order cannot be completed.

Two simple structural alternatives for POS and Monitor are drawn on page 19. POS split view keeps totals visible; catalog plus modal is used on mobile. Monitor queue/detail preserves age priority more clearly than splitting orders into status columns.

## Main frames

| Feature | Desktop light/dark | Tablet light/dark | Mobile light/dark |
| --- | --- | --- | --- |
| dashboard | 24046 / 24297 | 24562 / 24721 | 24880 / 24970 |
| pos | 25060 / 25311 | 25576 / 25735 | 25894 / 25984 |
| shelves | 26074 / 26325 | 26590 / 26749 | 26908 / 26998 |
| monitor | 27088 / 27339 | 27604 / 27763 | 27922 / 28012 |

Desktop 1440×1000, tablet 834×1112, mobile 390×844. PNGs use `<feature>-dl/dd/tl/td/ml/md.png`; `cms-operations.pdf` contains the 24 main frames. Comparative boards and handoff have their own PNGs. Four `review-*.png` sheets show all operational dialogs; profile previews are separate.

## States and interaction contract

64 dialog/sheet designs: checkout and example receipt; active/no shift; camera and manual fallback guidance; rack management with nonempty deletion restriction; four product placement examples; four development order details; ready-but-unpaid warning; customer/delivery address. Wide/mobile and light/dark variants.

Connected native actions include module navigation, theme pairs, account/navigation overlays, opening relevant dialogs and checkout → example receipt. Saves, shift review, order-step actions and delivery confirmation only close the design overlay; they do not mutate data. The blocked-order payment action is a visual specification and closes without capturing payment. Search, category/rack filters, quantity editing, scanning, customer/address selection, actual payments, printing and locale changes require implementation. Do not treat the example receipt as a real transaction. Some state frames are review-only and not reachable from the main flow.

Loading/error/empty results, server validation, permission failures, stock/price conflicts, retry and duplicate-payment protection are documented in the page-19 handoff; they are not all separate rendered screens. Implement Escape, focus trap/return, background scroll locking, accessible labels and keyboard behavior. Keep monetary calculations, authorization, stock mutation and online completion validation on the server.

## Source and verification

Read local Blade layouts/account menu, dashboard, POS, shelves, order monitor, routes and seed products. Playwright inspected the running local Dashboard and Monitor; POS/Shelves returned the Laravel page without the expected main content, so those were designed from source rather than verified as working runtime screens. No transactions or state changes were submitted.

Skills used: product-design-sop and antislop core/UI. Figcraft MCP performed native edits, inspection, prototype wiring and render/export; Playwright provided local web evidence. ui-ux-pro-max was not available and is not claimed.

Reviewed all 24 main screen renders and all 64 dialog renders through contact sheets and targeted full-size images. Fixed independent POS scrolling, initial active shelf visibility, unit wrapping, excessive dialog height, delivery field padding, dark delivery surfaces, and overlapping mobile payment-warning header text. Immediate-parent text bounds were checked across the 24 main screens and 64 dialogs; none were out of bounds at that pass. Intentional clipping contains scrollable navigation, lists and forms. No Figcraft lint command was available. Native presenter playback, continuous browser resizing, backend workflows and runtime accessibility were not tested; no accessibility-compliance claim.

## Icon attribution

Font Awesome Free SVG icons, including moon/sun from the official 7.x repository:
- https://github.com/FortAwesome/Font-Awesome/tree/7.x/svgs/solid
- https://fontawesome.com/license/free

The downloaded SVG headers identify Font Awesome Free 7.3.1, Copyright 2026 Fonticons, Inc.; icons CC BY 4.0. Source SVG headers are retained under `assets/`. Existing project icon assets are reused. No package or dependency was installed.
