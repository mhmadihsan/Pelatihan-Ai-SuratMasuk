# 14 Laporan dan Ekspor
Status: FINAL

## Filter (GET `/laporan`, `/laporan/cetak`, `/laporan/ekspor`)
| Parameter | Arti | Bawaan |
|---|---|---|
| `dari`, `sampai` | rentang tanggal diterima, inklusif, `YYYY-MM-DD` | tanggal 1 bulan berjalan s.d. hari ini |
| `sifat` | salah satu nilai sifat; kosong = semua | semua |
| `asal` | cocok sebagian (LIKE) pada asal surat | kosong |

Tombol pintas "Buku Agenda Tahun Ini" mengisi `dari` = 1 Jan dan `sampai` = 31 Des tahun berjalan. Data: surat tidak terhapus, urut `tahun_agenda` lalu `nomor_urut` menaik. Layar `/laporan` menampilkan pratinjau seluruh baris (tanpa halaman).

## Kolom laporan (cetak dan CSV, urutan sama)
No. Agenda, No. Surat, Tgl Surat, Tgl Diterima, Asal Surat, Perihal, Sifat, Lampiran, Keterangan.

## Cetak (`/laporan/cetak`)
- Halaman mandiri tanpa navigasi; memanggil `window.print()` lewat tombol.
- `@page { size: A4 landscape; margin: 12mm; }`; header tabel diulang tiap halaman, baris tidak terpotong antarhalaman.
- Kop: `INSTANSI_NAMA` dan `INSTANSI_ALAMAT` (`docs/12_CONFIGURATION.md`), judul "BUKU AGENDA SURAT MASUK", baris periode, lalu tabel. Kaki: "Dicetak: tanggal-waktu oleh nama pengguna".
- Sifat tampil sebagai teks (tanpa warna).

## Ekspor CSV (`/laporan/ekspor`)
- Nama berkas `simasuk_laporan_{dari}_{sampai}.csv`; `Content-Type: text/csv; charset=UTF-8`.
- Pemisah `;`, akhir baris CRLF, diawali BOM UTF-8 agar Excel macOS membaca UTF-8.
- Tanggal format `YYYY-MM-DD`; sel dibungkus tanda kutip ganda bila perlu.
- Sanitasi sel: aturan CSV injection di `docs/13_SECURITY.md`.
