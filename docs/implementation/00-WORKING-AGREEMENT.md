# Aturan pelaksanaan dan perpindahan agent

[Indeks](README.md)

## Konteks yang harus dibawa

Warbun melayani pelanggan warung, kasir, manager dan owner. Customer perlu menemukan produk, mengatur jumlah/alamat, mengetahui total, membayar dan memeriksa pesanan. CMS perlu mempercepat operasional tanpa merusak stok, saldo, shift, histori dan audit.

Stack saat audit: Laravel 13 (`composer.json` meminta PHP ^8.3), Blade, Alpine, Vite, Tailwind, Spatie Permission, MySQL. Versi instalasi mengikuti lockfiles, bukan angka tebakan atau update terbaru. `package.json` memiliki beberapa dependency Tailwind lintas versi; inspect konfigurasi aktual, jangan sekalian migrasi framework. Tidak perlu React/SPA atau design library baru untuk meniru Figcraft.

Source bisnis: `app/Services/`, controllers, models, migrations, permission middleware/policies. Source tampilan: `resources/views/`, `resources/css/app.css`, `resources/js/`. Modul JS existing termasuk cart, catalog, checkout, pos, receipt, address-book, delivery-map dan barcode-camera.

## Keputusan yang sudah ditetapkan

- Soft neobrutalism Warbun: burgundy + teal, Georgia headings, system-ui body, logo lowercase existing. Jangan kembali ke desain generik atau mengganti identitas.
- Cart dan product picker memakai overlay; route direct-link tetap berfungsi sebagai fallback.
- CMS mempertahankan semua navigasi, permission dan data existing.
- Radius geografis: `<5 km` gratis; `>=5 dan <20 km` Rp10.000; `>=20 km` tidak tersedia. Pickup gratis. Detail pada P04.
- QA adalah pekerjaan implementer: temukan → perbaiki → uji ulang. Jangan hanya menyerahkan daftar bug.

## Ketika tools/skills berbeda

Baca product-design-sop, ui-ux-pro-max/promax dan antislop jika benar-benar tersedia. Pada penyusunan rencana, product-design-sop dan antislop tersedia; ui-ux-pro-max tidak diklaim digunakan. Ketidaktersediaan skill tidak menghentikan implementasi: workflow mandiri dalam paket ini adalah fallback. Jangan install skill/framework tanpa kebutuhan.

Periksa kemampuan MCP, jangan menebak tool atau kemampuan export. `.figcraft` adalah sumber desain, bukan kode aplikasi. Native prototype hanya menyimulasikan beberapa interaksi. Bila tidak bisa membaca file, pakai [preview](../../design/figcraft/PREVIEWS.md) dan dokumen handoff; catat ketidakpastian visual dan selesaikan bagian yang dapat diverifikasi. Jangan mengedit file native hanya demi implementasi.

Workspace pembuat memakai `rtk` sebagai prefix shell sesuai instruksi lokal; agent lain mengikuti instruksi environment sendiri. Command dokumentasi ditulis sebagai command dasar agar portabel, tambahkan wrapper jika diwajibkan. Tidak ada dependency pada subscription Codex, memorinya, atau agent ID sebelumnya.

## Perlindungan fungsi dan data

- Validasi harga, stok, permission, ownership, fee, diskon, radius dan perubahan status di server. Hidden button bukan authorization.
- Pertahankan transaksi DB, urutan locking, idempotency, money helper existing, ledger dan audit. Jangan memakai floating point untuk nominal uang.
- Jangan menjalankan `migrate:fresh`, seeder, destructive setup atau QA penjualan terhadap database pengguna. PHPUnit saat audit memakai SQLite in-memory; verifikasi effective config sebelum eksekusi. Browser QA dan concurrency harus memakai database disposable tersendiri.
- Additive migrations, null/default kompatibel untuk histori lama, strategi backfill dan rollback tanpa kehilangan transaksi. Test upgrade pada fixture legacy, bukan hanya database kosong.
- Jangan log secret, password, session, token gateway atau payload pelanggan ke artefak publik.
- Nilai demo, harga contoh, layout rak contoh dan logo payment bukan bukti provider tersedia/live. Katalog dan metrik runtime harus berasal dari data aplikasi.

## Commit per fase atau fitur — wajib

1. Cek `git status` dan branch. Jangan memasukkan `.DS_Store`, `.env`, database, vendor, node_modules, screenshot massal atau perubahan pengguna yang tidak terkait.
2. Buat/pakai branch implementasi yang disepakati, contoh `feat/warbun-final-ui`. Jika branch yang relevan sudah ada, lanjutkan; jangan reset/rebase pekerjaan orang lain.
3. Satu commit memuat fitur utuh beserta test dan dokumentasi relevan. Fase besar dipecah misalnya `feat(checkout): add authoritative delivery quotes` lalu `feat(checkout): implement payment accordion`.
4. Jalankan pemeriksaan yang sesuai, review staged diff, `git diff --cached --check`, lalu commit dengan scope jelas. Hindari `git add .`.
5. Catat hash setelah commit di STATUS.md pada commit dokumentasi berikutnya atau awal fase berikutnya; jangan mencoba memasukkan hash commit ke dalam commit itu sendiri.
6. Bug QA diselesaikan lewat commit `fix(scope): ...` disertai regression evidence. Jangan squash/amend history published tanpa instruksi. Push/deploy memerlukan instruksi tersendiri.

Contoh: `chore(ui): record revamp baseline`, `feat(ui): add Warbun shared components`, `feat(storefront): add cart sheet`, `feat(cms): apply workspace shell`, `feat(shelves): persist editable grid`, `fix(checkout): reject stale delivery quote`, `docs(qa): record final revamp verification`.

## Definition of done setiap fase

- Semua task fase selesai atau blocker terinci; semua halaman cakupan diperbarui.
- UI terhubung ke backend/data, interaksi tidak dummy, loading/empty/error/validation/retry relevan tersedia.
- Review visual light/dark di 390, 834, 1440 px; resize tambahan sesuai QA. Screenshot benar-benar dibuka dan diperiksa.
- Test terarah lulus; build dan pemeriksaan perubahan sesuai scope lulus; regression terkait tidak memburuk.
- Status, keputusan, command/hasil, bukti, commit dan residual risk tercatat. Test yang tidak jalan ditulis NOT RUN, tidak dianggap PASS.

## Handoff saat sesi terputus

Simpan di STATUS.md: branch/HEAD, fase/task terakhir, file berubah, test aktual, bug/blocker, keputusan yang belum jelas, next command aman. Agent berikutnya memeriksa ulang dirty tree dan hasil test, tidak mengandalkan percakapan sebelumnya. Task selesai ditandai hanya setelah exit criteria terpenuhi. Pekerjaan parsial tidak boleh diberi label complete.
