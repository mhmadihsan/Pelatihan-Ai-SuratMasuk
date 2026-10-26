# 24 Peta Jalan
Status: FINAL

Vertical slice: tiap slice berjalan ujung ke ujung dan bisa didemokan. Kerjakan berurutan; satu slice selesai setelah kriteria terimanya lulus (`docs/23_TEST_PLAN.md`) dan `bash scripts/validate.sh` bersih.

| Slice | Isi | FR | Demo |
|---|---|---|---|
| S1 Fondasi + login | Struktur folder, bootstrap, Router, Database, migrasi + seed, layout Tailwind, login/logout, ganti kata sandi, CSRF, halaman galat | FR-01, FR-02 | Login dengan akun seed, dipaksa ganti kata sandi, tiba di dashboard kosong |
| S2 Catat surat | Form tambah, nomor agenda otomatis, detail, daftar dasar | FR-03, FR-05 | Catat 3 surat, nomor 001-003 |
| S3 Kelola surat | Pencarian, filter, paginasi, ubah, hapus (Admin) | FR-04, FR-06, FR-07 | Cari, ubah, hapus; nomor tidak dipakai ulang |
| S4 Laporan | Pratinjau, cetak A4, ekspor CSV | FR-08, FR-09, FR-10 | Buku agenda bulan ini tercetak dan terekspor |
| S5 Pengguna + dashboard | Kelola pengguna, dashboard ringkas | FR-11, FR-12 | Admin menambah operator yang bisa mencatat surat |
| S6 Pengerasan | Tes otomatis lengkap, cadangan/pemulihan teruji, daftar periksa rilis | — | `docs/25_RELEASE_CHECKLIST.md` semua lulus |

## Catatan implementasi S1–S6

S1 telah diimplementasikan: fondasi MVC, migrasi dan seed, layout Tailwind, autentikasi, ganti kata sandi wajib, CSRF, dan halaman galat.

S2 telah diimplementasikan: form tambah surat, validasi kolom dan tanggal, peringatan serta konfirmasi duplikat, nomor agenda otomatis dalam satu transaksi, daftar dasar, dan detail surat. Navigasi Surat sudah tersedia.

S3 telah diimplementasikan: pencarian nomor surat/asal surat/perihal, filter sifat dan rentang tanggal diterima, paginasi dengan filter yang dipertahankan, ubah surat oleh Admin maupun Operator, dan soft delete khusus Admin dengan konfirmasi browser. Nomor agenda tetap saat surat diubah dan tidak dipakai ulang setelah hapus. Rincian suite verifikasi ada di `docs/23_TEST_PLAN.md`; perintah menjalankan aplikasi dan tes ada di `docs/11_COMMANDS.md`.

S4 telah diimplementasikan: pratinjau seluruh baris dengan filter periode/sifat/asal literal, pintasan buku agenda tahunan, cetak A4 landscape mandiri, serta CSV dengan BOM UTF-8, delimiter titik koma, CRLF, dan sanitasi formula. Surat terhapus dikecualikan.

S5 telah diimplementasikan: kelola pengguna khusus Admin (tambah, ubah, reset sandi, aktif/nonaktif), kewajiban ganti sandi awal/reset, validasi username unik, serta dashboard jumlah bulan/tahun berjalan, jumlah per sifat seluruh periode, dan lima surat terbaru. Perubahan peran/status mengunci pengguna dalam transaksi; identitas dan hak Admin pelaku diperiksa ulang setelah kunci diperoleh agar perubahan bersamaan tidak menghapus Admin aktif terakhir.

S6: pekerjaan teknis dan verifikasi pengembangan selesai, termasuk tes regresi, persaingan perubahan Admin, cadangan privat, dan pemulihan teruji dengan perbandingan seluruh tabel. **S6 belum dinyatakan selesai sebagai gerbang rilis**: kriteria rilis mensyaratkan seluruh pemeriksaan lulus. Hasil dan pemeriksaan target yang masih tertunda ada di `docs/25_RELEASE_CHECKLIST.md`.

Verifikasi pengembangan pada 26 Oktober 2026 memakai PHP Herd 8.4 dan MySQL Homebrew 9.7 terisolasi: suite lengkap `21 lulus, 0 gagal`, termasuk dump/pemulihan; CSS, sintaks PHP, dan validator blueprint lulus. Konfigurasi bawaan tetap mengikuti `docs/12_CONFIGURATION.md`. Apache MAMP/MySQL 8.0 belum diverifikasi karena MAMP tidak terpasang. Direktori kerja belum memiliki metadata Git; konfigurasi dan akun rilis nyata belum tersedia.
