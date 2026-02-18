<?php

namespace App\Services;

use App\Models\RegistroHallazgo;
use App\Models\IndicadorDiario;
use App\Models\AnimalProcesado;
use Carbon\Carbon;

class CalculadoraIndicadores
{
    /**
     * Calcular indicadores del día
     */
    public function calcularIndicadoresDia($fecha)
    {
        $fecha = Carbon::parse($fecha)->format('Y-m-d');
        
        // Obtener total de animales procesados
        $totalAnimales = AnimalProcesado::whereDate('fecha_procesamiento', $fecha)->count();
        
        if ($totalAnimales === 0) {
            return null; // No hay datos para calcular
        }
        
        // Obtener hallazgos del día
        $hallazgos = RegistroHallazgo::with('tipoHallazgo')
            ->whereDate('created_at', $fecha)
            ->get();
        
        // Calcular totales por tipo
        $totalHallazgos = $hallazgos->count();
        $hallazgosCriticos = $hallazgos->filter(function($hallazgo) {
            return $hallazgo->tipoHallazgo && $hallazgo->tipoHallazgo->es_critico;
        })->count();
        $hallazgosLeves = $totalHallazgos - $hallazgosCriticos;
        
        // Calcular canales liberadas (sin hallazgos críticos)
        $canalesLiberadas = $totalAnimales - $hallazgosCriticos;
        
        // Calcular porcentajes
        $porcentajeLibracion = $totalAnimales > 0 
            ? round(($canalesLiberadas / $totalAnimales) * 100, 2) 
            : 0;
            
        $porcentajeHallazgos = $totalAnimales > 0 
            ? round(($totalHallazgos / $totalAnimales) * 100, 2) 
            : 0;
        
        // Calcular indicadores por puesto de trabajo
        $indicadoresPorPuesto = $this->calcularIndicadoresPorPuesto($fecha);
        
        // Guardar o actualizar indicadores
        $indicador = IndicadorDiario::updateOrCreate(
            ['fecha' => $fecha],
            [
                'total_animales' => $totalAnimales,
                'total_hallazgos' => $totalHallazgos,
                'hallazgos_criticos' => $hallazgosCriticos,
                'hallazgos_leves' => $hallazgosLeves,
                'canales_liberadas' => $canalesLiberadas,
                'porcentaje_liberacion' => $porcentajeLibracion,
                'porcentaje_hallazgos' => $porcentajeHallazgos,
                'indicadores_puesto' => json_encode($indicadoresPorPuesto)
            ]
        );
        
        return $indicador;
    }
    
    /**
     * Calcular indicadores por puesto de trabajo
     */
    protected function calcularIndicadoresPorPuesto($fecha)
    {
        $hallazgosPorPuesto = RegistroHallazgo::with(['puestoTrabajo', 'tipoHallazgo'])
            ->whereDate('created_at', $fecha)
            ->get()
            ->groupBy('puesto_trabajo_id');
        
        $indicadores = [];
        
        foreach ($hallazgosPorPuesto as $puestoId => $hallazgos) {
            $puesto = $hallazgos->first()->puestoTrabajo;
            $totalHallazgos = $hallazgos->count();
            $criticos = $hallazgos->filter(function($hallazgo) {
                return $hallazgo->tipoHallazgo && $hallazgo->tipoHallazgo->es_critico;
            })->count();
            
            $indicadores[] = [
                'puesto_id' => $puestoId,
                'puesto_nombre' => $puesto ? $puesto->nombre : 'Sin puesto',
                'total_hallazgos' => $totalHallazgos,
                'criticos' => $criticos,
                'leves' => $totalHallazgos - $criticos
            ];
        }
        
        return $indicadores;
    }
    
    /**
     * Calcular indicadores mensuales
     */
    public function calcularIndicadoresMes($mes, $anio)
    {
        $fechaInicio = Carbon::create($anio, $mes, 1)->startOfMonth();
        $fechaFin = Carbon::create($anio, $mes, 1)->endOfMonth();
        
        $indicadoresDiarios = IndicadorDiario::whereBetween('fecha', [$fechaInicio, $fechaFin])->get();
        
        return [
            'total_animales' => $indicadoresDiarios->sum('total_animales'),
            'total_hallazgos' => $indicadoresDiarios->sum('total_hallazgos'),
            'hallazgos_criticos' => $indicadoresDiarios->sum('hallazgos_criticos'),
            'hallazgos_leves' => $indicadoresDiarios->sum('hallazgos_leves'),
            'canales_liberadas' => $indicadoresDiarios->sum('canales_liberadas'),
            'promedio_liberacion' => round($indicadoresDiarios->avg('porcentaje_liberacion'), 2),
            'dias_procesados' => $indicadoresDiarios->count()
        ];
    }
    
    /**
     * Obtener tendencia semanal
     */
    public function obtenerTendenciaSemanal()
    {
        $fechaInicio = Carbon::now()->subDays(7);
        
        return IndicadorDiario::where('fecha', '>=', $fechaInicio)
            ->orderBy('fecha', 'asc')
            ->get()
            ->map(function($indicador) {
                return [
                    'fecha' => $indicador->fecha,
                    'porcentaje_liberacion' => $indicador->porcentaje_liberacion,
                    'total_hallazgos' => $indicador->total_hallazgos
                ];
            });
    }
    
    /**
     * Calcular indicadores por operario
     */
    public function calcularIndicadoresPorOperario($fecha)
    {
        $hallazgosPorOperario = RegistroHallazgo::with(['operario', 'tipoHallazgo'])
            ->whereDate('created_at', $fecha)
            ->get()
            ->groupBy('operario_id');
        
        $indicadores = [];
        
        foreach ($hallazgosPorOperario as $operarioId => $hallazgos) {
            $operario = $hallazgos->first()->operario;
            $totalHallazgos = $hallazgos->count();
            $criticos = $hallazgos->filter(function($hallazgo) {
                return $hallazgo->tipoHallazgo && $hallazgo->tipoHallazgo->es_critico;
            })->count();
            
            $indicadores[] = [
                'operario_id' => $operarioId,
                'operario_nombre' => $operario ? $operario->nombre_completo : 'Sin operario',
                'total_hallazgos' => $totalHallazgos,
                'criticos' => $criticos,
                'leves' => $totalHallazgos - $criticos,
                'eficiencia' => $criticos > 0 ? round((1 - ($criticos / $totalHallazgos)) * 100, 2) : 100
            ];
        }
        
        return collect($indicadores)->sortByDesc('eficiencia')->values()->all();
    }
}