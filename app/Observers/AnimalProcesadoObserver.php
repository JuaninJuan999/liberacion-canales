<?php

namespace App\Observers;

use App\Models\AnimalProcesado;
use App\Models\IndicadorDiario;
use App\Models\RegistroHallazgo;
use App\Models\TipoHallazgo;
use Illuminate\Support\Facades\Log;

class AnimalProcesadoObserver
{
    public function created(AnimalProcesado $animalProcesado): void
    {
        $this->recalcularIndicadores($animalProcesado->fecha_operacion);
    }

    public function updated(AnimalProcesado $animalProcesado): void
    {
        $this->recalcularIndicadores($animalProcesado->fecha_operacion);
    }

    public function deleted(AnimalProcesado $animalProcesado): void
    {
        $this->recalcularIndicadores($animalProcesado->fecha_operacion);
    }

    protected function recalcularIndicadores($fecha)
    {
        try {
            $animalesProcesados = AnimalProcesado::where('fecha_operacion', $fecha)->sum('cantidad_animales');
            $mediasCanalTotal = $animalesProcesados * 2;

            $statsQuery = RegistroHallazgo::where('fecha_operacion', $fecha)
                ->selectRaw("
                    COUNT(*) as total_hallazgos,
                    SUM(CASE WHEN producto_id = 1 THEN 1 ELSE 0 END) as medias_canal_1,
                    SUM(CASE WHEN producto_id = 2 THEN 1 ELSE 0 END) as medias_canal_2
                ");

            $tiposHallazgo = TipoHallazgo::all();
            $desgloseHallazgos = [];
            $dataIndicadores = [];
            $participacionTotal = 0;

            foreach ($tiposHallazgo as $tipo) {
                $alias = strtolower(str_replace(' ', '_', preg_replace('/[^A-Za-z0-9 ]/', '', $tipo->nombre)));
                $statsQuery->selectRaw("SUM(CASE WHEN tipo_hallazgo_id = ? THEN 1 ELSE 0 END) as {$alias}", [$tipo->id]);
            }

            $stats = $statsQuery->first();

            foreach ($tiposHallazgo as $tipo) {
                $alias = strtolower(str_replace(' ', '_', preg_replace('/[^A-Za-z0-9 ]/', '', $tipo->nombre)));
                $count = $stats->$alias ?? 0;
                $desgloseHallazgos[$tipo->nombre] = $count;

                $columnName = null;
                $isMajorFinding = false;

                switch (strtoupper($tipo->nombre)) {
                    case 'COBERTURA DE GRASA':
                        $columnName = 'cobertura_grasa';
                        $isMajorFinding = true;
                        break;
                    case 'HEMATOMAS':
                        $columnName = 'hematomas';
                        $isMajorFinding = true;
                        break;
                    case 'CORTES EN LA PIERNA':
                        $columnName = 'cortes_piernas';
                        $isMajorFinding = true;
                        break;
                    case 'SOBREBARRIGA ROTA':
                        $columnName = 'sobrebarriga_rota';
                        $isMajorFinding = true;
                        break;
                }

                if ($columnName) {
                    $dataIndicadores[$columnName] = $count;
                    if ($isMajorFinding && $mediasCanalTotal > 0) {
                        $participacionTotal += ($count / $mediasCanalTotal);
                    }
                }
            }
            
            $dataIndicadores = array_merge($dataIndicadores, [
                'animales_procesados' => $animalesProcesados,
                'medias_canales_total' => $mediasCanalTotal,
                'medias_canal_1' => $stats->medias_canal_1 ?? 0,
                'medias_canal_2' => $stats->medias_canal_2 ?? 0,
                'total_hallazgos' => $stats->total_hallazgos ?? 0,
                'participacion_total' => round($participacionTotal * 100, 2),
                'desglose_hallazgos' => json_encode($desgloseHallazgos),
                'mes' => date('m', strtotime($fecha)),
                'año' => date('Y', strtotime($fecha)),
            ]);

            IndicadorDiario::updateOrCreate(
                ['fecha_operacion' => $fecha],
                $dataIndicadores
            );

        } catch (\Exception $e) {
            Log::error("Error al recalcular indicadores para la fecha {$fecha} desde AnimalProcesadoObserver: " . $e->getMessage());
        }
    }
}
