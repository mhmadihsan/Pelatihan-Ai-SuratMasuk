<?php

declare(strict_types=1);

$values = [];
$envFile = dirname(__DIR__) . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (!preg_match('/^([A-Z][A-Z0-9_]*)\s*=\s*(.*)$/', $line, $match)) {
            throw new RuntimeException('Format konfigurasi tidak valid.');
        }
        $value = trim($match[2]);
        if (strlen($value) >= 2 && in_array($value[0], ['"', "'"], true)
            && $value[-1] === $value[0]) {
            $value = substr($value, 1, -1);
        }
        $values[$match[1]] = $value;
    }
}

$env = static function (string $key, string $default) use ($values): string {
    $value = getenv($key);
    return $value !== false ? $value : ($values[$key] ?? $default);
};
$positiveInt = static function (string $key, string $default) use ($env): int {
    $value = filter_var($env($key, $default), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($value === false) {
        throw new RuntimeException('Konfigurasi bilangan tidak valid: ' . $key);
    }
    return $value;
};

$config = [
    'APP_NAME' => $env('APP_NAME', 'SIMasuk'),
    'APP_ENV' => $env('APP_ENV', 'local'),
    'APP_URL' => $env('APP_URL', 'http://localhost:8888'),
    'APP_TIMEZONE' => $env('APP_TIMEZONE', 'Asia/Makassar'),
    'DB_HOST' => $env('DB_HOST', '127.0.0.1'),
    'DB_PORT' => $positiveInt('DB_PORT', '8889'),
    'DB_NAME' => $env('DB_NAME', 'simasuk'),
    'DB_USER' => $env('DB_USER', 'root'),
    'DB_PASS' => $env('DB_PASS', 'root'),
    'INSTANSI_NAMA' => $env('INSTANSI_NAMA', 'NAMA INSTANSI'),
    'INSTANSI_ALAMAT' => $env('INSTANSI_ALAMAT', 'Alamat instansi'),
    'PER_PAGE' => $positiveInt('PER_PAGE', '20'),
    'SESSION_IDLE_MINUTES' => $positiveInt('SESSION_IDLE_MINUTES', '120'),
    'SEED_ADMIN_USERNAME' => $env('SEED_ADMIN_USERNAME', 'admin'),
    'SEED_ADMIN_PASSWORD' => $env('SEED_ADMIN_PASSWORD', 'GantiSaya123'),
];
if (!in_array($config['APP_ENV'], ['local', 'testing', 'production'], true)
    || !preg_match('/^[a-zA-Z0-9_]+$/D', $config['DB_NAME'])
    || !preg_match('/^[a-zA-Z0-9.:-]+$/D', $config['DB_HOST'])
    || $config['DB_PORT'] > 65535) {
    throw new RuntimeException('Konfigurasi aplikasi tidak valid.');
}

return $config;
