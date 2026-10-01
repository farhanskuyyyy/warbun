# Barcode produk dan kasir

## Menyiapkan produk

1. Buka Products, lalu tambah atau edit produk.
2. Klik kolom Barcode. Scan barcode yang tercetak pada kemasan, atau ketik nilainya persis. Angka nol di awal harus tetap ada.
3. Tekan Save Product / Update Product. Enter dari scanner hanya mengisi kolom barcode dan tidak langsung menyimpan form.
4. Satu barcode hanya boleh digunakan oleh satu produk. Barcode boleh kosong untuk barang yang dicari manual. Data yang sudah diarsipkan tetap mempertahankan barcode uniknya.

Nama, SKU dan barcode tetap bisa dicari dari daftar produk. Nilai barcode ditampilkan di detail produk. Kode pada dataset demo tidak dianggap barcode produsen yang telah diverifikasi; isi dari kemasan yang benar. Menyimpan nilai barcode tidak membuat label barcode fisik untuk produk tanpa kemasan.

## Menyiapkan scanner

Fitur ini menerima scanner USB/Bluetooth yang bekerja sebagai keyboard, dengan akhiran Enter. Atur perangkat ke HID Keyboard / keyboard emulation dan aktifkan suffix Enter menggunakan panduan model scanner. USB serial/COM dan pembacaan kamera belum diimplementasikan.

Pastikan scanner bisa mengetik kode yang benar di kolom teks biasa. Jika angka nol hilang, huruf berubah atau ada awalan tambahan, periksa pengaturan perangkat. Dasar konfigurasi: [Zebra USB HID Keyboard](https://docs.zebra.com/us/en/scanners/general/sm72-ig/usb-interface/usb-parameter-defaults/usb-device-type-.html) dan [Enter suffix](https://docs.zebra.com/us/en/scanners/general/sm72-ig/user-preferences-and-miscellaneous-options/miscellaneous-scanner-parameters/enter-key.html). Langkah pengaturan fisik berbeda menurut model.

## Scan sampai cetak struk

1. Login sebagai kasir atau staf dengan izin POS dan penjualan. Buka POS / Cashier, lalu buka shift.
2. Klik kolom Scan barcode. Kolom ini mendapat fokus awal ketika shift aktif.
3. Scan barang. Setelah Enter, sistem mencocokkan barcode penuh dan langsung menambah satu barang ke keranjang. Scan produk yang sama menambah jumlah, bukan membuat baris ganda.
4. Kolom scan langsung dikosongkan dan tetap siap untuk scan berikutnya. Pencarian nama/SKU tetap tersedia untuk barang tanpa barcode.
5. Periksa jumlah, total, metode pembayaran dan uang yang diterima. Preview kembalian ditampilkan. Pembelian utang tetap membutuhkan pelanggan yang memenuhi aturan kredit.
6. Pilih kertas 58 atau 80 mm. Aktifkan Buka dialog cetak setelah pembayaran jika ingin dialog cetak terbuka setelah transaksi berhasil. Pilihan lebar kertas diingat pada browser bila storage tersedia.
7. Tekan Complete sale. Harga dan stok divalidasi lagi secara transaksional. Sesudah berhasil, halaman struk menampilkan jumlah, harga satuan, subtotal, diskon, pembayaran, kembalian dan sisa utang bila ada.
8. Pilih printer yang sudah tersedia di perangkat dan ukuran kertas yang sama. Matikan header/footer browser, gunakan skala 100% dan atur margin di driver sesuai printer. Cetak atau simpan PDF.
9. Cetak ulang lewat Cetak struk. Pilih Transaksi baru untuk kembali ke POS; struk lama tetap tersedia melalui riwayat penjualan.

Cetak memakai [window.print()](https://developer.mozilla.org/en-US/docs/Web/API/Window/print), yang membuka dialog browser. Ini bukan koneksi langsung ESC/POS atau silent print. Membuka dialog/cancel cetak tidak membatalkan penjualan yang sudah selesai. Aplikasi tidak mengklaim printer telah mencetak kertas. Jika dialog otomatis tidak muncul atau dibatalkan, tombol Cetak struk tetap tersedia. Dialog otomatis hanya dipanggil sekali per struk dalam sesi browser bila sessionStorage tersedia; reload dan membuka struk dari riwayat tidak memicu cetak otomatis lagi.

## Respons ketika ada masalah

- Barcode tidak terdaftar atau produk tidak aktif/diarsipkan: tidak ditambahkan, periksa katalog.
- Stok habis atau jumlah melampaui stok: tidak ditambahkan, keranjang tetap tersedia.
- Koneksi scan gagal: tampil pesan untuk scan ulang; barang yang sudah masuk keranjang tetap ada.
- Scan cepat berulang: lookup diproses berurutan, pembayaran menunggu seluruh scan selesai.
- Pembayaran gagal: pesan tampil tanpa mengosongkan keranjang. Retry tanpa perubahan transaksi menggunakan request key yang sama agar tidak menduplikasi penjualan.
- Stok berubah setelah scan: backend menolak checkout yang sudah tidak memenuhi stok, bukan menjual stok negatif.

QA simulasi keyboard scanner dan layout cetak: [BARCODE_QA_REPORT.md](BARCODE_QA_REPORT.md). Perangkat scanner dan printer fisik perlu dicoba dengan model yang digunakan toko.
