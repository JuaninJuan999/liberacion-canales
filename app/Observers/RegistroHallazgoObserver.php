<?php

namespace App\Observers;

use App\Models\RegistroHallazgo;
use App\Models\IndicadorDiario;
use App\Models\AnimalProcesado;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegistroHallazgoObserver
{
    public function created(RegistroHallazgo $hallazgo)
    {
        $this->recalcularIndicadores($hallazgo->fecha_operacion);
    }

    public function updated(RegistroHallazgo $hallazgo)
    {
        $this->recalcularIndicadores($hallazgo->fecha_operacion);
        
        if ($hallazgo->wasChanged('fecha_operacion')) {
            $this->recalcularIndicadores($hallazgo->getOriginal('fecha_operacion'));
        }
    }

    public function deleted(RegistroHallazgo $hallazgo)
    {
        $this->recalcularIndicadores($hallazgo->fecha_operacion);
    }

    /**
     * Recalcula los indicadores para una fecha específica de forma eficiente.
     */
    protected function recalcularIndicadores($fecha)
    {
        try {
            $animalesProcesados = AnimalProcesado::where('fecha_operacion', $fecha)->value('cantidad_animales') ?? 0;

            if ($animalesProcesados == 0) {
                // Si no hay animales, se borra el indicador diario si existiera.
                IndicadorDiario::where('fecha_operacion', $fecha)->delete();
                Log::info("No hay animales procesados para la fecha {$fecha}. Indicador diario eliminado.");
                return;
            }

            // Consulta única para obtener todas las estadísticas de hallazgos
            $stats = DB::table('registro_hallazgos as rh')
                ->join('tipos_hallazgo as th', 'rh.tipo_hallazgo_id', '=', 'th.id')
                ->join('productos as p', 'rh.producto_id', '=', 'p.id')
                ->where('rh.fecha_operacion', $fecha)
                ->selectRaw(
                    'COUNT(rh.id) as total_hallazgos',
                    "SUM(CASE WHEN th.nombre LIKE ? THEN 1 ELSE 0 END) as cobertura_grasa",
                    "SUM(CASE WHEN th.nombre LIKE ? THEN 1 ELSE 0 END) as hematomas",
                    "SUM(CASE WHEN th.nombre LIKE ? THEN 1 ELSE 0 END) as cortes_piernas",
                    "SUM(CASE WHEN th.nombre LIKE ? OR th.nombre LIKE ? THEN 1 ELSE 0 END) as sobrebarriga_rota",
                    "SUM(CASE WHEN p.nombre LIKE ? THEN 1 ELSE 0 END) as medias_canal_1",
                    "SUM(CASE WHEN p.nombre LIKE ? THEN 1 ELSE 0 END) as medias_canal_2"
                )
                ->setBindings([
                    '%cobertura%',
                    '%hematoma%',
                    '%corte%',
                    '%sobrebarriga%', '%sobarriga%',
                    '%Media Canal 1%',
                    '%Media Canal 2%'
                ])
                ->first();

            $mediasCanalTotal = ($stats->medias_canal_1 ?? 0) + ($stats->medias_canal_2 ?? 0);
            $participacionTotal = ($animalesProcesados > 0) 
                ? round((($stats->total_hallazgos ?? 0) / ($animalesProcesados * 2)) * 100, 2)
                : 0;

            $data = [
                'fecha_operacion' => $fecha,
                'animales_procesados' => $animalesProcesados,
                'medias_canales_total' => $mediasCanalTotal,
                'medias_canal_1' => $stats->medias_canal_1 ?? 0,
                'medias_canal_2' => $stats->medias_canal_2 ?? 0,
                'total_hallazgos' => $stats->total_hallazgos ?? 0,
                'cobertura_grasa' => $stats->cobertura_grasa ?? 0,
                'hematomas' => $stats->hematomas ?? 0,
                'cortes_piernas' => $stats->cortes_piernas ?? 0,
                'sobrebarriga_rota' => $stats->sobrebarriga_rota ?? 0,
                'participacion_total' => $participacionTotal,
                'mes' => date('m', strtotime($fecha)),
                'año' => date('Y', strtotime($fecha)),
            ];

            // Actualizar o crear el indicador diario
            IndicadorDiario::updateOrCreate(
                ['fecha_operacion' => $fecha],
                $data
            );

            Log::info("Indicadores recalculados eficientemente para {$fecha}");

        } catch (\Exception $e) {
            Log::error("Error al recalcular indicadores para {$fecha}: " . $e->getMessage());
        }
    }
}
