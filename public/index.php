<?php

declare(strict_types=1);

use App\Core\HttpException;
use App\Core\Request;
use App\Core\Router;
use App\Core\View;
use App\Support\Logger;

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');
header('Cache-Control: no-store');

try {
    require dirname(__DIR__) . '/app/bootstrap.php';
    $router = new Router(require dirname(__DIR__) . '/config/routes.php');
    $router->dispatch(Request::capture())->send();
} catch (HttpException $exception) {
    View::error($exception->status)->send();
} catch (Throwable $exception) {
    Logger::exception($exception);
    if (isset($GLOBALS['config'])) {
        View::error(500)->send();
    } else {
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
        echo '<!doctype html><html lang="id"><meta charset="utf-8"><title>Terjadi kendala</title><h1>Terjadi kendala pada sistem.</h1><p>Silakan coba kembali nanti.</p></html>';
    }
}
