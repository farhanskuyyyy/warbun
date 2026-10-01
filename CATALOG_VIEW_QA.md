# Mobile grid/list QA

2026-10-01. PASS. Mobile shop now has Grid / List buttons, default grid, two product columns and a saved browser preference. List retains one-column rows. Desktop retains its existing catalog layout. Reason: customers can browse compact cards or choose more room for names, without changing products, prices or cart behavior. Existing ENERGY 2 / RHYTHM 3 / MOTION 1 direction applies.

Verification: Vite build and git diff check pass. Chrome browser checks against `127.0.0.1:8080` pass at 320, 375, 390, 479, 480, 600, 767, 768, 1024 and 1440px. Both mobile modes have no document overflow and at least 44px button targets. Preference survives reload/search, keyboard Enter switches mode, cart survives switching, empty search still works, and switching works when storage is blocked. No order or database mutation was submitted. No browser runtime errors. Backend tests were not rerun because backend behavior did not change. Safari/Firefox and screen-reader sessions were not run.

Evidence: [results](docs/qa/catalog-view-results.json), [runner](scripts/qa-catalog-view.cjs), [grid screenshot](docs/qa/screenshots/shop-mobile-grid.png), [list screenshot](docs/qa/screenshots/shop-mobile-list.png).

## Anti-slop delivery gate

Scope is the new display controls and their responsive layouts; previous auth/shopping evidence remains in SHOPPING_QA_REPORT.md.

- R-02 PASS: new labels contain no em dash.
- R-03 PASS: both modes pass mobile overflow checks at seven widths.
- R-17 PASS: no statistics introduced; product data unchanged.
- R-18 PASS: no testimonials introduced.
- R-23 PASS: user explicitly requested display selection; no visual assets invented.
- R-24 PASS: existing product destinations unchanged; controls target the real catalog ID.
- R-25 PASS: unchanged measured palette, white on burgundy 9.05:1, ink on white exceeds AA.
- R-26 PASS: both buttons switch layout, pressed state and saved preference in browser tests.
- R-27 PASS: existing empty results exercised; storage failure falls back and switching remains usable.
- R-28 PASS: no FAQ introduced.
- R-32 PASS: native buttons, labeled group, aria-pressed, aria-controls, visible focus and Enter tested.
- R-33 PASS: source authored with apply_patch.
- R-34 PASS: existing light theme retained, no toggle introduced.
- R-35 PASS: production build and recorded click-through of both new controls pass without runtime errors.
- R-36 PASS: no performance/security/customer claims introduced.
- R-37 PASS: existing declared direction and dials retained.
- R-38 PASS: catalog still uses actual database products and honest placeholders.

- R-01 PASS: no gradients introduced.
- R-04 PASS: text labels avoid ambiguous decorative icons.
- R-06 PASS: existing system control typography retained.
- R-07 PASS: no background pattern introduced.
- R-08 PASS: no decorative arrows added.
- R-09 PASS: selected mode uses pressed state, no promotional badge.
- R-10 PASS: no glassmorphism introduced.
- R-12 PASS: controls use borders without floating shadows.
- R-13 PASS: no glow introduced.
- R-14 PASS: repeated grid cards represent equivalent product choices; list gives the same items more reading space.
- R-19 PASS: only a small pressed response, no entrance animation; existing reduced-motion rule retained.
- R-22 PASS: existing honest category/unit placeholders retained.

- Dials PASS: ENERGY 2 / RHYTHM 3 / MOTION 1 retained in DESIGN_SYSTEM.md.
- Consistency PASS: quiet controls sit within the existing varied retail sections.
- Focal point PASS: products and purchase actions remain dominant.
- Whitespace PASS: spaced 44px selector buttons and product gutters inspected in both screenshots.
- Accent PASS: burgundy identifies selected mode and existing purchase actions.
- Identity PASS: existing retail typography, unit prices and category placeholders retained.
- Design read PASS: existing declared direction applied before editing.

- C-1 PASS: responsive choice and default rationale recorded in DESIGN_SYSTEM.md.
- C-2 PASS: both new controls tested through click and keyboard interaction.
- C-3 PASS: selection directly serves browsing preference.
- C-4 PASS: both layouts, persistence, empty search, blocked storage and desktop behavior checked.
- C-5 PASS: no fabricated evidence or content introduced.
- R-05 PASS: layout represents actual product browsing rather than new marketing sections.
- R-11 PASS: rectangular controls retain existing modest radius.
- R-15 PASS: Grid / List name the available modes.
- R-16 PASS: no marketing buzzwords introduced.
- R-20 PASS: actual unit prices and stock maintain the retail identity.
- R-21 PASS: established light theme retained.
- R-29 PASS: existing palette reused.
- R-30 PASS: no visual clone introduced.
- R-31 PASS: layout and selected-state reasons documented.

Mobile supplement PASS: distinct grid/list states, responsive width ranges, compact phone spacing, no clipping, 44px targets with gaps, tap/keyboard behavior and existing compact navigation all verified. Existing cart bar and reserved page space retained; cart remains visible after switching.
