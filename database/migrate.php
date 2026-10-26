<?php

declare(strict_types=1);

use App\Core\Database;
use App\Repositories\MigrationRepository;
use App\Support\Logger;

try {
    require dirname(__DIR__) . '/app/bootstrap.php';
    $migrations = (new MigrationRepository(Database::connection()))->migrate(__DIR__ . '/migrations');
    foreach ($migrations as $migration) {
        echo 'OK ' . $migration . PHP_EOL;
    }
    if ($migrations === []) {
        echo 'Semua migrasi sudah dijalankan.' . PHP_EOL;
    }
} catch (Throwable $exception) {
    Logger::exception($exception);
    fwrite(STDERR, 'Migrasi gagal. Periksa konfigurasi, koneksi database, dan storage/logs/app.log.' . PHP_EOL);
    exit(1);
}
