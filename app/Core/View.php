<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $template, array $data = [], int $status = 200): Response
    {
        if (!preg_match('~^[a-zA-Z0-9_/-]+$~D', $template)) {
            throw new \InvalidArgumentException('Nama tampilan tidak valid.');
        }
        $templatePath = dirname(__DIR__) . '/Views/' . $template . '.php';
        if (!is_file($templatePath)) {
            throw new \RuntimeException('Tampilan tidak ditemukan.');
        }
        extract($data, EXTR_SKIP);
        $title ??= config('APP_NAME');
        $currentUser ??= null;
        $errors ??= [];
        $old ??= [];
        $flash = Session::pullFlash();
        ob_start();
        try {
            if ($template === 'laporan/cetak') {
                require $templatePath;
            } else {
                require dirname(__DIR__) . '/Views/layouts/app.php';
            }
            return new Response((string) ob_get_clean(), $status);
        } catch (\Throwable $exception) {
            ob_end_clean();
            throw $exception;
        }
    }

    public static function error(int $status): Response
    {
        return self::render('errors/' . $status, ['title' => 'Terjadi kendala'], $status);
    }
}
