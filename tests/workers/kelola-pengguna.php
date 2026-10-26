<?php

declare(strict_types=1);

putenv('APP_ENV=testing');
putenv('DB_NAME=simasuk_test');
require dirname(__DIR__, 2) . '/app/bootstrap.php';

use App\Core\Database;
use App\Core\HttpException;
use App\Core\Validator;
use App\Repositories\UserRepository;

$validator = new Validator();
$actor = $validator->integer(['id' => $argv[1] ?? ''], 'id', 'Admin', 1, PHP_INT_MAX);
$target = $validator->integer(['id' => $argv[2] ?? ''], 'id', 'Pengguna', 1, PHP_INT_MAX);
$mode = $validator->choice(['mode' => $argv[3] ?? ''], 'mode', 'Mode', ['status', 'peran']);
if ($validator->errors() !== []) {
    exit(2);
}
fgets(STDIN);
try {
    $repo = new UserRepository(Database::connection());
    $user = $repo->findById($target);
    $repo->manage($target, $actor, $mode === 'status' ? null : [
        'username' => $user['username'], 'nama_lengkap' => $user['nama_lengkap'], 'peran' => 'operator',
    ]);
    exit(0);
} catch (HttpException|DomainException $exception) {
    exit(3);
}
