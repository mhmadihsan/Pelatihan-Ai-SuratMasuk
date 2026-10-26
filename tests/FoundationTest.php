<?php

declare(strict_types=1);

use App\Core\Csrf;
use App\Core\Validator;
use App\Core\View;

function test_escape_and_form_preserve_only_username(): void
{
    $_SESSION = [];
    $html = View::render('auth/login', ['old' => ['username' => '"><script>alert(1)</script>']])->body;
    assertTrue(!str_contains($html, '<script>'), 'Input HTML harus di-escape.');
    assertTrue(str_contains($html, '&lt;script&gt;'), 'Username lama harus dipertahankan dengan aman.');
    assertSame('&quot;&#039;&lt;&gt;&amp;', e("\"'<>&"));
    assertTrue(!preg_match('/type="password"[^>]*value=/', $html), 'Kata sandi tidak diisi ulang.');
}

function test_csrf_rejects_missing_array_and_old_token(): void
{
    $_SESSION = [];
    assertSame(false, Csrf::verify('anything'));
    $token = Csrf::token();
    assertSame(64, strlen($token));
    assertSame(true, Csrf::verify($token));
    assertSame(false, Csrf::verify(null));
    assertSame(false, Csrf::verify([$token]));
    Csrf::rotate();
    assertSame(false, Csrf::verify($token));
    assertSame(true, Csrf::verify(Csrf::token()));
}

function test_validator_username_password_and_malformed_input(): void
{
    foreach (['admin', 'a.b_123', str_repeat('a', 50)] as $username) {
        $validator = new Validator();
        $validator->username($username);
        assertSame([], $validator->errors());
    }
    foreach (['ab', 'Admin', 'a-b', str_repeat('a', 51)] as $username) {
        $validator = new Validator();
        $validator->username($username);
        assertTrue(isset($validator->errors()['username']), 'Username tidak valid harus ditolak.');
    }
    $validator = new Validator();
    $validator->password('1234567');
    assertTrue(isset($validator->errors()['password']), 'Password pendek harus ditolak.');
    $validator = new Validator();
    $validator->password('12345678');
    assertSame([], $validator->errors());
    $validator = new Validator();
    $validator->password(str_repeat('a', 73));
    assertTrue(isset($validator->errors()['password']), 'Sandi melebihi batas byte bcrypt harus ditolak.');
    $validator = new Validator();
    $validator->string(['username' => ['admin']], 'username', 'Username');
    $validator->string(['password' => "bad\0value"], 'password', 'Kata sandi', false);
    assertSame(2, count($validator->errors()));
}

function test_error_pages_are_indonesian_and_have_correct_status(): void
{
    $_SESSION = [];
    foreach ([403, 404, 419, 500] as $status) {
        $response = View::error($status);
        assertSame($status, $response->status);
        assertTrue(str_contains($response->body, 'lang="id"'), 'Bahasa halaman harus Indonesia.');
        assertTrue(!str_contains($response->body, 'SQLSTATE'), 'Detail database tidak boleh ditampilkan.');
    }
}

function test_http_guests_csrf_headers_assets_and_secure_cookie(): void
{
    withServer(static function (string $base): void {
        $cookies = [];
        $response = httpRequest($base . '/surat', $cookies);
        assertSame(302, $response['status']);
        assertSame('/login', $response['headers']['location']);
        $login = httpRequest($base . '/login', $cookies);
        assertSame(200, $login['status']);
        csrfToken($login);
        assertSame('nosniff', $login['headers']['x-content-type-options']);
        assertSame('DENY', $login['headers']['x-frame-options']);
        assertSame('same-origin', $login['headers']['referrer-policy']);
        $cookie = strtolower($response['headers']['set-cookie']);
        assertTrue(str_contains($cookie, 'httponly') && str_contains($cookie, 'samesite=lax'), 'Cookie sesi harus aman.');
        foreach (['/login', '/logout', '/ganti-password', '/tidak-ada'] as $path) {
            $response = httpRequest($base . $path, $cookies, 'POST', ['username' => 'admin']);
            assertSame(419, $response['status'], 'Semua POST wajib CSRF.');
        }
        $response = httpRequest($base . '/assets/app.css', $cookies);
        assertSame(200, $response['status']);
        assertTrue(strlen($response['body']) > 1000, 'CSS Tailwind hasil build harus tersedia.');
    });
    withServer(static function (string $base): void {
        $cookies = [];
        $response = httpRequest($base . '/login', $cookies);
        assertTrue(str_contains(strtolower($response['headers']['set-cookie']), 'secure'),
            'Produksi wajib cookie Secure.');
    }, ['APP_ENV' => 'production']);
}

function test_http_database_error_is_generic_and_logged(): void
{
    $production = runCli('tests/workers/check-production.php', ['APP_ENV' => 'production']);
    assertSame(0, $production['code']);
    assertSame('disabled', $production['output'], 'Produksi harus mematikan display_errors.');
    withServer(static function (string $base): void {
        $cookies = [];
        $token = csrfToken(httpRequest($base . '/login', $cookies));
        $response = httpRequest($base . '/login', $cookies, 'POST', [
            '_token' => $token, 'username' => 'admin', 'password' => 'TidakDicatat123',
        ]);
        assertSame(500, $response['status']);
        assertTrue(!str_contains($response['body'], 'SQLSTATE') && !str_contains($response['body'], 'PDOException'),
            'Error database tidak boleh bocor.');
        $log = file_get_contents(dirname(__DIR__) . '/storage/logs/app.log');
        assertTrue(str_contains($log, 'PDOException'), 'Exception harus dicatat.');
        assertTrue(!str_contains($log, 'TidakDicatat123'), 'Password tidak boleh masuk log.');
    }, ['DB_PORT' => '1', 'APP_ENV' => 'production']);
}
