<?php

namespace App\Services;

use App\Models\IndicadorDiario;
use App\Observers\RegistroHallazgoObserver;
use Carbon\Carbon;

class CalculadoraIndicadores
{
    /**
     * Recalcula y devuelve los indicadores del día (tabla indicadores_diarios).
     */
    public function calcularIndicadoresDia(string|Carbon $fecha): ?IndicadorDiario
    {
        $fechaStr = Carbon::parse($fecha)->toDateString();
        app(RegistroHallazgoObserver::class)->sincronizarIndicadoresParaFecha($fechaStr);

        return IndicadorDiario::where('fecha_operacion', $fechaStr)->first();
    }

    /**
     * Calcular indicadores mensuales a partir de los registros diarios.
     */
    public function calcularIndicadoresMes($mes, $anio)
    {
        $fechaInicio = Carbon::create($anio, $mes, 1)->startOfMonth();
        $fechaFin = Carbon::create($anio, $mes, 1)->endOfMonth();

        $indicadoresDiarios = IndicadorDiario::whereBetween('fecha_operacion', [$fechaInicio, $fechaFin])->get();

        if ($indicadoresDiarios->isEmpty()) {
            return null; // No hay datos para el mes
        }

        // Sumar todos los campos numéricos de los indicadores diarios
        $sumas = [];
        $columnas = array_keys($indicadoresDiarios->first()->getAttributes());

        foreach ($columnas as $columna) {
            if (is_numeric($indicadoresDiarios->first()->$columna) && ! in_array($columna, ['id', 'mes', 'año'])) {
                $sumas[$columna] = $indicadoresDiarios->sum($columna);
            }
        }

        // Calcular promedios y porcentajes agregados
        $diasProcesados = $indicadoresDiarios->count();
        $totalAnimales = $sumas['animales_procesados'] ?? 0;
        $totalHallazgos = $sumas['total_hallazgos'] ?? 0;
        $mediasCanalTotal = $sumas['medias_canales_total'] ?? 0;
        $canalesLiberadas = ($sumas['medias_canal_1'] ?? 0) + ($sumas['medias_canal_2'] ?? 0);

        $promedio_liberacion = $mediasCanalTotal > 0
            ? round(($canalesLiberadas / $mediasCanalTotal) * 100, 2)
            : 0;

        $participacionMensual = $mediasCanalTotal > 0
            ? round(($totalHallazgos / $mediasCanalTotal) * 100, 2)
            : 0;

        // Hallazgos críticos y leves (hallazgos críticos son cobertura_grasa + hematomas + cortes_piernas + sobrebarriga_rota)
        $hallazgos_criticos = ($sumas['cobertura_grasa'] ?? 0) + ($sumas['hematomas'] ?? 0)
                            + ($sumas['cortes_piernas'] ?? 0) + ($sumas['sobrebarriga_rota'] ?? 0);
        $hallazgos_leves = $totalHallazgos - $hallazgos_criticos;

        // Construir el resultado mensual
        $resultado = array_merge(
            $sumas, // Todas las sumas de los campos
            [
                'dias_procesados' => $diasProcesados,
                'participacion_promedio_mensual' => $participacionMensual,
                'promedio_liberacion' => $promedio_liberacion,
                'canales_liberadas' => $canalesLiberadas,
                'hallazgos_criticos' => $hallazgos_criticos,
                'hallazgos_leves' => $hallazgos_leves,
                'mes' => $mes,
                'año' => $anio,
            ]
        );

        return $resultado;
    }

    /**
     * Obtener tendencia semanal de los últimos 7 días.
     */
    public function obtenerTendenciaSemanal()
    {
        $fechaInicio = Carbon::now()->subDays(7)->format('Y-m-d');

        return IndicadorDiario::where('fecha_operacion', '>=', $fechaInicio)
            ->orderBy('fecha_operacion', 'asc')
            ->get([
                'fecha_operacion as fecha',
                'participacion_total as participacion',
                'total_hallazgos',
            ]);
    }
}
