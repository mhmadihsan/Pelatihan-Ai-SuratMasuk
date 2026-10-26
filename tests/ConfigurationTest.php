<?php

declare(strict_types=1);

function test_configuration_defaults_dotenv_and_real_environment(): void
{
    $directory = sys_get_temp_dir() . '/simasuk-config-' . bin2hex(random_bytes(6));
    mkdir($directory . '/config', 0700, true);
    copy(dirname(__DIR__) . '/config/config.php', $directory . '/config/config.php');
    preg_match_all('/^([A-Z_]+)=/m', file_get_contents(dirname(__DIR__) . '/.env.example'), $matches);
    $original = [];
    foreach ($matches[1] as $key) {
        $original[$key] = getenv($key);
        putenv($key);
    }
    try {
        $load = static fn (): array => require $directory . '/config/config.php';
        $defaults = $load();
        assertSame('Asia/Makassar', $defaults['APP_TIMEZONE']);
        assertSame(8889, $defaults['DB_PORT']);
        assertSame('127.0.0.1', $defaults['DB_HOST']);
        assertSame('simasuk', $defaults['DB_NAME']);
        file_put_contents($directory . '/.env', "# Konfigurasi uji\nAPP_NAME=\"Nama Instansi\"\nDB_PORT=13389\nDB_PASS='dari berkas'\n");
        $file = $load();
        assertSame('Nama Instansi', $file['APP_NAME']);
        assertSame(13389, $file['DB_PORT']);
        assertSame('dari berkas', $file['DB_PASS']);
        putenv('APP_NAME=Dari Lingkungan');
        putenv('DB_PASS=');
        $external = $load();
        assertSame('Dari Lingkungan', $external['APP_NAME']);
        assertSame('', $external['DB_PASS'], 'Environment kosong tetap menimpa .env.');
        putenv('DB_PORT=abc');
        $rejected = false;
        try {
            $load();
        } catch (RuntimeException) {
            $rejected = true;
        }
        assertTrue($rejected, 'Port tidak valid harus ditolak.');
    } finally {
        foreach ($original as $key => $value) {
            putenv($value === false ? $key : $key . '=' . $value);
        }
        unlink($directory . '/.env');
        unlink($directory . '/config/config.php');
        rmdir($directory . '/config');
        rmdir($directory);
    }
}
