<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/app/bootstrap.php';

if (config('APP_ENV') !== 'testing' || config('DB_NAME') !== 'simasuk_test') {
    exit(1);
}
$data = json_decode($argv[1], true, flags: JSON_THROW_ON_ERROR);
$repository = new App\Repositories\SuratRepository(App\Core\Database::connection());
$id = $repository->create($data, (int) $argv[2]);
echo $repository->find($id)['nomor_urut'];
