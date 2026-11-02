# Implementasi Warbun dari final.figcraft

Status: **implementasi dan QA software P00–P11 selesai, 2 November 2026**. [Laporan akhir dan batas verifikasi](qa/FINAL-QA.md). Disusun 1 November 2026 dari source Laravel, daftar 141 route terdaftar, dan 20 halaman native Figcraft. Checklist baru ini tidak mewarisi tanda selesai dari tasklist development lama.

## Mulai di sini — untuk agent mana pun

Implementasikan seluruh halaman customer dan CMS Warbun mengikuti [final.figcraft](../../design/figcraft/soft-neobrutalism/final.figcraft), tanpa menghilangkan fungsi aplikasi existing. Selesaikan fase berurutan, commit per fase atau fitur yang sudah diverifikasi, lalu lakukan QA menyeluruh dan **langsung perbaiki bug yang ditemukan**, uji ulang, dan catat buktinya. Jangan berhenti pada daftar rekomendasi atau screenshot statis.

Dokumen ini bermula sebagai tasklist. Implementasi sudah dijalankan; agent berikutnya harus membaca STATUS.md dan laporan QA sebelum mengulang pekerjaan. Jalankan ulang pemeriksaan yang relevan ketika ada perubahan baru.

1. Tentukan repo root dari folder yang berisi `artisan`, `composer.json`, dan `routes/web.php`. Semua path di dokumen ini relatif terhadap repo; tidak membutuhkan path Mac pembuatnya.
2. Baca `AGENTS.md`, `README.md`, `PRD.md`, `ARCHITECTURE.md`, `tasklist.md`, `DESIGN_SYSTEM.md`, serta dokumen fase. Periksa instruksi ancestor yang tersedia. Jangan menimpa pekerjaan lokal pengguna.
3. Baca [aturan kerja dan handoff](00-WORKING-AGREEMENT.md), [acuan desain](01-DESIGN-MAP.md), lalu [cakupan semua halaman](02-PAGE-COVERAGE.md).
4. Baca [status pelaksanaan](STATUS.md), lanjutkan task pertama yang belum selesai setelah memverifikasi kode dan commit sebelumnya.
5. Buka native file menggunakan Figcraft MCP jika tersedia. Tanpa MCP, gunakan preview terkurasi dan spesifikasi Markdown; laporkan bagian yang tidak dapat dilihat. Jangan mengaku membaca canvas atau menyalin pixel yang tidak terlihat.
6. Kerjakan satu fase/fitur sampai berjalan, uji, perbaiki, commit file terkait secara eksplisit, lalu perbarui status. Tidak perlu persetujuan untuk keputusan implementasi rutin dalam scope.
7. Lakukan [QA akhir dan perbaikan](07-QA-AND-RELEASE.md). Jangan menandai selesai bila masih ada bug penting, halaman hilang, atau pengujian kritis yang belum dijalankan.

## Urutan fase

| Fase | Hasil | Dependensi | Instruksi |
| --- | --- | --- | --- |
| P00 | Baseline, inventaris halaman, lingkungan QA terisolasi | — | [Fondasi](03-FOUNDATION.md) |
| P01 | Token, typography, komponen, status, accessibility dasar | P00 | [Fondasi](03-FOUNDATION.md) |
| P02 | Customer shell, landing, auth, profil | P01 | [Customer](04-CUSTOMER.md) |
| P03 | Katalog, product picker, cart bottom sheet/dock | P02 | [Customer](04-CUSTOMER.md) |
| P04 | Alamat, radius, quote, payment accordion dan checkout | P03 | [Customer](04-CUSTOMER.md) |
| P05 | Pesanan saya, detail/struk, pesan lagi | P04 | [Customer](04-CUSTOMER.md) |
| P06 | CMS shell, 29 tujuan sidebar, dashboard | P01, P05 untuk alur terpadu | [CMS](05-CMS.md) |
| P07 | Semua master data, stok, pelanggan, manajemen | P06 | [CMS](05-CMS.md) |
| P08 | POS, shift, struk, riwayat penjualan | P04, P07 | [Operasional](06-OPERATIONS.md) |
| P09 | Monitoring, pesanan, payment, refund, debt, reports, audit | P08 | [Operasional](06-OPERATIONS.md) |
| P10 | Penataan produk: grid interaktif, resize, persist layout | P07 | [Operasional](06-OPERATIONS.md) |
| P11 | QA seluruh halaman, fix loop, handoff siap review | P00–P10 | [QA](07-QA-AND-RELEASE.md) |

Jalankan berurutan secara default. Fase besar boleh memiliki beberapa commit fitur yang masing-masing utuh. Jangan menunggu semua fase selesai untuk melakukan pengujian.

## Scope dan batas

- **All page** = seluruh halaman aplikasi yang dapat dijangkau dari route sekarang, termasuk create/edit/detail, auth, profil, struk, modal dan state penting; bukan membuat halaman baru untuk setiap endpoint JSON/webhook.
- Frame khusus diimplementasikan sesuai referensi. Halaman yang belum punya frame khusus memakai shell/pola komponen yang sama dengan konten dan business rules existing. Tandai sebagai **adaptasi**, bukan desain native yang sudah tersedia.
- Backend yang sudah benar dipertahankan. Tambahkan hanya gap yang dibutuhkan desain, terutama quote fee/radius, snapshot struk dan persistence layout rak; jangan menulis ulang payment/POS/stock tanpa alasan terukur.
- Light/dark, mobile/tablet/desktop, bahasa ID/EN, permissions, keyboard dan state jaringan termasuk scope.
- Integrasi gateway live, pembayaran nyata, deploy dan push tidak otomatis termasuk. Gunakan adapter existing/sandbox untuk QA. Tarif contoh bukan kebijakan produksi yang sudah disetujui.

## Prompt untuk agent penerus

> Baca `docs/implementation/README.md` dan seluruh dokumen yang dirujuk fase aktif di project Warbun. Jalankan implementasi dari `design/figcraft/soft-neobrutalism/final.figcraft` untuk semua halaman sesuai phase plan. Verifikasi kondisi repo terlebih dahulu; jangan menganggap checklist lama sebagai bukti. Pertahankan stack Laravel/Blade/Alpine dan business rules existing. Commit per fase atau fitur setelah pemeriksaan lulus, perbarui STATUS.md dan bukti QA. Setelah semua fase, lakukan QA fungsi, UI/UX, responsive, accessibility dan integritas data; langsung perbaiki bug, uji ulang sampai tidak ada masalah penting. Laporkan blocker aktual dan NOT RUN dengan jujur; jangan klaim selesai jika masih ada gap. Jangan push/deploy tanpa instruksi.
