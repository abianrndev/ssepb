<?php

namespace App\Http\Controllers\Concerns;

trait FormatsPdfNumbers
{
    protected function fmtNum(float|int|string|null $value, int $maxDecimals = 4): string
    {
        $n = (float) ($value ?? 0);

        $s = number_format($n, $maxDecimals, ',', '.');
        $s = rtrim(rtrim($s, '0'), ',');

        return $s === '-0' ? '0' : $s;
    }

    protected function fmtInt(float|int|string|null $value): string
    {
        return number_format((float) ($value ?? 0), 0, ',', '.');
    }

    protected function fmtM3(float|int|string|null $value, int $maxDecimals = 4): string
    {
        return $this->fmtNum($value, $maxDecimals) . ' m³';
    }

    protected function fmtRp(float|int|string|null $value): string
    {
        return 'Rp ' . $this->fmtInt($value);
    }
}