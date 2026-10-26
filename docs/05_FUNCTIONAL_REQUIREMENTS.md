# 05 Kebutuhan Fungsional
Status: FINAL

Daftar fitur. Aturan rinci di `docs/06_BUSINESS_RULES.md`; cara verifikasi di `docs/23_TEST_PLAN.md`; rute di `docs/09_ROUTES.md`.

| ID | Sebagai | Saya ingin | Slice |
|---|---|---|---|
| FR-01 | Pengguna | login dan logout | S1 |
| FR-02 | Pengguna | dipaksa mengganti kata sandi awal/hasil reset sebelum memakai aplikasi | S1 |
| FR-03 | Operator/Admin | mencatat surat masuk baru dan mendapat nomor agenda otomatis | S2 |
| FR-04 | Operator/Admin | melihat daftar surat berhalaman, mencari, dan menyaring (sifat, rentang tanggal diterima) | S3 |
| FR-05 | Operator/Admin | melihat detail satu surat | S2 |
| FR-06 | Operator/Admin | mengubah data surat | S3 |
| FR-07 | Admin | menghapus surat (soft delete) | S3 |
| FR-08 | Operator/Admin | melihat laporan per periode dengan filter tanggal, sifat, asal surat | S4 |
| FR-09 | Operator/Admin | mencetak laporan/buku agenda ke A4 dari browser | S4 |
| FR-10 | Operator/Admin | mengekspor laporan ke CSV | S4 |
| FR-11 | Admin | menambah, mengubah, mereset kata sandi, dan menonaktifkan pengguna | S5 |
| FR-12 | Pengguna | melihat dashboard ringkas (jumlah surat bulan ini, tahun ini, per sifat, 5 surat terbaru) | S5 |
