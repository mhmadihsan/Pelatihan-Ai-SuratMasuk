# 18 Pencatatan Log
Status: COLLAPSED

Mengikuti bawaan. Satu berkas `storage/logs/app.log`, satu baris per kejadian: waktu, level, pesan, user_id bila ada. Dicatat: pengecualian tak tertangani dan login gagal. Tidak dicatat: kata sandi, token, isi form. Jejak audit perubahan data tidak ada di versi 1 (`docs/02_SCOPE.md`); jejak minimum hanya `dibuat_oleh` dan `dihapus_oleh`/`dihapus_pada` (`docs/07_DATA_MODEL.md`).
