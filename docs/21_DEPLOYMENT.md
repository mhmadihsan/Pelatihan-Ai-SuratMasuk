# 21 Deployment
Status: FINAL

## Lokal (macOS + MAMP) — target versi 1
1. Pasang MAMP; nyalakan Apache dan MySQL.
2. Di MAMP > Preferences > Server, setel Document Root ke folder `public/` proyek. Hasilnya `APP_URL=http://localhost:8888`.
3. Salin `.env.example` menjadi `.env`, sesuaikan (`docs/12_CONFIGURATION.md`).
4. Unduh Tailwind CLI, build CSS, buat database, migrasi, seed (`docs/11_COMMANDS.md`).
5. Buka `APP_URL`, login dengan akun seed, ganti kata sandi.

## Pemindahan ke server lain
Belum diputuskan. Bila nanti dipindah, syarat minimum: PHP ≥ 8.2 dengan `pdo_mysql` dan `mbstring`, MySQL 8, Apache/Nginx dengan document root `public/`, `APP_ENV=production`, HTTPS, folder `storage/logs/` dapat ditulis, kredensial DB bukan bawaan MAMP. Ubah dokumen ini bersama `docs/12_CONFIGURATION.md` saat keputusan diambil.
