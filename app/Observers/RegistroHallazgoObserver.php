<?php

namespace App\Observers;

use App\Events\HallazgoPublicado;
use App\Models\AnimalProcesado;
use App\Models\IndicadorDiario;
use App\Models\RegistroHallazgo;
use App\Models\TipoHallazgo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RegistroHallazgoObserver
{
    public function created(RegistroHallazgo $registroHallazgo): void
    {
        // Recalcular para la fecha efectiva del turno
        $fechaEfectiva = $registroHallazgo->getFechaOperacionEfectiva();
        $this->sincronizarIndicadoresParaFecha($fechaEfectiva);

        try {
            $registroHallazgo->loadMissing(['tipoHallazgo', 'usuario', 'producto', 'lado']);
            $mediaEtiqueta = match ((int) ($registroHallazgo->producto_id ?? 0)) {
                1 => 'Media Canal 1',
                2 => 'Media Canal 2',
                default => '—',
            };
            broadcast(new HallazgoPublicado([
                'origen' => 'hallazgos',
                'usuario_registro_id' => $registroHallazgo->usuario_id,
                'tipo_nombre' => $registroHallazgo->tipoHallazgo?->nombre,
                'usuario_nombre' => $registroHallazgo->usuario?->name,
                'codigo' => $registroHallazgo->codigo,
                'producto_nombre' => $registroHallazgo->producto?->nombre,
                'lado_nombre' => $registroHallazgo->lado?->nombre,
                'media_etiqueta' => $mediaEtiqueta,
                'registro_id' => $registroHallazgo->id,
                'registrado_en' => now()->toIso8601String(),
            ]));
        } catch (\Throwable $e) {
            Log::warning('Broadcast hallazgo: '.$e->getMessage());
        }
    }

    public function updated(RegistroHallazgo $registroHallazgo): void
    {
        // Recalcular para la fecha efectiva del turno
        $fechaEfectiva = $registroHallazgo->getFechaOperacionEfectiva();
        $this->sincronizarIndicadoresParaFecha($fechaEfectiva);

        if ($registroHallazgo->isDirty('fecha_operacion')) {
            $fechaAnterior = $registroHallazgo->getOriginal('fecha_operacion');
            if ($fechaAnterior !== null && $fechaAnterior !== '') {
                $this->sincronizarIndicadoresParaFecha(
                    Carbon::parse($fechaAnterior)->toDateString()
                );
            }
        }
    }

    public function deleted(RegistroHallazgo $registroHallazgo): void
    {
        // Recalcular para la fecha efectiva del turno
        $fechaEfectiva = $registroHallazgo->getFechaOperacionEfectiva();
        $this->sincronizarIndicadoresParaFecha($fechaEfectiva);
    }

    /**
     * Persiste fila en indicadores_diarios según hallazgos y animales del día (Y-m-d).
     * Público para poder invocarlo tras clonar / recalcular desde servicios.
     */
    public function sincronizarIndicadoresParaFecha($fechaOperacionEfectiva): void
    {
        $this->recalcularIndicadores($fechaOperacionEfectiva);
    }

    protected function recalcularIndicadores($fechaOperacionEfectiva)
    {
        try {
            // Convertir fecha a formato Y-m-d si es necesario
            $fechaFormato = is_string($fechaOperacionEfectiva)
                ? $fechaOperacionEfectiva
                : $fechaOperacionEfectiva->format('Y-m-d');

            // Obtener animales procesados para la fecha efectiva
            // AnimalProcesado usa fecha_operacion directamente
            $animalesProcesados = AnimalProcesado::where('fecha_operacion', $fechaFormato)->sum('cantidad_animales');
            $mediasCanalTotal = $animalesProcesados > 0 ? $animalesProcesados * 2 : 0;

            // Obtener estadísticas de hallazgos usando el scope de turno
            $query = RegistroHallazgo::porFechaConTurno($fechaFormato);

            $stats = $query->selectRaw('
                COUNT(*) as total_hallazgos,
                SUM(CASE WHEN producto_id = 1 THEN 1 ELSE 0 END) as medias_canal_1,
                SUM(CASE WHEN producto_id = 2 THEN 1 ELSE 0 END) as medias_canal_2
            ')->first();

            $desgloseHallazgos = [];
            $dataIndicadores = [];
            $participacionTotal = 0;

            // Obtener conteos por tipo de hallazgo
            $tiposHallazgo = TipoHallazgo::all();
            foreach ($tiposHallazgo as $tipo) {
                $count = RegistroHallazgo::porFechaConTurno($fechaFormato)
                    ->where('tipo_hallazgo_id', $tipo->id)
                    ->count();

                $desgloseHallazgos[$tipo->nombre] = $count;

                // Mapear tipos de hallazgo a columnas de indicadores
                $tipoNombre = strtoupper($tipo->nombre);

                if (strpos($tipoNombre, 'COBERTURA') !== false && strpos($tipoNombre, 'GRASA') !== false) {
                    $dataIndicadores['cobertura_grasa'] = $count;
                    if ($mediasCanalTotal > 0) {
                        $participacionTotal += ($count / $mediasCanalTotal);
                    }
                } elseif (strpos($tipoNombre, 'HEMATOMA') !== false) {
                    $dataIndicadores['hematomas'] = $count;
                    if ($mediasCanalTotal > 0) {
                        $participacionTotal += ($count / $mediasCanalTotal);
                    }
                } elseif (strpos($tipoNombre, 'CORTE') !== false && strpos($tipoNombre, 'PIERNA') !== false) {
                    $dataIndicadores['cortes_piernas'] = $count;
                    if ($mediasCanalTotal > 0) {
                        $participacionTotal += ($count / $mediasCanalTotal);
                    }
                } elseif (strpos($tipoNombre, 'SOBREBARRIGA') !== false && strpos($tipoNombre, 'ROTA') !== false) {
                    $dataIndicadores['sobrebarriga_rota'] = $count;
                    if ($mediasCanalTotal > 0) {
                        $participacionTotal += ($count / $mediasCanalTotal);
                    }
                }
            }

            // Asegurar que todos los campos estén presentes
            $dataIndicadores = array_merge([
                'cobertura_grasa' => 0,
                'hematomas' => 0,
                'cortes_piernas' => 0,
                'sobrebarriga_rota' => 0,
            ], $dataIndicadores);

            // Preparar datos finales
            $dataIndicadores = array_merge($dataIndicadores, [
                'fecha_operacion' => $fechaFormato,
                'animales_procesados' => $animalesProcesados,
                'medias_canales_total' => $mediasCanalTotal,
                'medias_canal_1' => $stats->medias_canal_1 ?? 0,
                'medias_canal_2' => $stats->medias_canal_2 ?? 0,
                'total_hallazgos' => $stats->total_hallazgos ?? 0,
                'participacion_total' => $mediasCanalTotal > 0 ? round($participacionTotal * 100, 2) : 0,
                'desglose_hallazgos' => json_encode($desgloseHallazgos),
                'mes' => date('m', strtotime($fechaFormato)),
                'año' => date('Y', strtotime($fechaFormato)),
            ]);

            // Guardar o actualizar indicador
            IndicadorDiario::updateOrCreate(
                ['fecha_operacion' => $fechaFormato],
                $dataIndicadores
            );

        } catch (\Exception $e) {
            Log::error("Error al recalcular indicadores para la fecha {$fechaFormato}: ".$e->getMessage().' | Trace: '.$e->getTraceAsString());
        }
    }
}
