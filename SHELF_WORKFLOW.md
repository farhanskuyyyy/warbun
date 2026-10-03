# Etalase dan penataan produk

POS `/pos`: minimap etalase bernomor, semua etalase, dan produk belum ditata. Klik etalase untuk menampilkan produk aktif dengan stok tersedia. Klik produk untuk membuka info nama, harga jual, SKU, barcode, kategori, unit, stok dan lokasi. Tombol Tambahkan memasukkan satu unit; scan barcode tetap langsung menambah produk. Keranjang tetap tersedia saat mengganti filter.

Memilih etalase membersihkan pencarian dan kategori sebelumnya agar isi etalase langsung terlihat. Setelah itu pencarian dan kategori menyaring di dalam etalase terpilih. Daftar memuat 60 produk per halaman dengan tombol muat berikutnya; produk tidak tersembunyi karena batas daftar.

Menu **Katalog & stok → Penataan produk**, `/shelves`: tambah etalase dengan nomor unik 1–9999 dan nama, ubah nomor/nama, cari produk berdasarkan nama/SKU/barcode, lalu buka **Atur lokasi** pada produk. Pilih etalase serta catatan posisi, misalnya `rak atas, sebelah kiri`. Pilihan **Belum ditata** menghapus etalase dan catatan lama. Daftar mencakup produk yang diarsipkan dengan label tersendiri, sehingga lokasinya bisa dibersihkan sebelum menghapus etalase kosong.

Satu produk mempunyai satu lokasi utama. Minimap adalah skema urutan nomor, bukan koordinat denah toko. Stok tetap global per SKU; penataan tidak memindahkan jumlah stok atau mengubah harga. Monitoring dan detail pesanan menggunakan **lokasi produk saat ini**, termasuk ketika produk berpindah setelah pesanan dibuat. Buka bagian Barang pada kartu monitoring untuk melihat posisi pengambilannya.

Pembaca membutuhkan `products.view`; perubahan etalase/penempatan membutuhkan `products.update`. Kasir dapat melihat lokasi, sedangkan owner/admin/manager dapat mengatur. POS tetap mengikuti `pos.access` dan transaksi `sales.create`. Perubahan melalui CMS menggunakan transaksi DB, lock dan audit; FK melindungi etalase yang masih direferensikan produk.

## Seeder

`ShelfSeeder` menyediakan contoh sembilan kelompok berdasarkan kategori katalog existing: makanan, minuman, sembako, rokok, perawatan tubuh, kopi/teh, bayi, camilan dan kebutuhan rumah. Pada katalog demo penuh, 53 produk mendapat lokasi contoh. Nama etalase dan posisi diberi awalan `Contoh:` karena posisi fisik toko perlu disesuaikan sendiri.

Seeder hanya berjalan pada `local`/`testing`, memakai kategori/produk existing, tidak membuat transaksi penjualan atau mengubah stok/harga. Nomor dimulai setelah nomor etalase terbesar; lokasi manual yang sudah terisi dipertahankan. Penanda `demo_shelves_v1` membuat pengulangan tidak menduplikasi etalase atau mengembalikan lokasi yang sengaja dikosongkan. Database tanpa produk tidak ditandai selesai. Kategori tanpa produk tidak membuat etalase kosong.

`DatabaseSeeder` memanggil seeder etalase setelah dataset demo pada local/testing. Untuk menambahkan fitur ini pada database existing, jalankan hanya migrasi dan seeder berikut setelah MySQL tersedia:

```sh
php artisan migrate --path=database/migrations/2026_10_03_000001_add_product_shelves.php
php artisan db:seed --class=ShelfSeeder
```

Migrasi bersifat tambahan: tabel `shelves`, FK nullable `products.shelf_id`, catatan nullable `products.shelf_position`. Produk existing tetap belum ditata sampai penempatan diisi atau seeder khusus dijalankan. Penerapan MySQL lokal **belum dijalankan** pada 3 Oktober 2026: port 3306 tidak tersedia dan MySQL installed gagal start. Database existing tidak direset atau di-seed. Implementasi dan seed telah diverifikasi pada SQLite terisolasi; bukti di [SHELF_QA.md](SHELF_QA.md).
