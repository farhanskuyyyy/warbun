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

## Barcode POS and receipts

- [x] Preserve unique barcode strings and leading zeroes; improve product form/detail guidance
- [x] Add permission-protected exact barcode lookup with missing/inactive/stock feedback
- [x] Support keyboard scanner Enter, sequential rapid scans and quantity/stock limits
- [x] Keep manual search and authoritative transactional checkout with retry idempotency
- [x] Add change preview, optional automatic print dialog, 58/80mm receipts and manual reprint
- [x] Pass 78 tests / 616 assertions, build, Pint, operational regression and barcode browser QA
- [x] Record sample receipt PDFs, responsive screenshots and scanner/printer setup guide

## Order monitoring and cashier delivery

- [x] Unified online and POS delivery queue with valid permission-aware actions
- [x] Direct/delivery POS, customer modal, optional login and address snapshot
- [x] Server-calculated shipping, eligible debt preview, deposit and repayment verification
- [x] Audit fulfillment without duplicate stock, revenue or debt; refund regression
- [x] 90 tests / 741 assertions, build, scoped Pint, seven browser scenarios and responsive QA
- [x] Rehearse additive migration on temporary MySQL copy, apply locally and record ORDER_MONITOR_QA.md

## Etalase dan penataan produk

- [x] Migrasi tambahan, relasi lokasi utama, permission, locking dan audit
- [x] Minimap etalase, modal info/tambah produk, pagination POS
- [x] Menu penataan dan lokasi pengambilan pada monitoring/detail pesanan
- [x] Seeder contoh etalase, lokasi kategori, idempotence dan perlindungan penataan manual
- [x] Regression backend, QA browser mobile/desktop, build, Pint dan delivery gate
- [ ] Terapkan migrasi/seeder khusus di MySQL existing setelah service lokal dapat berjalan; bukti keterbatasan ada di SHELF_WORKFLOW.md

## Kamera barcode

- [x] Pilihan hardware/kamera pada POS dan form tambah/edit produk
- [x] Toggle mirror preview tersimpan, keyboard/device/retry/navigation dan deteksi otomatis saat mirror diuji
- [x] Pencarian baris diperluas, request fokus kondisional dan guidance scan belum berhasil; 15 skenario kamera dan screenshot pengguna diuji dengan batas hasil terdokumentasi
- [x] Decoder lokal, pemilihan kamera, satu hasil, cleanup permission/modal/visibility
- [x] Antrean scan kasir existing, barcode input tanpa auto-save dan validation unique
- [x] 12 skenario kamera simulasi, real decoding Code 128/EAN-13, responsive dan regression backend
- [x] Preview 8080 dengan SQLite demo terpisah dari database QA dan MySQL existing
- [x] Panduan secure context, batas device testing dan delivery gate

## Integration scope

Development QA is complete for the implemented scope; evidence is in [QA_REPORT.md](QA_REPORT.md). Live payment-provider session/refund integration and real email delivery require the selected provider/configuration. Deployment and DevOps are outside this task. Existing operational data must be rehearsed and reconciled on a copy before applying the new migration.
