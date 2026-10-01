# Navigation icon QA

2026-10-01. PASS. User requested real icons for language, cart and CMS menus. Font Awesome Free 6.7.2 Solid paths are stored in a local SVG sprite, with attribution and license. All 21 CMS destinations and master-data disclosure have semantic icons plus existing labels. Account actions, storefront cart/shop, locale and drawer controls use the same family. Existing direction: ENERGY 2 / RHYTHM 3 / MOTION 1.

Vite build and git diff check pass. [Icon runner](scripts/qa-icons.cjs) passes at 320/375/768/1440px: rendered SVG path geometry, all CMS icon coverage, no document overflow, named controls, locale submission, logout and drawer focus restoration. [Results](docs/qa/icon-results.json) record no runtime errors. Existing UI runner passes all 10 scenarios including every sidebar destination, cart behavior, account links, locale and Escape/backdrop. Existing server 8080 checked read-only at 320/375/1440px on shop/login: icons render without document overflow. Existing business database and environment untouched.

Chrome with disposable SQLite fixtures. Backend tests not rerun because business logic did not change. Safari/Firefox and actual screen-reader sessions not run. SVGs inherit existing measured colors, including white on burgundy 9.05:1. [Font Awesome license](https://fontawesome.com/license/free) and [local license](public/icons/LICENSE-fontawesome.txt).

Screenshots: [store mobile](docs/qa/screenshots/icons-store-mobile.png), [CMS desktop](docs/qa/screenshots/icons-cms-1440.png), [CMS mobile drawer](docs/qa/screenshots/icons-cms-375.png).

## Anti-slop delivery gate

Scope: changed navigation components and controls. Previous shopping states remain covered by SHOPPING_QA_REPORT.md.

- R-02 PASS: new authored labels use ordinary punctuation.
- R-03 PASS: responsive matrix and existing-server checks show no document overflow.
- R-17 PASS: no statistics added; cart quantity uses actual state.
- R-18 PASS: no testimonials added.
- R-23 PASS: user requested icons; official licensed SVGs used.
- R-24 PASS: UI runner clicks all sidebar destinations and account/store links.
- R-25 PASS: existing measured AA palette inherited, no new text colors.
- R-26 PASS: locale, logout, drawer, disclosures and links work in browser QA.
- R-27 PASS: auth error, empty search and cart states remain passing in UI regression.
- R-28 PASS: no FAQ added.
- R-32 PASS: native controls retained, icon-only controls labeled, decorative SVGs hidden; focus/Escape tested.
- R-33 PASS: implementation/assets authored using apply_patch.
- R-34 PASS: established light theme retained, no toggle added.
- R-35 PASS: build and both runners pass; SVG geometry confirms painted paths, no runtime errors.
- R-36 PASS: no fabricated security/performance/customer claims.
- R-37 PASS: existing declared direction retained before editing.
- R-38 PASS: official icons and real application destinations, no fabricated store content.

- R-01 PASS: no gradients added.
- R-04 PASS: semantic mapping records task-specific cash register, receipt, stock, customers and other icons, not decorative library usage.
- R-06 PASS: existing typography retained with documented reasons.
- R-07 PASS: no background patterns added.
- R-08 PASS: navigation glyphs replaced; editorial arrows retain destination meaning.
- R-09 PASS: cart badge reports actual quantity, no promotional badge.
- R-10 PASS: no glassmorphism added.
- R-12 PASS: existing popover elevation retained, no repeated floating shadows.
- R-13 PASS: no glows added.
- R-14 PASS: existing content-specific compositions retained.
- R-19 PASS: existing hover/focus/disclosure behavior, no entrance animation.
- R-22 PASS: no illustrations added; symbols identify tasks.

- Dials PASS: ENERGY 2 / RHYTHM 3 / MOTION 1 explicit in DESIGN_SYSTEM.md.
- Consistency PASS: quiet icons preserve the varied retail layout.
- Focal point PASS: navigation supports unchanged dashboard/catalog tasks.
- Whitespace PASS: consistent icon/label gaps and 44px controls inspected.
- Accent PASS: icons inherit active burgundy and inactive neutral colors.
- Identity PASS: retail wordmark, typography and ledger content retained.
- Design read PASS: previously declared warm retail direction carried forward.

- C-1 PASS: icon family, sizing, local delivery and label reasons documented.
- C-2 PASS: links clicked; icon controls submit or toggle intended actions.
- C-3 PASS: icons serve recognition and compact controls, no filler sections.
- C-4 PASS: responsive layout, keyboard, locale, logout and SVG rendering verified; limits stated above.
- C-5 PASS: official assets and truthful evidence, no fabricated claims.
- R-05 PASS: content-driven retail composition retained.
- R-11 PASS: existing modest radii and account indicator retained.
- R-15 PASS: CMS labels retained; icon-only actions have explicit names.
- R-16 PASS: no marketing buzzwords added.
- R-20 PASS: stock, units, suppliers, debt and cashier icons represent actual retail tasks.
- R-21 PASS: established light theme retained.
- R-29 PASS: semantic palette reused via currentColor.
- R-30 PASS: no named-product clone used.
- R-31 PASS: major decisions have written reasons in DESIGN_SYSTEM.md.
