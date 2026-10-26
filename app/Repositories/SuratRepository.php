<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class SuratRepository
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function paginate(array $filters, int $page, int $perPage): array
    {
        $conditions = ['dihapus_pada IS NULL'];
        $params = [];
        if ($filters['q'] !== '') {
            $conditions[] = "(nomor_surat LIKE :nomor ESCAPE '!' OR asal_surat LIKE :asal ESCAPE '!'
                OR perihal LIKE :perihal ESCAPE '!')";
            $term = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $filters['q']) . '%';
            $params += ['nomor' => $term, 'asal' => $term, 'perihal' => $term];
        }
        foreach (['sifat' => 'sifat = :sifat', 'dari' => 'tanggal_diterima >= :dari',
            'sampai' => 'tanggal_diterima <= :sampai'] as $field => $condition) {
            if ($filters[$field] !== '') {
                $conditions[] = $condition;
                $params[$field] = $filters[$field];
            }
        }
        $where = implode(' AND ', $conditions);
        $count = $this->db->prepare('SELECT COUNT(*) FROM surat_masuk WHERE ' . $where);
        $count->execute($params);
        $total = (int) $count->fetchColumn();
        $pages = $total === 0 ? 1 : intdiv($total - 1, $perPage) + 1;
        $page = min($page, $pages);
        $statement = $this->db->prepare('SELECT * FROM surat_masuk WHERE ' . $where
            . ' ORDER BY tahun_agenda DESC, nomor_urut DESC LIMIT :limit OFFSET :offset');
        foreach ($params as $name => $value) {
            $statement->bindValue(':' . $name, $value, PDO::PARAM_STR);
        }
        $statement->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $statement->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
        $statement->execute();
        return ['items' => array_map($this->cast(...), $statement->fetchAll()),
            'total' => $total, 'page' => $page, 'pages' => $pages];
    }

    public function report(array $filters): array
    {
        $where = ['dihapus_pada IS NULL', 'tanggal_diterima >= :dari', 'tanggal_diterima <= :sampai'];
        $params = ['dari' => $filters['dari'], 'sampai' => $filters['sampai']];
        if ($filters['sifat'] !== '') {
            $where[] = 'sifat = :sifat';
            $params['sifat'] = $filters['sifat'];
        }
        if ($filters['asal'] !== '') {
            $where[] = "asal_surat LIKE :asal ESCAPE '!'";
            $params['asal'] = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $filters['asal']) . '%';
        }
        $statement = $this->db->prepare('SELECT * FROM surat_masuk WHERE ' . implode(' AND ', $where)
            . ' ORDER BY tahun_agenda ASC, nomor_urut ASC');
        $statement->execute($params);
        return array_map($this->cast(...), $statement->fetchAll());
    }

    public function dashboard(): array
    {
        $statement = $this->db->prepare('SELECT
            COALESCE(SUM(tanggal_diterima >= :bulan AND tanggal_diterima <= :hari_bulan), 0) AS bulan,
            COALESCE(SUM(tanggal_diterima >= :tahun AND tanggal_diterima <= :hari_tahun), 0) AS tahun
            FROM surat_masuk WHERE dihapus_pada IS NULL');
        $statement->execute(['bulan' => date('Y-m-01'), 'hari_bulan' => date('Y-m-d'),
            'tahun' => date('Y-01-01'), 'hari_tahun' => date('Y-m-d')]);
        $counts = $statement->fetch();
        $statement = $this->db->prepare('SELECT sifat, COUNT(*) AS jumlah FROM surat_masuk
            WHERE dihapus_pada IS NULL GROUP BY sifat');
        $statement->execute();
        $sifat = array_fill_keys(['biasa', 'penting', 'segera', 'rahasia'], 0);
        foreach ($statement->fetchAll() as $row) {
            $sifat[$row['sifat']] = (int) $row['jumlah'];
        }
        $statement = $this->db->prepare('SELECT * FROM surat_masuk WHERE dihapus_pada IS NULL
            ORDER BY tahun_agenda DESC, nomor_urut DESC LIMIT 5');
        $statement->execute();
        return ['bulan' => (int) $counts['bulan'], 'tahun' => (int) $counts['tahun'],
            'sifat' => $sifat, 'terbaru' => array_map($this->cast(...), $statement->fetchAll())];
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM surat_masuk WHERE id = :id AND dihapus_pada IS NULL');
        $statement->execute(['id' => $id]);
        $surat = $statement->fetch();
        return $surat === false ? null : $this->cast($surat);
    }

    public function hasDuplicate(string $nomorSurat, string $asalSurat, int $excludeId = 0): bool
    {
        $statement = $this->db->prepare(
            'SELECT id FROM surat_masuk
            WHERE nomor_surat = :nomor AND asal_surat = :asal AND id <> :exclude AND dihapus_pada IS NULL LIMIT 1'
        );
        $statement->execute(['nomor' => $nomorSurat, 'asal' => $asalSurat, 'exclude' => $excludeId]);
        return $statement->fetchColumn() !== false;
    }

    public function create(array $data, int $userId): int
    {
        $this->db->beginTransaction();
        try {
            $tahun = (int) substr($data['tanggal_diterima'], 0, 4);
            $nomorUrut = (new AgendaCounterRepository($this->db))->next($tahun);
            $statement = $this->db->prepare(
                'INSERT INTO surat_masuk
                (tahun_agenda, nomor_urut, nomor_surat, tanggal_surat, tanggal_diterima,
                 asal_surat, perihal, sifat, jumlah_lampiran, keterangan, dibuat_oleh)
                VALUES (:tahun, :urut, :nomor_surat, :tanggal_surat, :tanggal_diterima,
                        :asal_surat, :perihal, :sifat, :jumlah_lampiran, :keterangan, :user_id)'
            );
            $statement->execute([
                'tahun' => $tahun, 'urut' => $nomorUrut, 'nomor_surat' => $data['nomor_surat'],
                'tanggal_surat' => $data['tanggal_surat'], 'tanggal_diterima' => $data['tanggal_diterima'],
                'asal_surat' => $data['asal_surat'], 'perihal' => $data['perihal'], 'sifat' => $data['sifat'],
                'jumlah_lampiran' => $data['jumlah_lampiran'], 'keterangan' => $data['keterangan'],
                'user_id' => $userId,
            ]);
            $id = (int) $this->db->lastInsertId();
            $this->db->commit();
            return $id;
        } catch (\Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }

    public function update(int $id, array $data): bool
    {
        $statement = $this->db->prepare(
            'UPDATE surat_masuk SET nomor_surat = :nomor_surat, tanggal_surat = :tanggal_surat,
            tanggal_diterima = :tanggal_diterima, asal_surat = :asal_surat, perihal = :perihal,
            sifat = :sifat, jumlah_lampiran = :jumlah_lampiran, keterangan = :keterangan
            WHERE id = :id AND dihapus_pada IS NULL'
        );
        $params = ['id' => $id];
        foreach (['nomor_surat', 'tanggal_surat', 'tanggal_diterima', 'asal_surat', 'perihal',
            'sifat', 'jumlah_lampiran', 'keterangan'] as $field) {
            $params[$field] = $data[$field];
        }
        $statement->execute($params);
        return $this->find($id) !== null;
    }

    public function softDelete(int $id, int $userId): bool
    {
        $statement = $this->db->prepare(
            'UPDATE surat_masuk SET dihapus_pada = NOW(), dihapus_oleh = :user
            WHERE id = :id AND dihapus_pada IS NULL'
        );
        $statement->execute(['user' => $userId, 'id' => $id]);
        return $statement->rowCount() === 1;
    }

    private function cast(array $surat): array
    {
        foreach (['id', 'tahun_agenda', 'nomor_urut', 'jumlah_lampiran', 'dibuat_oleh'] as $field) {
            $surat[$field] = (int) $surat[$field];
        }
        $surat['dihapus_oleh'] = $surat['dihapus_oleh'] === null ? null : (int) $surat['dihapus_oleh'];
        return $surat;
    }
}
