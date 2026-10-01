# Warbun

Aplikasi POS Laravel/Blade + Alpine, Vite dan Tailwind. Versi: composer.json/composer.lock serta package.json/package-lock.json. Baca tasklist.md untuk pekerjaan existing.

Area penting: routes/web.php, app/Http/Controllers/, app/Models/, database/migrations/, resources/views/, tests/. Permission menggunakan Spatie. Perubahan POS/inventory/debt/payment harus mempertahankan authorization, transaksi DB, perhitungan uang, dan konsistensi stok.

Check bila runtime/dependency tersedia: php artisan test --filter=<test>, vendor/bin/pint --test, npm run build. Baca phpunit.xml sebelum test; gunakan DB test terisolasi. Ini command kandidat, bukan hasil test yang sudah dijalankan.

## Cara kerja
Baca manifest, lockfile, README, CI, dan kode terkait sebelum patch. Ikuti stack/convention existing. Tugas kecil langsung dikerjakan; gunakan tasklist.md existing untuk pekerjaan panjang. Jangan install Boost/framework atau upgrade dependency hanya untuk memulai tugas.

Gunakan regression test untuk behavior yang berubah, check terarah, lalu review diff. Laporkan command dan hasil aktual; catat NOT RUN bila environment tidak tersedia. Jangan membuka/menyalin nilai secret ke output. Test database terisolasi; jangan menjalankan setup/migrate:fresh/seed terhadap environment existing. Deployment, push, DNS/SSL mengikuti scope yang diotorisasi.

Baca PRD.md, ARCHITECTURE.md dan tasklist.md di folder project ini. Laporan hasil verifikasi disimpan di QA_REPORT.md. Project instructions dan requirement user lebih spesifik daripada workflow global. Model/provider dipilih di runtime, tidak dipatok di file ini.
