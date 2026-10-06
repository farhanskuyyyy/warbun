# Buku alamat pelanggan

6 Oktober 2026. User bisa menyimpan beberapa tujuan di **Akun > Alamat saya** (/my-addresses). Tambah alamat lewat modal: label Rumah/Kantor, penerima, nomor telepon, jalan/nomor/patokan, dan pin peta opsional. Alamat pertama otomatis utama; pilih alamat lain sebagai utama lewat kartu atau saat menambah alamat. Ubah dan hapus memakai ikon Font Awesome dengan label aksesibel. Menghapus alamat utama mempromosikan alamat tersisa; menghapus terakhir membuat buku kosong.

Di /cart, pilih Diantar lalu pilih alamat tersimpan. Alamat/penerima tampil sebelum pemesanan. Jika belum ada, Tambah alamat membuka modal; sukses menyimpan langsung memilih alamat tersebut. Delivery checkout ditahan sampai ada pilihan. Guest diminta masuk untuk menyimpan alamat; isi cart dan pilihan Diantar bertahan. Draft pilihan alamat setelah login terikat customer ID agar pergantian akun tidak memakai alamat akun lama.

Di POS, pilih pelanggan dahulu. Alamat dimuat hanya untuk pelanggan itu; pergantian pelanggan mengosongkan tujuan lama sebelum request baru. Respons terlambat diabaikan. Tambah alamat memakai modal yang sama dan langsung dipilih setelah simpan. Pelanggan baru dari modal kasir otomatis mendapatkan alamat pertamanya. Cashier dengan sales.create boleh menyimpan tujuan untuk pelanggan aktif; pos.access diperlukan membaca buku alamat.

Alamat lama di customers diimpor sekali menjadi Alamat utama lewat migration tambahan. Kolom address/latitude/longitude legacy mencerminkan alamat utama. CRUD customer yang mengubah alamat memperbarui alamat utama dan menghapus pin lama; mengosongkannya menghapus default dan mempromosikan alamat tersisa. Seeder CustomerAddressSeeder hanya mengimpor alamat existing bila buku kosong, tidak mengarang tujuan atau menambah salinan saat dijalankan ulang.

Order/sale menyimpan teks penerima + telepon + alamat dan snapshot koordinat, tanpa foreign key ke alamat tersimpan. Edit/hapus alamat tidak mengubah pesanan lama. address_id diselesaikan server berdasarkan customer transaksi; detail yang dipalsukan di request tidak mengganti record. API lama yang mengirim alamat manual tetap kompatibel; UI cart/POS terbaru meminta pilihan tersimpan. Pickup/in-store tidak memakai koordinat pengiriman. Ongkir tetap tarif toko.

Peta berada di modal tambah/ubah alamat; ketuk/geser pin, pilih pusat peta via keyboard, atau gunakan GPS atas permintaan. [Provider dan batas GPS](DELIVERY_MAPS.md) tetap berlaku. Gangguan jaringan/validasi menampilkan error dengan isian tetap tersedia; requests dibatasi 15 detik. Perangkat GPS/iPhone/Safari fisik belum diuji.

Preview http://127.0.0.1:8080 berjalan dengan demo SQLite dan migration tambahan sudah diterapkan tanpa reset. Database MySQL operasional belum dimigrasikan. Saat memasang versi ini di lingkungan lain, jalankan php artisan migrate --force dan build asset sesuai prosedur project; jangan migrate:fresh atau seed ulang data operasional.

[QA](ADDRESS_BOOK_QA.md).
