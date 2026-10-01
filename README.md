# Warbun

Aplikasi operasional warung: katalog pelanggan, POS, pesanan online, stok, piutang, pembayaran, shift kasir, retur, laporan, dan audit. Laravel + Blade + Tailwind + Spatie Permission; database utama MySQL.

## Dokumen project

- [PRD.md](PRD.md): brief asli dari warbun.md.
- [ARCHITECTURE.md](ARCHITECTURE.md): arsitektur, ERD, alur transaksi, permissions, dan keputusan implementasi.
- [DESIGN_SYSTEM.md](DESIGN_SYSTEM.md): pedoman tampilan yang mengikuti project existing.
- [tasklist.md](tasklist.md): pekerjaan development dan statusnya.
- [QA_REPORT.md](QA_REPORT.md): hasil pengujian dan batas verifikasi.

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

Untuk database development yang terpisah, isi master data dan contoh operasional:

```sh
php artisan db:seed --class=DemoOperationsSeeder
```

Akun demo: `owner@warbun.local`, `admin@warbun.local`, `manager@warbun.local`, `kasir@warbun.local`, `customer@warbun.local`; password `password`. Seeder demo menolak APP_ENV=production. Jangan gunakan akun demo sebagai akun toko sungguhan.

Gambar produk memakai disk public Laravel. Jika link storage belum tersedia, jalankan `php artisan storage:link` di checkout development.

## Cara memakai alur utama

1. Kasir membuka shift di POS, mencari nama/SKU/barcode, mengisi keranjang dan pembayaran, lalu mendapat struk.
2. Pembelian utang harus memakai pelanggan yang terhubung ke akun terdaftar, aktif, eligible, dan mempunyai limit kredit.
3. Pelunasan dapat dicatat per penjualan lewat Payments atau per pelanggan lewat Debt. Pembayaran pelanggan dialokasikan ke utang terbuka menurut due date dan ID.
4. Pelanggan masuk ke Shop, checkout pickup/delivery, lalu melihat status pesanan miliknya. Checkout mengurangi stok sebagai reservasi.
5. Staf memproses pending → confirmed → preparing → ready → completed. Selesai memerlukan pelunasan. Pembatalan hanya untuk pesanan tanpa penerimaan uang; transaksi berbayar menggunakan retur.
6. Retur memilih sumber transaksi, ID produk, jumlah, dan alasan. Sistem membatasi jumlah yang belum diretur, membalik utang terlebih dahulu, lalu mengalokasikan pengembalian uang ke receipt asal. Refund tunai memerlukan shift aktif.
7. Stock Opname menyimpan expected stock ketika dibuat. Approve menolak hitungan jika stok sudah berubah.
8. Kasir menutup shift dengan uang aktual. Sistem mencatat expected cash dan variance; kasir hanya melihat riwayat shift sendiri, manajer/owner dapat melihat seluruh staf.

Settings menyediakan nama toko, termin utang, dan ongkir. Profil pelanggan menyimpan identitas, telepon, alamat, dan riwayat pembelian. ID/EN tersedia melalui pemilih bahasa; default bahasa Indonesia dan timezone Asia/Jakarta.

## Definisi laporan

Sales mencakup POS finalized dan order yang sudah diterima toko (confirmed/preparing/ready/completed), termasuk transaksi yang kemudian direfund. Gross memakai subtotal, net memakai total dikurangi retur pada periode retur; ongkir termasuk total order. Penjualan mengikuti tanggal pembuatan transaksi. Kas yang diterima diperiksa melalui Payments dan Shifts, sehingga pesanan yang belum lunas tidak dianggap sebagai penerimaan uang.

Payments mengelompokkan penerimaan dan pengembalian dana menurut metode. Debt menyajikan saldo, pembayaran, overdue, dan aging dari remaining principal. Staff memisahkan nilai penjualan, penerimaan, utang baru, refund, dan variance shift. Inventory menampilkan stok dan unit POS terjual setelah retur.

## Payment gateway

Default `PAYMENT_GATEWAY=manual`: cashier memverifikasi cash/transfer/e-wallet/QR. Gateway online memakai interface `App\Payments\PaymentGateway`. Adapter `signed-webhook` menyediakan kontrak pengujian yang terverifikasi, bukan integrasi Midtrans/Xendit yang sudah aktif.

Jika memakai adapter tersebut, atur `PAYMENT_WEBHOOK_SECRET` minimal 32 karakter lewat konfigurasi lingkungan. POST `/payments/webhook` menerima JSON `payment_number`, `amount`, `status` (paid/failed/cancelled), dan `reference`; header `X-Payment-Timestamp` berisi Unix timestamp, header `X-Payment-Signature` adalah HMAC-SHA256 atas `timestamp.raw_body`. Toleransi waktu 300 detik. Amount harus sama dengan payment intent; replay identik tidak menulis transaksi ulang. Callback berbeda untuk payment yang finalized ditolak.

Provider live memerlukan adapter sesuai dokumentasi provider, pembuatan payment session, serta refund eksternal. Refund di aplikasi saat ini mencatat pengembalian yang dilakukan toko; aplikasi belum mengirim uang lewat API bank/gateway. Email eksternal mengikuti MAIL_* project dan belum diuji dengan provider sungguhan. Database notifications menyimpan event operasional untuk integrasi kanal lanjutan.

## QA

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
