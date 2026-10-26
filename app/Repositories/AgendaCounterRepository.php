<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class AgendaCounterRepository
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function next(int $tahun): int
    {
        if (!$this->db->inTransaction()) {
            throw new \LogicException('Nomor agenda harus dibuat dalam transaksi surat.');
        }
        $this->db->prepare(
            'INSERT INTO agenda_counter (tahun, terakhir) VALUES (:tahun, LAST_INSERT_ID(1))
            ON DUPLICATE KEY UPDATE terakhir = LAST_INSERT_ID(terakhir + 1)'
        )->execute(['tahun' => $tahun]);
        $statement = $this->db->prepare('SELECT LAST_INSERT_ID()');
        $statement->execute();
        return (int) $statement->fetchColumn();
    }
}
