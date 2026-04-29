<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Lectura en BD PostgreSQL externa (trazabilidad).
 * Solo insensibilización del día actual (plan/turno del día — mismos ID producto del día).
 */
class TrazabilidadInsensibilizacionReader
{
    /**
     * Únicamente registros donde la fecha de insensibilización es la fecha actual del servidor BD (CURRENT_DATE).
     * Orden ascendente por ins.id para cola FIFO (siguiente pendiente).
     */
    public static function sqlInsensibilizacionDelDiaActual(): string
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
    AND (ins.fecha_registro)::date = CURRENT_DATE
ORDER BY ins.id ASC, pe.fecha_registro DESC NULLS LAST, pe.hora_registro DESC NULLS LAST
LIMIT 5000
SQL;
    }

    /**
     * @return list<object>
     */
    public function filasDelDiaActual(): array
    {
        $conn = config('database.connections.pgsql_trazabilidad');
        if (! is_array($conn) || empty($conn['database'])) {
            return [];
        }

        try {
            return DB::connection('pgsql_trazabilidad')->select(self::sqlInsensibilizacionDelDiaActual());
        } catch (Throwable $e) {
            report($e);

            return [];
        }
    }

    public function configuracionLista(): bool
    {
        $conn = config('database.connections.pgsql_trazabilidad');

        return is_array($conn) && ! empty($conn['database']);
    }
}
