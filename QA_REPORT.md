# Warbun — QA development

## Final design implementation P00–P11 · 2 November 2026

Implemented storefront/CMS design adaptations, cart/product/receipt overlays, authoritative fee/radius quotes and immutable snapshots, POS/monitor changes and a persistent shelf grid editor. Software QA completed:117 SQLite tests/962 assertions;41 MySQL financial/policy/layout tests/189 assertions at checkpoint;324 owner page/theme/viewport checks;36 customer and48 guest route checks; real pointer/keyboard browser interactions; MySQL last-item race and legacy migration fixture. Bugs found were corrected and retested. [Full results, screenshots, rollout instructions and external verification limits](docs/implementation/qa/FINAL-QA.md). Local development database backed up and additively upgraded;12 protected table hashes unchanged. No push/deploy.


## Final UI implementation baseline P00 · 1 November 2026

Verified on branch `feat/warbun-final-ui`:113 backend tests/929 assertions pass, production build passes,21 browser route/viewport combinations pass without document overflow or JavaScript page errors. Mobile drawer focus/Escape and customer authorization smoke pass. Fixed pre-existing formatting in AuditController and12 migrations; Pint and backend retest pass. User database untouched; browser fixtures used disposable SQLite. [Evidence, screenshots, gap inventory and limits](docs/implementation/qa/P00-BASELINE.md). P01 is next; this is not final UI completion.

## Figcraft shelf grid view/editor · 1 November 2026

Updated six existing Shelf entry frames in the same native file and added page 20 with separate view/edit, example move/resize, matching saved states, rack detail and unsaved confirmation. Preserved the prior numbered-product list for placement. Reviewed 54 main state renders, 24 dialogs and handoff; fixed footer spacing, initial save state, outlines, independent gesture geometry and product-assignment navigation. Text bounds checked across those 79 trees without accidental overflow; 216 example rack rectangles fit the grid without overlaps. Native drag uses predetermined frame transitions; free-form manipulation, editable inputs, persistence, collision detection and runtime keyboard/touch/accessibility remain unimplemented/untested. No Laravel/DB changes. [Frames, previews and implementation contract](design/figcraft/soft-neobrutalism/shelf-grid/README.md).

## Figcraft CMS status colors · 1 November 2026

Applied reusable semantic status badges to 50 CMS frames across Monitor, order dialogs, Dashboard, POS, Products and product details. Source-aligned light colors and corresponding dark variants distinguish order stage, payment, active product/shift, stock and unassigned location; text labels remain. Added 36 native components for 18 states and two reference boards. Reviewed all affected render sheets, fixed reference/waiting-label wrapping, and checked 16 badge color pairs (light 6.15–7.39:1; dark 8.19–10.37:1). Design-only; no Laravel/DB changes or runtime accessibility/compliance claim. [Mappings, evidence and limitations](design/figcraft/soft-neobrutalism/cms-operations/STATUS_COLORS.md).

## Figcraft CMS operational screens · 1 November 2026

Updated the same native document: web-matching navbar with moon/sun and icon account menu across the eight prior shells; Dashboard, POS, numbered product placement and oldest-first order monitor across desktop/tablet/mobile and light/dark (24 main screens). Added 64 operational dialog designs and structural comparisons/handoff. Used product-design-sop, antislop core/UI, Figcraft MCP and local Playwright inspection. Dashboard/Monitor were inspected in the running app; POS/Shelves were checked against source because their runtime pages lacked the expected main content.

Reviewed all main/dialog renders. Corrected independent POS scrolling, active shelf visibility, unit wrapping, excess modal height, delivery field padding/dark surfaces, and a mobile payment-warning title overlap. Text-parent bounds checked across 24 main and 64 dialog trees without accidental overflow at that pass; scroll clipping is intentional. Native links are design connections, not verified presenter playback. Form/filter/qty/scan/payment/shift/status mutations and runtime accessibility remain unimplemented and untested. No Laravel/DB changes or transactions. [Frame IDs, previews, source evidence and handoff limits](design/figcraft/soft-neobrutalism/cms-operations/README.md).

6 October address book: **113 tests / 929 assertions**, 10 address-book and 8 map browser scenario groups PASS. Multiple destinations, add/edit/delete/default, cart/POS selection and immutable snapshots verified, including delayed/failed network responses and six viewport sizes. GPS and tiles are mocked; MySQL operational migration and physical Safari/GPS untested. [Evidence](ADDRESS_BOOK_QA.md).

6 October delivery maps: **103 tests / 849 assertions**, nine browser groups and six viewport sizes PASS. Cart/cashier pins persist through login/checkout and appear in monitoring. Build/scoped checks pass. GPS/tiles are simulated; physical iPhone/Safari and this migration on MySQL remain untested. [Evidence](DELIVERY_MAPS_QA.md).

6 October focus/portrait update: 18 camera browser scenarios PASS. Selected-device reset ends previous tracks, tolerates unavailable/rejected focus controls and preserves mirror/input. Portrait video retains intrinsic ratio; close/reset/retry/return stay visible at narrow and short landscape viewports. Build, Blade compilation, scoped Pint and syntax checks PASS. iPhone hardware/Safari not tested. Preview 8080 restarted with a new isolated demo DB after old temporary files disappeared. Evidence: [CAMERA_BARCODE_QA.md](CAMERA_BARCODE_QA.md).

Latest CRUD action icons: [CRUD_ACTION_QA.md](CRUD_ACTION_QA.md). 15 browser scenarios PASS across ten indexes, two detail pages and shelf deletion, with rendered Font Awesome glyphs, translated accessible labels/tooltips, keyboard navigation, preserved confirmations and five viewport widths. Build, Blade compilation, scoped Pint, SVG validity and diff check PASS; backend tests not repeated for presentation-only changes.

Latest scan recovery update: 15 synthetic-camera scenarios PASS, including blue off-center barcode and ongoing-scan guidance after six seconds. Conditional focus request uses mocked camera capabilities. Build, JS syntax, scoped Pint and responsive checks PASS. The user's still screenshot remains undecodable with both production configurations; live device resolution/focus is not verified. Evidence: [CAMERA_BARCODE_QA.md](CAMERA_BARCODE_QA.md).

Latest mirror preview update: 13 camera browser scenarios PASS with keyboard toggle, persisted preference, camera switching and real Code 128/EAN-13 decoding while mirrored. Five-width layouts, build, JS syntax, scoped Pint and diff check PASS. Backend/checkout results below remain the previous baseline; no backend logic changed. Evidence: [CAMERA_BARCODE_QA.md](CAMERA_BARCODE_QA.md).

Latest camera/hardware scan: [CAMERA_BARCODE_QA.md](CAMERA_BARCODE_QA.md), [usage/preview](CAMERA_BARCODE.md). Full 97 tests / 799 assertions, 12 synthetic-camera browser scenarios, build and pinned dependency install PASS. Real Code 128/EAN-13 video decoding, product validation, one-scan POS, permission/stream teardown and five-width layouts verified. Physical device testing remains outside this evidence.

Latest shelf minimap, product detail/add dialog, placement management and sample seeder: [SHELF_QA.md](SHELF_QA.md), [workflow](SHELF_WORKFLOW.md). Full 97 tests / 799 assertions, build, scoped Pint, feature/category/barcode browser checks and five-width layout PASS on isolated SQLite. Existing MySQL migration/seeder not applied: local MySQL fails to start.

Latest MCP development setup: [MCP_SETUP.md](MCP_SETUP.md). Context7/GitHub/Boost read calls, Playwright tool discovery, project scoping, Composer validation and full 90 tests / 741 assertions pass. Five development dependencies added without changing existing versions.

Status-color update: [STATUS_COLOR_QA.md](STATUS_COLOR_QA.md). Shared semantic badges, monitoring accents, 16 targeted tests / 222 assertions, production build, AA contrast and five-width browser checks pass.

Order monitoring, cashier delivery and debt verification: [ORDER_MONITOR_QA.md](ORDER_MONITOR_QA.md). 90 tests / 741 assertions, production build, seven browser scenarios, responsive checks and additive local MySQL migration rehearsal pass. Pint changes pass; unrelated existing formatting findings are recorded in the report.

Earlier cashier category filter: [POS_CATEGORY_QA.md](POS_CATEGORY_QA.md). 80 tests / 634 assertions, build and category/browser checks pass; category buttons remain a single horizontal scrolling row.

Latest barcode POS and receipt flow: [BARCODE_QA_REPORT.md](BARCODE_QA_REPORT.md). 78 tests / 616 assertions, production build, 13 operational browser scenarios and 7 barcode/printing scenarios pass on isolated QA data. Physical scanner/printer testing was not performed.

Latest navigation-icon change: [ICON_QA_REPORT.md](ICON_QA_REPORT.md), local SVG rendering, responsive layout, destinations, locale and logout verified.

Latest mobile catalog display change: [CATALOG_VIEW_QA.md](CATALOG_VIEW_QA.md), grid/list behavior checked on the existing server at ten viewport widths.

Tanggal: 1 Oktober 2026. Hasil: **PASS untuk implementasi development yang diuji**. Pengujian memakai database sementara; `.env` dan database operasional existing tidak diubah. Tidak ada deployment.

Pembaruan auth, seeder dan shopping: [SHOPPING_QA_REPORT.md](SHOPPING_QA_REPORT.md). Verifikasi terbaru: 72 tes / 562 assertions pada SQLite, build, 13 skenario operasional, 10 skenario UI dan 9 skenario shopping. Bukti MySQL di bawah adalah baseline sebelumnya, bukan pengujian ulang perubahan terbaru.

Pembaruan UI (1 Oktober 2026): lihat [UI_QA_REPORT.md](UI_QA_REPORT.md) untuk screenshot desktop/mobile, bukti kontras, penerapan kedua skill desain, dan delivery gate anti-slop. Regression test 64/312 serta 13 skenario browser operasional telah lulus lagi setelah perubahan UI.

## Hasil verifikasi

| Pemeriksaan | Hasil | Bukti |
| --- | --- | --- |
| PHPUnit, SQLite in-memory | 64 tes lulus, 312 assertions | [phpunit-sqlite.json](docs/qa/phpunit-sqlite.json) |
| PHPUnit, MySQL 9.4 dengan strict SQL mode | 64 tes lulus, 312 assertions | [phpunit-mysql.json](docs/qa/phpunit-mysql.json) |
| Penjualan bersamaan, dua kasir / dua proses PHP | Satu berhasil, satu ditolak; stok 0; satu sale, payment, dan movement | [concurrency-results.json](docs/qa/concurrency-results.json) |
| Migrasi lengkap, rollback/up migrasi tambahan, seed | Lulus pada database MySQL khusus QA | [migration-results.json](docs/qa/migration-results.json) |
| Backfill utang legacy dan pelunasan setelah migrasi | Debit/credit asli tetap, principal tersisa sesuai, pelunasan lanjut konsisten | [legacy-migration-results.json](docs/qa/legacy-migration-results.json) |
| Chrome / Playwright | 13 kelompok skenario lulus, tanpa JavaScript runtime error | [browser-results.json](docs/qa/browser-results.json) |
| Render halaman ID dan EN | 46 halaman pada tiap locale lulus, ditambah pemeriksaan katalog | PageSmokeTest dalam PHPUnit |
| Vite production build | Lulus | `npm run build`, Vite 8.3.1 |
| Blade compile | Lulus | `php artisan view:cache` dengan environment QA |
| PHP syntax | Lulus | [syntax-results.json](docs/qa/syntax-results.json) |
| Laravel Pint pada PHP yang berubah | Lulus | [style-results.json](docs/qa/style-results.json) |
| Git whitespace check | Lulus | `git diff --check` |

Baseline repository: 25 tes, 6 lulus, 17 gagal, 2 error. Perbaikan memulihkan auth dan melengkapi aturan operasional; tes baseline yang relevan tetap dijalankan bersama regresi baru.

## Perilaku yang diperiksa

- POS memakai harga server, menggabungkan baris produk yang sama, menolak oversell dan override tidak berizin, serta menjaga sale/item/payment/stock/debt/audit dalam transaksi yang sama.
- Retry dengan request key tidak menggandakan transaksi atau mengurangi stok lagi. Kasir lain yang berlomba memperebutkan stok terakhir diuji dengan proses PHP nyata pada MySQL.
- Utang memerlukan pelanggan terdaftar dan eligible. Limit kredit, pembayaran parsial/penuh, FIFO, due today, overdue, overpayment, write-off, dan saldo sale/customer/account diperiksa.
- Order memisahkan lifecycle dan payment status, membatasi akses customer ke pesanannya sendiri, mereservasi stok sekali, mengembalikan stok saat pembatalan yang sah, dan menolak penyelesaian sebelum lunas.
- Pembayaran order parsial dibatasi saldo tersisa. Webhook memeriksa signature, timestamp, amount, status, dan replay; callback gagal tidak mengurangi stok kembali.
- Retur membatasi kuantitas, mempertahankan pembulatan diskon, menangani sisa barang setelah retur parsial, mengalokasikan receipt cash/noncash dan pembayaran utang FIFO, serta merekonsiliasi kas shift.
- Opname hanya memindahkan stok sekali dan menolak expected stock yang stale.
- Customer tidak bisa mengakses backoffice. Kasir tidak bisa mengubah settings, staf, refund, atau stok sensitif. Pengelola staf yang diberi izin terbatas tidak dapat memberikan role dengan permissions yang tidak dimilikinya. Shift history kasir hanya miliknya.
- Akun dengan aktivitas operasional dinonaktifkan ketika dihapus, sehingga riwayat tetap ada. Produk yang diarsipkan tetap muncul pada struk dan dapat diretur melalui ledger stok.
- Profil memperbarui identitas pelanggan tanpa membolehkan perubahan credit limit lewat mass assignment. Login telepon dan penolakan akun nonaktif diperiksa.
- Laporan menghitung gross, discount, refund, net, penerimaan per metode, aging piutang, stok, dan aktivitas staf. Uang menggunakan integer minor units; tampilan mempertahankan pecahan dan mengikuti locale.

## QA browser

Chrome headless dengan viewport desktop 1440×900 dan mobile 375×812. Pengujian meliputi login, pilihan bahasa, navigasi operasional, settings persist, pembuatan staf, submit/approve opname, keyboard/Escape dialog inventory, pencarian POS, keranjang, pembayaran, struk, refund, penutupan shift, checkout customer, dan penolakan akses customer ke data staf/keuangan. Halaman yang diperiksa tidak mempunyai overflow horizontal pada viewport tersebut; tabel lebar memiliki scroll di dalam container.

Screenshot akhir:

- [Struk desktop](docs/qa/screenshots/receipt-desktop.png)
- [POS mobile](docs/qa/screenshots/pos-mobile.png)
- [Order customer mobile](docs/qa/screenshots/customer-order-mobile.png)

## Batas verifikasi

- Provider payment live belum dipilih/dikonfigurasi. Adapter signed-webhook adalah kontrak integrasi yang sudah diuji; pembuatan sesi checkout dan transfer/refund lewat API provider belum dijalankan. Pencatatan refund toko tidak mengirim dana otomatis.
- Pengiriman email sungguhan belum diuji. Pengujian auth/reset memakai mail array; event notifications operasional tersimpan lewat kanal database.
- Pengujian konkurensi membuktikan skenario stok terakhir dengan dua kasir. Load test besar, gangguan jaringan berkepanjangan, dan seluruh kombinasi race operasional belum diklaim teruji.
- Migrasi diuji pada fixture fresh dan contoh ledger legacy. Isi database existing/VPS tidak diaudit atau dimigrasikan langsung. Data identitas/shift duplikat dan bukti pembayaran lama yang tidak lengkap perlu direkonsiliasi pada salinan database sebelum penggunaan operasional.
- Pajak, multi-outlet, promosi bertingkat, dan campuran banyak metode tender dalam satu POS belum diaktifkan. POS mendukung satu metode pembayaran per transaksi serta deposit tunai + sisa utang; pembayaran order dapat dicatat bertahap dengan metode berbeda.

Panduan menjalankan project dan mengulangi QA ada di [README.md](README.md). Daftar implementasi ada di [tasklist.md](tasklist.md).

## Figcraft SOP pilot · 30 October 2026

Applied the product-design SOP to customer checkout/payment/confirmation in the same native design file. Corrected implicit payment selection, category open-state behavior, channel destinations and confirmation metadata; added scoped Auto Layout/paint styles and reviewed renders. [Findings, evidence and limitations](design/figcraft/soft-neobrutalism/SOP_PILOT_QA.md). This is design-only work; no application/database changes or runtime accessibility claims.

## Figcraft typography · 31 October 2026

Aligned customer design typography with the local web: Georgia headings and system UI body, controls and prices. Adjusted hero spacing, emphasized action labels, updated foundation examples and repeated text styles, and saved the same native design file. Reviewed 44 main screen renders and 12 representative overlays across mobile/desktop and light/dark. [Typography decisions, evidence and limits](design/figcraft/soft-neobrutalism/TYPOGRAPHY_QA.md). No Laravel runtime changes.

## Figcraft orders and receipts · 31 October 2026

Reworked customer order history into product cards with per-order receipt actions and completed-order reorder review. Added tablet designs and receipt variants for percentage/fixed payment promotions, with separate product discounts, customer service fee, shipping and final total. Reviewed 30 light/dark mobile/tablet/desktop frame renders; corrected currency alignment, total row sizing and tablet navigation. Final text bounds check found no parent overflow and demo receipt totals reconcile. [Frame IDs, calculations, evidence and implementation limits](design/figcraft/soft-neobrutalism/orders/README.md). Design/prototype changes only; live cart/payment logic and runtime accessibility remain untested.

## Figcraft payment fees and delivery radius · 1 November 2026

Added QRIS 0.7% and e-wallet Rp2.000 example fee information per payment type/channel, synchronized fee totals and new receipt previews, and free/paid/outside/unknown/pickup delivery designs. Radius rules: <5 km free, 5–<20 km Rp10.000, ≥20 km unavailable; pickup free. Mobile/tablet/desktop and light/dark layouts included. Inspected 252 frames and checked all 192 payment-frame destinations and text bounds; no missing target roots or accidental payment text overflow after fixing tablet symbols. Mobile scroll clipping is intentional. Generated 96 PNG examples and refreshed main PDF/payment exports; reviewed distinct fee/radius layouts and corrected dark wordmark contrast. [Rules, examples, scope and runtime limitations](design/figcraft/soft-neobrutalism/fees-radius/README.md). Design/prototype only: actual coordinate lookup, backend fee/shipping computation, provider configuration and runtime accessibility were not implemented or tested.

## Figcraft CMS workspace · 1 November 2026

Revamped the CMS layout in the same native file, with desktop expanded/compact sidebar, tablet drawer/table scroll, mobile product cards, and light/dark variants. Included all 23 source navigation entries plus five report subpages and the Debt Dashboard (29 destinations), with profile in the account menu. Representative product forms, validation, detail/archive/filter/account overlays and reusable control states accompany the shell; other page contents are out of scope. Rendered/exported 45 targets, reviewed the eight main layouts and overlay/component patterns, and corrected icon alignment, pagination wrapping, select/upload affordances, input contrast and semantic feedback. Final text-bounds and native destination checks found no accidental overflow or invalid targets. [Menu inventory, frame IDs, preview files and prototype limitations](design/figcraft/soft-neobrutalism/cms/README.md). Local port 8000 was unavailable, so navigation coverage was checked against source rather than authenticated runtime. No Laravel/database implementation changes; actual forms, CRUD/search/filter and accessibility runtime QA remain pending.
