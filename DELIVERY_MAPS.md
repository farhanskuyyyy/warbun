# Titik pengiriman

Pilih Diantar di /cart atau kasir, isi alamat lengkap, lalu Pilih di peta. Ketuk/geser pin atau Gunakan lokasi saya. Keyboard: tombol arah lalu pilih pusat peta. Konfirmasi menyimpan; Batal menjaga titik sebelumnya; Hapus mengembalikan alamat tanpa pin. Alamat tertulis wajib, pin opsional. Modal pelanggan baru kasir juga mendukung pin.

Draft cart menyimpan titik saat login. Pickup tidak menyimpan koordinat pengiriman. Pelanggan menyimpan default; order/sale menyimpan snapshot mandiri. Mengubah alamat melalui CRUD menghapus default lama. Link muncul di sukses pesanan, detail/monitoring dan halaman struk kasir, tidak dicetak. Ongkir tetap tarif toko.

Leaflet1.9.4 dipin dan lazy-loaded. Default OpenStreetMap tanpa API key, attribution terlihat. [Kebijakan OSM](https://operations.osmfoundation.org/policies/tiles/): best effort tanpa SLA, bukan kapasitas unlimited; tanpa bulk/offline/prefetch. Referer/caching normal browser dipertahankan. Tidak memakai geocoder/autocomplete.

Environment opsional: MAP_TILE_URL, MAP_ATTRIBUTION (HTML administrator tepercaya), MAP_DEFAULT_LATITUDE, MAP_DEFAULT_LONGITUDE, MAP_DEFAULT_ZOOM. Default tile https://tile.openstreetmap.org/{z}/{x}/{y}.png; pusat -2.5/118/5 menampilkan Indonesia, bukan lokasi toko. GPS perlu HTTPS/localhost; alamat manual tetap tersedia saat izin/tile gagal.

Preview http://127.0.0.1:8080 memakai demo SQLite dan sudah dimigrasikan. Login admin@warbun.local / password. Tambah barang dari /shop, buka /cart dan pilih Diantar.

MySQL operasional belum dimigrasikan. Pemasangan versi ini membutuhkan php artisan migrate --force, npm ci --ignore-scripts dan npm run build serta refresh cache sesuai prosedur lingkungan. Migrasi tambahan nullable, tanpa reset/seed ulang.

[QA](DELIVERY_MAPS_QA.md).
