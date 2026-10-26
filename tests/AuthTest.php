<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Database;
use App\Core\HttpException;
use App\Repositories\Testing\UserFixtureRepository;
use App\Repositories\UserRepository;

function test_auth_seed_migrations_and_http_lifecycle(): void
{
    $first = runCli('database/migrate.php');
    assertSame(0, $first['code'], 'Migrasi gagal: ' . $first['error']);
    $second = runCli('database/migrate.php');
    assertSame(0, $second['code']);
    assertTrue(str_contains($second['output'], 'Semua migrasi sudah dijalankan.'), 'Migrasi harus idempoten.');
    $db = Database::connection();
    $users = new UserRepository($db);
    $fixtures = new UserFixtureRepository($db);
    assertSame('+08:00', $fixtures->sessionTimeZone());
    $suffix = bin2hex(random_bytes(5));
    $username = 's1seed_' . $suffix;
    $initial = 'AwalPengujian123';
    $changed = 'BaruPengujian456';
    $ids = [];
    try {
        $environment = ['SEED_ADMIN_USERNAME' => $username, 'SEED_ADMIN_PASSWORD' => $initial];
        $seed = runCli('database/seed.php', $environment);
        assertSame(0, $seed['code'], 'Seed gagal: ' . $seed['error']);
        assertSame('Admin dibuat', trim($seed['output']));
        $user = $users->findByUsername($username);
        assertTrue($user !== null, 'Admin seed harus tersimpan.');
        $ids[] = $user['id'];
        assertSame('admin', $user['peran']);
        assertSame(true, $user['wajib_ganti_password']);
        assertTrue(password_verify($initial, $user['password_hash']), 'Seed harus memakai password_hash.');
        $seed = runCli('database/seed.php', $environment);
        assertSame(0, $seed['code']);
        assertSame('Admin sudah ada', trim($seed['output']));
        assertSame($user['password_hash'], $users->findByUsername($username)['password_hash']);
        $inactiveUsername = 's1off_' . $suffix;
        $ids[] = $fixtures->create($inactiveUsername, password_hash($initial, PASSWORD_DEFAULT), false);
        $operatorUsername = 's1op_' . $suffix;
        $operatorId = $fixtures->create($operatorUsername, password_hash($initial, PASSWORD_DEFAULT));
        $ids[] = $operatorId;
        $_SESSION = ['user_id' => $operatorId];
        try {
            Auth::requireRole('admin');
            throw new RuntimeException('Operator tidak boleh lolos otorisasi Admin.');
        } catch (HttpException $exception) {
            assertSame(403, $exception->status);
        }
        $_SESSION = [];

        withServer(static function (string $base) use ($username, $initial, $changed, $inactiveUsername, $users, $operatorUsername, $operatorId, $fixtures): void {
            $cookies = [];
            $login = httpRequest($base . '/login', $cookies);
            $token = csrfToken($login);
            $guestSession = $cookies['simasuk_session'];
            foreach ([[$username, 'SalahPassword'], [$inactiveUsername, $initial], ['tidak_ada_' . $username, $initial]] as [$name, $password]) {
                $response = httpRequest($base . '/login', $cookies, 'POST', [
                    '_token' => $token, 'username' => $name, 'password' => $password,
                ]);
                assertSame(200, $response['status']);
                assertTrue(str_contains($response['body'], 'Username atau kata sandi salah, atau akun tidak aktif.'),
                    'Login salah dan akun nonaktif harus mendapat pesan umum.');
            }
            $response = httpRequest($base . '/login', $cookies, 'POST', [
                '_token' => $token, 'username' => [$username], 'password' => [$initial],
            ]);
            assertSame(200, $response['status'], 'Input berbentuk array harus menjadi galat form.');
            $response = httpRequest($base . '/login', $cookies, 'POST', [
                '_token' => $token, 'username' => $username, 'password' => $initial,
            ]);
            assertSame(302, $response['status']);
            assertSame('/ganti-password', $response['headers']['location']);
            assertTrue($guestSession !== $cookies['simasuk_session'], 'ID sesi harus berganti saat login.');
            $revoked = ['simasuk_session' => $guestSession];
            assertSame('/login', httpRequest($base . '/surat', $revoked)['headers']['location']);
            foreach (['/', '/surat', '/laporan', '/pengguna', '/login'] as $path) {
                $response = httpRequest($base . $path, $cookies);
                assertSame('/ganti-password', $response['headers']['location'], 'Wajib ganti sandi harus mengunci semua halaman lain.');
            }
            $form = httpRequest($base . '/ganti-password', $cookies);
            $token = csrfToken($form);
            assertSame(200, $form['status']);
            $response = httpRequest($base . '/ganti-password', $cookies, 'POST', [
                '_token' => $token, 'password_lama' => 'Salah', 'password_baru' => 'pendek', 'konfirmasi_password' => 'beda',
            ]);
            assertSame(200, $response['status']);
            assertTrue(str_contains($response['body'], 'Kata sandi saat ini salah.'), 'Kata sandi lama harus diperiksa.');
            assertTrue(str_contains($response['body'], 'minimal 8 karakter'), 'Kata sandi pendek harus ditolak.');
            assertTrue(str_contains($response['body'], 'Konfirmasi kata sandi tidak cocok.'), 'Konfirmasi harus diperiksa.');
            assertSame(true, $users->findByUsername($username)['wajib_ganti_password']);
            $response = httpRequest($base . '/ganti-password', $cookies, 'POST', [
                '_token' => $token, 'password_lama' => $initial, 'password_baru' => $initial, 'konfirmasi_password' => $initial,
            ]);
            assertSame(200, $response['status']);
            assertTrue(str_contains($response['body'], 'Gunakan kata sandi yang berbeda'), 'Sandi awal harus benar-benar diganti.');
            $beforeChange = $cookies['simasuk_session'];
            $response = httpRequest($base . '/ganti-password', $cookies, 'POST', [
                '_token' => $token, 'password_lama' => $initial, 'password_baru' => $changed, 'konfirmasi_password' => $changed,
            ]);
            assertSame(302, $response['status']);
            assertSame('/', $response['headers']['location']);
            assertTrue($beforeChange !== $cookies['simasuk_session'], 'ID sesi harus berganti setelah perubahan sandi.');
            $saved = $users->findByUsername($username);
            assertSame(false, $saved['wajib_ganti_password']);
            assertTrue(password_verify($changed, $saved['password_hash']), 'Kata sandi baru harus tersimpan sebagai hash.');
            assertSame(false, password_verify($initial, $saved['password_hash']));
            $dashboard = httpRequest($base . '/', $cookies);
            assertSame(200, $dashboard['status']);
            assertTrue(str_contains($dashboard['body'], 'Selamat datang, Administrator'), 'Dashboard kosong harus tampil.');
            assertTrue(str_contains($dashboard['body'], 'Kata sandi berhasil diganti.'), 'Pesan sukses harus tampil.');
            assertTrue(!str_contains(httpRequest($base . '/', $cookies)['body'], 'Kata sandi berhasil diganti.'), 'Flash hanya sekali.');
            assertSame(404, httpRequest($base . '/tidak-ada', $cookies)['status']);
            assertSame(404, httpRequest($base . '/logout', $cookies)['status'], 'GET tidak boleh logout.');
            assertSame(200, httpRequest($base . '/', $cookies)['status']);
            assertSame(419, httpRequest($base . '/logout', $cookies, 'POST', ['_token' => $token])['status'], 'Token lama harus dicabut.');
            $token = csrfToken($dashboard);
            $response = httpRequest($base . '/logout', $cookies, 'POST', ['_token' => $token]);
            assertSame('/login', $response['headers']['location']);
            assertSame('/login', httpRequest($base . '/surat', $cookies)['headers']['location']);
            $token = csrfToken(httpRequest($base . '/login', $cookies));
            $response = httpRequest($base . '/login', $cookies, 'POST', ['_token' => $token, 'username' => $username, 'password' => $initial]);
            assertSame(200, $response['status'], 'Sandi lama tidak boleh bisa login.');
            $response = httpRequest($base . '/login', $cookies, 'POST', ['_token' => $token, 'username' => $username, 'password' => $changed]);
            assertSame('/', $response['headers']['location']);

            $operatorCookies = [];
            $token = csrfToken(httpRequest($base . '/login', $operatorCookies));
            $response = httpRequest($base . '/login', $operatorCookies, 'POST', ['_token' => $token, 'username' => $operatorUsername, 'password' => $initial]);
            assertSame('/', $response['headers']['location']);
            $dashboard = httpRequest($base . '/', $operatorCookies);
            assertTrue(str_contains($dashboard['body'], '&lt;Operator &amp; Uji&gt;'), 'Nama pengguna harus di-escape.');
            $fixtures->deactivate($operatorId);
            assertSame('/login', httpRequest($base . '/', $operatorCookies)['headers']['location'], 'Akun nonaktif harus kehilangan akses.');
        });

        withServer(static function (string $base) use ($username, $changed): void {
            $cookies = [];
            $token = csrfToken(httpRequest($base . '/login', $cookies));
            httpRequest($base . '/login', $cookies, 'POST', ['_token' => $token, 'username' => $username, 'password' => $changed]);
            $file = dirname(__DIR__) . '/storage/sessions/sess_' . $cookies['simasuk_session'];
            $session = file_get_contents($file);
            $expired = time() - config('SESSION_IDLE_MINUTES') * 60 - 1;
            $session = preg_replace('/last_activity\|i:[0-9]+;/', 'last_activity|i:' . $expired . ';', $session);
            file_put_contents($file, $session);
            $response = httpRequest($base . '/', $cookies);
            assertSame('/login', $response['headers']['location'], 'Sesi idle harus kedaluwarsa.');
        });
    } finally {
        $_SESSION = [];
        foreach (array_reverse($ids) as $id) {
            $fixtures->delete($id);
        }
    }
}
