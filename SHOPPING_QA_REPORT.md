# Auth, seed data and shopping QA

Date: 2026-10-01. Result: PASS for the tested development scope. Includes all six auth screens, researched demo seeders, mobile catalog/cart, checkout context, order details and mobile order history. Applied anti-slop and UI UX Pro Max using the existing retail direction in [DESIGN_SYSTEM.md](DESIGN_SYSTEM.md).

## Verification

- PASS: PHPUnit, 72 tests / 562 assertions on isolated SQLite. New tests cover exact quote totals, unavailable stock, invalid quantities, registration return, all 31 business tables, stock-ledger consistency, debt balances, closed-shift variance, refund allocations, repeat seeding and production guards.
- PASS: Vite production build, Pint on changed PHP files, and `git diff --check`. No dependencies or migrations added.
- PASS: 13 operational scenarios in [browser-results.json](docs/qa/browser-results.json), including POS, refund, shift close and customer checkout.
- PASS: 10 UI scenarios in [ui-results.json](docs/qa/ui-results.json), including navigation, mobile dialogs, keyboard, locale and catalog links.
- PASS: 9 shopping scenarios in [shopping-results.json](docs/qa/shopping-results.json), runner [qa-shopping.cjs](scripts/qa-shopping.cjs). Includes auth, filters, empty states, quantity controls, delivery address, totals, payment choice, login/register return, completed checkout, order details, old-order cart preservation, failed quote retry, malformed cart recovery and unavailable stock.
- PASS: responsive auth/catalog/cart at 320 / 375 / 390 / 768 / 1024 / 1440px. Other customer and CMS screens checked on mobile; wide ledgers remain contained in scrolling tables.
- PASS: existing server `127.0.0.1:8080`, 24 route/viewport checks plus a read-only cart quote. [live-responsive-results.json](docs/qa/live-responsive-results.json) records zero document overflow. No order submitted or demo seeder run against that database.
- PASS: eight new contrast pairings, minimum 5.02:1, in [shopping-contrast-results.json](docs/qa/shopping-contrast-results.json).
- PASS: inspected desktop/mobile screenshots for auth hierarchy, product readability, cart summary, order padding and button placement. Browser runners recorded no JavaScript errors.

Browser tests use Chrome and disposable SQLite fixtures. Safari, Firefox, actual screen-reader sessions and a new MySQL run were not performed. Earlier MySQL evidence remains in [QA_REPORT.md](QA_REPORT.md). External email and live payment-provider integration retain their existing limits. Prices, suppliers, customers and transactions in the seed are explicit demo assumptions; research and framework-managed table exclusions are in [SEED_DATA.md](SEED_DATA.md).

## Screenshots

- [Login desktop](docs/qa/screenshots/auth-login-1440.png) and [mobile](docs/qa/screenshots/auth-login-375.png)
- [Registration mobile](docs/qa/screenshots/auth-register-375.png)
- [Shop desktop](docs/qa/screenshots/shop-shopping-1440.png) and [mobile](docs/qa/screenshots/shop-shopping-375.png)
- [Existing server shop](docs/qa/screenshots/shop-live-mobile.png)
- [Guest cart](docs/qa/screenshots/cart-shopping-guest-mobile.png) and [checkout cart](docs/qa/screenshots/cart-shopping-mobile.png)
- [Order desktop](docs/qa/screenshots/order-shopping-1440.png) and [mobile](docs/qa/screenshots/order-shopping-375.png)
- [Order history mobile](docs/qa/screenshots/orders-shopping-mobile.png)

## Anti-slop delivery gate

Scope: presentation and controls changed in this iteration. Shopping/password/login/register controls have browser click-through evidence; existing auth backend tests cover forgot/reset/confirm/verification behavior. This is not a claim that every possible CRUD state or assistive-technology combination was exercised.

### Hard gate

- R-02 PASS: authored interface copy uses ordinary punctuation, no new em dashes.
- R-03 PASS: six-width browser matrix and existing-server checks show no document overflow; wide ledgers scroll within containers.
- R-17 PASS: product counts, quantities and totals derive from database or actual cart state.
- R-18 PASS: no testimonials or invented people are presented as marketing evidence.
- R-23 PASS: user requested the shopping flow and order layout; existing wordmark retained, missing product images use honest category/unit placeholders.
- R-24 PASS: shop, cart, auth continuation and order-history destinations resolve in browser tests.
- R-25 PASS: eight new measured contrast combinations pass AA, minimum 5.02:1.
- R-26 PASS: filters, add/remove/quantity actions, password toggles, checkout, retry and order actions have implemented behavior and test evidence.
- R-27 PASS: empty cart/search, quote loading/error/retry, unavailable stock, auth errors and successful order states are exercised.
- R-28 PASS: no FAQ section introduced.
- R-32 PASS: visible focus, labels, native controls, password pressed state and keyboard navigation checked; dialog Escape regression remains passing.
- R-33 PASS: implementation source changes authored with apply_patch; scripts used for research and QA.
- R-34 PASS: no theme toggle requested or introduced; existing light retail presentation retained.
- R-35 PASS: production build, 72 tests and all three browser runners pass; changed shopping controls have recorded click-throughs with no runtime errors.
- R-36 PASS: no fabricated security, compliance, performance or customer claims.
- R-37 PASS: existing declared warm retail direction and ENERGY 2 / RHYTHM 3 / MOTION 1 retained through auth and shopping.
- R-38 PASS: storefront content comes from real database rows; researched demo records are explicitly disclosed as fixtures, not actual store activity.

### Purpose gate

- R-01 PASS: no gradients or glows introduced.
- R-04 PASS: text controls replace decorative icons; plus/minus directly adjust quantity.
- R-06 PASS: system sans controls and Georgia headings extend the documented storefront typography.
- R-07 PASS: no background grids or decorative patterns introduced.
- R-08 PASS: navigation arrows retain destination meaning; form actions use task text.
- R-09 PASS: badges report real cart quantity or order status; no promotional capsule added.
- R-10 PASS: no glassmorphism introduced.
- R-12 PASS: neutral panels and dividers group content; no large repeated floating shadows.
- R-13 PASS: no glow effects.
- R-14 PASS: auth introduction/form, catalog rows, checkout summary and order ledger have different content-driven proportions.
- R-19 PASS: hover/focus-only interaction respects MOTION 1; reduced-motion browser checks pass.
- R-22 PASS: no generic illustration or fabricated product photo introduced.

### Liveliness

- Dials PASS: ENERGY 2 / RHYTHM 3 / MOTION 1 are explicit in DESIGN_SYSTEM.md.
- Consistency PASS: editorial auth, catalog rows and ledger/order compositions vary rhythm while retaining quiet controls.
- Focal point PASS: auth form, product purchase action, checkout total and order next-step action lead their respective tasks.
- Whitespace PASS: phone gutters, form gaps and padded order sections separate information and actions; screenshots inspected.
- Accent PASS: burgundy identifies primary purchase/auth actions; outlined secondary order action reduces competition.
- Identity PASS: retail headings, actual unit prices, tabular totals and ledger dividers repeat the established motif.
- Design read PASS: the prior declared retail direction was carried forward and its new layout reasons recorded.

### Craft and consistency

- C-1 PASS: auth layout, phone rows, filters, cart bar, checkout summary and order buttons have explicit reasons in DESIGN_SYSTEM.md.
- C-2 PASS: new controls perform concrete navigation, state changes or validated submissions; browser and backend evidence above.
- C-3 PASS: sections serve identity, browsing, item review, fulfillment, payment and order tracking.
- C-4 PASS: tested narrow/wide layouts, keyboard, empty/error/loading states and auth return remain usable; untested browsers are identified above.
- C-5 PASS: no fabricated testimonials, statistics or product images; seed assumptions disclosed.
- R-05 PASS: auth split layout, compact phone product rows and order ledger serve their tasks rather than a uniform marketing-card template.
- R-11 PASS: modest panels, rectangular form controls and quantity/status indicators use purpose-specific shapes.
- R-15 PASS: actions name the task: add to cart, review cart, sign in, place order, my orders and continue shopping.
- R-16 PASS: no AI marketing buzzwords added.
- R-20 PASS: units, price ledger, fulfillment options and shop-specific payment guidance anchor the UI in everyday retail.
- R-21 PASS: established warm light identity retained; no forced dark theme.
- R-29 PASS: warm neutrals, charcoal and burgundy remain the core palette; error/stock/status colors communicate state.
- R-30 PASS: no named-product visual clone used.
- R-31 PASS: major visual and flow decisions have one-line reasons in DESIGN_SYSTEM.md.
