# Data demo warung UMKM

Dataset development: **53 SKU, 9 kategori dan 31 tabel aplikasi terisi**. Komposisi menggambarkan warung lingkungan dengan kebutuhan harian, kemasan kecil dan sachet. Ini keputusan contoh, bukan survei penjualan atau klaim produk terlaris.

## Dasar riset

Riset: 1 Oktober 2026. Referensi digunakan untuk kategori dan keluarga produk. Harga, ukuran kemasan yang tidak diverifikasi, supplier, pelanggan, alamat, limit kredit dan transaksi adalah **asumsi demo**, bukan harga resmi atau harga pasar terkini.

| Referensi | Penggunaan |
| --- | --- |
| [Alfagift](https://alfagift.id/?standalone=true) | Kategori makanan/minuman, bahan masakan, rumah tangga, perawatan diri dan bayi. |
| [Indomie](https://www.indomie.com/product/soup-based-noodles) | Contoh mi instan kuah dan variasi rasa. |
| [Indofood CBP](https://www.indofoodcbp.com/investor-relation/annual-report/15) | Keluarga Indomie, Sarimi, Pop Mie, Indomilk, Chitato, Qtela. |
| [Mayora](https://www.mayora.com/en/about-us/Brand-Story) | Roma, Energen, Kopiko dan Le Minerale. |
| [Wings](https://wingscorp.com/) | Mi Sedaap dan keluarga rumah tangga Daia/SoKlin. |
| [Unilever Indonesia](https://www.unilever.co.id/brands/) | Lifebuoy, Pepsodent, Rinso, Sunlight. |
| [Kapal Api Store](https://www.kapalapistore.com/products/2865-good-day-latte-original) | Contoh Good Day Latte Original. |

Beras, gula, garam, telur dan produk tanpa merek melengkapi contoh berdasarkan penilaian praktis. Kemasan kecil menyediakan variasi keranjang murah. Sepuluh SKU sebelumnya tetap tersedia; gambar produk tidak dibuat atau diambil dari situs lain.

## Cakupan tabel

Kategori: Makanan, Minuman, Sembako, Rokok, Kebutuhan Rumah, Personal Care, Kopi & teh, Camilan, Kebutuhan bayi. Produk mencakup mi, air kemasan, kopi/teh sachet, beras/gula/minyak/tepung, bumbu, telur, susu, biskuit/wafer, detergen, sabun, pasta gigi, pembalut, minyak telon, popok, tisu dan baterai. Rokok master lama tetap untuk POS dan tidak dibuka online oleh seeder katalog. Ada contoh stok cukup, rendah dan kosong.

| Tabel | Isi |
| --- | --- |
| users, roles, permissions | Owner/admin/manager/kasir, dua staf tambahan, enam pelanggan. |
| model_has_roles, role_has_permissions, model_has_permissions | Assignment Spatie; petugas stok demo mendapat izin langsung approve opname. |
| categories, product_types, brands, units, suppliers, products | Master lama dan katalog tambahan dengan relasi slug/code/symbol, bukan ID tetap. |
| inventory_transactions | Saldo awal, penjualan, reservasi, pembatalan, retur, opname. |
| customers | Akun terhubung; lima pelanggan eligible utang dan satu tanpa fasilitas utang. |
| cashier_shifts | Tujuh shift tertutup dengan kas cocok dan satu aktif milik kasir demo. |
| sales, sale_items | 21 penjualan selama tujuh hari: cash, transfer dan utang. |
| orders, order_items | Enam pesanan pickup/delivery: pending, confirmed, preparing, ready, completed, cancelled. |
| payments | Penerimaan penjualan, cicilan, payment intent/pelunasan pesanan. |
| debt_accounts, debt_transactions, debt_allocations | Utang, pembayaran sebagian FIFO, sisa saldo dan contoh jatuh tempo lewat. |
| refunds, refund_items, refund_payments | Retur tunai sebagian dengan receipt asal. |
| stock_opnames, stock_opname_items | Satu approved dan satu pending. |
| store_settings | Nama toko, termin, ongkir, petunjuk pembayaran demo, marker dataset selesai. |
| notifications, audit_logs | Event asli dari service, terkait entitas fixture yang dibuat. |

Tabel internal Laravel (`migrations`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `password_reset_tokens`) mengikuti lifecycle framework. Seeder tidak membuat session, job palsu atau token reset aktif untuk memaksa jumlah barisnya tidak nol.

## Menjalankan

Gunakan database development yang sudah dimigrasi dan terpisah dari data toko:

```sh
php artisan db:seed
# Dataset yang sama, eksplisit:
php artisan db:seed --class=DemoOperationsSeeder
# Katalog saja, tanpa transaksi demo:
php artisan db:seed --class=WarungCatalogSeeder
```

Dataset lengkap/katalog tambahan hanya menerima APP_ENV=local/testing. Pengujian tugas ini memakai database sementara; database server pengguna pada port 8080 tidak di-seed. Perintah menambah data dan tidak membutuhkan migrate:fresh pada database existing.

Seeder lengkap memakai satu transaksi dan marker `demo_dataset_v2`: pengulangan setelah sukses tidak menambah transaksi, notifikasi, shift atau stok. Katalog memakai SKU dan tidak mereset harga/stok yang sudah ada. Auth user dan waktu untuk membuat histori dipulihkan setelah proses. Untuk contoh baru, gunakan database terpisah; jangan menghapus marker pada dataset terisi.

Password fixture: `password`. Akun utama: `owner@warbun.local`, `admin@warbun.local`, `manager@warbun.local`, `kasir@warbun.local`. Pelanggan: `customer@warbun.local`, `sari@warbun.local`, `budi@warbun.local`, `rani@warbun.local`, `agus@warbun.local`, `dewi@warbun.local`. Staf tambahan: `demo.cashier@warbun.local`, `demo.stock@warbun.local`. Supplier lama maupun supplier bernama Demo adalah contoh, bukan relasi distributor nyata. Fixture tidak mempunyai rekening/QR pembayaran sungguhan.

## Bukti

[SeedDataTest.php](tests/Feature/SeedDataTest.php) memeriksa isi 31 tabel aplikasi, 53 SKU/9 kategori, stock ledger setiap SKU, saldo utang/customer/remaining principal, rekonsiliasi shift, alokasi refund, idempotensi dan penolakan production sebelum penulisan. Transaksi dibuat melalui service aplikasi, sehingga bukan baris acak yang saldo dan stoknya tidak cocok.
