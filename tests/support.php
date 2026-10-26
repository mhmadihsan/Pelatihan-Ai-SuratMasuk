<?php

declare(strict_types=1);

function assertSame(mixed $expected, mixed $actual, string $message = ''): void
{
    if ($expected !== $actual) {
        throw new RuntimeException($message !== '' ? $message : sprintf('Harap %s, mendapat %s',
            var_export($expected, true), var_export($actual, true)));
    }
}

function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function withServer(callable $test, array $environment = []): void
{
    $socket = stream_socket_server('tcp://127.0.0.1:0', $code, $message);
    if ($socket === false) {
        throw new RuntimeException('Port server tes tidak tersedia.');
    }
    $address = stream_socket_get_name($socket, false);
    fclose($socket);
    $log = dirname(__DIR__) . '/storage/logs/test-server.log';
    $environment = array_merge(getenv(), $environment);
    $process = proc_open(
        preserveEmptyEnvironment([PHP_BINARY, '-S', $address, '-t', dirname(__DIR__) . '/public'], $environment),
        [0 => ['pipe', 'r'], 1 => ['file', $log, 'a'], 2 => ['file', $log, 'a']],
        $pipes,
        dirname(__DIR__),
        $environment,
    );
    if (!is_resource($process)) {
        throw new RuntimeException('Server tes tidak dapat dimulai.');
    }
    fclose($pipes[0]);
    try {
        $ready = false;
        for ($attempt = 0; $attempt < 50; $attempt++) {
            $connection = @stream_socket_client('tcp://' . $address, $code, $message, 0.1);
            if ($connection !== false) {
                fclose($connection);
                $ready = true;
                break;
            }
            usleep(100000);
        }
        assertTrue($ready, 'Server tes tidak siap; periksa storage/logs/test-server.log.');
        $test('http://' . $address);
    } finally {
        proc_terminate($process);
        proc_close($process);
    }
}

function httpRequest(string $url, array &$cookies, string $method = 'GET', array $data = []): array
{
    $cookieHeader = [];
    foreach ($cookies as $key => $value) {
        $cookieHeader[] = $key . '=' . $value;
    }
    $headers = ['Connection: close'];
    if ($cookieHeader !== []) {
        $headers[] = 'Cookie: ' . implode('; ', $cookieHeader);
    }
    if ($method === 'POST') {
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    }
    $context = stream_context_create(['http' => [
        'method' => $method,
        'header' => implode("\r\n", $headers),
        'content' => $method === 'POST' ? http_build_query($data) : '',
        'ignore_errors' => true,
        'follow_location' => 0,
        'timeout' => 10,
    ]]);
    $body = file_get_contents($url, false, $context);
    if ($body === false) {
        throw new RuntimeException('HTTP tes gagal.');
    }
    $responseHeaders = $http_response_header;
    preg_match('/HTTP\/\S+ (\d+)/', $responseHeaders[0], $match);
    $mappedHeaders = [];
    foreach (array_slice($responseHeaders, 1) as $header) {
        [$name, $value] = explode(':', $header, 2);
        $mappedHeaders[strtolower($name)] = trim($value);
        if (strtolower($name) === 'set-cookie' && preg_match('/^([^=]+)=([^;]*)/', trim($value), $cookie)) {
            if ($cookie[2] === '' || $cookie[2] === 'deleted') {
                unset($cookies[$cookie[1]]);
            } else {
                $cookies[$cookie[1]] = $cookie[2];
            }
        }
    }
    return ['status' => (int) $match[1], 'headers' => $mappedHeaders, 'body' => $body];
}

function csrfToken(array $response): string
{
    assertTrue((bool) preg_match('/name="_token" value="([a-f0-9]{64})"/', $response['body'], $match),
        'Token CSRF harus tersedia.');
    return $match[1];
}

function runCli(string $script, array $environment = []): array
{
    $environment = array_merge(getenv(), $environment);
    $command = preserveEmptyEnvironment([PHP_BINARY, dirname(__DIR__) . '/' . $script], $environment);
    $process = proc_open($command,
        [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
        $pipes, dirname(__DIR__), $environment);
    if (!is_resource($process)) {
        throw new RuntimeException('CLI tes tidak dapat dimulai.');
    }
    fclose($pipes[0]);
    $output = stream_get_contents($pipes[1]);
    $error = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    return ['code' => proc_close($process), 'output' => $output, 'error' => $error];
}

function preserveEmptyEnvironment(array $command, array $environment): array
{
    // proc_open melewatkan nilai kosong; DB_PASS kosong tetap harus menimpa .env.
    foreach ($environment as $name => $value) {
        if ($value === '') {
            $command = array_merge(['/usr/bin/env', $name . '='], $command);
        }
    }
    return $command;
}

function loginHttp(string $base, string $username, string $password, array &$cookies): string
{
    $token = csrfToken(httpRequest($base . '/login', $cookies));
    $response = httpRequest($base . '/login', $cookies, 'POST', [
        '_token' => $token, 'username' => $username, 'password' => $password,
    ]);
    assertSame(302, $response['status']);
    return csrfToken(httpRequest($base . '/ganti-password', $cookies));
}
