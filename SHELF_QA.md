# QA etalase POS dan penataan produk

3 Oktober 2026. **PASS untuk implementasi yang diuji pada database terisolasi.** Penerapan migrasi/seeder pada MySQL existing belum dilakukan: port 3306 tidak tersedia dan service installed gagal start. Tidak ada reset/seeding pada database operasional. Lihat [panduan fitur dan seeder](SHELF_WORKFLOW.md).

## Hasil

- Full suite `php artisan test`: **97 tests / 799 assertions PASS**, SQLite in-memory. Tujuh regression khusus etalase memeriksa permission, validasi, pemindahan tanpa stok/harga berubah, etalase berisi/produk arsip, filter/scan, pagination 65 produk, lokasi picking dan idempotence seeder. Dataset demo juga memverifikasi sembilan etalase dan semua 53 produk terpetakan.
- Vite production build, `node --check resources/js/pos.js`, Pint pada perubahan dan `git diff --check`: PASS. Dependency existing tidak diubah.
- [Browser etalase](scripts/qa-shelves.cjs): sembilan skenario PASS pada Chrome dengan database SQLite disposable. Tambah/rename/hapus etalase kosong, assign/clear posisi, filter penataan, reset filter ketika memilih etalase POS, modal info, tambah sekali, Escape/fokus kembali, error jaringan/keranjang tetap, pagination UI mock, picking monitoring/detail dan keyboard. [Hasil](docs/qa/shelves-results.json).
- [Regresi kategori](scripts/qa-pos-categories.cjs): lima skenario PASS, termasuk stale request, kombinasi pencarian/kategori, keranjang dan scan dari kategori lain. [Regresi barcode](scripts/qa-barcode.cjs): tujuh skenario PASS, termasuk scan antre, limit stok, gagal jaringan, pembayaran kurang, retry checkout, 58/80mm PDF dan print. Runner manual product clicks diperbarui mengikuti modal/tombol tambah.
- POS, dialog produk dan penataan diuji pada **320/375/768/1024/1440px**, tanpa horizontal document overflow. Target etalase minimal 44px; Add terlihat pada ponsel. Screenshot mobile/desktop ditinjau: [POS](docs/qa/screenshots/shelves-pos-375.png), [modal](docs/qa/screenshots/shelves-dialog-375.png), [penataan](docs/qa/screenshots/shelves-management-375.png), [desktop](docs/qa/screenshots/shelves-pos-1440.png).
- Playwright MCP juga berhasil membuka app, login, membuka shift dan membaca minimap/data produk pada server QA. Tidak ada page errors pada runner fitur.
- [Regresi monitoring/delivery/debt](scripts/qa-order-monitor.cjs): tujuh skenario PASS, termasuk modal pelanggan, pembelian delivery hingga struk, seluruh tahap pemenuhan, debt checkout, pembayaran online dan blokir penyelesaian belum lunas. Total empat runner: 28 skenario, tanpa page errors. Setiap runner yang memerlukan keadaan awal memakai reset hanya pada database disposable.
- Kontras checker anti-slop-human: muted/ivory **5.57:1**, burgundy/selected **7.57:1**, lokasi/white **7.23:1**. Palette dan CTA existing diwariskan dari DESIGN_SYSTEM.md.
- MySQL migration compatibility belum diverifikasi lewat eksekusi lokal. Safari/Firefox, screen reader, hardware scanner/printer tidak diuji. Minimap berbasis nomor, satu lokasi utama per SKU; tata letak seed adalah contoh yang perlu dicocokkan ke toko.

## Anti-slop delivery gate

Scope UI etalase, penataan, info produk dan tambahan lokasi pada pesanan. Direction retail existing ENERGY 2 / RHYTHM 3 / MOTION 1; alasan desain di [DESIGN_SYSTEM.md](DESIGN_SYSTEM.md).

- R-02 PASS: copy baru tanpa em dash.
- R-03 PASS: browser tiga permukaan pada lima lebar tanpa overflow.
- R-17 PASS: count berasal dari query products; jumlah POS menghitung produk aktif/stok tersedia.
- R-18 PASS: tidak ada testimonial.
- R-23 PASS: menu/minimap/modal secara eksplisit diminta; icon memakai sprite existing, data seed diberi awalan Contoh.
- R-24 PASS: link Penataan produk, Buka kasir, filter etalase dan detail pesanan dicoba browser.
- R-25 PASS: tiga pasangan teks baru memenuhi AA, rasio aktual tercatat di atas.
- R-26 PASS: tambah/rename/hapus kosong, assign/clear, filter, disclosure, modal close/Add dan muat produk memiliki behavior yang diuji.
- R-27 PASS: belum ada etalase, lokasi belum diatur, produk tidak ditemukan, loading dan gagal fetch memiliki pesan; network failure browser mempertahankan keranjang.
- R-28 PASS: tidak menambah FAQ.
- R-32 PASS: native buttons/dialog, aria-pressed/controls/busy, live state, label form, focus-visible; Enter dan Escape/focus kembali diuji.
- R-33 PASS: seluruh source ditulis dengan apply_patch, script QA tidak memodifikasi source aplikasi.
- R-34 PASS: tema terang existing; tidak ada toggle baru.
- R-35 PASS: full suite, build, browser CRUD/filter/modal/picking dan scanner checkout dijalankan dengan evidence file.
- R-36 PASS: bukti dinyatakan sesuai SQLite/Chrome; kegagalan MySQL dan batas hardware dicatat.
- R-37 PASS: direction Warbun yang telah diterima digunakan sebelum implementasi.
- R-38 PASS: nama/lokasi aktual berasal dari DB; layout demo Contoh dan pagination mock QA dijelaskan.

- R-01 PASS: tanpa gradient.
- R-04 PASS: layer-group untuk lokasi, box untuk belum ditata, cash-register untuk kasir; tanpa icon generik dekoratif.
- R-06 PASS: system sans pada controls/fakta, heading retail existing; angka nomor membantu identifikasi fisik.
- R-07 PASS: grid adalah tombol etalase berdata, bukan background pattern.
- R-08 PASS: tidak menambah panah dekoratif.
- R-09 PASS: informasi lokasi/count bersumber data, tanpa badge promosi.
- R-10 PASS: tanpa glassmorphism.
- R-12 PASS: border rak dan outline pilihan mempunyai fungsi; tanpa shadow tambahan.
- R-13 PASS: tanpa glow.
- R-14 PASS: ukuran tile konsisten untuk membandingkan etalase; nomor/nama/count membentuk hierarki, product list dan editor berbeda.
- R-19 PASS: interaksi klik/focus existing, tanpa entrance animation, MOTION 1.
- R-22 PASS: tanpa ilustrasi atau raster asset baru.

- Dials PASS: ENERGY 2 / RHYTHM 3 / MOTION 1 tertulis dan mengikuti CMS existing.
- Consistency PASS: minimap, search/disclosure list, dialog fakta dan panel checkout punya komposisi sesuai tugas.
- Focal point PASS: etalase terpilih dan Add burgundy menunjukkan tujuan tiap langkah.
- Whitespace PASS: gap 10–20px dan padding 16–24px memisahkan browsing, informasi dan tindakan; screenshot ditinjau.
- Accent PASS: burgundy untuk pilihan dan CTA, ivory/charcoal untuk permukaan/data.
- Identity PASS: garis rak, angka etalase dan ledger harga melanjutkan motif retail Warbun.
- Design read PASS: direction existing dipakai dan keputusan dicatat sebelum handoff.

- C-1 PASS: minimap bernomor membantu lokasi fisik, disclosure mengurangi kepadatan form, dialog menempatkan informasi sebelum tambah.
- C-2 PASS: kontrol baru dicoba melalui browser dan validation/permission backend.
- C-3 PASS: setiap area berkaitan dengan etalase, pencarian, pemilihan atau pemrosesan barang; tidak ada section filler.
- C-4 PASS: lima lebar, keyboard, network failure, unassigned dan keranjang lintas filter teruji.
- C-5 PASS: count/location dari DB, dataset demo dan batas verifikasi disebutkan.
- R-05 PASS: overview, editor, product ledger dan checkout berbeda mengikuti pekerjaan nyata.
- R-11 PASS: tile radius 5px, panel existing dan input/control biasa; tanpa pill baru.
- R-15 PASS: label tambah, simpan lokasi, kelola etalase dan muat produk menjelaskan tindakan.
- R-16 PASS: copy operasional, tanpa buzzword.
- R-20 PASS: nomor etalase, posisi rak, SKU dan picking sesuai warung/POS Warbun.
- R-21 PASS: light theme existing dipertahankan.
- R-29 PASS: palette brand existing, selected tint burgundy dan warna feedback existing.
- R-30 PASS: mengikuti Warbun, bukan meniru dashboard produk lain.
- R-31 PASS: layout, warna, motif, typography, icon dan spacing mempunyai alasan tertulis di DESIGN_SYSTEM.md.
