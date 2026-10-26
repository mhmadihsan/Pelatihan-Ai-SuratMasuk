# 08 Arsitektur
Status: FINAL

## Stack
| Lapisan | Pilihan |
|---|---|
| Bahasa | PHP ≥ 8.2, `declare(strict_types=1)` di setiap file, tanpa Composer dan tanpa framework |
| Database | MySQL 8.0 lewat PDO (prepared statement, mode exception) |
| CSS | Tailwind CSS v4, CLI standalone (tanpa Node) |
| JS | Vanilla seperlunya (dialog konfirmasi, tombol cetak); tanpa framework JS |
| Server | Apache (MAMP) dengan `mod_rewrite` |

## Struktur folder
```
simasuk/
  public/            index.php (front controller), .htaccess, assets/app.css (hasil build)
  app/
    bootstrap.php    autoloader (namespace App\ -> app/), muat konfigurasi, mulai sesi
    Core/            Router, Request, Response, View, Database, Auth, Csrf, Session, Validator
    Controllers/     AuthController, DashboardController, SuratController, LaporanController, PenggunaController
    Repositories/    UserRepository, SuratRepository, AgendaCounterRepository (satu-satunya tempat SQL)
    Support/         AgendaNumber, CsvWriter, fungsi bantu (e(), tanggal)
    Views/           layouts/, auth/, dashboard/, surat/, laporan/, pengguna/, errors/
  config/            config.php, routes.php
  database/          migrations/*.sql, migrate.php, seed.php
  resources/css/     app.css (sumber Tailwind)
  bin/               tailwindcss (binary, tidak di-commit)
  storage/logs/      app.log
  tests/             run.php, *Test.php
  docs/  scripts/    blueprint dan validator
```

## Alur request
`public/index.php` → `bootstrap.php` → `Router` mencocokkan `config/routes.php` (`docs/09_ROUTES.md`) → middleware auth/peran/CSRF → Controller → Repository (PDO) → `View` (PHP + Tailwind) → Response.

## Batas tanggung jawab
- Controller: validasi input, cek hak akses, panggil Repository, pilih View. Tanpa SQL.
- Repository: semua SQL; mengembalikan array atau objek sederhana.
- View: tampilan dan perulangan saja; semua keluaran lewat `e()`.
- Dokumen yang memiliki fakta terkait: skema `docs/07_DATA_MODEL.md`, konfigurasi `docs/12_CONFIGURATION.md`, keamanan `docs/13_SECURITY.md`.
