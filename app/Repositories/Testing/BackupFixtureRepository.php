<?php

declare(strict_types=1);

namespace App\Repositories\Testing;

use PDO;

final class BackupFixtureRepository
{
    public function __construct(private readonly PDO $db)
    {
        if (config('APP_ENV') !== 'testing' || config('DB_NAME') !== 'simasuk_test') {
            throw new \RuntimeException('Fixture cadangan hanya untuk database tes.');
        }
    }

    public function snapshot(): array
    {
        $result = [];
        foreach (['schema_migrations' => 'nama', 'users' => 'id', 'agenda_counter' => 'tahun', 'surat_masuk' => 'id'] as $table => $key) {
            $statement = $this->db->prepare('SELECT * FROM ' . $table . ' ORDER BY ' . $key);
            $statement->execute();
            $rows = $statement->fetchAll();
            $result[$table] = ['count' => count($rows), 'hash' => hash('sha256', serialize($rows))];
        }
        return $result;
    }
}
