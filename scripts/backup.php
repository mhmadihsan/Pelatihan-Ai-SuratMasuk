<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

if (PHP_SAPI !== 'cli' || $argc !== 2) {
    fwrite(STDERR, "Pemakaian: php scripts/backup.php /path/cadangan-baru.sql\n");
    exit(1);
}
try {
    App\Support\DatabaseBackup::dump($argv[1]);
    echo "Cadangan selesai.\n";
} catch (Throwable $exception) {
    fwrite(STDERR, "Cadangan gagal. Pastikan path baru, klien MySQL, konfigurasi, dan koneksi tersedia.\n");
    exit(1);
}
