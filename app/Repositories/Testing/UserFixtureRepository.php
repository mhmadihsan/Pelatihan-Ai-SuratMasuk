<?php

declare(strict_types=1);

namespace App\Repositories\Testing;

use PDO;

final class UserFixtureRepository
{
    public function __construct(private readonly PDO $db)
    {
        if (config('APP_ENV') !== 'testing' || config('DB_NAME') !== 'simasuk_test') {
            throw new \RuntimeException('Fixture hanya boleh memakai database tes.');
        }
    }

    public function create(string $username, string $passwordHash, bool $active = true, string $role = 'operator'): int
    {
        $statement = $this->db->prepare(
            "INSERT INTO users (username, nama_lengkap, password_hash, peran, aktif, wajib_ganti_password)
            VALUES (:username, :nama, :hash, :peran, :aktif, 0)"
        );
        $statement->execute([
            'username' => $username, 'nama' => '<Operator & Uji>', 'hash' => $passwordHash,
            'aktif' => (int) $active, 'peran' => $role,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function deactivate(int $id): void
    {
        $this->db->prepare('UPDATE users SET aktif = 0 WHERE id = :id')->execute(['id' => $id]);
    }

    public function sessionTimeZone(): string
    {
        $statement = $this->db->prepare('SELECT @@session.time_zone');
        $statement->execute();
        return (string) $statement->fetchColumn();
    }

    public function delete(int $id): void
    {
        $this->db->prepare('DELETE FROM users WHERE id = :id')->execute(['id' => $id]);
    }
}
