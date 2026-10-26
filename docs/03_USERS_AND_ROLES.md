# 03 Pengguna dan Hak Akses
Status: FINAL

Dua peran. Pemeriksaan dilakukan di controller (`Auth::requireRole()`), bukan hanya dengan menyembunyikan tombol.

| Aksi | Admin | Operator |
|---|---|---|
| Login, ganti kata sandi sendiri | ya | ya |
| Dashboard | ya | ya |
| Lihat daftar dan detail surat | ya | ya |
| Tambah surat | ya | ya |
| Ubah surat (semua surat) | ya | ya |
| Hapus surat (soft delete) | ya | tidak |
| Lihat, cetak, ekspor laporan | ya | ya |
| Kelola pengguna | ya | tidak |

Pelanggaran hak akses menghasilkan HTTP 403 (lihat `docs/17_ERROR_HANDLING.md`). Rute per peran: `docs/09_ROUTES.md`.
