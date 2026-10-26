<?php

declare(strict_types=1);

namespace App\Repositories\Testing;

use PDO;

final class SuratFixtureRepository
{
    public function __construct(private readonly PDO $db)
    {
        if (config('APP_ENV') !== 'testing' || config('DB_NAME') !== 'simasuk_test') {
            throw new \RuntimeException('Fixture hanya boleh memakai database tes.');
        }
    }

    public function unusedYears(): array
    {
        $statement = $this->db->prepare('SELECT tahun FROM agenda_counter UNION SELECT tahun_agenda FROM surat_masuk');
        $statement->execute();
        $used = array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));
        for ($year = 1800; $year < 2000; $year += 2) {
            if (!in_array($year, $used, true) && !in_array($year + 1, $used, true)) {
                return [$year, $year + 1];
            }
        }
        throw new \RuntimeException('Tahun fixture tidak tersedia.');
    }

    public function counter(int $year): ?int
    {
        $statement = $this->db->prepare('SELECT terakhir FROM agenda_counter WHERE tahun = :tahun');
        $statement->execute(['tahun' => $year]);
        $value = $statement->fetchColumn();
        return $value === false ? null : (int) $value;
    }

    public function hide(int $id, int $userId): void
    {
        $this->db->prepare('UPDATE surat_masuk SET dihapus_pada = NOW(), dihapus_oleh = :user WHERE id = :id')
            ->execute(['user' => $userId, 'id' => $id]);
    }

    public function deletion(int $id): array
    {
        $statement = $this->db->prepare('SELECT dihapus_pada, dihapus_oleh FROM surat_masuk WHERE id = :id');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();
        return ['dihapus_pada' => $row['dihapus_pada'],
            'dihapus_oleh' => $row['dihapus_oleh'] === null ? null : (int) $row['dihapus_oleh']];
    }

    public function cleanup(int $userId, array $years): void
    {
        $this->db->prepare('DELETE FROM surat_masuk WHERE dibuat_oleh = :user')->execute(['user' => $userId]);
        foreach ($years as $year) {
            $this->db->prepare(
                'DELETE FROM agenda_counter WHERE tahun = :tahun
                AND NOT EXISTS (SELECT 1 FROM surat_masuk WHERE tahun_agenda = :agenda)'
            )->execute(['tahun' => $year, 'agenda' => $year]);
        }
    }
}
