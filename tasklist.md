# Warbun development

Source requirement: [PRD.md](PRD.md). Implementation decisions: [ARCHITECTURE.md](ARCHITECTURE.md). Legacy unchecked completion claims preserved in tasklist-legacy.md.

- [x] Inspect existing implementation and run baseline QA
- [x] Write architecture, ERD, business flows, permission matrix and migration/test plan
- [x] Restore auth and enforce granular backend authorization
- [x] Transactional stock/POS/debt/payment/shifts with exact money and retries
- [x] Customer identity, online orders, lifecycle, payments and verified gateway boundary
- [x] Refunds, debt reversals/allocations and stock opname
- [x] Staff/users/roles/settings/reports and audit
- [x] Indonesian/English localization and usable responsive screens
- [x] Automated regression QA, migrations/seed, build and browser checks
- [x] Record QA evidence and remaining environment limitations

## UI refinement

- [x] Apply anti-slop and UI UX Pro Max to the existing Warbun identity
- [x] Group CMS navigation and add master-data disclosure, active routes and mobile drawer
- [x] Redesign customer navbar, account menu, footer and actual-catalog landing
- [x] Improve dashboard hierarchy, catalog placeholders, auth/profile and cart feedback
- [x] Run responsive/keyboard/contrast checks and operational regression
- [x] Record screenshots and the delivery gate in UI_QA_REPORT.md

## Auth, seed data and shopping UX

- [x] Match all six auth screens to the storefront direction and localize form feedback
- [x] Research and seed 53 SKU across 9 categories and all 31 business tables with explicit demo assumptions
- [x] Verify seeder idempotence, production guard and stock/debt/refund/shift consistency
- [x] Reflow shop and cart from 320px and simplify filters, quantities and cart navigation
- [x] Validate current prices/stock with a read-only quote before checkout
- [x] Preserve checkout context through sign-in and new-account registration
- [x] Refine order details, padding, payment guidance and primary/secondary button placement
- [x] Preserve a new cart when viewing earlier orders and improve mobile order history
- [x] Pass 72 tests / 562 assertions, build, formatting and three browser runners
- [x] Check the existing 8080 server read-only and record the delivery gate in SHOPPING_QA_REPORT.md

## Integration boundary

Development QA is complete for the implemented scope; evidence is in [QA_REPORT.md](QA_REPORT.md). Live payment-provider session/refund integration and real email delivery require the selected provider/configuration. Deployment and DevOps are outside this task. Existing operational data must be rehearsed and reconciled on a copy before applying the new migration.
