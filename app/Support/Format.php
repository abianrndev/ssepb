<?php

namespace App\Support;

class Format
{
    public static function num(float|int|string|null $value, int $maxDecimals = 4): string
    {
        $n = (float) ($value ?? 0);

        // format awal pakai decimal maksimal
        $s = number_format($n, $maxDecimals, ',', '.');

        // buang nol belakang di desimal + buang koma kalau desimal habis
        $s = rtrim(rtrim($s, '0'), ',');

        return $s === '-0' ? '0' : $s;
    }

    public static function m3(float|int|string|null $value, int $maxDecimals = 4): string
    {
        return self::num($value, $maxDecimals) . ' m³';
    }

    public static function rp(float|int|string|null $value): string
    {
        return 'Rp ' . self::num($value, 0);
    }
}