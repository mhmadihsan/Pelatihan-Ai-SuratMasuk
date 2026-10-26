# 10 UI/UX
Status: FINAL

- Bahasa antarmuka: Indonesia. Format tanggal tampil `dd/mm/yyyy`; input memakai `<input type="date">`.
- Layout: bilah navigasi atas (Dashboard, Surat, Laporan, Pengguna [hanya Admin], nama pengguna + Keluar). Konten di tengah, lebar maksimum `max-w-6xl`.
- Warna: dasar `slate`, aksen `blue-700` untuk tombol utama, `red-600` untuk aksi hapus.
- Lencana sifat: Biasa = `slate`, Penting = `amber`, Segera = `red`, Rahasia = `purple`.
- Form: galat tampil di bawah tiap kolom, isian lama dipertahankan; pesan sukses/gagal tampil sebagai flash di atas konten.
- Daftar surat: tabel berhalaman (`PER_PAGE` di `docs/12_CONFIGURATION.md`), kolom No. Agenda, No. Surat, Tgl Diterima, Asal Surat, Perihal, Sifat, Aksi. Urutan: terbaru di atas (tahun_agenda, nomor_urut menurun).
- Hapus: dialog `confirm()` sebelum POST.
- Halaman cetak laporan: aturan di `docs/14_REPORTS_EXPORT.md`.
- Kelas Tailwind hanya dipakai di `app/Views/`; build dan perintahnya di `docs/11_COMMANDS.md`.
