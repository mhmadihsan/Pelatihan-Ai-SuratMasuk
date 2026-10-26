<?php

declare(strict_types=1);

use App\Core\Database;
use App\Repositories\UserRepository;
use App\Repositories\Testing\UserFixtureRepository;
use App\Repositories\Testing\SuratFixtureRepository;
use App\Repositories\SuratRepository;

function test_pengguna_admin_crud_reset_and_permissions(): void
{
    assertSame(0, runCli('database/migrate.php')['code']);
    $repo = new UserRepository(Database::connection());
    $fixtures = new UserFixtureRepository(Database::connection());
    $suffix = bin2hex(random_bytes(5));
    $admin = 's5admin_' . $suffix;
    $operator = 's5op_' . $suffix;
    $new = 's5new_' . $suffix;
    $password = 'PengujianAwal123';
    $ids = [];
    $suratFixtures = new SuratFixtureRepository(Database::connection());
    $years = $suratFixtures->unusedYears();
    try {
        $adminId = $fixtures->create($admin, password_hash($password, PASSWORD_DEFAULT), true, 'admin');
        $ids[] = $adminId;
        $operatorId = $fixtures->create($operator, password_hash($password, PASSWORD_DEFAULT));
        $ids[] = $operatorId;
        withServer(static function ($base) use ($repo, $admin, $adminId, $operator, $operatorId, $new, $password, $years, &$ids): void {
            $cookies = [];
            $token = loginHttp($base, $admin, $password, $cookies);
            assertSame(200, httpRequest($base . '/pengguna', $cookies)['status']);
            assertSame(200, httpRequest($base . '/pengguna/tambah', $cookies)['status']);
            $data = ['_token' => $token, 'username' => $new, 'nama_lengkap' => '<Nama & Baru>', 'peran' => 'operator', 'password' => $password];
            assertSame(419, httpRequest($base . '/pengguna', $cookies, 'POST', array_replace($data, ['_token' => 'salah']))['status']);
            // Data sandi tidak pernah dipantulkan, termasuk saat validasi gagal.
            foreach ([['username' => 'UPPER'], ['username' => $operator], ['password' => 'pendek'], ['password' => str_repeat('é', 37)], ['peran' => 'root'], ['nama_lengkap' => ['x']], ['username' => ['x']]] as $bad) {
                $response = httpRequest($base . '/pengguna', $cookies, 'POST', array_replace($data, $bad));
                assertSame(422, $response['status']);
                assertTrue(!str_contains($response['body'], 'value="' . $password . '"'), 'Sandi tidak boleh dipantulkan.');
            }
            assertSame(302, httpRequest($base . '/pengguna', $cookies, 'POST', $data)['status']);
            $created = $repo->findByUsername($new);
            $id = $created['id'];
            $ids[] = $id;
            assertSame(true, $created['wajib_ganti_password']);
            assertTrue(password_verify($password, $created['password_hash']), 'Sandi harus berupa hash.');
            $page = httpRequest($base . '/pengguna', $cookies);
            assertTrue(str_contains($page['body'], '&lt;Nama &amp; Baru&gt;'), 'Nama harus di-escape.');
            $newCookies = [];
            $newToken = loginHttp($base, $new, $password, $newCookies);
            assertSame('/ganti-password', httpRequest($base . '/surat', $newCookies)['headers']['location']);
            $changed = 'PengujianBaru456';
            assertSame(302, httpRequest($base . '/ganti-password', $newCookies, 'POST', ['_token' => $newToken,
                'password_lama' => $password, 'password_baru' => $changed, 'konfirmasi_password' => $changed])['status']);
            $letterForm = httpRequest($base . '/surat/tambah', $newCookies);
            assertSame(200, $letterForm['status']);
            $letter = httpRequest($base . '/surat', $newCookies, 'POST',
                array_replace(suratData($years[0]), ['_token' => csrfToken($letterForm), 'jumlah_lampiran' => '0']));
            assertSame(302, $letter['status'], 'Operator baru harus dapat mencatat surat setelah mengganti sandi.');
            $letterId = (int) basename($letter['headers']['location']);
            $letters = new SuratRepository(Database::connection());
            assertSame($id, $letters->find($letterId)['dibuat_oleh']);
            $edit = ['_token' => $token, 'username' => $new, 'nama_lengkap' => 'Nama diubah', 'peran' => 'admin'];
            assertSame(302, httpRequest($base . '/pengguna/' . $id, $cookies, 'POST', $edit)['status']);
            assertSame('admin', $repo->findById($id)['peran']);
            assertSame('Nama diubah', $repo->findById($id)['nama_lengkap']);
            assertSame(422, httpRequest($base . '/pengguna/' . $adminId, $cookies, 'POST', array_replace($edit, ['username' => $admin, 'peran' => 'operator']))['status']);
            assertSame(422, httpRequest($base . '/pengguna/' . $adminId . '/status', $cookies, 'POST', ['_token' => $token])['status']);
            assertSame(true, $repo->findById($adminId)['aktif']);
            assertSame('admin', $repo->findById($adminId)['peran']);
            assertSame(419, httpRequest($base . '/pengguna/' . $id . '/reset-password', $cookies, 'POST', ['password' => $password])['status']);
            assertSame(422, httpRequest($base . '/pengguna/' . $id . '/reset-password', $cookies, 'POST', ['_token' => $token, 'password' => ['bad']])['status']);
            assertSame(302, httpRequest($base . '/pengguna/' . $id . '/reset-password', $cookies, 'POST', ['_token' => $token, 'password' => $password])['status']);
            assertSame(true, $repo->findById($id)['wajib_ganti_password']);
            assertTrue(password_verify($password, $repo->findById($id)['password_hash']), 'Reset harus menyimpan hash sandi baru.');
            assertSame(false, password_verify($changed, $repo->findById($id)['password_hash']));
            assertSame('/ganti-password', httpRequest($base . '/', $newCookies)['headers']['location']);
            assertSame(302, httpRequest($base . '/pengguna/' . $id . '/status', $cookies, 'POST', ['_token' => $token])['status']);
            assertSame(false, $repo->findById($id)['aktif']);
            assertSame($id, $letters->find($letterId)['dibuat_oleh'], 'Nonaktif tidak boleh menghapus surat pengguna.');
            assertSame('/login', httpRequest($base . '/', $newCookies)['headers']['location']);
            assertSame(302, httpRequest($base . '/pengguna/' . $id . '/status', $cookies, 'POST', ['_token' => $token])['status']);
            assertSame(true, $repo->findById($id)['aktif']);
            $opCookies = [];
            $opToken = loginHttp($base, $operator, $password, $opCookies);
            foreach (['/pengguna', '/pengguna/tambah', '/pengguna/' . $adminId . '/ubah'] as $path) {
                assertSame(403, httpRequest($base . $path, $opCookies)['status']);
            }
            foreach (['/pengguna', '/pengguna/' . $adminId, '/pengguna/' . $adminId . '/reset-password', '/pengguna/' . $adminId . '/status'] as $path) {
                assertSame(403, httpRequest($base . $path, $opCookies, 'POST', ['_token' => $opToken])['status']);
            }
            foreach (['/pengguna/999999999/ubah', '/pengguna/0/ubah'] as $path) {
                assertSame(404, httpRequest($base . $path, $cookies)['status']);
            }
            assertTrue(!str_contains(httpRequest($base . '/', $opCookies)['body'], 'href="/pengguna"'), 'Navigasi pengguna hanya untuk Admin.');
        });
    } finally {
        // Temukan juga fixture bila assertion gagal setelah INSERT HTTP.
        $created = $repo->findByUsername($new);
        if ($created !== null && !in_array($created['id'], $ids, true)) {
            $ids[] = $created['id'];
        }
        foreach (array_reverse($ids) as $id) {
            $suratFixtures->cleanup($id, $years);
            $fixtures->delete($id);
        }
    }
}

function test_dashboard_counts_recent_and_soft_delete(): void
{
    withSuratFixture(static function ($surat, $fixtures, $years, $userId, $username, $password): void {
        $before = $surat->dashboard();
        $data = suratData($years[0]);
        $data['sifat'] = 'rahasia';
        $data['perihal'] = '<Dashboard Uji>';
        $visible = $surat->create($data, $userId);
        $hidden = $surat->create($data, $userId);
        $data['tanggal_surat'] = date('Y-m-d');
        $data['tanggal_diterima'] = date('Y-m-d');
        $surat->update($visible, $data);
        $surat->update($hidden, $data);
        $surat->softDelete($hidden, $userId);
        $after = $surat->dashboard();
        assertSame($before['bulan'] + 1, $after['bulan']);
        assertSame($before['tahun'] + 1, $after['tahun']);
        assertSame($before['sifat']['rahasia'] + 1, $after['sifat']['rahasia']);
        assertTrue(!in_array($hidden, array_column($after['terbaru'], 'id'), true), 'Dashboard mengabaikan surat terhapus.');
        $candidates = [...$before['terbaru'], $surat->find($visible)];
        for ($i = 0; $i < 6; $i++) {
            $extra = suratData($years[0]);
            $extra['perihal'] = '<Dashboard Uji>';
            $candidates[] = $surat->find($surat->create($extra, $userId));
        }
        usort($candidates, static fn ($a, $b): int =>
            [$b['tahun_agenda'], $b['nomor_urut']] <=> [$a['tahun_agenda'], $a['nomor_urut']]);
        assertSame(array_column(array_slice($candidates, 0, 5), 'id'),
            array_column($surat->dashboard()['terbaru'], 'id'), 'Lima surat terbaru harus berurutan dan dibatasi tepat.');
        withServer(static function ($base) use ($username, $password): void {
            $cookies = [];
            loginHttp($base, $username, $password, $cookies);
            $response = httpRequest($base . '/', $cookies);
            assertSame(200, $response['status']);
            assertTrue(str_contains($response['body'], 'Surat bulan ini'), 'Dashboard ringkasan tampil.');
            assertTrue(str_contains($response['body'], '5 surat terbaru'), 'Daftar terbaru tampil.');
        });
    });
}

function test_pengguna_concurrent_admin_changes_preserve_active_admin(): void
{
    assertSame(0, runCli('database/migrate.php')['code']);
    $fixtures = new UserFixtureRepository(Database::connection());
    $repo = new UserRepository(Database::connection());
    foreach (['status', 'peran'] as $mode) {
        $ids = [];
        $processes = [];
        try {
            for ($i = 0; $i < 2; $i++) {
                $ids[] = $fixtures->create('s6race_' . bin2hex(random_bytes(6)),
                    password_hash('PengujianRace123', PASSWORD_DEFAULT), true, 'admin');
            }
            foreach ([$ids, array_reverse($ids)] as [$actor, $target]) {
                $environment = getenv();
                $command = preserveEmptyEnvironment([PHP_BINARY, __DIR__ . '/workers/kelola-pengguna.php',
                    (string) $actor, (string) $target, $mode], $environment);
                $process = proc_open($command,
                    [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes,
                    dirname(__DIR__), $environment);
                assertTrue(is_resource($process), 'Worker Admin harus dimulai.');
                $processes[] = [$process, $pipes];
            }
            foreach ($processes as [$process, $pipes]) {
                fwrite($pipes[0], "go\n");
                fclose($pipes[0]);
            }
            $codes = [];
            foreach ($processes as [$process, $pipes]) {
                stream_get_contents($pipes[1]);
                stream_get_contents($pipes[2]);
                fclose($pipes[1]);
                fclose($pipes[2]);
                $codes[] = proc_close($process);
            }
            sort($codes);
            assertSame([0, 3], $codes, 'Hanya satu perubahan silang Admin boleh berhasil.');
            $remaining = array_filter(array_map($repo->findById(...), $ids),
                static fn ($row): bool => $row['aktif'] && $row['peran'] === 'admin');
            assertSame(1, count($remaining), 'Satu Admin aktif harus tersisa.');
        } finally {
            foreach ($processes as [$process, $pipes]) {
                if (is_resource($process)) {
                    proc_terminate($process);
                    proc_close($process);
                }
                foreach ($pipes as $pipe) {
                    if (is_resource($pipe)) {
                        fclose($pipe);
                    }
                }
            }
            foreach ($ids as $id) {
                $fixtures->delete($id);
            }
        }
    }
}
