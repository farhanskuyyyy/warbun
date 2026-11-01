# P11 — QA menyeluruh, perbaikan langsung dan handoff

[Indeks](README.md) · Dependency P00–P10. QA juga wajib dilakukan sepanjang setiap fase.

## Perintah kandidat, bukan klaim PASS

Jalankan dari repo root setelah memeriksa environment dan dependency. Ikuti wrapper shell lokal bila diwajibkan.

```sh
php artisan route:list --json
php artisan test --filter=ShoppingFlowTest
php artisan test
vendor/bin/pint --test
npm run build
git diff --check
```

`phpunit.xml` saat handoff menunjuk SQLite `:memory:` dan mail/session/cache aman. Pastikan environment override/config cache tidak mengarah ke DB pengguna. Uji MySQL terpisah untuk transaksi, migrations, locks, concurrency; script `scripts/qa-concurrency.php` mensyaratkan nama database QA. Baca script sebelum menjalankan. Setup browser tests dan route fixtures harus mengarah ke server QA. Jangan menjalankan `composer setup` tanpa audit karena mengandung migration.

Scripts existing: `qa-browser.cjs`, `qa-ui.cjs`, `qa-shopping.cjs`, `qa-catalog-view.cjs`, `qa-address-book.cjs`, `qa-delivery-maps.cjs`, `qa-order-monitor.cjs`, `qa-status-colors.cjs`, `qa-shelves.cjs`, `qa-barcode.cjs`, `qa-camera.cjs`, `qa-concurrency.php`, `qa-migration.php`. Baca parameter/env/dependency masing-masing; jangan menebak CLI atau menyatakan script masih cocok setelah DOM berubah. Update selector berbasis role/label/test-id yang stabil, jangan melonggarkan assertion agar test lulus.

## QA coverage wajib

- [ ] Semua baris page inventory mempunyai bukti UI, fungsi dan QA atau alasan non-page yang terverifikasi. Re-run route inventory; tidak ada page baru tertinggal. API endpoint diuji melalui flow dan server tests.
- [ ] Role matrix guest/customer/cashier/manager/owner/super-admin sesuai role aktual: allowed + denied routes/actions; own-resource isolation dan hidden menu. Role yang tidak ada pada seed jangan diklaim diuji.
- [ ] Seluruh halaman ditinjau visual pada 390×844, 834×1112, 1440×1000 (customer boleh1440×960), light/dark. Untuk halaman adaptasi CRUD, review tiap layout unik dan semua halaman untuk content/wrapping/action, tidak hanya Products.
- [ ] Sweep 320/375/768/1024/1280px, portrait/landscape dan resize kontinu pada shell/form/table/POS/monitor/grid. Tidak ada horizontal page overflow; tabel/grid boleh scroll terlokalisasi dan jelas.
- [ ] Screenshot implementasi dibanding frame final/preview, benar-benar dibuka; catat route/frame/viewport/theme dan beda yang berpengaruh. Perbaiki spacing, logo/icon alignment, typography, missing images, clipping, dropdown keluar layar, subtotal melayang, sticky footer menutup konten.
- [ ] Keyboard Tab/Shift-Tab/Enter/Space/Escape, fokus modal dan pengembaliannya, focus-visible, label/error association, screen reader smoke bila tool tersedia, zoom200%, reduced motion, contrast teks/control/status light/dark. Target WCAG AA untuk pemeriksaan relevan, jangan klaim compliant dari screenshot saja.
- [ ] Long product/user/order names, nominal besar, 0/1/banyak item, no-image, archived products, empty search, validation error, loading, permission denied, expired session, slow network/offline/error/retry. Tidak ada stale response atau kehilangan draft tanpa penjelasan.
- [ ] ID/EN locale, tanggal/timezone Asia/Jakarta, currency dan tabular alignment; locale persisted sesuai existing.
- [ ] Devtools console dan failed network ditinjau; tidak ada uncaught error, request loop, listener ganda, 404 asset atau broken href. Image dimensions/lazy loading dan motion tidak menyebabkan layout shift mengganggu; ukur performa sebelum/sesudah dengan tool tersedia, jangan membuat skor.
- [ ] Uji browser Chromium dan WebKit/Firefox jika tersedia; laporkan OS/browser aktual. Safari iOS, camera hardware, GPS nyata dan printer tidak dianggap lolos hanya karena emulasi desktop.

## Skenario end-to-end minimum

| ID | Skenario dan bukti yang harus ada |
| --- | --- |
| E01 | Guest browse → picker → cart → login → cart terjaga → pickup checkout → pending/success sesuai server → history/struk sendiri |
| E02 | Delivery free/paid/outside/unknown pin; batas tepat 5/20km; ubah alamat/channel saat quote loading; total akhir benar |
| E03 | QRIS/wallet/manual supported channel; fee/promo fixture; tampered/stale quote rejected; tidak mengklaim live payment |
| E04 | Double-submit/timeout retry checkout; stok terakhir dua pelanggan; satu mutation/receipt valid sesuai hasil transaksi |
| E05 | History/receipt setelah harga/policy/alamat berubah; reorder item habis/archived; own-resource denial |
| E06 | Cashier open shift → scan/search → cash sale → receipt → history → close shift; expected cash reconcile |
| E07 | POS debt/partial/noncash yang didukung; permission/limit/tender errors; refund kembali sesuai ledger, bukan delete sale |
| E08 | Monitor pending→confirmed→preparing→ready→completed; unpaid blocked; cancel/refund rules; concurrent refresh/action |
| E09 | CRUD→stock-in/out→opname stale/approve→reports→audit; mutation konsisten, role restricted |
| E10 | Shelf drag/resize/numeric edit→save→reload→click rack products; overlap/conflict/network failure mempertahankan data |
| E11 | Semua sidebar + auth/profile/settings/report pages, deep-link/back/refresh, modal/keyboard/theme/locale |
| E12 | Legacy database migration→old receipt readable→new order snapshot; additive schema rollback strategy diuji tanpa kehilangan histori |

## Loop bug → fix → retest

1. Catat ID, severity, route, role, viewport/theme, langkah reproduksi, expected/actual dan bukti di STATUS atau issue table QA.
2. Perbaiki langsung dalam scope. P0: keamanan/data/uang rusak; P1: alur utama gagal; P2: fungsi/layout/accessibility mengganggu; P3: polish kecil. Jangan menunda P0–P2 demi commit kosmetik.
3. Tambahkan regression test pada perilaku yang berisiko; untuk visual, screenshot ulang dan cek interaksi terkait. Uji area adjacent yang memakai komponen sama.
4. Commit `fix(scope): ...`, catat hash dan hasil retest. Ulangi sampai tidak ada bug penting; bug minor juga diperbaiki bila memungkinkan, jangan otomatis jadi backlog.
5. Bila butuh kredensial eksternal/hardware/keputusan bisnis, tandai BLOCKED dengan reproduksi dan langkah berikutnya. Lanjut bagian independen; jangan mengklaim full QA selesai.

## Bukti dan penyimpanan

Perbarui `QA_REPORT.md` dengan bagian revamp final bertanggal; simpan detail per fase di `docs/implementation/qa/` bila panjang. Catat command, exit/status, jumlah test aktual, environment, DB jenis, screenshot path, frame target, findings/fix/retest. File screenshot terpilih cukup; bulk artefak boleh lokal/CI dan diabaikan Git. Jangan menggandakan ratusan export desain yang sudah sengaja dihapus.

Template record:

```text
Phase/task:
Commit tested:
Route/role/theme/viewport:
Environment and isolated DB:
Command or manual steps:
Expected / actual:
PASS / FAIL / BLOCKED / NOT RUN:
Evidence path:
Bug ID / fix commit / retest result:
Remaining limitation:
```

## Exit final dan handoff

- [ ] P00–P10 dan seluruh halaman selesai, tidak ada mock action yang tampak aktif.
- [ ] Semua E01–E12 yang berlaku lulus; tidak ada P0/P1/P2 terbuka. Keterbatasan external dicatat dan status akhir dibedakan dari fully verified.
- [ ] Backend suite, build, lint terarah, isolated MySQL checks dan browser suite lulus; kegagalan existing tidak disembunyikan.
- [ ] README cara running, DESIGN_SYSTEM, architecture/migration notes dan tasklist diperbarui sesuai implementasi nyata.
- [ ] Catat env key baru tanpa value rahasia, langkah migrasi/rollback, kebijakan fee/radius, fitur yang memerlukan config dan manual QA hardware yang tersisa.
- [ ] Riwayat commit rapi per fitur/fase, working tree review, tidak ada secret/artefak besar/data pengguna ikut.
- [ ] Ringkasan akhir: cakupan selesai, bukti pengujian, bug diperbaiki, commit, limitasi dan kesiapan review. Jangan deploy/push otomatis.

Commit penutup contoh `docs(qa): record final Warbun revamp verification`. Commit ini hanya setelah bukti aktual tersedia; dokumen rencana ini bukan hasil QA runtime.
