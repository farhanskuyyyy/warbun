# Peta desain dan prioritas referensi

[Indeks](README.md) · [Preview terkurasi](../../design/figcraft/PREVIEWS.md)

File kanonis: [final.figcraft](../../design/figcraft/soft-neobrutalism/final.figcraft). SHA-256 saat handoff: `1c7609637fda9179509da2c5f74cf563adc0551739bbb5ee9d3b809c659ba1ba`. File tidak diubah saat menyusun tasklist. Jika berubah kemudian, inspect ulang dan catat versi; hash bukan larangan revisi.

## Prioritas

Permintaan terbaru pengguna → revisi final native + handoff terbaru → pola reusable final → source fungsi existing. Jangan memilih frame eksplorasi atau backup sebagai tampilan utama. Desain tidak boleh mengalahkan authorization dan integritas transaksi. Konflik business rule harus dicatat dan diselesaikan, bukan diubah diam-diam.

Native memiliki 20 halaman, namun penomoran nama tidak berurutan karena beberapa light/dark memakai nomor sama. Semua halaman adalah referensi desain; bukan 20 route aplikasi.

| Page ID | Isi | Fase |
| --- | --- | --- |
| 1 / 654 | Customer mobile light/dark | P02–P05 |
| 422 / 1075 | Customer desktop light/dark | P02–P05 |
| 581 / 1234 | Foundations/states light/dark | P01 |
| 1343 | Payment accordion states | P04 |
| 2968 | Payment channel logos | P04 |
| 4608 | Landing alternatives dan motion | P02; gunakan final, bukan alternatif |
| 5166 | Cart overlays dan handoff | P03 |
| 5614 | Product picker overlays | P03 |
| 7106 | Navbar auth states | P02 |
| 8360 | Orders, receipts, responsive | P05 |
| 10082 | Fee/radius | P04–P05 |
| 20103 | Quote fee/shipping variants | P04 |
| 20599 | CMS layout/components | P06–P09 |
| 20600 | CMS route coverage | Semua CMS |
| 24044 | Dashboard/POS/shelves/monitor | P06/P08/P09/P10 |
| 24045 | CMS states/handoff/statuses | Semua CMS |
| 32521 | Grid rak editor/view | P10; menggantikan entry numbered-list lama |

## Frame awal yang mudah dicari

| Layar | Desktop light/dark | Tablet light/dark | Mobile light/dark |
| --- | --- | --- | --- |
| Landing | 423 / 1076 | Adaptasi | 2 / 655 |
| Payment entry | 522 / 1175 | Page 20103 | 154 / 807 |
| Pesanan saya | 557 / 1210 | 8361 / 8395 | 205 / 858 |
| CMS Products shell | 20805 / 21053 | 21315 / 21470 | 21625 / 21715 |
| Dashboard | 24046 / 24297 | 24562 / 24721 | 24880 / 24970 |
| POS | 25060 / 25311 | 25576 / 25735 | 25894 / 25984 |
| Shelves view | 26074 / 26325 | 26590 / 26749 | 26908 / 26998 |
| Order monitor | 27088 / 27339 | 27604 / 27763 | 27922 / 28012 |
| Shelves edit | 33762 / 33936 | 34110 / 34180 | 34250 / 34316 |

Status boards: 31607/31701. Shelf handoff: 43014. Detail state ID lainnya ada di dokumen masing-masing. Ukuran acuan customer desktop 1440×960, CMS 1440×1000, tablet 834×1112, mobile 390×844. Tidak boleh menerapkan seluruh UI sebagai canvas fixed pixel.

## Fondasi yang dipertahankan

| Token | Light | Dark |
| --- | --- | --- |
| Brand primary | #8B1E2D | Adaptasi contrast dari native |
| Secondary | #236B65 | #75C9BD |
| Background | #F8F6F1 | #171717 |
| Surface | #FFFFFF | #242321 |
| Text | #282724 | #F8F6F1 |
| Muted | #68635C | #B8B1A6 |

Georgia headings; system-ui body, controls, harga; logo lowercase weight 800 dan tracking sekitar -0.065em mengikuti source web/native. Jangan meng-copy font sistem Apple. Verifikasi font fallback Windows/Linux dan line wrap. Native hero sekitar 39px mobile/64px desktop, line-height 108%; bukan ukuran wajib bagi semua heading. Inspect komponen sebelum mapping tokens spacing/radius/border/shadow.

Font Awesome lokal dipakai konsisten; logo channel memakai aset berlisensi di payment-logos dengan SOURCES.md. Placeholder produk adalah fallback, ganti dengan gambar katalog jika tersedia. Jangan hotlink semua foto ke placehold.co saat runtime atau menyertakan seluruh desain sebagai gambar aplikasi.

Status: pending/unpaid amber; confirmed blue; preparing purple; ready teal; completed/paid/active green; partial/debt/low-stock orange; failed/cancelled/out-stock red; refunded/expired/inactive/unassigned slate. Selalu label teks, bukan warna saja. Status order dan payment adalah konsep berbeda.

## Handoff topik yang harus dibaca

- [Typography](../../design/figcraft/soft-neobrutalism/TYPOGRAPHY_QA.md), [wordmark](../../design/figcraft/soft-neobrutalism/WORDMARK_QA.md), [landing/motion](../../design/figcraft/soft-neobrutalism/LANDING_QA.md).
- [Cart](../../design/figcraft/soft-neobrutalism/CART_OVERLAY_QA.md), [picker](../../design/figcraft/soft-neobrutalism/PRODUCT_PICKER_QA.md), [navbar auth](../../design/figcraft/soft-neobrutalism/NAVBAR_AUTH_QA.md).
- [Orders/receipt](../../design/figcraft/soft-neobrutalism/orders/README.md), [fee/radius](../../design/figcraft/soft-neobrutalism/fees-radius/README.md).
- [CMS shell/menu](../../design/figcraft/soft-neobrutalism/cms/README.md), [operations](../../design/figcraft/soft-neobrutalism/cms-operations/README.md), [status](../../design/figcraft/soft-neobrutalism/cms-operations/STATUS_COLORS.md), [grid rak](../../design/figcraft/soft-neobrutalism/shelf-grid/README.md).

Dokumen QA desain adalah bukti review **desain**, bukan bukti aplikasi berfungsi. Bulk PNG/PDF lama sengaja dihapus; jangan menganggap nama export historis masih ada. Native tetap lengkap, 21 preview terpilih tersedia. Dark/tablet/state lain dapat dirender dari native jika tools tersedia.
