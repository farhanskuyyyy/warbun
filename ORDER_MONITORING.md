# Monitoring dan pengantaran kasir

Pesanan online tetap memakai Order. Transaksi kasir yang diantar memakai Sale dengan status pemenuhan terpisah: confirmed, preparing, ready, completed. Pembayaran, piutang, dan pengurangan stok terjadi satu kali saat transaksi kasir; perubahan pemenuhan hanya memperbarui status dan audit. Retur memakai alur refund existing, bukan pembatalan transaksi berbayar.

Alamat pengantaran disimpan sebagai snapshot. Ongkir berasal dari pengaturan toko dan masuk total serta perhitungan utang. Pelanggan baru dari kasir belum mendapat kredit; akses utang tetap membutuhkan akun terdaftar, aktif, dan persetujuan kredit.

Desain mengikuti DESIGN_SYSTEM.md: ENERGY 2 / RHYTHM 3 / MOTION 1. Antrean tertua tampil lebih dulu supaya pesanan lama tidak tertutup pesanan baru. Filter status dan jumlah berasal dari database. Setiap kartu memberi satu tindakan berikutnya sesuai permission. Panel pengantaran muncul hanya ketika diperlukan; modal pelanggan menjaga keranjang tetap di halaman. Burgundy menandai tindakan, warna ivory menjaga keterbacaan, ikon truck dan user-plus menjelaskan pengantaran dan tambah pelanggan. Spasi 16–24px memisahkan data pelanggan, pembayaran, dan tindakan. Tidak ada animasi dekoratif.
