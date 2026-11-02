# P02–P05: customer experience

[Indeks](README.md) · Implemented; [actual QA scope and external limits](qa/FINAL-QA.md) · Semua fase mengikuti DoD dan commit per fitur/fase. Source utama `CustomerFrontController`, `CustomerAddressController`, `resources/views/customer/`, layout customer dan modul JS terkait.

## P02 — Shell, landing, auth dan profil

Dependency P01. Acuan native landing, navbar auth, typography; auth/profile yang tidak punya frame spesifik adalah adaptasi pola final.

- [x] Navbar desktop/mobile: logo, link aktif, cart Font Awesome, login saat guest, user icon/account dropdown saat login; role CMS hanya untuk yang berhak. Dropdown tetap di viewport, bisa ditutup keyboard/outside click, fokus kembali.
- [x] Bottom navigation rounded dan active item jelas; icon Beranda center. Cart dock dan nav tidak saling menimpa.
- [x] Landing story-led final, CTA ke katalog, kategori dan produk data nyata, fulfillment info. Tidak membuat promo/testimonial/metrik palsu atau menu tidak berfungsi.
- [x] Motion hero opsional memakai opacity/translate ringan dari spesifikasi (sekitar 12px, 550ms), tidak menghambat baca/klik. `prefers-reduced-motion` menampilkan final state langsung; tanpa JS konten tetap terlihat. Tidak replay agresif saat back navigation.
- [x] Seluruh enam layar auth: login, register, forgot/reset password, verify email, confirm password; profil termasuk password/delete dan error/success. Pertahankan redirect tujuan checkout dan cart setelah login.
- [x] Footer, language switch, logout POST/CSRF, flash message dan session expired tetap benar. Link langsung dan refresh tidak mengandalkan state dari landing.

QA: auth regression suite/ProfileTest, guest/customer/staff navbar, dropdown kanan pada 320–1440px, ID/EN, light/dark, reduced-motion, koneksi lambat, login kembali ke checkout. Exit: navigasi dan auth benar-benar bekerja. Commit `feat(storefront): apply final shell landing and account flows`.

## P03 — Katalog, product picker dan keranjang overlay

Dependency P02. Acuan pages 5166/5614 dan customer catalog. Source `catalog.js`, `cart.js`, `customer/shop`, `customer/product`, `customer/cart`.

- [x] Katalog search/filter/kategori dan pagination/view modes existing tetap bekerja; thumbnail aspect ratio stabil, fallback gambar, nama panjang dan status stok.
- [x] Klik produk membuka modal/sheet detail, jumlah, harga dan opsi yang memang ada di model. Jangan mengarang varian produk baru. Tombol tambah nonaktif jika tidak tersedia; validasi min/max/jumlah integer.
- [x] `/shop/{product}` tetap bisa direct-link; implement history/back dan fallback tanpa memaksa semua navigasi menjadi SPA.
- [x] Klik keranjang membuka bottom sheet mobile / modal atau panel desktop sesuai final; perubahan qty/hapus/subtotal sinkron dengan picker, badge dan cart dock.
- [x] Cart dock hanya muncul jika ada item, menampilkan jumlah/total dan CTA jelas. Sisakan ruang konten/safe area agar tidak menutup produk, bottom nav atau tombol.
- [x] Persist cart memakai kontrak existing, aman terhadap data rusak, refresh dan login; jangan mengganti harga server dengan nilai localStorage. Empty/cart error/stock changed states tersedia.
- [x] Checkout CTA mengarah ke langkah existing; `/cart` tetap fallback yang berfungsi. Hindari sheet bertumpuk saat picker dibuka dari cart.

QA: ShoppingFlowTest dan scripts shopping/catalog yang relevan setelah memeriksa target DB; tambah/hapus hingga kosong, duplicate item, qty invalid, stok berubah, refresh/back, harga besar dan nama panjang. Exit: tidak ada cart bercabang atau total stale. Commit per catalog/picker/cart jika perlu.

## P04 — Delivery, fee policy, payment accordion dan checkout

Dependency P03. Acuan pages 10082/20103 dan payment accordion. Baca ADDRESS_BOOK.md, DELIVERY_MAPS.md, fee/radius handoff, `OrderService`, `PaymentService`, `app/Payments/`, money helper existing sebelum patch.

### Kontrak backend sebelum UI

- [x] Audit quote/checkout/snapshot saat ini. Tulis kontrak request/response nyata dan migration plan sebelum mengubah nominal; reuse transaksi/idempotency existing.
- [x] Tambahkan konfigurasi pin toko, radius bands dan fee channel dengan default aman dan validation. Settings CMS diselesaikan pada P07; jangan hardcode tarif demo sebagai kebijakan provider live.
- [x] Radius dihitung server dari koordinat valid, jarak garis lurus bukan jalan. Band `<5000m` gratis, `5000 <= d <20000m` Rp10.000, `>=20000m` blocked. Bandingkan jarak belum dibulatkan; pickup gratis tanpa pin.
- [x] Missing pin, permission GPS ditolak, map gagal, alamat teks tanpa koordinat: jelaskan perbaikan, jangan default gratis. Alamat tersimpan harus dibuktikan milik customer atau scope POS yang sah.
- [x] Quote memuat authoritative line prices/availability, subtotal, diskon, ongkir, payment promo, fee basis/rate/amount, grand total, policy version dan masa berlaku/identitas quote yang dapat diverifikasi. Client tidak menentukan nilai final.
- [x] Recompute/validate saat checkout. Bila harga/stok/alamat/channel/policy berubah, tampilkan total baru untuk konfirmasi; jangan diam-diam charge berbeda. Cegah stale async response menimpa quote terbaru.
- [x] Snapshot order/payment menyimpan nilai uang dan kebijakan yang dipakai, fulfillment/address/distance; legacy receipt tetap bisa dibaca tanpa mengarang fee lama. Additive migration, kompatibilitas dan test upgrade.

### Kalkulasi dan kebijakan yang belum final

- [x] Implement fee configurable, integer/decimal money sesuai project, rounding eksplisit. Fixture QRIS 0.7% × (net merchandise + shipping, sebelum fee); e-wallet flat Rp2.000. VA/card contoh Rp0 bukan tarif nyata. Cash Rp0.
- [x] Pisahkan diskon barang, promo pembayaran, customer service fee dan potongan settlement merchant. Potongan merchant tidak otomatis dibebankan pelanggan.
- [x] Fixture: barang Rp25.000 + ongkir Rp10.000 → QRIS Rp245, total Rp35.245; wallet total Rp37.000. Gratis ongkir → QRIS Rp175, total Rp25.175; wallet total Rp27.000.
- [x] Persen/flat promo configurable dengan basis, eligible methods, cap dan rounding. Contoh struk desain: Rp25.000 − Rp2.000 − 5%×Rp23.000 = Rp21.850, atau minus promo flat Rp1.000 = Rp22.000. Jangan aktivasi promo fiktif.
- [x] Jika policy nyata belum tersedia, gunakan fixture QA dan mode existing/manual; catat keputusan yang diperlukan sebelum live. Jangan mengklaim tarif atau adapter sudah production-ready.

### UI dan alur

- [x] Pilih pickup/delivery, address book CRUD/default/pin, quote loading/error/expired/retry dan outside-radius alternative pickup.
- [x] Accordion tipe E-wallet/VA/QRIS/Card/Cash hanya menampilkan metode yang backend mendukung. Channel BRI/BCA/GoPay/dll berdasarkan config; logo dan fee terlihat per channel, pilihan satu channel jelas.
- [x] Subtotal/discount/shipping/fee/total dalam summary yang terikat layout, tidak melayang jauh dari CTA. Summary desktop sticky bila ruang cukup, mobile aman dari keyboard/nav.
- [x] Tombol lanjut disabled dengan alasan bila quote/channel/alamat invalid. Submit locking + idempotency server; retry setelah timeout tidak membuat order/payment ganda.
- [x] Integrasikan manual/signed-webhook existing; UI success hanya setelah server memastikan status. Jangan mengubah pending menjadi paid via tombol frontend atau demo receipt.

QA wajib: batas 0/4999/5000/19999/20000/20001m; rounding money; tampered quote/address/fee; expired quote; double submit/retry; stok terakhir concurrent; unknown channel; webhook replay/signature/amount; snapshot tetap sama setelah harga/policy/alamat berubah. SQLite + MySQL isolated untuk lock/concurrency. Exit: UI dan charge memakai satu sumber total yang konsisten. Pisah commit policy/quote, migrations dan UI jika fitur masing-masing dapat diverifikasi.

## P05 — Pesanan, detail, struk dan pesan lagi

Dependency P04. Acuan page 8360, fees receipts. Source `customer/history`, `customer/order-success`, order relationships dan receipt code.

- [x] Kartu pesanan dengan produk/gambar/jumlah, order ref/date, fulfillment, status order + payment, total; semua pesanan punya Lihat struk.
- [x] Modal/sheet struk menampilkan item dan rincian diskon/promo/ongkir/fee/total dari snapshot. Pending memakai Belum dibayar/Total tagihan, bukan bukti lunas.
- [x] Detail/success route aman direct-link dan refresh; ownership server mencegah mengakses pesanan customer lain. Jika perlu endpoint receipt baru, dokumentasikan route dan test authorization.
- [x] Pesan lagi untuk completed membuka review harga/stok saat ini; jelaskan unavailable/changed price, qty dapat disesuaikan, merge cart secara eksplisit tanpa otomatis checkout.
- [x] Empty/loading/error/pagination dan konten banyak; historical product dihapus tetap tampil dari snapshot.
- [x] Layout 1/2/3 kolom sesuai ruang (acuan <768, 768–1199, >=1200), minimum konten tetap terbaca; jangan memaksa 3 kolom jika container sempit.

QA: customer A/B isolation, histori sebelum migrasi, receipt totals, reorder stock change, receipt scroll/close/print bila didukung, enam viewport/theme combinations. Exit: riwayat dan struk tetap benar setelah perubahan katalog/policy. Commit `feat(orders): add visual history receipts and reorder review`.
