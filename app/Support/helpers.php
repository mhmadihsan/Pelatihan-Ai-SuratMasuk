<?php

declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function config(string $key): mixed
{
    if (!array_key_exists($key, $GLOBALS['config'])) {
        throw new InvalidArgumentException('Konfigurasi tidak dikenal.');
    }
    return $GLOBALS['config'][$key];
}

function tanggal(string $value): string
{
    return (new DateTimeImmutable($value))->format('d/m/Y');
}
