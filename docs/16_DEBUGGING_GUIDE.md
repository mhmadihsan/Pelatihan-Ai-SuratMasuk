# 16 Panduan Debugging dan Jebakan
Status: FINAL

Jebakan yang bisa menyesatkan agen. Baca sebelum mendiagnosis galat lingkungan.

| Gejala | Penyebab | Tindakan |
|---|---|---|
| `SQLSTATE[HY000] [2002]` / "No such file or directory" | `DB_HOST=localhost` memakai soket Unix, sedangkan MAMP memakai TCP | Pakai `127.0.0.1` dan `DB_PORT=8889` |
| Koneksi ditolak | MySQL MAMP belum menyala atau port beda | Nyalakan server di MAMP; cek port di preferensi MAMP, samakan `.env` |
| Skrip CLI dan web memberi hasil beda | MAMP punya beberapa versi PHP; `php` di terminal bisa versi macOS lain | `php -v`; arahkan `PATH` ke PHP MAMP yang sama dengan web |
| Semua rute selain `/` 404 | `.htaccess` tidak terbaca (`AllowOverride`) atau `mod_rewrite` mati | Pastikan document root = `public/` dan `AllowOverride All` aktif |
| Kelas Tailwind tidak berefek | CSS belum di-build, atau `@source` tidak mencakup `app/Views` | Jalankan build (`docs/11_COMMANDS.md`); cek `@source` di `resources/css/app.css` |
| Nomor agenda ganda/bolong | Penomoran dibuat di luar transaksi, atau `LAST_INSERT_ID()` dibaca di koneksi lain | Ikuti algoritma di `docs/07_DATA_MODEL.md`; baca lewat `SELECT LAST_INSERT_ID()` pada koneksi yang sama |
| Jam `dibuat_pada` meleset | Zona waktu sesi MySQL tidak diset | `Database` menjalankan `SET time_zone = '+08:00'` saat konek |
| Galat tipe saat `strict_types` | Kolom angka dari PDO berupa string | Cast di Repository |

**Masalah diketahui:** Excel bisa mengubah nomor surat seperti `005/12/2026` menjadi tanggal saat membuka CSV. Tidak diperbaiki di versi 1; jangan "memperbaiki" dengan mengubah format CSV tanpa persetujuan.
