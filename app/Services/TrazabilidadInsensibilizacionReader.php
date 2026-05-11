<?php

namespace App\Services;

use App\Support\TurnoVerificacionPcc;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Lectura en BD PostgreSQL externa (trazabilidad).
 * Insensibilización para una fecha de registro (`ins.fecha_registro`), alineada al día operativo PCC de la aplicación.
 */
class TrazabilidadInsensibilizacionReader
{
    /**
     * Registros donde la fecha de insensibilización coincide con una fecha calendario dada ($fechaYmd).
     * Parámetro enlazado (no concatenar fecha en texto crudo desde fuera).
     * Orden ascendente por ins.id para cola FIFO (siguiente pendiente).
     */
    public static function sqlInsensibilizacionParaFecha(): string
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
    AND (ins.fecha_registro)::date = CAST(? AS date)
ORDER BY ins.id ASC, pe.fecha_registro DESC NULLS LAST, pe.hora_registro DESC NULLS LAST
LIMIT 5000
SQL;
    }

    /**
     * @return list<object>
     */
    public function filasParaFecha(string $fechaYmd): array
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaYmd)) {
            return [];
        }

        $conn = config('database.connections.pgsql_trazabilidad');
        if (! is_array($conn) || empty($conn['database'])) {
            return [];
        }

        try {
            return DB::connection('pgsql_trazabilidad')->select(
                self::sqlInsensibilizacionParaFecha(),
                [$fechaYmd]
            );
        } catch (Throwable $e) {
            report($e);

            return [];
        }
    }

    /**
     * Filas correspondientes al día operativo PCC actual (permite madrugada como continuación del turno anterior).
     *
     * @return list<object>
     */
    public function filasDelDiaOperativo(?CarbonInterface $instanteReferencia = null): array
    {
        return $this->filasParaFecha(
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
