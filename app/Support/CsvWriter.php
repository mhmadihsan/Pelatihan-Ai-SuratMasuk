<?php

declare(strict_types=1);

namespace App\Support;

final class CsvWriter
{
    public static function write(array $rows): string
    {
        $stream = fopen('php://temp', 'w+');
        if ($stream === false) {
            throw new \RuntimeException('Ekspor tidak dapat dibuat.');
        }
        try {
            fwrite($stream, "\xEF\xBB\xBF");
            foreach ($rows as $row) {
                $cells = array_map(static function (mixed $value): string {
                    $value = (string) $value;
                    return preg_match('/^[=+@\\-\t\r]/', $value) ? "'" . $value : $value;
                }, $row);
                fputcsv($stream, $cells, ';', '"', '', "\r\n");
            }
            rewind($stream);
            return (string) stream_get_contents($stream);
        } finally {
            fclose($stream);
        }
    }
}
