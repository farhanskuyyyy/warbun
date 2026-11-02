# Final design implementation and software QA

Completed 2 November 2026 on `feat/warbun-final-ui`. Work started 1 November. Native reference: `design/figcraft/soft-neobrutalism/final.figcraft`, unchanged SHA-256 `1c7609637fda9179509da2c5f74cf563adc0551739bbb5ee9d3b809c659ba1ba`.

## Implemented scope

| Phase | Delivered behavior | Main implementation commits |
| --- | --- | --- |
| P00 | Isolated baseline, route/view inventory, formatting repair | `8d3a03f` |
| P01 | Shared tokens, buttons/inputs, theme initialization/persistence, native dialogs, focus/scroll behavior, semantic dark statuses and reduced motion | `30b4573`, `02447a1`, `512afb2`, `675c085` |
| P02 | Native-reference storefront hero/illustration, category/featured catalog, mobile bottom navigation, account/login controls, role-aware auth return paths | `500d7c5`, `288e3ff` |
| P03 | Product quantity picker, cart bottom sheet/dock, synchronized quantities, direct-route fallback and overlay back navigation | `500d7c5`, `369c0fa` |
| P04 | Server fee/radius policy, signed expiring quotes, stale-price rejection, manual payment channels/logos, settings and immutable charges | `1bda1e3`, `d84db15`, `88e8e4b` |
| P05 | Product-rich order history, per-order receipt overlay, current-price reorder review, saved product names and monetary snapshots | `500d7c5`, checkout/storefront fixes |
| P06 | Final CMS shell, 264px desktop navigation, drawer, 29 destinations, shared typography and surfaces | `d220622`, `675c085` |
| P07 | Existing CRUD/stock/customer/management flows adapted through shared forms, mobile record cards, table scrolling and policy settings | `d220622`, `d84db15`, `675c085` |
| P08 | POS catalog/cart workspace, mobile cart sheet, payment modal, delivery quote and existing shift/receipt/history workflows | `88e8e4b`, `d220622`, `675c085` |
| P09 | Oldest-first monitor queue with selected detail/sidebar or mobile overlay; consistent transaction/financial/report/audit surfaces | `d220622`, `675c085` |
| P10 | Actual grid drag/resize, numeric and keyboard alternatives, atomic revisioned save, bounds/collision checks and live shelf-product detail | `88e8e4b`, `512afb2`, QA fixes |
| P11 | Automated page matrix, real browser interactions, SQLite/MySQL regressions, concurrent stock test, migration checks, visual corrections and this handoff | cb49418 |

Dedicated frames were used for major customer/CMS/operational layouts. CRUD pages without dedicated final frames reuse the shared shell/components with existing source content; they are adaptations, not invented native frames. Native nodes 423/2, 25060, 27088 and33762 were inspected and rendered through Figcraft MCP; related page IDs and state specifications are in DESIGN-MAP. Laravel/Blade/Alpine architecture and transactional services were retained.

## Verification actually performed

| Check | Result |
| --- | --- |
| Full isolated SQLite PHPUnit suite | PASS:117 tests,962 assertions |
| MySQL financial/policy/layout regression subset | PASS:41 tests,189 assertions |
| Production build | PASS:241 modules; no dependency upgrades required |
| Pint and diff whitespace checks | PASS |
| Standalone Chrome page matrix | PASS:54 owner-accessible pages ×390/834/1440px ×light/dark =324 page checks; zero document overflow and no uncaught JS errors |
| Additional customer routes | PASS:history, addresses, own order, product, verification prompt, password confirmation ×3 widths ×2 themes =36 checks |
| Guest routes | PASS:landing/shop/login/register/forgot/reset ×320/390/834/1440px ×2 themes =48 checks |
| Trusted mouse/keyboard interactions | PASS:modal opening, focus containment/return and Escape; picker/add/dock/cart; cart-to-checkout navigation; shelf pointer drag, arrow-key move, Shift+arrow resize, save/reload and live products; customer checkout/receipt/access denial; failed quote/retry; POS payment/receipt; mobile monitor detail |
| Completed-order reorder | PASS:completed paid demo order through authorized owner action; customer review shows current prices/quantities; adding returns to cart with5 items |
| MySQL stock concurrency | PASS:two PHP workers competing for the final item produced one sale and one rejected request |
| Legacy MySQL migration fixture | PASS:debit/credit preserved, remaining principal backfill and subsequent repayment reconcile |
| Additive UI schema upgrade | PASS:both new migrations applied to the disposable browser DB with legacy orders, and to a dedicated MySQL QA database |

The page matrix verifies rendering, overflow and runtime errors. The interaction and backend suites establish additional behavior; the matrix alone is not represented as complete functional coverage of every row/action permutation.

Browser tests ran on a disposable SQLite server at127.0.0.1:8876, separate session cookies, array cache/mail and testing environment. MySQL tests used only `warbun_qa_final_20261101`. Test transactions did not use the local development database; `.env` was unchanged. After testing, the local development database was backed up and additively upgraded, without reset or seed. The unrelated `.DS_Store` change was excluded. No payment, email, deploy or push to an external service occurred.

## Visual review and fixes

Reviewed page contact sheets covering all54 owner routes across both themes and three widths, with full-size checks on the primary storefront/POS/monitor/grid screens. Fixed actual defects found rather than treating screenshots as a pass: mobile table min-width clipped values; dark empty/filter/banner/header/link surfaces lacked contrast; mobile wordmark wrapped; payment select and settings fieldset caused overflow; modal migration initially failed pointer opening; shelf redraw lost keyboard focus; pickup quote fingerprints included an irrelevant disabled delivery address; cart links intercepted checkout navigation. Re-ran affected tests after changes.

Static screenshot review uses reduced motion/disabled screenshot animations so an entrance frame is not mistaken for low contrast. Existing account, transaction, money and stock behavior remains server-authoritative. The final grid is an application editor, not navigation between predetermined prototype frames.

Contrast samples: white/burgundy9.05:1; dark body/surface14.54:1; dark muted/surface7.39:1; input boundary/white3.36:1; dark teal/surface8.11:1. These samples and keyboard checks do not constitute a blanket accessibility-compliance claim.

Retained evidence (bulk screenshots remain outside Git):

- [Page matrix results](final/page-matrix-results.json), [interaction results](final/interaction-results.json).
- [Landing](final/landing-desktop.png), [mobile product records](final/products-mobile.png), [dark master data](final/master-data-dark.png).
- [POS workspace](final/pos-desktop.png), [monitor](final/monitor-desktop.png), [tablet shelf view](final/shelves-tablet-dark.png).
- [Cart sheet](final/cart-sheet.png), [shelf editor](final/shelf-editor.png), [receipt sheet](final/receipt-sheet.png).

## Reproduce the browser QA

Read the README guarded browser-fixture setup first. Start a server with the same disposable DB/environment as fixture setup. Then:

```sh
APP_ENV=testing DB_DATABASE=/tmp/warbun-qa-EXAMPLE/database.sqlite \
QA_BASE_URL=http://127.0.0.1:8876 QA_OUTPUT=/tmp/warbun-qa-final-run \
PLAYWRIGHT_MODULE=/path/to/existing/playwright \
node scripts/qa-final-revamp.cjs
```

Set `QA_INTERACTIONS_ONLY=1` for targeted interaction retests. `QA_BROWSER=webkit` or `firefox` selects a secondary engine; `QA_BROWSER_EXECUTABLE` can select an already installed matching binary without downloading dependencies. The runner expects the standard demo seed with IDs1 and preconfigured shelves. It mutates only disposable fixtures (orders, sales and layout), so do not point it at an operational server. It refuses a nonlocal host or missing QA environment guard. Review its selectors and preconditions when changing seed data.

## Local rollout and policy configuration

From the repository on this branch, with your development DB configured and backed up:

```sh
php artisan migrate
npm run build
php artisan serve
```

Restart the existing PHP server if necessary and refresh the browser. New columns/table require the migration before the affected routes are used. Do not run `migrate:fresh` on an existing store.

Settings → delivery/payment policies exposes the store pin, radius mode, QRIS basis points, e-wallet fixed fee and optional flat/percentage/capped payment promotion. Defaults preserve fixed shipping and zero new customer fees/promotions. Radius mode enforces <5km free, 5–<20km Rp10.000, >=20km unavailable. Missing coordinates block a radius quote. QRIS basis is net merchandise plus shipping, rounded to the nearest rupiah; wallet fee is flat. Promotions are explicitly configured store-wide discounts with an eligible method list and optional cap; merchant settlement deductions are not automatically passed to customers.

Manual channel choices/logos are instructions for store-confirmed payments, not proof of an activated bank/gateway API. Supported channel lists are in `config/payments.php`. A real provider contract must determine actual rates and settlement rules. Historical receipts use saved values. Roll back application code while retaining additive schema; migration down guards refuse to erase recorded checkout snapshots or saved layouts.

## Boundaries of this verification

No live gateway/refund transfer, external email, physical printer/scanner/camera/GPS, real iOS/Windows hardware or full screen-reader certification was performed. System font rendering can vary by OS. Browser emulation and CSS fallback coverage do not replace those checks. The full page/interaction run used Chrome. Installed WebKit passed14 secondary page/theme checks plus native modal and product/cart interactions. Firefox was NOT RUN: its installed executable timed out during launch with the available runner; no compatibility pass is claimed. These operational/provider/hardware checks remain required before claiming those particular production integrations work.

No P0/P1/P2 software defect from the executed checks remains open. Full production deployment and real-provider enablement are separate operations, not part of this implementation report.

## Local development schema upgrade

The configured local MySQL database had five pending migrations, including three existing migrations. A private pre-upgrade dump is retained at `/tmp/warbun-local-backup-20261102-075648/database.sql` (directory0700/file0600); copy it to durable private storage if needed because temporary storage can be cleared. No dump is committed.

An in-process upgrade helper initially reused a cached connection: additive schema changes reached the local database while migration records were written to the disposable copy. Repeated verification also produced duplicate generated address backfills. The helper was replaced with a separate migration subprocess against a restored copy. All12 protected tables matched their pre-upgrade record hashes, all eight affected schemas matched the verified copy, duplicate generated addresses were removed while retaining the original generated row, and the final address backfill matched the copy. Only then were the five local migration records reconciled. No existing financial, customer, product or user record was changed. The disposable real-data copy was deleted.

Local migration status is now fully applied. This development upgrade does not imply deployment or migration of another environment.
