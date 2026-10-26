<?php

declare(strict_types=1);

namespace App\Support;

final class AgendaNumber
{
    public static function format(int $nomorUrut, int $tahun): string
    {
        return sprintf('%03d/%d', $nomorUrut, $tahun);
    }
}
