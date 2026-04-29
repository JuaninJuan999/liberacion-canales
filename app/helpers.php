<?php

declare(strict_types=1);

if (! function_exists('format_decimal_es_trim')) {
    /**
     * Vista es-ES: coma decimal y sin ceros finales (10 → "10", 240 → "240", 10,25 → "10,25").
     */
    function format_decimal_es_trim(float|string|null $value, int $maxDecimals = 3): string
    {
        if ($value === null || $value === '') {
            return '0';
        }

        $x = is_numeric($value) ? (float) $value : 0.0;

        if (! is_finite($x)) {
            return '0';
        }

        $x = round($x, $maxDecimals);
        $s = sprintf('%.'.$maxDecimals.'f', $x);
        $s = rtrim(rtrim($s, '0'), '.');

        return str_replace('.', ',', $s);
    }
}
