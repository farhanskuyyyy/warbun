# QA buku alamat

6 Oktober 2026. **PASS untuk lingkungan development yang diuji.**

- Full backend: **113 tests / 929 assertions**, SQLite in-memory. Sepuluh test buku alamat memeriksa multi-address, default/edit/delete/promotion, owner isolation, auth/permission/inactive, validation/owner injection, online/POS snapshot dan retry, stale/deleted selection, pickup, backfill/seeder idempotence serta penerima dengan nomor telepon berbeda dari akun dan alamat panjang.
- [Browser buku alamat](scripts/qa-address-book.cjs), [hasil](docs/qa/address-book-results.json): **10 kelompok PASS** tanpa page errors. Guest/login/cart, kosong, add/edit/delete/default, nested map, pindah tujuan, draft, checkout online/POS, delayed response saat pindah customer, save/load failure/retry dan validation retention diuji.
- [Regresi peta](scripts/qa-delivery-maps.cjs), [hasil](docs/qa/delivery-maps-results.json): **8 kelompok PASS** pada layout baru. Lazy import, GPS explicit, cancel/focus, keyboard pan/pusat, drag/remove, denied/timeout/unavailable/insecure/late GPS, tile retry, new customer dan POS receipt/monitor teruji.
- Chrome: cart/POS/Alamat saya, dialog alamat dan peta pada **320×568, 375×812, 768×900, 1024×900, 1440×1000, 812×375**. Tidak ada overflow dokumen; X/Batal/Simpan dan footer peta terlihat; Escape/focus restoration diuji.
- [Cart mobile](docs/qa/screenshots/address-cart-selected-375.png), [buku alamat](docs/qa/screenshots/address-book-page-1440.png), [modal mobile](docs/qa/screenshots/address-cart-375.png), [POS mobile](docs/qa/screenshots/address-pos-selected-375.png) ditinjau. Nama/alamat QA adalah fixture, tile bertuliskan Synthetic QA tile sintetis. GPS dimock; tidak mengirim traffic otomasi ke OSM.
- Vite production build, JS syntax, scoped Pint, Blade compilation dan diff check PASS. Tidak menambah dependency.
- Preview8080 hanya migration tambahan terhadap demo terpisah; QA8765 database disposable berbeda. MySQL operasional tidak disentuh. Migration versi ini pada MySQL, perangkat GPS fisik dan Safari/Firefox belum diuji. Client 15-second deadline ditinjau pada source; browser menguji failed response dan delayed customer swap, bukan elapsed timeout 15 detik.

## Anti-slop delivery gate

Scope buku alamat, modal dan integrasi checkout/POS; bukan audit ulang seluruh project. Direction existing yang diterima: ENERGY2/RHYTHM3/MOTION1.

- R-01 PASS: Tanpa gradient.
- R-02 PASS: Tanpa emoji UI.
- R-03 PASS: Copy alamat operasional.
- R-04 PASS: Truck terkait pengiriman, pen/trash untuk CRUD dari sprite existing.
- R-05 PASS: Kartu alamat berisi tujuan; modal/input/summary sesuai tugas.
- R-06 PASS: Heading Georgia/controls sans mengikuti retail.
- R-07 PASS: Tile fixture berlabel, tanpa pattern dekoratif.
- R-08 PASS: Tanpa panah dekoratif.
- R-09 PASS: Label utama merupakan fakta default.
- R-10 PASS: Tanpa glassmorphism.
- R-11 PASS: Radius kartu6/modal8/controls existing.
- R-12 PASS: Native dialog memisahkan tugas.
- R-13 PASS: Tanpa glow.
- R-14 PASS: Kartu mewakili record pelanggan nyata saat runtime.
- R-15 PASS: CTA Tambah/Simpan/Pilih menyebut tindakan.
- R-16 PASS: Tanpa buzzword.
- R-17 PASS: Tanpa metrik promosi.
- R-18 PASS: Tanpa testimonial.
- R-19 PASS: Tanpa motion dekoratif.
- R-20 PASS: Alamat mendukung pengantaran Warbun.
- R-21 PASS: Light theme existing.
- R-22 PASS: Tanpa ilustrasi generik.
- R-23 PASS: Screenshot berisi fixture berlabel dalam laporan.
- R-24 PASS: Routes/links benar dan authorization diuji.
- R-25 PASS: Palette existing, text charcoal/wine pada putih/ivory.
- R-26 PASS: Add/edit/delete/default/select/retry/map teruji.
- R-27 PASS: Empty/loading/save/validation/network feedback tersedia.
- R-28 PASS: Tanpa FAQ.
- R-29 PASS: Neutral dan satu accent burgundy.
- R-30 PASS: Direction project existing.
- R-31 PASS: Tujuan visual dicatat di DESIGN_SYSTEM.md.
- R-32 PASS: Labels/live status/alerts/44px/native dialog, keyboard/Escape/focus.
- R-33 PASS: Source melalui apply_patch.
- R-34 PASS: Tidak menambah theme toggle.
- R-35 PASS: Backend/build/browser nyata dijalankan.
- R-36 PASS: Batas simulated/physical dicatat.
- R-37 PASS: Direction diterima; dials eksplisit.
- R-38 PASS: QA fiktif dibatasi fixture, alamat legacy dari data existing.

- Dials PASS: ENERGY2/RHYTHM3/MOTION1.
- Consistency PASS: buku alamat, form/modal dan checkout mempunyai fungsi berbeda.
- Focal point PASS: pilihan tujuan dan Simpan di modal.
- Whitespace PASS: padding20px/gap12–20px/body scroll/footer tetap.
- Accent PASS: CTA/default/selected outline burgundy.
- Identity PASS: typography/kontrol retail Warbun.
- Design read PASS: direction accepted existing dipakai sejak awal.
- C-1 PASS: tujuan visual operasional tercatat.
- C-2 PASS: semua kontrol punya behavior.
- C-3 PASS: tanpa section pengisi.
- C-4 PASS: matrix/recovery/keyboard diuji; batas platform dicatat.
- C-5 PASS: bukti aktual dan fixture dinyatakan.
