# 15 Standar Kode
Status: FINAL

- Gaya PSR-12; `declare(strict_types=1);` di setiap file PHP; satu kelas per file; namespace `App\...` mengikuti path folder.
- Penamaan: istilah domain mengikuti `docs/04_DOMAIN_GLOSSARY.md` (Indonesia, snake_case di DB dan kolom; camelCase di metode); istilah teknis memakai Inggris (`Router`, `Repository`).
- SQL hanya di `app/Repositories/`; View tidak memanggil Repository; Controller tidak berisi SQL (`docs/08_ARCHITECTURE.md`).
- Setiap input POST/GET melewati `Validator`; hasil DB berupa string, ubah tipe secara eksplisit di Repository.
- Satu slice (`docs/24_ROADMAP.md`) = satu commit atau rangkaian commit kecil; pesan commit awalan `S2:` dst.
- Tidak menambah dependensi atau pustaka tanpa persetujuan; tidak ada kode untuk fitur di luar `docs/02_SCOPE.md`.
- Komentar hanya untuk "mengapa", bukan "apa".
