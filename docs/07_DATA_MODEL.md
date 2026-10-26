# 07 Model Data
Status: FINAL

Satu-satunya rumah skema. MySQL 8.0, InnoDB, `utf8mb4_unicode_ci`. Satu file migrasi per tabel di `database/migrations/` dengan urutan di bawah.

## 001_schema_migrations.sql
```sql
CREATE TABLE schema_migrations (
  nama VARCHAR(100) NOT NULL PRIMARY KEY,
  dijalankan_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 002_users.sql
```sql
CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL,
  nama_lengkap VARCHAR(100) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  peran ENUM('admin','operator') NOT NULL DEFAULT 'operator',
  aktif TINYINT(1) NOT NULL DEFAULT 1,
  wajib_ganti_password TINYINT(1) NOT NULL DEFAULT 1,
  dibuat_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  diubah_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_users_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 003_agenda_counter.sql
```sql
CREATE TABLE agenda_counter (
  tahun SMALLINT UNSIGNED NOT NULL PRIMARY KEY,
  terakhir INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 004_surat_masuk.sql
```sql
CREATE TABLE surat_masuk (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tahun_agenda SMALLINT UNSIGNED NOT NULL,
  nomor_urut INT UNSIGNED NOT NULL,
  nomor_surat VARCHAR(100) NOT NULL,
  tanggal_surat DATE NOT NULL,
  tanggal_diterima DATE NOT NULL,
  asal_surat VARCHAR(150) NOT NULL,
  perihal VARCHAR(255) NOT NULL,
  sifat ENUM('biasa','penting','segera','rahasia') NOT NULL DEFAULT 'biasa',
  jumlah_lampiran SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  keterangan TEXT NULL,
  dibuat_oleh BIGINT UNSIGNED NOT NULL,
  dibuat_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  diubah_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  dihapus_pada DATETIME NULL,
  dihapus_oleh BIGINT UNSIGNED NULL,
  UNIQUE KEY uq_surat_agenda (tahun_agenda, nomor_urut),
  KEY idx_surat_diterima (tanggal_diterima),
  KEY idx_surat_sifat (sifat),
  KEY idx_surat_dihapus (dihapus_pada),
  CONSTRAINT fk_surat_dibuat_oleh FOREIGN KEY (dibuat_oleh) REFERENCES users(id),
  CONSTRAINT fk_surat_dihapus_oleh FOREIGN KEY (dihapus_oleh) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Nomor agenda tidak disimpan sebagai kolom; ia diturunkan dari `nomor_urut` dan `tahun_agenda` lewat `AgendaNumber::format()` (aturan di `docs/06_BUSINESS_RULES.md`).

## Algoritma nomor agenda (wajib dalam satu transaksi)
```sql
START TRANSACTION;
INSERT INTO agenda_counter (tahun, terakhir) VALUES (:tahun, LAST_INSERT_ID(1))
  ON DUPLICATE KEY UPDATE terakhir = LAST_INSERT_ID(terakhir + 1);
SELECT LAST_INSERT_ID();   -- hasilnya = nomor_urut, pada koneksi yang sama
-- INSERT INTO surat_masuk (... tahun_agenda = :tahun, nomor_urut = <hasil> ...);
COMMIT;                    -- bila INSERT gagal: ROLLBACK, counter ikut kembali
```
Kunci baris `agenda_counter` menyerialkan pencatatan bersamaan pada tahun yang sama; rollback menjamin tanpa lompat.

## Seeder
`database/seed.php` membuat satu Admin awal (`wajib_ganti_password = 1`). Nilai awal ada di `docs/12_CONFIGURATION.md`.
