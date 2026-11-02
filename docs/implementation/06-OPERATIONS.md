# P08–P10: operasional dan grid rak

[Indeks](README.md) · Implemented; [actual QA scope and external limits](qa/FINAL-QA.md) · Terapkan transaksi/permission existing dan DoD setiap fase.

## P08 — POS, shift, receipt, sales history

Dependency P04/P07. Source `pos/index`, `pos/history`, `pos/receipt`, `resources/js/pos.js`, barcode-camera, SaleService/ShiftService. Acuan POS frames 25060–25984 dan page 24045 dialogs.

- [x] Desktop/tablet katalog dan cart terpisah, katalog scroll sendiri dan total/checkout terlihat; mobile cart dock membuka sheet. Tanpa memotong tombol dengan viewport height fixed.
- [x] Search nama/SKU/barcode, category filter, camera + manual fallback, scan keyboard Enter dan error tidak ditemukan. Hapus stale search response; scan tidak masuk ke field qty secara tidak sengaja.
- [x] Qty/remove/discount sesuai permission, out-of-stock dan changed price, customer selection/create/address book, pickup/delivery sesuai policy P04. Jangan mengubah business rule POS delivery tanpa memeriksa service existing.
- [x] Open/close shift, cash received/change, noncash/manual channel, debt eligibility dan limit, payment breakdown, submit progress/retry. User tidak boleh menggunakan shift orang lain.
- [x] Sale mutation idempotent; klik ganda, timeout atau retry menghasilkan satu sale dan satu mutation stok/ledger/audit. Pertahankan lock order existing.
- [x] Receipt nyata setelah server success, print 58/80mm sesuai fungsi existing, status/unpaid jika relevan; history filter/detail dan authorization tetap ada. PDF/print tidak ikut sidebar/nav/theme gelap.

QA: BarcodePosTest/OperationsTest; scripts barcode/camera/POS categories setelah audit environment; camera denied/manual fallback; qty last stock concurrent pada MySQL; credit limit; shift ownership; wrong tender; double submit; receipt totals/print preview. Kamera fisik/printer yang tidak tersedia dicatat NOT RUN, bukan PASS.

Exit: kasir dapat menyelesaikan cash/noncash/debt yang didukung tanpa kehilangan state dan stok/uang akurat. Commit POS shell, checkout/shift, receipt/history sebagai fitur utuh jika besar.

## P09 — Monitor, transaksi, piutang, laporan dan audit

Dependency P08. Source OrderMonitorController, OrderController, PaymentController, DebtController, ReportController, AuditController, OperationsController + services.

- [x] Monitor oldest-first queue, filter/status/fulfillment/search, selected detail panel desktop dan modal mobile/tablet. Poll/refresh sesuai existing tanpa kehilangan pilihan, menggandakan listener atau reset scroll setiap tick.
- [x] Order status dan payment status terpisah, age/fulfillment/address/rack/item tampil. Stale transition menampilkan state terbaru. Ready tetapi unpaid tidak boleh complete online order; arahkan ke payment yang authorized.
- [x] Detail delivery/map links menggunakan snapshot, transisi delivery sesuai aturan; cancel/complete memerlukan server validation dan feedback persisten. Order/paid cancellation mengikuti refund flow existing.
- [x] Orders index/show dan manual payment actions, Payments index/create/show/confirm/refund, refunds list/form: breakdown uang, detail item, status, confirm destructive, reason, retry dan ownership.
- [x] Debt index/create/dashboard/payment/correction: saldo/outstanding, due/aging, customer info, payment allocation dan audit. Jangan memformat debt sebagai pendapatan lunas.
- [x] Shifts list/details existing, report index + sales/inventory/payments/staff/debt dengan filter/periode/export yang sudah ada. Label definisi total mengikuti ARCHITECTURE/README; tidak mengubah formula agar mirip angka desain.
- [x] Audit index/show: actor/action/time/entity, filter dan detail aman; tidak mengekspos secret. Read-only log bukan form edit.
- [x] Badge semantic di semua layar ini, dashboard, produk, receipt/history terkait; unknown/partial/refunded/expired state terlihat dengan label.

QA: OrderMonitoringTest/OperationsTest, script order monitor/status colors; oldest-first setelah refresh, invalid transitions, partial payment, refund parsial/berulang, cancelled stock release sekali, debt overpayment/limit, shift cash reconciliation dan report ties-to-ledger. Uji role customer/cashier/manager/owner dan URL langsung, bukan hanya visibility tombol.

Exit: monitoring dan semua child transaction pages berfungsi; total laporan konsisten dengan sumber transaksi. Commit per monitor/payments/debt/reports jika dibutuhkan, tidak satu commit raksasa.

## P10 — Penataan produk grid yang benar-benar interaktif

Dependency P07. Acuan [grid handoff](../../design/figcraft/soft-neobrutalism/shelf-grid/README.md), page 32521, board 43014. Source ShelfController, shelves/index, ShelfTest dan models terkait.

### Data dan save

- [x] Audit schema shelf/location existing. Desain schema additive untuk layout dimensions, revision dan setiap `shelf_id,x,y,width,height`; per-store scope mengikuti arsitektur aktual, jangan mengarang multi-tenant baru.
- [x] Endpoint read/update dengan authorization `products.update` untuk edit, validation integer/nonnegative/positive sizes, grid bounds, missing/deleted shelf dan overlap. Pertahankan ID rak/lokasi produk, tidak mengubah stok.
- [x] Save atomic, optimistic revision/concurrency conflict, audit, duplicate submission protection. Gagal save mempertahankan draft dan layout tersimpan terakhir. Test upgrade/rollback pada legacy fixture.

### View/edit

- [x] View default: grid saved layout, klik rak melihat produk, empty/unassigned states, Atur produk di rak membuka assignment existing. View mode tidak draggable.
- [x] Edit mode: select rak, pointer/touch drag snap-to-grid, resize handles, inspector numeric X/Y/width/height, keyboard alternative, valid/invalid preview. Tidak cukup mengganti screenshot saat onDrag.
- [x] Contoh 16×12 logical cells, desktop/tablet 40px, mobile20px dapat diadaptasi responsif; cells bukan meter. Jangan simpan koordinat pixel viewport. Rak contoh tidak otomatis dibuat di database pengguna.
- [x] Save disabled sebelum ada perubahan, saving indicator, cancel/discard confirmation, unsaved navigation guard. Setelah save + reload geometri sama; re-edit berulang dan semua rak bekerja, bukan hanya rak03 demo.
- [x] Collision/out-of-grid ditolak; Escape cancel gesture, touch-scroll tidak bentrok, keyboard move/resize/numeric access, announce selected rack dan validation. Jaga target sentuh/readability saat grid padat.
- [x] Nonempty rack deletion/placement tetap mengikuti rule existing; remove/rename rak tidak merusak layout. Tidak menambah zoom/pan kompleks kecuali konten nyata membutuhkannya.

QA: ShelfTest diperluas, geometry unit tests + feature persistence/auth/conflict, Playwright drag/resize numeric/keyboard/touch, viewport resize, save/reload, concurrent editor, failed network/retry, overlap/bounds, cancelled edits, deleted shelf dan product-location consistency.

Exit: arbitrary supported rack dapat diatur, persist dan dibuka ulang; prototype finite-state tidak dijadikan implementasi. Commit terpisah schema/API dan editor/UI bila masing-masing aman diuji.
