# CLAUDE.md — SIMasuk

Aplikasi web internal administrasi surat masuk (greenfield). Ringkasan: `docs/00_EXECUTIVE_SUMMARY.md`.

## Cara bekerja
1. Baca `INDEX.md`; muat HANYA dokumen yang ditunjuk untuk task Anda. Jangan memuat semua dokumen.
2. Kerjakan satu slice pada satu waktu sesuai `docs/24_ROADMAP.md`.
3. Setelah selesai, jalankan perintah verifikasi di `docs/11_COMMANDS.md` dan kriteria terima di `docs/23_TEST_PLAN.md`; laporkan hasilnya apa adanya.

## Aturan emas
- Satu fakta, satu rumah: skema hanya di `docs/07_DATA_MODEL.md`, aturan bisnis di `docs/06_BUSINESS_RULES.md`, rute di `docs/09_ROUTES.md`, perintah di `docs/11_COMMANDS.md`, konfigurasi di `docs/12_CONFIGURATION.md`. Rujuk, jangan salin.
- Bila dokumen bertentangan dengan kode yang berjalan, laporkan; jangan menebak mana yang benar.
- Jangan membangun apa pun di luar `docs/02_SCOPE.md`; jangan menambah dependensi tanpa persetujuan.
- SQL hanya di `app/Repositories/`; semua keluaran View lewat `e()`; semua POST wajib CSRF (`docs/13_SECURITY.md`).
- Jebakan lingkungan (MAMP, port, zona waktu): baca `docs/16_DEBUGGING_GUIDE.md` sebelum mendiagnosis.
- Mengubah sebuah fakta: perbarui dokumen pemiliknya dan `docs/_MANIFEST.json`, lalu `bash scripts/validate.sh`.
