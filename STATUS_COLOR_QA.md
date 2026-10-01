# Status transaksi berwarna

2026-10-01. PASS untuk scope perubahan. Component `transaction-status` dipakai pada monitoring, daftar/detail pesanan, riwayat kasir, detail/history pesanan pelanggan dan pembelian toko pelanggan. Label tetap tersedia; warna bukan satu-satunya penanda. Garis atas kartu dan strip filter monitoring mengikuti status pemenuhan; badge pembayaran punya status terpisah. Refund kasir ditampilkan sebagai refunded, termasuk di area pembayaran.

| Status | Warna | Kontras teks/latar |
| --- | --- | --- |
| Menunggu | Amber | 6.15:1 |
| Dikonfirmasi | Biru | 7.15:1 |
| Disiapkan | Ungu | 7.39:1 |
| Siap | Teal | 6.73:1 |
| Selesai / dibayar | Hijau | 6.49:1 |
| Dibatalkan / gagal | Merah | 6.80:1 |
| Dikembalikan | Slate | 6.15:1 |
| Utang / dibayar sebagian | Jingga | 6.38:1 |

Warna tambahan secara eksplisit diminta untuk membedakan status operasional. Palette brand ivory/charcoal/burgundy tetap digunakan untuk layout dan tindakan utama. Warna semantic dibatasi pada label kecil, garis kartu dan filter terpilih, sehingga bukan seluruh kartu berwarna. Titik dekoratif aria-hidden membantu pengenalan label; tidak ada emoji atau asset baru. Radius 5px dan spacing 6–10px membuat badge berbeda dari tombol dan tetap kompak. ENERGY 2 / RHYTHM 3 / MOTION 1 mengikuti direction retail existing.

## Bukti aktual

- `php artisan test --filter='OrderMonitoringTest|ShoppingFlowTest|PageSmokeTest'`: 16 tests / 222 assertions PASS, SQLite in-memory. Tidak menambah test yang hanya mencerminkan CSS.
- Vite build PASS, 9 modules; diff check PASS.
- Kontras diuji dengan `antislop-human/contrast-check.py`: seluruh delapan pasangan memenuhi AA teks normal.
- Chrome GET pada local MySQL 8080: monitoring, daftar order dan history kasir pada 320/375/768/1024/1440px tidak overflow. Badge nyata dibandingkan dengan computed styles. Detail order punya dua status terpisah. Keyboard Enter pada filter menunggu mempertahankan aria-current dan warna pilihan. Sepuluh variant palette diuji dengan swatch DOM sementara, bukan data bisnis rekaan. Tidak ada transaksi bisnis dibuat.
- [Runner](scripts/qa-status-colors.cjs), [hasil](docs/qa/status-color-results.json), [mobile](docs/qa/screenshots/status-monitor-375.png), [desktop](docs/qa/screenshots/status-monitor-1440.png). Tidak ada page errors. Screenshot ditinjau secara visual.
- Safari/Firefox dan screen reader tidak diuji. Struk thermal tetap memakai style cetak existing.

## Anti-slop delivery gate

Scope badge, garis kartu dan strip status. Layout/flow existing diwariskan dari [ORDER_MONITOR_QA.md](ORDER_MONITOR_QA.md).

- R-02 PASS: tidak menambah prose dengan em dash.
- R-03 PASS: tiga halaman, lima lebar tanpa overflow.
- R-17 PASS: counts memakai data existing, tanpa statistik baru.
- R-18 PASS: tanpa testimonial.
- R-23 PASS: warna status diminta user; tanpa asset/navigasi baru.
- R-24 PASS: destination existing dan keyboard filter browser lulus.
- R-25 PASS: delapan pasangan kontras 6.15:1 atau lebih, checker aktual.
- R-26 PASS: badge informational; filter existing tetap bekerja dengan keyboard.
- R-27 PASS: empty/error/loading existing dipertahankan, tidak mengubah request.
- R-28 PASS: tanpa FAQ.
- R-32 PASS: label terlihat, decorative dots aria-hidden, focus-visible existing dan keyboard filter diuji.
- R-33 PASS: source ditulis melalui apply_patch.
- R-34 PASS: tema terang existing, tanpa toggle baru.
- R-35 PASS: build, 16 tests, computed-style checks dan browser matrix dijalankan.
- R-36 PASS: bukti terbatas pada platform yang diuji.
- R-37 PASS: direction retail dan dials existing dipakai.
- R-38 PASS: label berasal dari status DB; swatch QA sementara dijelaskan.

- R-01 PASS: tanpa gradient.
- R-04 PASS: tidak menambah icon set, titik hanya redundant penanda status.
- R-06 PASS: font data existing, bobot 600 membantu label singkat.
- R-07 PASS: tanpa pola latar.
- R-08 PASS: tanpa panah tambahan.
- R-09 PASS: badge memiliki fungsi status nyata, tanpa promosi.
- R-10 PASS: tanpa glassmorphism.
- R-12 PASS: inset border filter menandai selection, tanpa shadow floating baru.
- R-13 PASS: tanpa glow.
- R-14 PASS: kartu pesanan existing, warna garis mengikuti status, bukan feature cards.
- R-19 PASS: tanpa animasi tambahan, MOTION 1.
- R-22 PASS: tanpa ilustrasi.

- Dials PASS: ENERGY 2 / RHYTHM 3 / MOTION 1 dipertahankan.
- Consistency PASS: heading/strip/form/ledger existing memberi variasi terarah.
- Focal point PASS: tombol tindakan tetap burgundy; badge kecil mendukung scanning.
- Whitespace PASS: padding badge 6–10px, jarak dot 7px, header wrap diuji mobile.
- Accent PASS: warna brand untuk tindakan; semantic color terikat status sesuai permintaan.
- Identity PASS: palette/layout retail Warbun dipertahankan.
- Design read PASS: direction retail existing digunakan sebelum patch.

- C-1 PASS: mapping warna, posisi dan ukuran punya alasan tertulis.
- C-2 PASS: filter tetap aktif; badge menyampaikan data nyata.
- C-3 PASS: penanda membantu membaca antrean, tanpa filler.
- C-4 PASS: status panjang membungkus, lima lebar dan keyboard diuji dalam scope.
- C-5 PASS: kontras aktual dan status nyata, tanpa klaim rekaan.
- R-05 PASS: layout tugas operasional dipertahankan.
- R-11 PASS: badge rectangular 5px, tidak semua elemen pill.
- R-15 PASS: CTA proses pesanan existing dipertahankan.
- R-16 PASS: tanpa buzzword.
- R-20 PASS: label/status sesuai alur Warbun.
- R-21 PASS: light theme retail yang diterima dipertahankan.
- R-29 PASS: tambahan warna khusus status diminta user; brand palette terpisah dan konsisten.
- R-30 PASS: tidak meniru produk lain.
- R-31 PASS: mapping/status/color/layout/spacing tercatat di laporan ini.
