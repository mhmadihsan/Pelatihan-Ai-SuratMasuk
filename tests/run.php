<?php

declare(strict_types=1);

if (getenv('DB_NAME') !== false && getenv('DB_NAME') !== 'simasuk_test') {
    fwrite(STDERR, "Tes hanya boleh memakai DB_NAME=simasuk_test.\n");
    exit(1);
}
putenv('APP_ENV=testing');
putenv('DB_NAME=simasuk_test');
require dirname(__DIR__) . '/app/bootstrap.php';
require __DIR__ . '/support.php';

$files = glob(__DIR__ . '/*Test.php');
sort($files);
foreach ($files as $file) {
    require $file;
}
$functions = array_filter(get_defined_functions()['user'], static fn (string $name): bool => str_starts_with($name, 'test_'));
sort($functions);
$passed = 0;
$failed = 0;
foreach ($functions as $function) {
    try {
        $function();
        echo 'PASS ' . $function . PHP_EOL;
        $passed++;
    } catch (Throwable $exception) {
        echo 'FAIL ' . $function . ': ' . $exception->getMessage() . PHP_EOL;
        $failed++;
    }
}
echo sprintf('Hasil: %d lulus, %d gagal', $passed, $failed) . PHP_EOL;
exit($failed > 0 ? 1 : 0);
