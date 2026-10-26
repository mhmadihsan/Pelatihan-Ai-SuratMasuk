<?php

declare(strict_types=1);

namespace App\Core;

use App\Repositories\ConnectionRepository;
use PDO;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection === null) {
            $connection = new PDO(
                sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                    config('DB_HOST'), config('DB_PORT'), config('DB_NAME')),
                config('DB_USER'),
                config('DB_PASS'),
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ],
            );
            (new ConnectionRepository($connection))->setTimeZone();
            self::$connection = $connection;
        }
        return self::$connection;
    }
}
