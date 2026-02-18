<?php

namespace App\Observers;

use App\Models\RegistroHallazgo;
use App\Models\IndicadorDiario;
use App\Models\AnimalProcesado;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegistroHallazgoObserver
{
    /**
     * Evento después de crear un hallazgo
     */
    public function created(RegistroHallazgo $hallazgo)
    {
        $this->recalcularIndicadores($hallazgo->fecha_operacion);
    }

    /**
     * Evento después de actualizar un hallazgo
     */
    public function updated(RegistroHallazgo $hallazgo)
    {
        $this->recalcularIndicadores($hallazgo->fecha_operacion);
        
        // Si cambió la fecha, recalcular también la fecha original
        if ($hallazgo->wasChanged('fecha_operacion')) {
            $this->recalcularIndicadores($hallazgo->getOriginal('fecha_operacion'));
        }
    }

    /**
     * Evento después de eliminar un hallazgo
     */
    public function deleted(RegistroHallazgo $hallazgo)
    {
        $this->recalcularIndicadores($hallazgo->fecha_operacion);
    }

    /**
     * Recalcular indicadores para una fecha específica
     */
    protected function recalcularIndicadores($fecha)
    {
        try {
            // Obtener cantidad de animales procesados
            $animales = AnimalProcesado::where('fecha_operacion', $fecha)->first();
            $animalesProcesados = $animales ? $animales->cantidad_animales : 0;

            // Si no hay animales procesados, no calcular indicadores
            if ($animalesProcesados == 0) {
                Log::info("No hay animales procesados para la fecha {$fecha}");
                return;
            }

            // Contar hallazgos por tipo
            $hallazgos = RegistroHallazgo::where('fecha_operacion', $fecha)
                ->with('tipoHallazgo')
                ->get();

            $totalHallazgos = $hallazgos->count();
            
            // Contar por tipo de hallazgo (asumiendo nombres estándar)
            $coberturaGrasa = $hallazgos->filter(function($h) {
                return stripos($h->tipoHallazgo->nombre, 'cobertura') !== false;
            })->count();

            $hematomas = $hallazgos->filter(function($h) {
                return stripos($h->tipoHallazgo->nombre, 'hematoma') !== false;
            })->count();

            $cortesPiernas = $hallazgos->filter(function($h) {
                return stripos($h->tipoHallazgo->nombre, 'corte') !== false;
            })->count();

            $sobebarrigaRota = $hallazgos->filter(function($h) {
                return stripos($h->tipoHallazgo->nombre, 'sobrebarriga') !== false ||
                       stripos($h->tipoHallazgo->nombre, 'sobarriga') !== false;
            })->count();

            // Contar medias canales por tipo de producto
            $mediasCanal1 = $hallazgos->filter(function($h) {
                return stripos($h->producto->nombre, 'Media Canal 1') !== false;
            })->count();

            $mediasCanal2 = $hallazgos->filter(function($h) {
                return stripos($h->producto->nombre, 'Media Canal 2') !== false;
            })->count();

            $mediasCanalTotal = $mediasCanal1 + $mediasCanal2;

            // Calcular porcentaje de participación
            $participacionTotal = $animalesProcesados > 0 
                ? round(($totalHallazgos / ($animalesProcesados * 2)) * 100, 2)
                : 0;

            // Datos para guardar
            $data = [
                'fecha_operacion' => $fecha,
                'animales_procesados' => $animalesProcesados,
                'medias_canales_total' => $mediasCanalTotal,
                'medias_canal_1' => $mediasCanal1,
                'medias_canal_2' => $mediasCanal2,
                'total_hallazgos' => $totalHallazgos,
                'cobertura_grasa' => $coberturaGrasa,
                'hematomas' => $hematomas,
                'cortes_piernas' => $cortesPiernas,
                'sobrebarriga_rota' => $sobebarrigaRota,
                'participacion_total' => $participacionTotal,
                'mes' => date('m', strtotime($fecha)),
                'año' => date('Y', strtotime($fecha)),
            ];

            // Actualizar o crear indicador
            IndicadorDiario::updateOrCreate(
                ['fecha_operacion' => $fecha],
                $data
            );

            Log::info("Indicadores recalculados para {$fecha}");
            
        } catch (\Exception $e) {
            Log::error("Error al recalcular indicadores para {$fecha}: " . $e->getMessage());
        }
    }
}
