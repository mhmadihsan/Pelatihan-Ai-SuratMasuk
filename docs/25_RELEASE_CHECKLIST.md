# 25 Daftar Periksa Rilis
Status: FINAL

Blok rilis: satu saja yang gagal = jangan rilis. Perintah ada di `docs/11_COMMANDS.md`.

| # | Pemeriksaan | Sinyal lulus |
|---|---|---|
| 1 | Cek sintaks PHP | tidak ada galat |
| 2 | `php tests/run.php` | `0 gagal`, kode keluar 0 |
| 3 | `bash scripts/validate.sh` | `[FAIL]` = 0 |
| 4 | Build CSS terbaru | `public/assets/app.css` lebih baru dari perubahan di `app/Views/` |
| 5 | Kriteria terima FR-01…FR-12 | semua lulus (`docs/23_TEST_PLAN.md`) |
| 6 | Akun seed sudah diganti kata sandinya; kata sandi seed tidak dipakai lagi | login dengan kata sandi seed gagal |
| 7 | `.env`: `APP_ENV` sesuai target, kredensial DB bukan bawaan bila produksi | diperiksa manual |
| 8 | `.env` dan `bin/tailwindcss` tidak ada di git | `git status` / `git ls-files` tidak menampilkannya |
| 9 | Cadangan dibuat dan pemulihan diuji ke `simasuk_test` | jumlah baris `surat_masuk` sama (`docs/22_BACKUP_RESTORE.md`) |
| 10 | `display_errors` mati di produksi | URL ngawur menampilkan halaman 404 biasa, bukan jejak PHP |

## Hasil pengembangan — 26 Oktober 2026

**Belum boleh dinyatakan lulus rilis.** Kolom di bawah membedakan bukti pada database uji terisolasi dari pemeriksaan instalasi target. Lingkungan: PHP Herd 8.4, MySQL Homebrew 9.7, server HTTP PHP sementara. MAMP belum terpasang.

| # | Hasil | Bukti / yang masih diperlukan |
|---|---|---|
| 1 | Lulus pengembangan | Seluruh file PHP aplikasi, konfigurasi, migrasi/seed, public, scripts, dan tes lolos lint |
| 2 | Lulus pengembangan | Suite lengkap termasuk pemulihan: `21 lulus, 0 gagal` |
| 3 | Lulus | Validator blueprint `0 FAIL, 0 WARN` |
| 4 | Lulus | Tailwind dibangun ulang setelah seluruh perubahan View |
| 5 | Sebagian; verifikasi target tertunda | FR-01…FR-12 tercakup tes HTTP/repository; cetak 80 baris menjadi enam halaman A4 landscape diperiksa di Chrome, termasuk header tabel berulang. Uji interaksi keyboard, pembukaan CSV di Excel/Numbers, dan kriteria terima pada MAMP belum dijalankan |
| 6 | Lulus pada akun fixture; target tertunda | Tes seed membuktikan sandi lama ditolak setelah diganti. Akun seed instalasi nyata belum disiapkan/diperiksa |
| 7 | Tertunda | `.env` target dan kredensial operasional belum tersedia |
| 8 | Sebagian | `.gitignore` mengabaikan `.env`, binary Tailwind, dan cadangan. Direktori belum menjadi repositori Git sehingga status tracked belum bisa dibuktikan |
| 9 | Lulus pengembangan; target tertunda | Dump dan restore ke `simasuk_test` menghasilkan jumlah/hash sama untuk seluruh tabel. Cadangan data operasional MAMP belum diuji |
| 10 | Lulus pengembangan; target tertunda | Pengaturan display_errors produksi dan halaman galat generik diperiksa otomatis; server target belum diverifikasi |

S4 dan S5 selesai diimplementasikan. Pekerjaan teknis S6 selesai dan telah diuji pada lingkungan pengembangan; penutupan S6 sebagai gerbang rilis menunggu pemeriksaan target di atas. Tidak ada data operasional yang diubah untuk mengisi checklist ini.
