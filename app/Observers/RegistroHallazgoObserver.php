<?php

namespace App\Observers;

use App\Models\AnimalProcesado;
use App\Models\IndicadorDiario;
use App\Models\RegistroHallazgo;
use App\Models\TipoHallazgo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegistroHallazgoObserver
{
    public function created(RegistroHallazgo $registroHallazgo): void
    {
        $this->recalcularIndicadores($registroHallazgo->fecha_operacion);
    }

    public function updated(RegistroHallazgo $registroHallazgo): void
    {
        $this->recalcularIndicadores($registroHallazgo->fecha_operacion);
        if ($registroHallazgo->isDirty('fecha_operacion')) {
            $this->recalcularIndicadores($registroHallazgo->getOriginal('fecha_operacion'));
        }
    }

    public function deleted(RegistroHallazgo $registroHallazgo): void
    {
        $this->recalcularIndicadores($registroHallazgo->fecha_operacion);
    }

    protected function recalcularIndicadores($fecha)
    {
        try {
            $animalesProcesados = AnimalProcesado::where('fecha_operacion', $fecha)->sum('cantidad_animales');
            $mediasCanalTotal = $animalesProcesados * 2;

            // Cambiado para contar por producto_id en lugar de lado_id
            $statsQuery = RegistroHallazgo::where('fecha_operacion', $fecha)
                ->selectRaw("
                    COUNT(*) as total_hallazgos,
                    SUM(CASE WHEN producto_id = 1 THEN 1 ELSE 0 END) as medias_canal_1,
                    SUM(CASE WHEN producto_id = 2 THEN 1 ELSE 0 END) as medias_canal_2
                ");

            $tiposHallazgo = TipoHallazgo::all();
            $desgloseHallazgos = [];

            foreach ($tiposHallazgo as $tipo) {
                $alias = strtolower(str_replace(' ', '_', preg_replace('/[^A-Za-z0-9 ]/', '', $tipo->nombre)));
                $statsQuery->selectRaw("SUM(CASE WHEN tipo_hallazgo_id = ? THEN 1 ELSE 0 END) as {$alias}", [$tipo->id]);
            }

            $stats = $statsQuery->first();

            foreach ($tiposHallazgo as $tipo) {
                $alias = strtolower(str_replace(' ', '_', preg_replace('/[^A-Za-z0-9 ]/', '', $tipo->nombre)));
                $desgloseHallazgos[$tipo->nombre] = $stats->$alias ?? 0;
            }
            
            $participacionTotal = $mediasCanalTotal > 0
                ? round((($stats->total_hallazgos ?? 0) / $mediasCanalTotal) * 100, 2)
                : 0;

            IndicadorDiario::updateOrCreate(
                ['fecha_operacion' => $fecha],
                [
                    'animales_procesados' => $animalesProcesados,
                    'medias_canales_total' => $mediasCanalTotal,
                    'medias_canal_1' => $stats->medias_canal_1 ?? 0, // Ahora es hallazgos en Producto 1
                    'medias_canal_2' => $stats->medias_canal_2 ?? 0, // Ahora es hallazgos en Producto 2
                    'total_hallazgos' => $stats->total_hallazgos ?? 0,
                    'participacion_total' => $participacionTotal,
                    'desglose_hallazgos' => json_encode($desgloseHallazgos),
                    'mes' => date('m', strtotime($fecha)),
                    'año' => date('Y', strtotime($fecha)),
                ]
            );

        } catch (\Exception $e) {
            Log::error("Error al recalcular indicadores para la fecha {$fecha}: " . $e->getMessage());
        }
    }
}
