# QA kamera dan scanner hardware

4 Oktober 2026. **PASS untuk implementasi dan lingkungan yang diuji.** Full suite 97 tests / 799 assertions PASS pada SQLite in-memory; build production, npm ci, syntax JS, Pint perubahan dan diff check dijalankan. Preview pengguna berjalan pada 8080 dengan database demo sendiri; QA browser pada 8765 memakai database lain. `.env` dan database MySQL existing tidak diubah.

## Bukti

Update kasus scan tidak terbaca: **15 skenario kamera PASS**, termasuk barcode Code 128 berwarna biru pada bagian atas frame yang dibaca decoder production dan petunjuk setelah enam detik tanpa hasil, sementara kamera tetap berjalan. Request continuous focus diverifikasi dengan mock capability/constraint; tidak membuktikan autofocus webcam fisik. Ideal resolution ditingkatkan, tidak dijamin tersedia pada device. Build, JS syntax, scoped Pint dan diff check PASS; lima viewport tetap diuji. Baseline backend/receipt tidak diulang karena tidak ada perubahan logika server.

Screenshot pengguna 544×415 diuji melalui [runner foto](scripts/qa-camera-photo.cjs), tanpa menyimpan foto ke repository. Decoder production tanpa maupun dengan TRY_HARDER **tidak membaca gambar tersebut**. Diagnosis awal juga mencoba crop, skala dan channel luminance melalui library, tanpa hasil. Blur/perspective/kemasan adalah kemungkinan dari gambar, bukan penyebab yang sudah terbukti. Perubahan ini memperluas pencarian dan memberi bantuan; bukan klaim foto atau webcam pengguna telah berhasil dibaca.

Update mirror: **13 skenario kamera PASS** dijalankan ulang setelah perubahan, termasuk keyboard Space pada checkbox, preview horizontal terbalik tanpa request kamera baru, camera switch, pilihan tersimpan saat Retry/navigation, toggle off dan decoding Code 128/EAN-13 ketika mirror aktif. Lima lebar tetap tanpa overflow; build production, syntax JS, scoped Pint dan diff check PASS. Full backend suite 97/799 dan tujuh skenario checkout/receipt di bawah adalah baseline fitur kamera sebelumnya, tidak diulang untuk perubahan preview ini. Storage-blocked browser dan kamera fisik tidak diuji pada update ini.

- [Runner kamera](scripts/qa-camera.cjs), [hasil](docs/qa/camera-results.json): **15 skenario PASS**. Chrome menerima synthetic `MediaStream` dari canvas, dengan barcode Code 128/EAN-13 valid. Decoder ZXing production sungguhan memproses frame video; hasil decode tidak dimock. Kasus ini tidak menguji webcam fisik.
- Edit membaca Code 128 `0012345678905`, mempertahankan nol, tidak auto-save, menghentikan track lalu menyimpan lewat controller existing. Create membaca EAN-13 `5901234123457`; duplicate barcode ditolak server, barcode unique dapat disimpan. Hardware Enter pada form tidak submit.
- Izin ditolak, kamera tidak ditemukan, kamera sibuk, Retry, preferensi belakang/no audio, pilihan device, Escape, izin yang selesai setelah Close, reopen saat inisialisasi video tertunda, dan backgrounding diuji dengan mock API kamera. Semua track yang diperoleh berakhir `ended` setelah penutupan/success.
- POS memblokir kamera tanpa shift, memasukkan satu unit per pembukaan, mendukung pembukaan ulang dan scan keyboard pada lookup/keranjang yang sama. Barcode 404 menjaga quantity lama. Kamera tidak otomatis dibuka saat halaman dimuat; chunk decoder baru dimuat setelah permintaan kamera.
- Insecure context dan browser tanpa `mediaDevices` dimock; pesan menjelaskan fallback hardware/manual tanpa memanggil kamera. Tidak menguji HTTPS dari HP nyata.
- POS, create, edit, dialog dan controls diuji pada **320/375/768/1024/1440px** tanpa horizontal document overflow. Screenshot ditinjau: [mobile POS](docs/qa/screenshots/camera-pos-375.png), [mobile create](docs/qa/screenshots/camera-create-375.png), [desktop edit](docs/qa/screenshots/camera-edit-1440.png). Video putih pada screenshot adalah frame fixture kosong untuk menguji switch/close tanpa auto-detect, bukan gambar kamera fisik.
- Playwright MCP pada preview 8080 berhasil login admin, membuka create/POS, membaca controls kamera/hardware, guard shift dan etalase seeded. Tidak meminta akses kamera fisik.
- Dependency: browser 0.1.5/library 0.21.3 dipin agar sesuai Node 22. Empat package records ditambahkan (dua langsung, dua transitif); seluruh record dependency existing dibandingkan dengan HEAD dan identik. `npm ci --ignore-scripts` serta Vite 236-module build PASS. Decoder dipisahkan menjadi chunk yang dimuat ketika perlu.
- [Regresi hardware/checkout/receipt](scripts/qa-barcode.cjs): tujuh skenario PASS, termasuk scan antre, limit stok, gagal jaringan, pembayaran kurang/retry, checkout hingga struk 58/80mm serta print manual/otomatis. Total kamera dan hardware: **19 skenario browser**, tanpa page errors.
- [Panduan](CAMERA_BARCODE.md) mencatat secure-context requirement berdasarkan MDN, sumber API ZXing, scope preview dan cara penggunaan. Scanner/printer fisik, Safari/Firefox dan perangkat HP tidak diuji.

## Anti-slop delivery gate

Scope dua pilihan scan, dialog kamera, responsivitas editor produk dan tambahan icons. Direction retail Warbun existing, ENERGY 2 / RHYTHM 3 / MOTION 1.

- R-02 PASS: copy baru tanpa em dash.
- R-03 PASS: tiga halaman/dialog pada lima lebar, document overflow false.
- R-17 PASS: tidak menambah statistik promosi; pilihan camera berasal dari enumerateDevices.
- R-18 PASS: tidak ada testimonial.
- R-23 PASS: opsi kamera/hardware eksplisit diminta; icon kamera/barcode berasal dari Font Awesome existing family, fixture video QA dijelaskan.
- R-24 PASS: tidak menambah route/navigation baru; POS/create/edit nyata dibuka browser.
- R-25 PASS: memakai pasangan CTA/teks existing dari DESIGN_SYSTEM.md; tidak menambah warna teks baru. Guide video memiliki garis putih dan outline charcoal untuk posisi barcode, bukan label teks.
- R-26 PASS: hardware focus, open, device change, mirror on/off, Retry, Return, X/Escape, fill field, save/update dan POS lookup dicoba; bukan kontrol dummy.
- R-27 PASS: permission wait/scanning, denied/missing/busy, insecure/unsupported dan barcode error punya feedback. Field/cart tetap dipertahankan.
- R-28 PASS: tidak menambah FAQ.
- R-32 PASS: native dialog/buttons/select/checkbox, label, status/alert, focus-visible existing; mirror dengan Space, Escape dan focus restoration diuji.
- R-33 PASS: source aplikasi ditulis melalui apply_patch; runner hanya membuat fixtures/test results.
- R-34 PASS: light theme existing; warna gelap sebatas permukaan video sebelum frame tersedia.
- R-35 PASS: full suite, build, decoder nyata melalui synthetic video, browser create/edit/POS, validation dan responsive checks dijalankan.
- R-36 PASS: physical-camera/HP/Safari belum diuji, scope simulasi dinyatakan terbuka.
- R-37 PASS: direction yang sudah diterima dipakai, alasan desain dicatat di DESIGN_SYSTEM.md.
- R-38 PASS: fixture video dan demo preview berlabel sebagai data uji; camera selection memakai device list nyata saat runtime.

- R-01 PASS: tanpa gradient.
- R-04 PASS: ikon barcode untuk keyboard scanner dan camera untuk akses perangkat, disertai label.
- R-06 PASS: tipografi kontrol/fakta existing; heading dialog sesuai modal retail existing.
- R-07 PASS: bingkai adalah petunjuk penempatan barcode di media, bukan background pattern.
- R-08 PASS: tidak menambah panah.
- R-09 PASS: tanpa badge promosi.
- R-10 PASS: tanpa glassmorphism.
- R-12 PASS: native overlay memisahkan tugas scan; tidak menambah shadow floating.
- R-13 PASS: tanpa glow; outline guide untuk batas area video.
- R-14 PASS: tidak menambah feature cards.
- R-19 PASS: video adalah media pekerjaan; tidak menambah entrance/motion dekoratif.
- R-22 PASS: tanpa ilustrasi; feed kamera berasal dari perangkat ketika digunakan.

- Dials PASS: ENERGY 2 / RHYTHM 3 / MOTION 1 tetap eksplisit.
- Consistency PASS: product form, pilihan input, video dialog dan transaksi mengikuti tugas berbeda.
- Focal point PASS: video/guide untuk scan dan field/keranjang setelah sukses; Return sebagai tindakan utama dialog.
- Whitespace PASS: padding dialog existing 20–24px, gaps 10–20px dan form satu kolom memberi ruang pada mobile.
- Accent PASS: burgundy pada CTA, neutral pada actions lainnya.
- Identity PASS: dialog/facts typography, ivory CMS dan kontrol berlabel mengikuti Warbun.
- Design read PASS: direction existing dipakai sejak awal dan keputusan tertulis sebelum handoff.

- C-1 PASS: satu hasil per pembukaan mencegah quantity ganda; selector menangani camera berbeda; lifecycle stop mengikuti pekerjaan.
- C-2 PASS: seluruh kontrol kamera/hardware memiliki behavior yang diuji.
- C-3 PASS: area baru hanya mengurus capture, pemilihan input dan recovery.
- C-4 PASS: lima lebar, error/permission/race/visibility dan Escape teruji; platform yang belum diuji dicatat.
- C-5 PASS: hasil aktual disimpan, tidak mengklaim physical-device testing.
- R-05 PASS: struktur form serta modal berbeda sesuai input dan capture, tanpa template marketing.
- R-11 PASS: radius panel/input existing dan media 5px; tidak menambah pill.
- R-15 PASS: Scan dengan kamera, Scanner hardware, Coba lagi dan Kembali menjelaskan tindakan.
- R-16 PASS: copy operasional, tanpa buzzword.
- R-20 PASS: workflow langsung barcode produk/keranjang Warbun.
- R-21 PASS: light theme existing dipertahankan.
- R-29 PASS: palette existing; tidak menambah accent selain burgundy.
- R-30 PASS: mengikuti UI project, bukan clone dashboard lain.
- R-31 PASS: controls, guide, media, typography, spacing dan icon relevance tercatat di DESIGN_SYSTEM.md.
