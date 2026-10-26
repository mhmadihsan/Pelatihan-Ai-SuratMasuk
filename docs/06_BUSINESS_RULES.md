# 06 Aturan Bisnis
Status: FINAL

Nama kolom dan tipe ada di `docs/07_DATA_MODEL.md`.

| ID | Aturan |
|---|---|
| BR-01 | Nomor agenda berformat `NNN/YYYY`: nomor urut diberi nol di depan sampai minimal 3 digit (`001/2026`; urut ke-1000 menjadi `1000/2026`). `YYYY` = tahun dari tanggal diterima saat surat dibuat. Urutan mulai 1 tiap tahun, naik 1, tanpa lompat. Dibuat di server dalam satu transaksi (algoritma di `docs/07_DATA_MODEL.md`). |
| BR-02 | Nomor agenda tidak pernah berubah setelah surat dibuat, termasuk bila tanggal diterima diubah ke tahun lain. |
| BR-03 | Tanggal diterima tidak boleh di masa depan dan tidak boleh lebih awal dari tanggal surat. Tanggal surat boleh di masa lalu. |
| BR-04 | Sifat hanya salah satu dari empat nilai di `docs/04_DOMAIN_GLOSSARY.md`; bawaan **Biasa**. Jumlah lampiran bilangan bulat ≥ 0, bawaan 0. |
| BR-05 | Hapus = soft delete oleh Admin. Nomor agenda surat terhapus tidak dipakai ulang. Surat terhapus tidak muncul di daftar, pencarian, laporan, atau dashboard. Tidak ada fitur memulihkan di versi 1. |
| BR-06 | Bila kombinasi nomor surat + asal surat sudah ada (surat tidak terhapus), simpan pertama ditolak dengan peringatan; simpan dilanjutkan bila form dikirim ulang dengan `konfirmasi_duplikat=1`. |
| BR-07 | Username unik, huruf kecil, 3-50 karakter (huruf, angka, titik, garis bawah). Kata sandi minimal 8 karakter, maksimal 72 byte agar tidak terpotong oleh bcrypt (`PASSWORD_DEFAULT`). |
| BR-08 | Kata sandi awal dan hasil reset menandai `wajib_ganti_password`; selama aktif, semua halaman selain ganti kata sandi dan logout dialihkan ke ganti kata sandi. Penggantian memverifikasi kata sandi saat ini, sandi baru yang berbeda, dan konfirmasi yang cocok. |
| BR-09 | Admin tidak boleh menonaktifkan atau menurunkan peran dirinya sendiri; sistem harus selalu punya minimal satu Admin aktif. |
| BR-10 | Pengguna nonaktif tidak bisa login; datanya (termasuk surat yang dibuatnya) tetap. |
