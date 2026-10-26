<?php

declare(strict_types=1);

namespace App\Support;

final class DatabaseBackup
{
    public static function dump(string $path): void
    {
        $output = fopen($path, 'x');
        if ($output === false) {
            throw new \RuntimeException('Berkas cadangan harus baru dan dapat ditulis.');
        }
        chmod($path, 0600);
        try {
            self::run('mysqldump', ['--single-transaction', '--no-tablespaces', '--set-gtid-purged=OFF',
                '--column-statistics=0', config('DB_NAME')], ['pipe', 'r'], $output);
        } catch (\Throwable $exception) {
            fclose($output);
            unlink($path);
            throw $exception;
        }
        fclose($output);
    }

    public static function restoreTest(string $path): void
    {
        if (config('APP_ENV') !== 'testing' || config('DB_NAME') !== 'simasuk_test'
            || getenv('SIMASUK_RESTORE_TEST') !== '1') {
            throw new \RuntimeException('Pemulihan otomatis hanya diizinkan pada simasuk_test dengan SIMASUK_RESTORE_TEST=1.');
        }
        $input = fopen($path, 'r');
        if ($input === false) {
            throw new \RuntimeException('Cadangan tidak dapat dibaca.');
        }
        try {
            self::run('mysql', [config('DB_NAME')], $input, ['pipe', 'w']);
        } finally {
            fclose($input);
        }
    }

    private static function run(string $binary, array $arguments, mixed $input, mixed $output): void
    {
        $directory = sys_get_temp_dir() . '/simasuk-client-' . bin2hex(random_bytes(8));
        if (!mkdir($directory, 0700)) {
            throw new \RuntimeException('Konfigurasi cadangan sementara gagal dibuat.');
        }
        $file = $directory . '/client.cnf';
        $quote = static fn (string $value): string => '"' . str_replace(
            ["\\", '"', "\n", "\r"], ["\\\\", '\\"', '\\n', '\\r'], $value) . '"';
        $options = "[client]\nprotocol=tcp\n";
        foreach (['host' => 'DB_HOST', 'port' => 'DB_PORT', 'user' => 'DB_USER', 'password' => 'DB_PASS'] as $key => $config) {
            $options .= $key . '=' . $quote((string) config($config)) . "\n";
        }
        $handle = fopen($file, 'x');
        if ($handle === false) {
            rmdir($directory);
            throw new \RuntimeException('Konfigurasi cadangan sementara gagal dibuat.');
        }
        chmod($file, 0600);
        fwrite($handle, $options);
        fclose($handle);
        try {
            $clientDir = getenv('MYSQL_BIN_DIR');
            $command = ($clientDir === false || $clientDir === '') ? $binary : rtrim($clientDir, '/') . '/' . $binary;
            $process = proc_open([$command, '--defaults-file=' . $file, ...$arguments],
                [0 => $input, 1 => $output, 2 => ['pipe', 'w']], $pipes);
            if (!is_resource($process)) {
                throw new \RuntimeException('Klien MySQL tidak dapat dijalankan.');
            }
            if (isset($pipes[0])) {
                fclose($pipes[0]);
            }
            if (isset($pipes[1])) {
                stream_get_contents($pipes[1]);
                fclose($pipes[1]);
            }
            stream_get_contents($pipes[2]);
            fclose($pipes[2]);
            if (proc_close($process) !== 0) {
                throw new \RuntimeException('Cadangan/pemulihan MySQL gagal. Periksa klien, koneksi, dan hak akses.');
            }
        } finally {
            unlink($file);
            rmdir($directory);
        }
    }
}
