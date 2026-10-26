# 12 Konfigurasi
Status: FINAL

Dibaca dari `.env` (parser kecil di `config/config.php`); variabel lingkungan sebenarnya menimpa `.env`. `.env` tidak di-commit; `.env.example` di-commit dengan nilai di bawah.

| Variabel | Nilai bawaan | Keterangan |
|---|---|---|
| `APP_NAME` | `SIMasuk` | |
| `APP_ENV` | `local` | `local`, `testing`, `production` |
| `APP_URL` | `http://localhost:8888` | tanpa sub-path (document root = `public/`) |
| `APP_TIMEZONE` | `Asia/Makassar` | dipakai PHP dan sesi MySQL (+08:00) |
| `DB_HOST` | `127.0.0.1` | jangan `localhost` (lihat `docs/16_DEBUGGING_GUIDE.md`) |
| `DB_PORT` | `8889` | bawaan MySQL MAMP |
| `DB_NAME` | `simasuk` | tes memakai `simasuk_test` |
| `DB_USER` / `DB_PASS` | `root` / `root` | bawaan MAMP; ganti di produksi |
| `INSTANSI_NAMA` | `NAMA INSTANSI` | kop laporan cetak |
| `INSTANSI_ALAMAT` | `Alamat instansi` | kop laporan cetak |
| `PER_PAGE` | `20` | baris per halaman daftar surat |
| `SESSION_IDLE_MINUTES` | `120` | batas tidak aktif |
| `SEED_ADMIN_USERNAME` | `admin` | hanya dipakai `database/seed.php` |
| `SEED_ADMIN_PASSWORD` | `GantiSaya123` | hanya seed; wajib diganti saat login pertama |
