# Scan barcode lewat kamera

POS dan form tambah/edit produk memiliki dua tombol: **Scanner hardware** memfokuskan kolom barcode, **Scan dengan kamera** membuka modal kamera. Scanner USB/Bluetooth tetap menggunakan mode keyboard dengan akhiran Enter. Barcode juga dapat diketik manual.

Kamera dipilih setelah pengguna menekan tombol dan memberi izin browser. Kamera belakang diprioritaskan bila tersedia; pilihan Kamera memungkinkan berpindah perangkat. Bingkai membantu menempatkan seluruh barcode kemasan di area video. Tahan perangkat dan hindari pantulan cahaya.

Satu barcode dibaca setiap kali modal dibuka. Kamera berhenti setelah berhasil, saat X/Kembali/Escape, saat halaman tersembunyi atau saat navigasi. Kamera yang baru mendapat izin setelah modal ditutup juga langsung dihentikan. Pengambilan video tidak meminta audio, dan frame diproses lokal di browser tanpa upload ke server.

Pada POS, hasil kamera masuk ke kolom scan dan menggunakan antrean lookup existing. Produk terdaftar/aktif/tersedia ditambah satu kali ke keranjang; barcode tidak dikenal atau stok kosong menampilkan feedback existing. Buka kamera lagi untuk unit berikutnya. Shift harus aktif; kontrol scanner/kamera dinonaktifkan ketika checkout diproses.

Pada tambah/edit produk, hasilnya hanya mengisi barcode. Nama, harga, posisi atau stok tidak disimpan otomatis. Periksa barcode lalu tekan Simpan/Perbarui. Validasi unique dan panjang maksimum 50 karakter tetap berlaku di server. Untuk mencoba kemasan sendiri, scan dan simpan barcode produk dahulu, lalu scan produk yang sama di kasir.

Decoder memakai `@zxing/browser` 0.1.5 dan `@zxing/library` 0.21.3, kompatibel dengan Node 22 existing. Reader 1D menangani barcode retail seperti EAN-13/EAN-8, UPC dan Code 128. Paket decoder dimuat lokal melalui chunk Vite hanya setelah kamera dibuka; tidak ada CDN runtime. Versi dipin, tanpa mengubah dependency existing. Dasar API: [ZXing Browser](https://github.com/zxing-js/browser), [reader 1D](https://github.com/zxing-js/browser/blob/master/src/readers/BrowserMultiFormatOneDReader.ts).

Browser memerlukan secure context untuk kamera: **HTTPS**, atau **localhost/127.0.0.1 pada perangkat yang menjalankan app**. Laptop dapat memakai preview lokal. HP yang membuka alamat IP laptop melalui HTTP biasanya tidak dapat memakai kamera; gunakan alamat HTTPS untuk akses dari HP. Akses hardware/manual tetap tersedia. Pesan khusus tersedia untuk izin ditolak, kamera tidak ada, sedang dipakai, browser tidak mendukung dan koneksi tidak aman. Sumber: [MDN getUserMedia](https://developer.mozilla.org/en-US/docs/Web/API/MediaDevices/getUserMedia).

## Preview yang dijalankan

Preview saat pengerjaan berjalan di `http://127.0.0.1:8080`, memakai SQLite demo terpisah di directory sementara `warbun-qa-preview-*`. Login `admin@warbun.local` atau `owner@warbun.local`, password `password`. `.env` dan MySQL operasional tidak diubah; MySQL lokal masih tidak tersedia. Preview sudah mempunyai dataset produk dan etalase contoh. Database/browser test memakai port 8765 dan database lain, sehingga transaksi QA tidak masuk preview pengguna.

Preview ini merupakan proses development yang sedang berjalan, bukan deployment. [QA kamera](CAMERA_BARCODE_QA.md) menjelaskan uji decoding dan batas pengujian perangkat fisik.
