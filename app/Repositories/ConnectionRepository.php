<?php

declare(strict_types=1);

namespace App\Repositories;

use DateTimeImmutable;
use DateTimeZone;
use PDO;

final class ConnectionRepository
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function setTimeZone(): void
    {
        $offset = (new DateTimeImmutable('now', new DateTimeZone(config('APP_TIMEZONE'))))->format('P');
        $this->db->prepare('SET time_zone = :offset')->execute(['offset' => $offset]);
    }
}
