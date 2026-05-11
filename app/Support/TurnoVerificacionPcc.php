<?php

namespace App\Support;

use Carbon\CarbonInterface;
use DateTimeZone;
use InvalidArgumentException;

/**
 * Día operativo PCC: desde turno_hora_fin del día calendario D hasta turno_hora_fin del día siguiente (no inclusive).
 *
 * Ej. con hora 7 — el día D abarca D 07:00:00 hasta (D+1) 06:59:59 según zona de la app.
 */
final class TurnoVerificacionPcc
{
    public static function horaLimiteDelDia(): int
    {
        $h = (int) config('verificacion_pcc.turno_hora_fin', 7);

        return max(0, min(23, $h));
    }

    public static function timezoneApp(): DateTimeZone|string|null
    {
        return config('app.timezone');
    }

    /**
     * Fecha operativa Y-m-d (inicio día calendario) asociada a un instante dado (por defecto ahora).
     */
    public static function fechaOperativa(?CarbonInterface $referencia = null): \Illuminate\Support\Carbon
    {
        $ref = $referencia !== null
            ? \Illuminate\Support\Carbon::parse($referencia, self::timezoneApp())
            : now(self::timezoneApp());

        if ((int) $ref->hour < self::horaLimiteDelDia()) {
            return $ref->copy()->subDay()->startOfDay();
        }

        return $ref->copy()->startOfDay();
    }

    /**
     * @return array{0: \Illuminate\Support\Carbon, 1: \Illuminate\Support\Carbon} inicio y fin inclusivos del turno
     */
    public static function ventanaCreacionParaFechaOperativa(string $fechaYmd): array
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaYmd)) {
            throw new InvalidArgumentException('fecha operativa inválida (se espera Y-m-d).');
        }

        $inicio = \Illuminate\Support\Carbon::parse($fechaYmd, self::timezoneApp())
            ->startOfDay()
            ->setTime(self::horaLimiteDelDia(), 0, 0);
        $fin = $inicio->copy()->addDay()->subSecond();

        return [$inicio, $fin];
    }
}
