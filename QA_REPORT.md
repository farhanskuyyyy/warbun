# Warbun — QA development

Latest order monitoring, cashier delivery and debt verification: [ORDER_MONITOR_QA.md](ORDER_MONITOR_QA.md). 90 tests / 741 assertions, production build, seven browser scenarios, responsive checks and additive local MySQL migration rehearsal pass. Pint changes pass; unrelated existing formatting findings are recorded in the report.

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
