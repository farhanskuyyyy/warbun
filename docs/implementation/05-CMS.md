# P06–P07: CMS shell dan seluruh halaman pendukung

[Indeks](README.md) · Implemented; [actual QA scope and external limits](qa/FINAL-QA.md) · Scope route lengkap ada di [coverage](02-PAGE-COVERAGE.md). Jangan menganggap sidebar lengkap berarti halaman selesai.

## P06 — CMS shell dan dashboard

Dependency P01; urutan default setelah P05. Acuan pages 20599/20600/24044/24045.

- [x] Layout app dan workspace navigation: navbar seperti web existing, konteks/breadcrumb di kiri, shortcut toko, moon/sun theme, account di kanan. Dropdown profile memakai icon dan tetap dalam viewport.
- [x] Sidebar seluruh 29 destinasi dari CMS handoff, termasuk lima report detail dan debt dashboard; active state mencakup child routes. Tidak menghilangkan menu hanya karena belum ada frame khusus.
- [x] Filter menu berdasarkan permission aktual, tetap validasi route server. Profile di account menu, logout POST, locale tetap berfungsi.
- [x] Desktop >=1200 persistent sidebar 264px; compact 88px bila diimplementasikan sesuai desain; 600–1199 drawer, <600 mobile drawer. Sidebar dan content scroll terpisah tanpa scroll trap.
- [x] Breadcrumb, page title/actions, filter/search toolbar, table/card, pagination, form, destructive confirmation, feedback dan modal mengikuti shared components.
- [x] Dashboard menampilkan angka operasional nyata dan actionable links: sales, receivable, low-stock, active shift dan recent sales sesuai query/permission existing. Jangan salin snapshot demo dari Figcraft.
- [x] Empty/loading/error/zero metrics, label periode dan scope role jelas; status badge konsisten. Tidak menambah KPI/chart yang tidak didukung data.

QA: 29 navigasi owner dan subset role lain, deep-link/refresh, semua dropdown di light/dark, drawer keyboard, sidebar panjang, chart/data kosong jika ada. Regression PageSmokeTest dan permission tests. Exit: shell reusable dan dashboard real-data; tidak ada link dummy. Commit shell dan dashboard boleh terpisah.

## P07 — CRUD, stok, pelanggan dan pengaturan

Dependency P06. Acuan CMS Products frames untuk pola list/detail/form/confirm. Halaman berikut **adaptasi** desain final menggunakan fungsi existing; tidak perlu menggambar semua ulang sebelum coding.

- [x] Products index/create/show/edit, gambar/barcode, harga/modal/stok/shelf/status, validation, filter/search/pagination, archive/delete dengan konsekuensi jelas. Tidak mengubah cara initial stock dicatat.
- [x] Categories, product-types, brands, units, suppliers: index/create/edit dan aksi delete sesuai route/controller. Tangani relasi yang menghalangi penghapusan. Units edit saat ini perlu dicocokkan dengan template yang dipakai controller, jangan mengarang view yang belum ada.
- [x] Inventory: saldo/movement, stock-in/out/adjust dialogs, alasan dan permission; angka negatif/oversell ditolak server. Stock opname: create/count/review/approve termasuk stale expected quantity.
- [x] Customers index/create/show/edit, saldo/piutang, address book/default/pin, history dan status aktif. Scope customer/staff tetap sesuai.
- [x] Users & roles: form pengguna, role/permission editor, validation dan proteksi privilege escalation. Jangan mengandalkan checkbox disabled sebagai keamanan.
- [x] Store settings: identitas, tema jika existing, payment instructions, delivery pin/radius dan fee policies dari P04. Form konfigurasi baru mengikuti pola CMS; tidak semua provider live otomatis diaktifkan.
- [x] Settings policy money: label basis fee/promo, flat/persen, preview simulasi yang ditandai contoh, validasi nonnegative/cap/radius ranges; perubahan memakai permission/settings audit dan policy version. Tidak merewrite transaksi lama.
- [x] Semua list/form: light/dark, ID/EN, field errors, delete confirm, saving/retry, empty, search no results dan backend 403/419/422 state. Return URL/filter setelah save tetap masuk akal.
- [x] Tabel tablet memakai scroll horizontal terlokalisasi; mobile cards mempertahankan identitas/harga/status/action. Jangan menyembunyikan field penting demi muat.

QA: CRUD valid/invalid, duplicate identifiers, relation-bound deletion, pagination after delete, file upload error, permission/CSRF, stok/audit consistency, stale opname, user role restrictions. Gunakan existing OperationsTest/AddressBookTest/DeliveryPointTest dan tambahan regression sesuai perubahan.

Exit: semua route modul pada coverage memiliki checklist UI + fungsi + QA; bukan hanya halaman Products contoh yang selesai. Commit per domain, misalnya `feat(catalog): apply final CRUD layouts`, `feat(inventory): update stock workflows`, `feat(settings): expose delivery and payment policies`.
