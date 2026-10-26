# 11 Perintah
Status: FINAL

Satu-satunya rumah perintah. Jalankan dari root proyek. Pastikan `php -v` ≥ 8.2 (tambahkan folder `bin` PHP milik MAMP ke `PATH`; lihat `docs/16_DEBUGGING_GUIDE.md` bila versi beda).

| Tujuan | Perintah | Sinyal lulus |
|---|---|---|
| Siapkan konfigurasi lokal | `cp .env.example .env` (hanya bila `.env` belum ada), lalu sesuaikan koneksi dengan `docs/12_CONFIGURATION.md` | `.env` tersedia dan tidak masuk git |
| Unduh Tailwind CLI (Apple Silicon) | `mkdir -p bin && curl -sL -o bin/tailwindcss https://github.com/tailwindlabs/tailwindcss/releases/latest/download/tailwindcss-macos-arm64 && chmod +x bin/tailwindcss` | `bin/tailwindcss --help` menampilkan bantuan |
| Unduh Tailwind CLI (Intel) | sama, ganti `macos-arm64` dengan `macos-x64` | sama |
| Build CSS | `bin/tailwindcss -i resources/css/app.css -o public/assets/app.css --minify` | file `public/assets/app.css` terbentuk, tanpa galat |
| Build CSS (pantau) | `bin/tailwindcss -i resources/css/app.css -o public/assets/app.css --watch` | proses tetap berjalan |
| Buat database | `mysql -h 127.0.0.1 -P 8889 -u root -p -e "CREATE DATABASE simasuk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"` | tanpa galat |
| Buat database tes | `mysql -h 127.0.0.1 -P 8889 -u root -p -e "CREATE DATABASE simasuk_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"` | tanpa galat |
| Migrasi | `php database/migrate.php` | menampilkan `OK` per migrasi baru; keluar kode 0 |
| Seed Admin awal | `php database/seed.php` | menampilkan `Admin dibuat` atau `Admin sudah ada` |
| Server pengembangan tanpa Apache | `php -S 127.0.0.1:8888 -t public` | `/login` terbuka; untuk MAMP, document root tetap `public/` |
| Cek sintaks PHP | `find app config database public tests -name '*.php' -print0 \| xargs -0 -n1 php -l` | tidak ada baris selain `No syntax errors detected` |
| Jalankan tes | `php tests/run.php` | baris akhir `Hasil: N lulus, 0 gagal`, keluar kode 0 |
| Validasi blueprint | `bash scripts/validate.sh` | `[FAIL]` = 0 |
| Cadangan lewat konfigurasi aplikasi | `php scripts/backup.php /path/privat/cadangan-baru.sql` | file baru mode 0600, keluaran `Cadangan selesai.`; file yang sudah ada ditolak |
| Tes lengkap termasuk pemulihan | `SIMASUK_RESTORE_TEST=1 php tests/run.php` | `21 lulus, 0 gagal`; hanya `simasuk_test`, tidak ada proses penulis lain |
| Cadangkan database | lihat `docs/22_BACKUP_RESTORE.md` | — |

Port 8889 dan akun `root` adalah bawaan MAMP; sesuaikan dengan `.env` (`docs/12_CONFIGURATION.md`). Untuk database tes: `DB_NAME=simasuk_test php database/migrate.php`.

Pelari tes menetapkan `APP_ENV=testing` dan `DB_NAME=simasuk_test`, serta menolak `DB_NAME` eksplisit selain itu. Database tes harus sudah dibuat; suite menjalankan migrasi dan membersihkan akun serta surat fixture yang dibuatnya sendiri. Tes surat memakai tahun yang belum terpakai dan membersihkan counter tahun fixture bila tidak ada surat tersisa. Tes HTTP membuka server PHP lokal di port sementara; koneksi lokal dan `proc_open` harus diizinkan. Tes penomoran bersamaan menjalankan empat proses PHP pada database tes yang sama.

Klien `mysql` dan `mysqldump` harus ada di `PATH` untuk cadangan/pemulihan. Jika perlu, tentukan `MYSQL_BIN_DIR` ke direktori bin MySQL MAMP/Homebrew. Kredensial dibaca dari konfigurasi aplikasi dan diteruskan melalui option file sementara privat, kemudian dihapus; kata sandi tidak dimasukkan ke argumen proses. Simpan cadangan di direktori privat di luar `public/`. `storage/backups/` diabaikan Git.

Perintah suite lengkap pada lingkungan verifikasi pengembangan (MySQL terisolasi yang sudah berjalan di port 13379): `DB_PORT=13379 DB_PASS='' MYSQL_BIN_DIR=/opt/homebrew/opt/mysql/bin SIMASUK_RESTORE_TEST=1 php tests/run.php`. Port ini hanya lingkungan uji; bukan perubahan konfigurasi bawaan aplikasi.
