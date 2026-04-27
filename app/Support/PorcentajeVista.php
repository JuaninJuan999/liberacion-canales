<?php

namespace App\Support;

class PorcentajeVista
{
    /**
     * % sobre medias canales, redondeado a 2 decimales, a partir de un ratio 0-1.
     * Misma base que {@see mediaCanalFormato2} (tarjetas y gráfica deben coincidir).
     */
    public static function mediaCanalPuntos2(float $ratio): float
    {
        return round($ratio * 100, 2);
    }

    /**
     * Formato con coma decimal (ej. 0,64%) a partir de un ratio 0-1.
     */
    public static function mediaCanalFormato2(float $ratio): string
    {
        $t = self::mediaCanalPuntos2($ratio);

        return number_format($t, 2, ',', '.').'%';
    }
}
