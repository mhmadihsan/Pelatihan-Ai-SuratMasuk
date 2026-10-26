# 17 Penanganan Galat
Status: COLLAPSED

Mengikuti bawaan. Hanya yang spesifik proyek:
- Halaman galat di `app/Views/errors/` untuk 403, 404, 419 (token CSRF), 500, berbahasa Indonesia, tanpa detail teknis.
- Pengecualian tak tertangani: tulis ke `storage/logs/app.log` (`docs/18_LOGGING.md`), tampilkan 500.
- Validasi form gagal bukan galat: kembali ke form dengan pesan per kolom.
