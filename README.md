# Warbun

Warbun is a store management application for Indonesian small retailers: a customer storefront plus a staff CMS/POS. Customers browse and order products for pickup or delivery; cashiers handle sales and shifts; managers and owners manage stock, payments, credit, refunds, reports and staff access.

This repository contains an existing Laravel application, a final Figcraft design, and a phased implementation handoff. **The design and its prototype states are not evidence that the new UI or backend behavior has been implemented.**

## Start here for a new agent or developer

| Need | Read |
| --- | --- |
| Agent instructions and safety rules | [AGENTS.md](AGENTS.md); [agent.md](agent.md) points to the same instructions |
| Product requirements and transaction rules | [PRD.md](PRD.md), [ARCHITECTURE.md](ARCHITECTURE.md) |
| Implement the final design across all pages | [Implementation plan, P00–P11](docs/implementation/README.md) |
| Resume work across agents/sessions | [Current phase status and handoff](docs/implementation/STATUS.md) |
| Check every route, child screen and view | [Page coverage](docs/implementation/02-PAGE-COVERAGE.md) |
| Open the canonical design | [final.figcraft](design/figcraft/soft-neobrutalism/final.figcraft), [frame map](docs/implementation/01-DESIGN-MAP.md), [selected previews](design/figcraft/PREVIEWS.md) |
| Verify and fix the completed implementation | [QA and repair plan](docs/implementation/07-QA-AND-RELEASE.md) |

The final-design implementation and software QA phases P00–P11 were completed on 2 November 2026. See [the final QA report](docs/implementation/qa/FINAL-QA.md) for tested behavior, evidence, configuration and the remaining external provider/hardware checks. STATUS is the source for future work; older completed checklists are not a substitute for current verification.

The implementation covers customer landing/auth/catalog/cart/checkout/orders and all CMS pages, including POS, monitoring and an interactive shelf-layout editor. Dedicated native frames guide major screens; other CRUD pages reuse final components with their existing functionality. Commit per verified phase or feature, then complete functional, visual and responsive QA and fix bugs directly. Full instructions are portable Markdown files and do not require this conversation or a specific coding agent.

## Stack and repository map

Laravel/PHP with Blade server rendering, Alpine.js interactions, Tailwind styling and Vite assets; MySQL is the primary database. Permissions use Spatie. Use manifest and lockfile versions; keep the existing stack rather than rebuilding as a SPA.

| Path | Purpose |
| --- | --- |
| `routes/web.php`, `routes/auth.php` | Storefront, CMS, authentication and action endpoints |
| `app/Http/Controllers/`, `app/Services/` | HTTP boundaries and transactional business workflows |
| `app/Models/`, `database/migrations/`, `database/seeders/` | Data model, schema changes and development fixtures |
| `app/Payments/` | Payment adapter contracts |
| `resources/views/` | Blade pages, shared layouts and components |
| `resources/js/`, `resources/css/` | Cart/POS/address/scanning behavior and styles |
| `tests/`, `scripts/qa-*` | Automated regression and browser/concurrency checks |
| `design/figcraft/` | Native design, handoff notes and curated previews |
| `docs/implementation/` | Phase tasks, coverage, execution status and final QA |

Main entry routes are `/`, `/shop`, `/cart`, `/my-orders` and `/my-addresses` for customers; `/dashboard`, `/pos`, `/products`, `/shelves` and `/orders/monitor` for staff. Access depends on authentication, ownership and permissions. Use `php artisan route:list` for the complete current list.

The remaining sections document local setup and existing workflows in Indonesian. Read the payment and QA limitations before testing transactions. The final design's fee/radius examples and grid gestures require implementation and verification; they must not be treated as live provider capabilities or persisted application behavior.

Aplikasi operasional warung: katalog pelanggan, POS, pesanan online, stok, piutang, pembayaran, shift kasir, retur, laporan, dan audit. Laravel + Blade + Tailwind + Spatie Permission; database utama MySQL.

## Dokumen project

- [ADDRESS_BOOK.md](ADDRESS_BOOK.md): beberapa alamat pelanggan, pilihan checkout/POS dan [QA](ADDRESS_BOOK_QA.md).

- [DELIVERY_MAPS.md](DELIVERY_MAPS.md): titik alamat cart/kasir, konfigurasi provider dan [QA](DELIVERY_MAPS_QA.md).

- [MCP_SETUP.md](MCP_SETUP.md): konfigurasi Context7, GitHub, Playwright dan Laravel Boost beserta QA.

- [ORDER_MONITORING.md](ORDER_MONITORING.md): alur monitoring, pengantaran dan utang kasir; [QA](ORDER_MONITOR_QA.md).

- [PRD.md](PRD.md): brief asli dari warbun.md.
- [ARCHITECTURE.md](ARCHITECTURE.md): arsitektur, ERD, alur transaksi, permissions, dan keputusan implementasi.
- [DESIGN_SYSTEM.md](DESIGN_SYSTEM.md): pedoman tampilan yang mengikuti project existing.
- [tasklist.md](tasklist.md): pekerjaan development dan statusnya.
- [QA_REPORT.md](QA_REPORT.md): hasil pengujian dan batas verifikasi.
- [SHOPPING_QA_REPORT.md](SHOPPING_QA_REPORT.md): QA auth, mobile shopping, checkout dan detail pesanan terbaru.
- [SEED_DATA.md](SEED_DATA.md): riset katalog, asumsi data demo dan cakupan seeder.

## Development lokal

Lingkungan QA menggunakan PHP 8.3.26, Composer 2.8.11, Node 22.19, MySQL 9.4, dan Chrome. Gunakan versi Node yang memenuhi engines dependencies di package-lock.json.

```sh
composer install
npm ci
npm run build
```

Pertahankan `.env` yang sudah ada. Untuk checkout baru, buat `.env` dari `.env.example`, atur koneksi database development sendiri, lalu jalankan `php artisan key:generate`.

```sh
php artisan migrate
php artisan serve
```

Migrasi baru bersifat tambahan. Jalankan migrasi pada salinan database existing terlebih dahulu. Migrasi menolak identitas pelanggan atau shift aktif yang duplikat sebelum perubahan schema dimulai; data tersebut perlu direkonsiliasi, bukan dihapus otomatis. Riwayat transaksi lama yang kurang lengkap memerlukan pemeriksaan terhadap bukti pembayaran dan shift sebelum dipakai untuk rekonsiliasi.

Untuk database development terpisah, isi 53 SKU dan dataset seluruh tabel aplikasi. Dasar riset, asumsi harga dan cakupan tabel: [SEED_DATA.md](SEED_DATA.md).

```sh
php artisan db:seed
```

Akun demo: `owner@warbun.local`, `admin@warbun.local`, `manager@warbun.local`, `kasir@warbun.local`, `customer@warbun.local`; password `password`. Dataset lengkap hanya menerima APP_ENV=local/testing dan aman diulang setelah sukses. Katalog saja: `php artisan db:seed --class=WarungCatalogSeeder`. Jangan gunakan akun demo sebagai akun toko sungguhan.

Gambar produk memakai disk public Laravel. Jika link storage belum tersedia, jalankan `php artisan storage:link` di checkout development.

## Cara memakai alur utama

Alur scan kasir dan cetak struk 58/80 mm: [BARCODE_WORKFLOW.md](BARCODE_WORKFLOW.md). Scanner yang didukung mengirim barcode sebagai keyboard dengan suffix Enter.

1. Kasir membuka shift di POS, mencari nama/SKU/barcode, mengisi keranjang dan pembayaran, lalu mendapat struk.
2. Pembelian utang harus memakai pelanggan yang terhubung ke akun terdaftar, aktif, eligible, dan mempunyai limit kredit.
3. Pelunasan dapat dicatat per penjualan lewat Payments atau per pelanggan lewat Debt. Pembayaran pelanggan dialokasikan ke utang terbuka menurut due date dan ID.
4. Pelanggan masuk ke Shop, checkout pickup/delivery, lalu melihat status pesanan miliknya. Checkout mengurangi stok sebagai reservasi.
5. Staf memproses pending → confirmed → preparing → ready → completed. Selesai memerlukan pelunasan. Pembatalan hanya untuk pesanan tanpa penerimaan uang; transaksi berbayar menggunakan retur.
6. Retur memilih sumber transaksi, ID produk, jumlah, dan alasan. Sistem membatasi jumlah yang belum diretur, membalik utang terlebih dahulu, lalu mengalokasikan pengembalian uang ke receipt asal. Refund tunai memerlukan shift aktif.
7. Stock Opname menyimpan expected stock ketika dibuat. Approve menolak hitungan jika stok sudah berubah.
8. Kasir menutup shift dengan uang aktual. Sistem mencatat expected cash dan variance; kasir hanya melihat riwayat shift sendiri, manajer/owner dapat melihat seluruh staf.

Settings menyediakan nama toko, termin utang, ongkir, kontak toko opsional dan instruksi pembayaran. Keranjang memeriksa harga/stok terkini sebelum checkout, menampilkan ongkir sesuai metode penerimaan, dan menyimpan pilihan pelanggan ketika perlu login atau daftar. Profil pelanggan menyimpan identitas, telepon, alamat, dan riwayat pembelian. ID/EN tersedia melalui pemilih bahasa; default bahasa Indonesia dan timezone Asia/Jakarta.

## Definisi laporan

Sales mencakup POS finalized dan order yang sudah diterima toko (confirmed/preparing/ready/completed), termasuk transaksi yang kemudian direfund. Gross memakai subtotal, net memakai total dikurangi retur pada periode retur; ongkir termasuk total order. Penjualan mengikuti tanggal pembuatan transaksi. Kas yang diterima diperiksa melalui Payments dan Shifts, sehingga pesanan yang belum lunas tidak dianggap sebagai penerimaan uang.

Payments mengelompokkan penerimaan dan pengembalian dana menurut metode. Debt menyajikan saldo, pembayaran, overdue, dan aging dari remaining principal. Staff memisahkan nilai penjualan, penerimaan, utang baru, refund, dan variance shift. Inventory menampilkan stok dan unit POS terjual setelah retur.

## Payment gateway

Default `PAYMENT_GATEWAY=manual`: cashier memverifikasi cash/transfer/e-wallet/QR. Gateway online memakai interface `App\Payments\PaymentGateway`. Adapter `signed-webhook` menyediakan kontrak pengujian yang terverifikasi, bukan integrasi Midtrans/Xendit yang sudah aktif.

Jika memakai adapter tersebut, atur `PAYMENT_WEBHOOK_SECRET` minimal 32 karakter lewat konfigurasi lingkungan. POST `/payments/webhook` menerima JSON `payment_number`, `amount`, `status` (paid/failed/cancelled), dan `reference`; header `X-Payment-Timestamp` berisi Unix timestamp, header `X-Payment-Signature` adalah HMAC-SHA256 atas `timestamp.raw_body`. Toleransi waktu 300 detik. Amount harus sama dengan payment intent; replay identik tidak menulis transaksi ulang. Callback berbeda untuk payment yang finalized ditolak.

Provider live memerlukan adapter sesuai dokumentasi provider, pembuatan payment session, serta refund eksternal. Refund di aplikasi saat ini mencatat pengembalian yang dilakukan toko; aplikasi belum mengirim uang lewat API bank/gateway. Email eksternal mengikuti MAIL_* project dan belum diuji dengan provider sungguhan. Database notifications menyimpan event operasional untuk integrasi kanal lanjutan.

## QA

Ikon navigasi: Font Awesome Free 6.7.2 Solid, disimpan lokal sebagai SVG. Sumber: [Font Awesome](https://github.com/FortAwesome/Font-Awesome/tree/6.x/svgs/solid). Ikon berlisensi [CC BY 4.0](https://fontawesome.com/license/free); atribusi dan lisensi lengkap di `public/icons/LICENSE-fontawesome.txt`. Path asli dibungkus dalam symbol untuk dipakai ulang. QA: [ICON_QA_REPORT.md](ICON_QA_REPORT.md).

```sh
php vendor/bin/phpunit --no-progress
npm run build
```

phpunit.xml memakai SQLite in-memory, cache/session array, dan mail array. Untuk MySQL, override DB_CONNECTION/DB_DATABASE/DB_USERNAME/DB_PASSWORD menggunakan database QA kosong yang khusus dibuat; RefreshDatabase menghapus schema database target. Pengujian konkurensi `scripts/qa-concurrency.php` menolak database yang tidak bernama `warbun_qa_*`.

Browser runner: `scripts/qa-browser.cjs` menggunakan Playwright + Chrome dan hanya menerima host localhost/127.0.0.1. Set PLAYWRIGHT_MODULE jika Playwright dipasang di lokasi lain. Data browser disiapkan oleh `scripts/qa-browser-setup.php`, yang mewajibkan APP_ENV=testing dan file SQLite dalam direktori sementara `warbun-qa-*`.

Contoh lingkungan browser QA, gunakan variabel yang sama pada setup dan server:

```sh
QA_DIR="$(mktemp -d /tmp/warbun-qa-XXXXXX)"
touch "$QA_DIR/database.sqlite"
export APP_ENV=testing DB_CONNECTION=sqlite DB_DATABASE="$QA_DIR/database.sqlite" DB_URL=""
export APP_URL=http://127.0.0.1:8765 SESSION_DRIVER=file SESSION_COOKIE=warbun_qa_session CACHE_STORE=array MAIL_MAILER=array QUEUE_CONNECTION=sync
php scripts/qa-browser-setup.php
php artisan serve --host=127.0.0.1 --port=8765 --no-reload
```

Di terminal lain, dari root project: `node scripts/qa-browser.cjs`. Tutup server sementara setelah pengujian. Hasil browser dan screenshot berada di `docs/qa/`.

## Final design branch: local upgrade

The final UI adds checkout snapshots and a versioned shelf-layout table. Back up your development database, then run `php artisan migrate` and `npm run build`; restart PHP and refresh the browser. Do not run `migrate:fresh` on existing store data. The configured local development database was backed up and upgraded on 2 November 2026; see the final QA report for verification and the private backup location.

Delivery radius and customer payment fees are configured in Store settings. Radius mode is opt-in and requires the store/address pins; defaults preserve fixed shipping and zero new fees/promotions. Channel options remain manual/store-confirmed unless an actual gateway adapter is configured. Read the [final QA and rollout notes](docs/implementation/qa/FINAL-QA.md) before enabling policies.
