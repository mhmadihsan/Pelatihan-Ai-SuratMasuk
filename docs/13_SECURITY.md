# 13 Keamanan
Status: FINAL

| Area | Aturan |
|---|---|
| Kata sandi | `password_hash(..., PASSWORD_DEFAULT)` dan `password_verify`; minimal 8 karakter (BR-07 di `docs/06_BUSINESS_RULES.md`). Jangan pernah mencatat kata sandi ke log. |
| Sesi | `session_regenerate_id(true)` saat login; cookie `HttpOnly`, `SameSite=Lax` (`Secure` bila `APP_ENV=production`); sesi berakhir setelah `SESSION_IDLE_MINUTES` tanpa aktivitas; logout menghancurkan sesi. |
| CSRF | Token acak per sesi di semua form POST, diverifikasi `hash_equals` oleh middleware; gagal = HTTP 419 (`docs/17_ERROR_HANDLING.md`). Logout juga POST. |
| SQL | Hanya prepared statement di `app/Repositories/`; dilarang menyambung string input ke SQL. Kolom urut/filter dari input dicocokkan ke daftar putih. |
| XSS | Semua keluaran lewat `e()` (`htmlspecialchars` dengan `ENT_QUOTES`, UTF-8). Tidak ada `echo` mentah dari input. |
| Otorisasi | Dicek di controller (`docs/03_USERS_AND_ROLES.md`); sembunyikan tombol saja tidak cukup. |
| CSV injection | Sel ekspor yang diawali `=`, `+`, `-`, `@`, tab, atau CR diberi awalan `'` (`docs/14_REPORTS_EXPORT.md`). |
| Berkas | `.env`, `storage/`, `database/`, `app/` di luar `public/` (document root); `.env` tidak masuk git. |
| Header | `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY`, `Referrer-Policy: same-origin` dikirim dari `public/index.php`. |
| Kesalahan | `display_errors` mati kecuali `APP_ENV=local`; pesan galat DB tidak pernah tampil ke pengguna. |
