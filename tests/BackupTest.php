<?php

declare(strict_types=1);

use App\Core\Database;
use App\Repositories\Testing\BackupFixtureRepository;
use App\Support\DatabaseBackup;

function test_backup_restore_guard(): void
{
    $previous = getenv('SIMASUK_RESTORE_TEST');
    putenv('SIMASUK_RESTORE_TEST=0');
    try {
        $blocked = false;
        try {
            DatabaseBackup::restoreTest('/tidak-ada.sql');
        } catch (RuntimeException $exception) {
            $blocked = str_contains($exception->getMessage(), 'hanya diizinkan');
        }
        assertTrue($blocked, 'Pemulihan harus membutuhkan opt-in eksplisit.');
    } finally {
        $previous === false ? putenv('SIMASUK_RESTORE_TEST') : putenv('SIMASUK_RESTORE_TEST=' . $previous);
    }
}

// Pemulihan mengembalikan snapshot seluruh database tes; jalankan tanpa proses tes lain.
if (getenv('SIMASUK_RESTORE_TEST') === '1') {
    function test_backup_restore_roundtrip_preserves_all_tables(): void
    {
        withSuratFixture(static function ($surat, $fixtures, $years, $userId): void {
            $directory = sys_get_temp_dir() . '/simasuk-backup-test-' . bin2hex(random_bytes(8));
            mkdir($directory, 0700);
            $path = $directory . '/backup.sql';
            $data = suratData($years[0]);
            $data['perihal'] = 'Cadangan é; "uji"';
            $id = $surat->create($data, $userId);
            $repo = new BackupFixtureRepository(Database::connection());
            $before = $repo->snapshot();
            $restored = false;
            try {
                DatabaseBackup::dump($path);
                assertTrue(filesize($path) > 0, 'Cadangan tidak boleh kosong.');
                assertTrue(str_contains(file_get_contents($path), 'Dump completed'), 'Dump harus selesai.');
                $surat->softDelete($id, $userId);
                assertSame(null, $surat->find($id));
                DatabaseBackup::restoreTest($path);
                $restored = true;
                assertSame($before, $repo->snapshot(), 'Semua tabel, jumlah baris, dan isinya harus sama setelah pemulihan.');
                assertSame($data['perihal'], $surat->find($id)['perihal']);
            } finally {
                // Pertahankan cadangan bila restore gagal agar pemulihan dapat diulang.
                if ($restored || !is_file($path)) {
                    if (is_file($path)) {
                        unlink($path);
                    }
                    rmdir($directory);
                }
            }
        });
    }
}
