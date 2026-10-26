<?php

declare(strict_types=1);

use App\Core\Database;
use App\Core\Validator;
use App\Repositories\UserRepository;
use App\Support\Logger;

try {
    require dirname(__DIR__) . '/app/bootstrap.php';
    $username = config('SEED_ADMIN_USERNAME');
    $password = config('SEED_ADMIN_PASSWORD');
    $validator = new Validator();
    $validator->username($username);
    $validator->password($password);
    if ($validator->errors() !== [] || str_contains($password, "\0")) {
        throw new RuntimeException('Konfigurasi akun seed tidak valid.');
    }
    $users = new UserRepository(Database::connection());
    if ($users->findByUsername($username) !== null) {
        echo 'Admin sudah ada' . PHP_EOL;
    } else {
        $users->createSeedAdmin($username, password_hash($password, PASSWORD_DEFAULT));
        echo 'Admin dibuat' . PHP_EOL;
    }
} catch (Throwable $exception) {
    Logger::exception($exception);
    fwrite(STDERR, 'Seed gagal. Periksa konfigurasi, koneksi database, dan storage/logs/app.log.' . PHP_EOL);
    exit(1);
}
