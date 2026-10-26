<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    public function __construct(
        public readonly string $body = '',
        public readonly int $status = 200,
        public readonly array $headers = [],
    ) {
    }

    public static function redirect(string $path): self
    {
        return new self('', 302, ['Location' => $path]);
    }

    public function send(): void
    {
        http_response_code($this->status);
        header('Content-Type: text/html; charset=UTF-8');
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }
        echo $this->body;
    }
}
