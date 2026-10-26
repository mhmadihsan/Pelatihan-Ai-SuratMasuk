# 04 Istilah Domain
Status: FINAL

Pakai istilah ini persis di UI, kode domain, dan dokumen.

| Istilah | Arti |
|---|---|
| Surat masuk | Surat dari pihak luar yang diterima instansi dan dicatat di SIMasuk |
| Nomor agenda | Nomor urut pencatatan internal, format `NNN/YYYY`; aturan di `docs/06_BUSINESS_RULES.md` |
| Nomor surat | Nomor yang tertulis pada surat dari pengirim |
| Asal surat | Instansi atau pihak pengirim |
| Tanggal surat | Tanggal yang tertera pada surat |
| Tanggal diterima | Tanggal surat diterima instansi; dasar tahun nomor agenda dan filter laporan |
| Sifat | Tingkat urgensi: **Biasa**, **Penting**, **Segera**, **Rahasia** |
| Buku agenda | Daftar surat masuk berurutan nomor agenda untuk satu periode |
| Periode | Rentang tanggal diterima (dari-sampai, inklusif) |
| Soft delete | Surat ditandai terhapus, datanya tetap ada di database |
| Admin / Operator | Dua peran pengguna; hak akses di `docs/03_USERS_AND_ROLES.md` |
