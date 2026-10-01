# Cashier category filter QA

2026-10-01. PASS. Actual active categories now appear as buttons before the POS product list. All categories removes the category restriction while retaining search. Buttons remain in one horizontal scrolling row. Selected category is burgundy and exposes aria-pressed. Switching categories preserves cart contents and keeps barcode lookup independent.

PHPUnit PASS: 80 tests / 634 assertions on isolated SQLite. Two new cases verify category/search combination, sellable-stock filtering, input validation and inactive-category omission. Vite production build, Pint and git diff check PASS. Browser checks use isolated Chrome/SQLite fixtures: [results](docs/qa/pos-category-results.json), [runner](scripts/qa-pos-categories.cjs). Checks cover combined search/All/empty results, preserved cart, mocked barcode outside the category, rapid category changes, keyboard/aria states, 44px targets, a single row, horizontal scrolling and zero document overflow at 320/375/768/1024/1440px. Existing 8080 server also passed read-only category/filter checks at 320/375/768/1440px; no business transaction submitted there.

Screenshots: [mobile](docs/qa/screenshots/pos-categories-375.png), [desktop](docs/qa/screenshots/pos-categories-1440.png). Barcode independence in the browser uses a mocked product response; exact barcode/stock/permissions remain covered by the backend suite. Physical scanners/printers, Safari/Firefox, screen readers and a new MySQL run were not tested. Existing scan/receipt QA remains in BARCODE_QA_REPORT.md.

## Anti-slop delivery gate

Scope is the category controls, filtering requests and responsive layout. Existing ENERGY 2 / RHYTHM 3 / MOTION 1 direction retained; rationale in DESIGN_SYSTEM.md.

- R-02 PASS: new authored label has no em dash.
- R-03 PASS: five-width matrix and existing-server checks show no document overflow; row scrolls within its panel.
- R-17 PASS: no statistics introduced; categories come from the database.
- R-18 PASS: no testimonials introduced.
- R-23 PASS: user explicitly requested category buttons; no visual assets added.
- R-24 PASS: existing destinations unchanged; category buttons query the real POS product endpoint.
- R-25 PASS: existing AA palette reused; selected white/burgundy contrast 9.05:1.
- R-26 PASS: category/All buttons and combined text search tested through actual clicks.
- R-27 PASS: loading aria-busy, empty results and existing error feedback retained; stale request cancellation tested.
- R-28 PASS: no FAQ introduced.
- R-32 PASS: native labeled buttons, aria-pressed/controls, visible focus, keyboard Enter and scroll-to-selection tested.
- R-33 PASS: source authored with apply_patch.
- R-34 PASS: established light theme retained, no toggle introduced.
- R-35 PASS: build, 80 backend tests and recorded browser control/scroll checks pass without runtime errors.
- R-36 PASS: no fabricated performance or compatibility claims.
- R-37 PASS: previously declared retail direction retained before editing.
- R-38 PASS: actual category names and product results, no invented storefront content.

- R-01 PASS: no gradients introduced.
- R-04 PASS: category names identify filters; no decorative icons added.
- R-06 PASS: existing system control typography reused.
- R-07 PASS: no background patterns added.
- R-08 PASS: no decorative arrows added.
- R-09 PASS: pressed state communicates selected filter, no promotional badge.
- R-10 PASS: no glassmorphism added.
- R-12 PASS: simple borders group buttons; no floating shadows added.
- R-13 PASS: no glow effects added.
- R-14 PASS: equivalent category choices share consistent control sizing; existing workbench hierarchy retained.
- R-19 PASS: native scrolling only, no entrance animation introduced.
- R-22 PASS: no illustration assets added.

- Dials PASS: ENERGY 2 / RHYTHM 3 / MOTION 1 retained in DESIGN_SYSTEM.md.
- Consistency PASS: quiet filter row fits the existing varied retail workbench.
- Focal point PASS: scanner and payment total remain dominant; categories support manual browsing.
- Whitespace PASS: 8px control gaps and focus padding inspected in screenshots.
- Accent PASS: burgundy marks the selected category alongside existing primary actions.
- Identity PASS: actual stock/category language and retail controls retained.
- Design read PASS: existing declared direction applied before editing.

- C-1 PASS: single-row scrolling, selected state and retained search have documented reasons.
- C-2 PASS: buttons/filter requests work and preserve cart state in browser tests.
- C-3 PASS: row serves faster product browsing, no filler sections.
- C-4 PASS: tested narrow/wide layouts, keyboard, empty results and rapid requests remain usable; limits stated above.
- C-5 PASS: actual data and scoped evidence, no invented claims.
- R-05 PASS: operational filter row serves actual cashier tasks.
- R-11 PASS: modest rectangular controls retain existing radii.
- R-15 PASS: actual category names and All categories describe the action.
- R-16 PASS: no marketing buzzwords added.
- R-20 PASS: retail category data remains specific to the store.
- R-21 PASS: established light theme retained.
- R-29 PASS: existing semantic palette reused.
- R-30 PASS: no named-product clone used.
- R-31 PASS: major decisions recorded in DESIGN_SYSTEM.md.
