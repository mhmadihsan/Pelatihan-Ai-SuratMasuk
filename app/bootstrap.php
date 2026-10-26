<?php

declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    if (str_starts_with($class, 'App\\')) {
        $file = __DIR__ . '/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (is_file($file)) {
            require $file;
        }
    }
});

require __DIR__ . '/Support/helpers.php';
$GLOBALS['config'] = require dirname(__DIR__) . '/config/config.php';
date_default_timezone_set(config('APP_TIMEZONE'));
error_reporting(E_ALL);
ini_set('display_errors', config('APP_ENV') === 'local' ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', dirname(__DIR__) . '/storage/logs/app.log');

if (PHP_SAPI !== 'cli') {
    App\Core\Session::start();
}
