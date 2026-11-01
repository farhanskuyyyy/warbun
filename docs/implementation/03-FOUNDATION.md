# P00–P01: baseline dan fondasi

[Indeks](README.md) · Terapkan [DoD setiap fase](00-WORKING-AGREEMENT.md).

## P00 — Baseline dan lingkungan aman

- [ ] Baca manifest/lockfile, routes, controllers/services, view components, desain final, tasklist dan QA existing. Verifikasi branch/dirty tree; jangan mengubah `.DS_Store` pengguna.
- [ ] Inventaris semua GET page dan child flows, JSON endpoints, aksi mutasi dan permission menggunakan `php artisan route:list --json`. Bandingkan dengan PAGE-COVERAGE; tambahkan route yang muncul setelah handoff.
- [ ] Tandai fungsi existing, gap visual dan gap backend per modul. Contoh temuan saat rencana dibuat: customer quote masih memakai ongkir flat dari setting; radius/fee prototype belum merupakan implementasi.
- [ ] Siapkan `.env` QA terpisah tanpa menampilkan secrets, DB disposable, session/cache/mail aman, user per role dan katalog fixture termasuk stok nol, nama panjang, gambar hilang.
- [ ] Baca scripts QA sebelum menjalankan: beberapa mengisi/mengubah data. Pastikan URL/server browser menunjuk DB QA, bukan server operasional pengguna.
- [ ] Jalankan baseline backend, build, formatting check dan smoke browser. Catat kegagalan existing sebagai issue terpisah dengan reproduksi; baseline lama di ARCHITECTURE/QA_REPORT bukan hasil saat ini.
- [ ] Simpan route/view inventory, baseline hasil dan keputusan di STATUS/QA report. Buat branch implementasi bila belum ada.

Exit: aplikasi dapat dijalankan dan diuji terisolasi, cakupan route lengkap, masalah baseline teridentifikasi. Jika runtime gagal, perbaiki penyebab minimum dulu; jangan lanjut kosmetik di atas aplikasi yang tidak dapat diakses.

Commit: `chore(ui): record final design implementation baseline`.

## P01 — Token dan reusable components

Sumber: `resources/css/app.css`, `resources/js/app.js`, `resources/views/components/`, `layouts/`, native foundations dan CMS components. Dependency: P00.

- [ ] Petakan color, typography, spacing, border, radius, elevation, z-index, container dan breakpoint ke sistem styling existing; catat mapping di DESIGN_SYSTEM.md, jangan duplikasi CSS per halaman.
- [ ] Logo lowercase, Georgia heading, system-ui body dan tabular numerals pada harga. Uji fallback OS dan format mata uang existing.
- [ ] Konsolidasikan button/link/icon-button, input/select/textarea/password, checkbox/radio, error/helper, loading, pagination, empty state, feedback, badge status dan table/card mobile.
- [ ] Overlay foundation: modal, drawer, bottom sheet, confirmation, focus trap/return, Escape, backdrop, body scroll lock, `aria-labelledby`, inert background yang sesuai; nested overlay tidak meninggalkan scroll lock.
- [ ] Modal header/footer tetap terlihat, body scroll, safe-area mobile, keyboard virtual tidak menutup CTA/input. Tidak ada container overflow yang memotong account dropdown.
- [ ] Theme light/dark persisten tanpa flash besar; respect pilihan existing/system saat belum memilih. Active navigation memakai lebih dari warna, icon benar-benar center.
- [ ] Komponen status menerima domain/value, unknown status punya fallback netral; jangan menganggap order.ready berarti payment.paid.
- [ ] Label tombol ikon, keyboard focus terlihat, target sentuh minimal nyaman, error terhubung ke field; toast bukan satu-satunya bukti error/payment.
- [ ] Pastikan ID/EN dan teks panjang tidak merusak komponen. Hindari menambah library hanya untuk tooltip atau sheet sederhana.

QA: buka component examples atau halaman representatif; uji dialog dengan keyboard dan mobile, perubahan tema + refresh, teks panjang, zoom 200%. Test behavior shared JS/Blade yang berisiko. Build harus lulus. Jika komponen shared memengaruhi halaman lama, smoke semua shell sebelum commit.

Exit: fondasi dapat dipakai tanpa menghilangkan perilaku existing; shared component bukan mockup inert. Commit contoh: `feat(ui): introduce Warbun tokens and accessible components`.
