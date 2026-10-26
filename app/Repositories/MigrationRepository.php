<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class MigrationRepository
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function migrate(string $directory): array
    {
        $lock = 'simasuk_migrations_' . sha1(config('DB_NAME'));
        $statement = $this->db->prepare('SELECT GET_LOCK(:name, 10)');
        $statement->execute(['name' => $lock]);
        if ((int) $statement->fetchColumn() !== 1) {
            throw new \RuntimeException('Migrasi lain sedang berjalan.');
        }
        try {
            $files = glob($directory . '/*.sql');
            sort($files, SORT_STRING);
            $statement = $this->db->prepare(
                "SELECT COUNT(*) FROM information_schema.tables
                WHERE table_schema = DATABASE() AND table_name = 'schema_migrations'"
            );
            $statement->execute();
            if ((int) $statement->fetchColumn() === 0) {
                $this->executeFile($directory . '/001_schema_migrations.sql');
            }
            $statement = $this->db->prepare('SELECT nama FROM schema_migrations');
            $statement->execute();
            $applied = $statement->fetchAll(PDO::FETCH_COLUMN);
            $new = [];
            foreach ($files as $file) {
                $name = basename($file);
                if (!preg_match('/^[0-9]{3}_[a-z_]+\.sql$/D', $name)) {
                    throw new \RuntimeException('Nama migrasi tidak valid.');
                }
                if (in_array($name, $applied, true)) {
                    continue;
                }
                if ($name !== '001_schema_migrations.sql') {
                    $this->executeFile($file);
                }
                $statement = $this->db->prepare('INSERT INTO schema_migrations (nama) VALUES (:nama)');
                $statement->execute(['nama' => $name]);
                $new[] = $name;
            }
            return $new;
        } finally {
            $this->db->prepare('SELECT RELEASE_LOCK(:name)')->execute(['name' => $lock]);
        }
    }

    private function executeFile(string $file): void
    {
        $sql = file_get_contents($file);
        if ($sql === false) {
            throw new \RuntimeException('Migrasi tidak dapat dibaca.');
        }
        $this->db->prepare($sql)->execute();
    }
}
