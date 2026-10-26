# 22 Cadangan dan Pemulihan
Status: FINAL

Data penting seluruhnya ada di database MySQL. Berkas aplikasi bisa dibangun ulang dari git.

| Tujuan | Perintah | Sinyal lulus |
|---|---|---|
| Cadangkan | `mysqldump -h 127.0.0.1 -P 8889 -u root -p --single-transaction simasuk > cadangan_$(date +%F).sql` | berkas terbentuk, ukuran > 0, akhir berkas berisi `Dump completed` |
| Pulihkan | `mysql -h 127.0.0.1 -P 8889 -u root -p simasuk < cadangan_YYYY-MM-DD.sql` | tanpa galat; jumlah baris `surat_masuk` sama dengan sebelum cadangan |

Aturan: cadangkan sebelum setiap migrasi di data nyata dan secara berkala (usulan: harian di jam kerja), simpan salinan di luar komputer yang sama, dan uji pemulihan ke database `simasuk_test` sebelum rilis (`docs/25_RELEASE_CHECKLIST.md`). Perintah `mysqldump` dan `mysql` ada di folder `bin` MySQL milik MAMP.

## Verifikasi pengembangan

Pada 26 Oktober 2026, `tests/BackupTest.php` berhasil membuat dump, mengubah surat fixture, memulihkan ke `simasuk_test`, dan membandingkan jumlah serta hash isi seluruh tabel (termasuk pengguna, counter, dan migrasi). Pengujian memakai MySQL Homebrew 9.7 terisolasi, bukan data operasional. Pelaksanaan membutuhkan opt-in dan akses eksklusif database tes; perintah ada di `docs/11_COMMANDS.md`. Bila pemulihan gagal, dump tes dipertahankan di direktori sementara privat untuk pemulihan ulang.

Cadangan terotomasi tersedia melalui `scripts/backup.php`; memakai konfigurasi aplikasi, menolak menimpa file, dan membuat file mode 0600. Pemulihan programatis dilindungi agar hanya berjalan dengan lingkungan testing, database `simasuk_test`, dan opt-in eksplisit. Pemulihan data operasional tetap mengikuti prosedur di atas. Cadangan dan pemulihan data target MAMP masih harus diperiksa sebelum rilis; status ada di `docs/25_RELEASE_CHECKLIST.md`.
