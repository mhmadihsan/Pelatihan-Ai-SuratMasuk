<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class UserRepository
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function findByUsername(string $username): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
        $statement->execute(['username' => $username]);
        return $this->cast($statement->fetch());
    }

    public function findById(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        return $this->cast($statement->fetch());
    }

    public function createSeedAdmin(string $username, string $passwordHash): void
    {
        $statement = $this->db->prepare(
            "INSERT INTO users (username, nama_lengkap, password_hash, peran, aktif, wajib_ganti_password)
            VALUES (:username, :nama, :password_hash, 'admin', 1, 1)"
        );
        $statement->execute(['username' => $username, 'nama' => 'Administrator', 'password_hash' => $passwordHash]);
    }

    public function changePassword(int $id, string $passwordHash): void
    {
        $statement = $this->db->prepare(
            'UPDATE users SET password_hash = :password_hash, wajib_ganti_password = 0 WHERE id = :id AND aktif = 1'
        );
        $statement->execute(['id' => $id, 'password_hash' => $passwordHash]);
    }

    public function all(): array
    {
        $statement = $this->db->prepare('SELECT id, username, nama_lengkap, peran, aktif, wajib_ganti_password
            FROM users ORDER BY username');
        $statement->execute();
        return array_map($this->cast(...), $statement->fetchAll());
    }

    public function create(array $data, string $hash): int
    {
        $statement = $this->db->prepare('INSERT INTO users (username, nama_lengkap, peran, password_hash)
            VALUES (:username, :nama_lengkap, :peran, :hash)');
        $statement->execute($data + ['hash' => $hash]);
        return (int) $this->db->lastInsertId();
    }

    public function resetPassword(int $id, string $hash): void
    {
        $statement = $this->db->prepare('UPDATE users SET password_hash = :hash, wajib_ganti_password = 1 WHERE id = :id');
        $statement->execute(['id' => $id, 'hash' => $hash]);
    }

    public function manage(int $id, int $actorId, ?array $data = null): void
    {
        $this->db->beginTransaction();
        try {
            // Kunci seluruh pengguna dengan urutan tetap agar dua Admin tidak saling menonaktifkan bersamaan.
            $statement = $this->db->prepare('SELECT id, peran, aktif FROM users ORDER BY id FOR UPDATE');
            $statement->execute();
            $rows = $statement->fetchAll();
            $target = null;
            $actor = null;
            foreach ($rows as $row) {
                if ((int) $row['id'] === $id) {
                    $target = $row;
                }
                if ((int) $row['id'] === $actorId) {
                    $actor = $row;
                }
            }
            if ($actor === null || !$actor['aktif'] || $actor['peran'] !== 'admin') {
                throw new \App\Core\HttpException(403);
            }
            if ($target === null) {
                throw new \App\Core\HttpException(404);
            }
            $role = $data['peran'] ?? $target['peran'];
            $active = $data === null ? !(bool) $target['aktif'] : (bool) $target['aktif'];
            if ($id === $actorId && (!$active || $role !== 'admin')) {
                throw new \DomainException('Admin tidak boleh menonaktifkan atau menurunkan peran dirinya sendiri.');
            }
            $admins = 0;
            foreach ($rows as $row) {
                $rowRole = (int) $row['id'] === $id ? $role : $row['peran'];
                $rowActive = (int) $row['id'] === $id ? $active : (bool) $row['aktif'];
                if ($rowActive && $rowRole === 'admin') {
                    $admins++;
                }
            }
            if ($admins < 1) {
                throw new \DomainException('Sistem harus memiliki minimal satu Admin aktif.');
            }
            if ($data === null) {
                $statement = $this->db->prepare('UPDATE users SET aktif = :aktif WHERE id = :id');
                $statement->execute(['aktif' => (int) $active, 'id' => $id]);
            } else {
                $statement = $this->db->prepare('UPDATE users SET username = :username,
                    nama_lengkap = :nama_lengkap, peran = :peran WHERE id = :id');
                $statement->execute($data + ['id' => $id]);
            }
            $this->db->commit();
        } catch (\Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }

    private function cast(array|false $user): ?array
    {
        if ($user === false) {
            return null;
        }
        $user['id'] = (int) $user['id'];
        $user['aktif'] = (bool) $user['aktif'];
        $user['wajib_ganti_password'] = (bool) $user['wajib_ganti_password'];
        return $user;
    }
}
