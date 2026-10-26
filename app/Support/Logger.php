<?php

declare(strict_types=1);

namespace App\Support;

final class Logger
{
    public static function write(string $level, string $message, ?int $userId = null): void
    {
        $line = sprintf("%s %s %s user_id=%s\n", date(DATE_ATOM), $level, $message, $userId ?? '-');
        error_log($line, 3, dirname(__DIR__, 2) . '/storage/logs/app.log');
    }

    public static function exception(\Throwable $exception): void
    {
        // Pesan dan argumen exception dapat memuat nilai form atau kredensial.
        self::write('ERROR', sprintf('%s code=%s at %s:%d',
            $exception::class, (string) $exception->getCode(), $exception->getFile(), $exception->getLine()),
            isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null);
    }
}
