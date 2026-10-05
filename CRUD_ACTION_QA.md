# CRUD action icons

5 October 2026. PASS for the tested scope. View/edit/delete text controls now use the shared `crud-action` component on products, categories, brands, product types, units, suppliers, customers, orders, payments and audit indexes; product/customer detail edit actions and shelf deletion use it too. Other controls such as Save, Create staff and Set location retain their task-specific text.

Font Awesome Free 6.7.2 Solid eye, pen-to-square and trash-can extend the local licensed sprite. No dependency/CDN was added. Translated aria-label and native title supply action names. The existing routes, POST/DELETE forms, CSRF, confirmations and shelf disabled guard remain in place.

## Evidence

- [Runner](scripts/qa-crud-icons.cjs) / [results](docs/qa/crud-icons-results.json): 15 scenarios PASS, no page errors. All ten populated indexes render SVG geometry, named/tooltipped actions and 44px targets. First applicable View/Edit links navigate by keyboard to 200 responses. Existing deletion confirmations are opened and dismissed on seven lists; no CRUD records deleted. Product/customer detail Edit links work. Populated shelf deletion remains disabled.
- Ten indexes fit 320/375/768/1024/1440px with no document overflow; wide tables retain local scrolling. [Desktop](docs/qa/screenshots/crud-icons-products-1440.png) and [mobile](docs/qa/screenshots/crud-icons-products-375.png) screenshots reviewed. Action columns on wide tables can require horizontal table scrolling on phones.
- Indonesian names verified: Lihat, Ubah, Hapus. Native tooltip appearance and actual screen-reader output are not tested separately.
- Production Vite build, all Blade template compilation via view:cache, scoped Pint, JS syntax, valid unique SVG symbols and git diff check PASS. Backend tests not rerun because this change only affects presentation. Preview 8080 uses its existing demo database; operational MySQL/.env unchanged.
- Icon-on-white contrast: charcoal 14.94:1, burgundy 9.05:1, delete red 6.47:1. Glyph shapes and accessible text provide meaning in addition to color.

## Anti-slop delivery gate

Existing Warbun retail direction, ENERGY 2 / RHYTHM 3 / MOTION 1. Design reasons recorded in DESIGN_SYSTEM.md.

- R-02 PASS: new labels use plain punctuation.
- R-03 PASS: ten indexes at five widths have no page overflow.
- R-17 PASS: no statistics added.
- R-18 PASS: no testimonials.
- R-23 PASS: official licensed icons explicitly requested by user.
- R-24 PASS: existing action destinations opened.
- R-25 PASS: contrast measured above.
- R-26 PASS: View/Edit navigation and Delete cancellation exercised.
- R-27 PASS: existing empty/error/confirmation states retained.
- R-28 PASS: no FAQ.
- R-32 PASS: native links/buttons, translated names, Enter navigation and visible focus.
- R-33 PASS: source changed through apply_patch.
- R-34 PASS: existing light theme; disabled shelf control retained.
- R-35 PASS: build, Blade compilation and browser click-through recorded.
- R-36 PASS: physical device/screen-reader coverage not claimed.
- R-37 PASS: existing accepted direction reused.
- R-38 PASS: preview seed data retained, no fabricated claims added.
- R-01 PASS: no gradients.
- R-04 PASS: eye/pen/bin correspond to requested actions.
- R-06 PASS: existing typography; no new font.
- R-07 PASS: no background pattern.
- R-08 PASS: no decorative arrows.
- R-09 PASS: no badges added.
- R-10 PASS: no glass.
- R-12 PASS: borders group actions without new shadows.
- R-13 PASS: no glow.
- R-14 PASS: no feature cards.
- R-19 PASS: no new animations.
- R-22 PASS: no illustrations.
- Dials PASS: ENERGY 2 / RHYTHM 3 / MOTION 1 explicit.
- Consistency PASS: compact row actions remain secondary to Create/Save.
- Focal point PASS: existing page heading and primary action.
- Whitespace PASS: 44px targets and existing row gaps.
- Accent PASS: burgundy edit; red communicates destructive intent.
- Identity PASS: local Font Awesome family and retail palette.
- Design read PASS: existing direction chosen before edits.
- C-1 PASS: icons requested to reduce repeated action text.
- C-2 PASS: all applicable action types exercised.
- C-3 PASS: shared control serves existing records.
- C-4 PASS: responsive, keyboard, disabled and confirmation states checked.
- C-5 PASS: actual evidence and limitations recorded.
- R-05 PASS: existing data tables retained.
- R-11 PASS: 5px square controls, no pills.
- R-15 PASS: accessible labels name the action.
- R-16 PASS: no marketing copy.
- R-20 PASS: actions connect to Warbun records.
- R-21 PASS: no forced dark theme.
- R-29 PASS: brand neutrals/burgundy plus semantic delete red.
- R-30 PASS: existing UI preserved.
- R-31 PASS: size, glyph, palette and focus reasons written.
