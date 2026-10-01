# QA monitoring dan pengantaran

2026-10-01. Functional QA PASS. Monitoring `/orders/monitor` menggabungkan Order online dan pengantaran Sale kasir: filter status, pelanggan/telepon/referensi, pagination 12 transaksi, counts nyata, kontak/alamat/items/total, dan tindakan sesuai permission. Antrean aktif tertua tampil pertama.

POS memilih Langsung/Diantar serta pelanggan existing atau modal pelanggan baru (nama/telepon/alamat, akun login customer opsional). Kredit memerlukan akun aktif dan persetujuan, tidak diberikan otomatis. UI menunjukkan limit, uang muka tunai dan sisa utang. Ongkir pengaturan toko termasuk total/piutang/struk. Pemenuhan terpisah dari pembukuan; tidak membuat penjualan, pembayaran atau pengurangan stok ulang. Detail online menampilkan tindakan valid dan sisa pembayaran sebelum completion.

## Hasil aktual

- PHPUnit: 90 tests / 741 assertions PASS, SQLite in-memory. Sepuluh kasus baru: ongkir/snapshot, retry/stok tunggal, deposit/pelunasan, kredit/inaktif/restricted, izin/transisi, pelanggan/role, refund, pagination/soft deletion.
- Vite build PASS (9 modules), Pint `--dirty --test` PASS, diff check PASS. Pint seluruh repository menemukan masalah format existing pada 14 file di luar perubahan; file tersebut tidak diubah.
- Chrome + SQLite disposable: tujuh skenario PASS tanpa page errors. [Hasil](docs/qa/order-monitor-results.json), [runner](scripts/qa-order-monitor.cjs). Modal Escape/duplicate phone/cart, akun opsional, checkout delivery/receipt, tindakan status POS, search/filter/refresh/history/empty state, debt checkout dan online payment/completion.
- 320/375/768/1024/1440px: tanpa overflow dokumen/modal, strip status scroll lokal. [Monitor mobile](docs/qa/screenshots/order-monitor-375.png), [desktop](docs/qa/screenshots/order-monitor-1440.png), [modal mobile](docs/qa/screenshots/pos-delivery-modal-375.png), [desktop](docs/qa/screenshots/pos-delivery-modal-1440.png).
- Regression kategori browser PASS: combined search, cart, scan luar kategori, stale request, scroll/keyboard. [Hasil](docs/qa/pos-category-results.json).
- MySQL migration rehearsed pada temporary table salinan seluruh 23 Sale existing; defaults dan baris terjaga. SQL pretend diperiksa lalu hanya migrasi fulfillment diterapkan lokal. GET monitor/POS di 8080 dan empat lebar PASS. Tidak ada transaksi bisnis QA dibuat di DB existing.

Utang adalah metode POS; checkout online tetap memakai metode existing. Completion membutuhkan orders.complete (owner/manager/admin); kasir default bisa menyiapkan dan menandai siap. Refund existing mengembalikan ongkir/reversal debt pada refund penuh. Safari/Firefox, screen reader, scanner/printer fisik dan deployment VPS tidak diuji. Fixture demo terisolasi.

## Anti-slop delivery gate

Scope monitor/detail order/POS delivery/customer. Direction dan alasan: [ORDER_MONITORING.md](ORDER_MONITORING.md), [DESIGN_SYSTEM.md](DESIGN_SYSTEM.md).

- R-02 PASS: teks baru tanpa em dash.
- R-03 PASS: matrix lima lebar tanpa overflow; scroll filter terkandung.
- R-17 PASS: counts berasal dari query database.
- R-18 PASS: tanpa testimonial.
- R-23 PASS: fitur/navigasi diminta user, SVG existing.
- R-24 PASS: route sidebar/monitor/detail/receipt nyata dan diuji browser.
- R-25 PASS: palette AA existing, white/burgundy 9.05:1; status berteks.
- R-26 PASS: tujuh skenario menjalankan kontrol baru dan submission.
- R-27 PASS: empty/error/validation/saving states, cart bertahan saat error.
- R-28 PASS: tanpa FAQ.
- R-32 PASS: native labels, dialog Escape, focus-visible dan tindakan minimal 44px.
- R-33 PASS: source ditulis dengan apply_patch.
- R-34 PASS: light theme existing, tanpa toggle baru.
- R-35 PASS: build/backend/browser dijalankan, page errors kosong.
- R-36 PASS: tidak mengklaim hardware/provider/deployment teruji.
- R-37 PASS: direction retail dinyatakan sebelum implementasi.
- R-38 PASS: data DB nyata, fixture demo terisolasi.

- R-01 PASS: tanpa gradient.
- R-04 PASS: truck pengantaran, user-plus pelanggan, clipboard-list monitoring.
- R-06 PASS: Georgia editorial heading, system sans data retail.
- R-07 PASS: tanpa pola latar.
- R-08 PASS: tombol baru tanpa panah dekoratif.
- R-09 PASS: status aktual, bukan badge promosi.
- R-10 PASS: tanpa glassmorphism, backdrop dialog memusatkan fokus.
- R-12 PASS: border panel existing, tanpa shadow berlebihan.
- R-13 PASS: tanpa glow.
- R-14 PASS: kartu transaksi dengan alamat, item disclosure dan tindakan terpisah hierarki.
- R-19 PASS: hover/native interactions sesuai MOTION 1.
- R-22 PASS: tanpa ilustrasi.

- Dials PASS: ENERGY 2 / RHYTHM 3 / MOTION 1 tercatat.
- Consistency PASS: heading, queue strip, filter dan ledger punya variasi terarah.
- Focal point PASS: tindakan berikutnya burgundy, detail sekunder outline.
- Whitespace PASS: 16–24px memisahkan kontak/pembayaran/tindakan, screenshot ditinjau.
- Accent PASS: burgundy untuk tindakan/status pilihan.
- Identity PASS: ivory/editorial heading/ledger retail Warbun.
- Design read PASS: direction operasional retail dinyatakan sebelum generasi.

- C-1 PASS: keputusan utama beralasan dalam ORDER_MONITORING.md.
- C-2 PASS: form/tombol nyata diuji browser, permission backend.
- C-3 PASS: setiap bagian membantu antrean, kontak, pemenuhan atau pembayaran.
- C-4 PASS: responsive/validation/empty/native keyboard/modal dalam scope; batas uji disebutkan.
- C-5 PASS: counts nyata dan evidence scoped, tanpa klaim rekaan.
- R-05 PASS: layout mengikuti tugas operasional.
- R-11 PASS: radius sistem 5–8px, bukan semua pill.
- R-15 PASS: tindakan spesifik Mulai siapkan/Tandai siap/Simpan dan pilih pelanggan.
- R-16 PASS: tanpa buzzword.
- R-20 PASS: ongkir/utang/struk dan bahasa retail Warbun.
- R-21 PASS: tema terang sesuai direction yang diterima.
- R-29 PASS: ivory/charcoal/burgundy existing.
- R-30 PASS: tidak meniru produk lain.
- R-31 PASS: alasan warna/layout/typography/spacing/cards/icons tertulis.
