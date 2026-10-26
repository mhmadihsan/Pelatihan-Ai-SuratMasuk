# 23 Rencana Uji dan Kriteria Terima
Status: FINAL

Pelari tes: `php tests/run.php` (`docs/11_COMMANDS.md`). Berkas `tests/*Test.php` berisi fungsi `test_*`; pelari memanggil tiap fungsi, mencetak `PASS`/`FAIL`, dan mengakhiri dengan `Hasil: N lulus, M gagal` (keluar kode 1 bila M > 0). Tes DB memakai database `simasuk_test` dengan `APP_ENV=testing`.

## Tes otomatis

Suite S1–S6 yang tersedia:

| Berkas | Memverifikasi |
|---|---|
| `tests/AuthTest.php` | Migrasi dan seed idempoten; zona waktu sesi DB; hash sandi; login salah/nonaktif; penguncian wajib ganti sandi; validasi sandi lama/baru/konfirmasi; pergantian ID sesi; dashboard setelah ganti sandi; logout POST + pencabutan token; login ulang; akun dinonaktifkan; sesi idle; otorisasi Admin |
| `tests/FoundationTest.php` | CSRF, XSS/escaping, validasi input, halaman 403/404/419/500, pengalihan tamu, header keamanan, cookie sesi/produksi, display_errors mati dan galat DB generik di produksi, CSS hasil build, galat database umum dan log tanpa sandi |
| `tests/ConfigurationTest.php` | Nilai bawaan, parser `.env`, prioritas environment termasuk nilai kosong, penolakan port tidak valid |
| `tests/SuratTest.php` | Format agenda; tiga nomor berurutan dan reset tahun; rollback counter lama/baru saat INSERT gagal; empat pencatatan bersamaan; tambah, daftar, detail; peringatan/konfirmasi duplikat; validasi tanggal, panjang kolom, sifat, lampiran, dan input rusak; nilai bawaan; CSRF, XSS, tamu, serta 404 untuk surat tidak tersedia |
| `tests/SuratManagementTest.php` | Pencarian tiga kolom dan karakter literal; filter gabungan dan batas tanggal inklusif; paginasi, batas halaman dan filter pada tautan; validasi GET; Operator mengubah surat pengguna lain; validasi dan duplikat saat ubah; nomor agenda/pembuat tetap; hapus Operator 403; hapus Admin dengan CSRF dan konfirmasi browser; soft delete menyimpan waktu/pelaku; surat terhapus tidak dapat dibuka/diubah/dihapus ulang, tidak muncul dalam pencarian, dan nomor tidak dipakai ulang |

| `tests/LaporanTest.php` | Filter periode inklusif/sifat/asal literal, urutan agenda, pengecualian soft delete, akses tamu, penolakan query rusak pada ketiga endpoint; cetak mandiri dan aturan A4; CSV BOM/delimiter/CRLF/escaping/Unicode/formula injection |
| `tests/PenggunaTest.php` | Tambah/ubah pengguna, username unik dan batas sandi, sandi tidak dipantulkan, wajib ganti awal/reset termasuk sesi yang sudah login, nonaktif dan aktivasi ulang, hak akses semua endpoint, larangan mengubah diri sendiri, perubahan status/peran silang dua Admin bersamaan, dashboard hitungan periode/per sifat dan soft delete |
| `tests/BackupTest.php` | Guard pemulihan, dump selesai, pemulihan surat yang berubah dan kesamaan jumlah serta hash isi seluruh tabel; roundtrip hanya didaftarkan bila `SIMASUK_RESTORE_TEST=1` |

Verifikasi pengembangan terakhir (26 Oktober 2026): **21 lulus, 0 gagal**, dengan pemulihan diaktifkan dan MySQL terisolasi. Tanpa opt-in pemulihan, suite berisi 20 tes; hasilnya tidak membuktikan backup/restore. Build CSS, pemeriksaan sintaks PHP, dan validasi blueprint juga lulus (`0 FAIL, 0 WARN`). Lingkungan dan batas verifikasi ada di `docs/24_ROADMAP.md`; perintah ada di `docs/11_COMMANDS.md`.

T-01 sampai T-08 telah tercakup pada suite. Tahun fixture dipilih dari tahun yang belum terpakai; dashboard mengubah tanggal diterima fixture ke periode berjalan tanpa mengubah tahun agendanya. Tes pemulihan mengambil snapshot seluruh database tes sebelum mutasi dan memeriksa jumlah/hash semua tabel setelah restore. Jangan jalankan suite atau penulisan database lain bersamaan dengan pengujian restore. Cetak 80 baris juga diuji melalui Chrome headless: PDF enam halaman berukuran 842×595 pt (A4 landscape), header tabel berulang pada seluruh halaman, serta nomor agenda dan tanggal terbaca utuh. Pengujian browser interaktif, aplikasi spreadsheet, dan lingkungan MAMP tetap dicatat terpisah di `docs/25_RELEASE_CHECKLIST.md`.

| ID | Memverifikasi | Aturan |
|---|---|---|
| T-01 | `AgendaNumber::format(1, 2026)` = `001/2026`; `format(1000, 2026)` = `1000/2026` | BR-01 |
| T-02 | Tiga surat tahun 2026 mendapat urut 1, 2, 3; surat pertama 2027 mendapat urut 1 | BR-01 |
| T-03 | Insert surat yang gagal tidak menaikkan `agenda_counter` (tanpa lompat) | BR-01 |
| T-04 | Surat dihapus: tidak muncul di daftar/laporan; surat baru berikutnya tidak memakai ulang nomornya | BR-05 |
| T-05 | Validator menolak tanggal diterima < tanggal surat dan tanggal diterima di masa depan | BR-03 |
| T-06 | Ubah tanggal diterima ke tahun lain: nomor agenda tidak berubah | BR-02 |
| T-07 | `CsvWriter` mengawali sel `=1+1` dengan `'`; delimiter `;`, BOM ada | `docs/14_REPORTS_EXPORT.md` |
| T-08 | Validator: username dan kata sandi sesuai BR-07; Admin terakhir tidak bisa dinonaktifkan | BR-07, BR-09 |

## Kriteria terima per fitur (manual + perintah)
| FR | Langkah | Sinyal lulus |
|---|---|---|
| FR-01 | `curl -s -o /dev/null -w "%{http_code}" http://localhost:8888/surat` tanpa login | `302` |
| FR-01 | Login benar lalu keluar | Dashboard tampil; setelah keluar `/surat` kembali `302` |
| FR-02 | Login dengan akun seed | Dialihkan ke `/ganti-password`; `/surat` tidak bisa dibuka sebelum ganti |
| FR-03 | Tambah surat dari form | Surat muncul dengan nomor agenda `001/<tahun>`; surat kedua `002/<tahun>` |
| FR-04 | Cari kata dari perihal; saring sifat | Hanya baris cocok; halaman 2 muncul bila > `PER_PAGE` |
| FR-05 / FR-06 | Buka detail; ubah perihal | Perubahan tersimpan; nomor agenda tetap |
| FR-07 | Admin hapus; Operator mencoba POST hapus | Surat hilang dari daftar; Operator mendapat 403 |
| FR-08 / FR-09 | Atur periode, buka cetak | Baris sesuai filter; pratinjau cetak A4 landscape, header tabel berulang |
| FR-10 | Ekspor CSV, buka di Excel/Numbers | Kolom sesuai urutan di `docs/14_REPORTS_EXPORT.md`; huruf Indonesia terbaca |
| FR-11 | Admin tambah operator, reset kata sandi, nonaktifkan | Operator baru wajib ganti kata sandi; nonaktif tak bisa login; Admin tak bisa nonaktifkan diri sendiri |
| FR-12 | Buka dashboard dengan data contoh | Angka cocok dengan hitungan manual dari daftar |
