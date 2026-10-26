<?php

declare(strict_types=1);

namespace App\Core;

final class Validator
{
    private array $errors = [];

    public function string(
        array $input,
        string $field,
        string $label,
        bool $trim = true,
        bool $required = true,
        ?int $max = null,
    ): string
    {
        $value = $input[$field] ?? '';
        if (!is_string($value)) {
            $this->errors[$field] = $label . ' tidak valid.';
            return '';
        }
        if (str_contains($value, "\0") || !mb_check_encoding($value, 'UTF-8')) {
            $this->errors[$field] = $label . ' tidak valid.';
            return '';
        }
        $value = $trim ? trim($value) : $value;
        if ($value === '' && $required) {
            $this->errors[$field] = $label . ' wajib diisi.';
        } elseif ($max !== null && mb_strlen($value, 'UTF-8') > $max) {
            $this->errors[$field] = $label . ' maksimal ' . $max . ' karakter.';
        }
        return $value;
    }

    public function date(array $input, string $field, string $label): string
    {
        $value = $this->string($input, $field, $label);
        if (isset($this->errors[$field])) {
            return $value;
        }
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        if (!preg_match('/^[1-9][0-9]{3}-[0-9]{2}-[0-9]{2}$/D', $value)
            || $date === false || $date->format('Y-m-d') !== $value) {
            $this->errors[$field] = $label . ' harus berupa tanggal yang valid.';
        }
        return $value;
    }

    public function integer(array $input, string $field, string $label, int $min, int $max): ?int
    {
        $value = $input[$field] ?? '';
        if (!is_string($value) || !preg_match('/^(0|[1-9][0-9]*)$/D', $value)) {
            $this->errors[$field] = $label . ' harus berupa bilangan bulat antara ' . $min . ' dan ' . $max . '.';
            return null;
        }
        $number = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => $min, 'max_range' => $max]]);
        if ($number === false) {
            $this->errors[$field] = $label . ' harus berupa bilangan bulat antara ' . $min . ' dan ' . $max . '.';
            return null;
        }
        return $number;
    }

    public function choice(array $input, string $field, string $label, array $choices, bool $required = true): string
    {
        $value = $this->string($input, $field, $label, required: $required);
        if (!in_array($value, $choices, true)) {
            $this->errors[$field] = $label . ' tidak valid.';
        }
        return $value;
    }

    public function username(string $value, string $field = 'username'): void
    {
        if (!preg_match('/^[a-z0-9._]{3,50}$/D', $value)) {
            $this->errors[$field] = 'Gunakan 3–50 karakter: huruf kecil, angka, titik, atau garis bawah.';
        }
    }

    public function password(string $value, string $field = 'password'): void
    {
        if (mb_strlen($value, 'UTF-8') < 8) {
            $this->errors[$field] = 'Kata sandi minimal 8 karakter.';
        } elseif (strlen($value) > 72) {
            $this->errors[$field] = 'Kata sandi maksimal 72 byte.';
        }
    }

    public function add(string $field, string $message): void
    {
        $this->errors[$field] = $message;
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
