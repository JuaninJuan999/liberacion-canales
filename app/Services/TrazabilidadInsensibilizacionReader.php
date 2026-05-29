<?php

namespace App\Services;

use App\Support\TurnoVerificacionPcc;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Lectura en BD PostgreSQL externa (trazabilidad).
 * Insensibilización alineada al día operativo / turno de faena PCC (no solo fecha calendario).
 */
class TrazabilidadInsensibilizacionReader
{
    /**
     * Cola FIFO de insensibilización para un día operativo de faena ($fechaYmd = inicio calendario D).
     *
     * Incluye:
     * - fecha_registro = D con hora >= cierre de turno (o sin hora).
     * - fecha_registro = D+1 con hora < cierre de turno (madrugada del mismo turno de faena).
     *
     * Excluye madrugada de D (hora < cierre) porque pertenece al turno D-1.
     */
    public static function sqlInsensibilizacionParaDiaOperativo(): string
    {
        return <<<'SQL'
SELECT DISTINCT ON (ins.id)
    ins.id,
    ins.id_proceso,
    ins.id_parte_producto,
    ins.id_producto,
    ins.fecha_registro,
    ins.hora_registro,
    pfp.id_plan_faena,
    pft.id_registro_turno,
    pft.user_name AS usuario_turno,
    e.nombre AS nombre_empresa,
    pe.fecha_registro AS fecha_asociacion,
    pe.hora_registro AS hora_asociacion
FROM trazabilidad_proceso.insensibilizacion ins
LEFT JOIN trazabilidad_proceso.plan_faena_producto pfp
    ON ins.id_producto = pfp.id_producto
LEFT JOIN trazabilidad_proceso.plan_faena_turno pft
    ON pfp.id_plan_faena = pft.id_plan_faena
LEFT JOIN trazabilidad_proceso.producto_empresa pe
    ON ins.id_producto = pe.id_producto
LEFT JOIN organizaciones.empresa e
    ON pe.id_empresa = e.id
WHERE ins.fecha_registro IS NOT NULL
    AND (
        (
            (ins.fecha_registro)::date = CAST(? AS date)
            AND (
                ins.hora_registro IS NULL
                OR EXTRACT(HOUR FROM ins.hora_registro)::int >= ?
            )
        )
        OR (
            (ins.fecha_registro)::date = (CAST(? AS date) + INTERVAL '1 day')::date
            AND ins.hora_registro IS NOT NULL
            AND EXTRACT(HOUR FROM ins.hora_registro)::int < ?
        )
    )
ORDER BY ins.id ASC, pe.fecha_registro DESC NULLS LAST, pe.hora_registro DESC NULLS LAST
LIMIT 5000
SQL;
    }

    /** @deprecated Use sqlInsensibilizacionParaDiaOperativo() — conservado por compatibilidad interna. */
    public static function sqlInsensibilizacionParaFecha(): string
    {
        return self::sqlInsensibilizacionParaDiaOperativo();
    }

    /**
     * Insensibilizaciones del turno de faena para la fecha operativa dada (Y-m-d).
     *
     * @return list<object>
     */
    public function filasParaDiaOperativo(string $fechaYmd): array
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaYmd)) {
            return [];
        }

        $conn = config('database.connections.pgsql_trazabilidad');
        if (! is_array($conn) || empty($conn['database'])) {
            return [];
        }

        $horaLimite = TurnoVerificacionPcc::horaLimiteDelDia();

        try {
            return DB::connection('pgsql_trazabilidad')->select(
                self::sqlInsensibilizacionParaDiaOperativo(),
                [$fechaYmd, $horaLimite, $fechaYmd, $horaLimite]
            );
        } catch (Throwable $e) {
            report($e);

            return [];
        }
    }

    /**
     * @return list<object>
     *
     * @deprecated Use filasParaDiaOperativo() — el parámetro es fecha operativa de faena, no solo calendario.
     */
    public function filasParaFecha(string $fechaYmd): array
    {
        return $this->filasParaDiaOperativo($fechaYmd);
    }

    /**
     * Filas correspondientes al día operativo PCC actual (permite madrugada como continuación del turno anterior).
     *
     * @return list<object>
     */
    public function filasDelDiaOperativo(?CarbonInterface $instanteReferencia = null): array
    {
        return $this->filasParaDiaOperativo(
            TurnoVerificacionPcc::fechaOperativa($instanteReferencia)->format('Y-m-d')
        );
    }

    /** @see filasDelDiaOperativo */
    public function filasDelDiaActual(): array
    {
        return $this->filasDelDiaOperativo();
    }

    public function configuracionLista(): bool
    {
        $conn = config('database.connections.pgsql_trazabilidad');

        return is_array($conn) && ! empty($conn['database']);
    }
}
