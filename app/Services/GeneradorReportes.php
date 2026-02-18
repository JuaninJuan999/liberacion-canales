<?php

namespace App\Services;

use App\Models\RegistroHallazgo;
use App\Models\IndicadorDiario;
use App\Models\AnimalProcesado;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class GeneradorReportes
{
    protected $calculadora;
    
    public function __construct(CalculadoraIndicadores $calculadora)
    {
        $this->calculadora = $calculadora;
    }
    
    /**
     * Generar reporte diario en PDF
     */
    public function generarReporteDiarioPDF($fecha)
    {
        $fecha = Carbon::parse($fecha);
        $indicadores = IndicadorDiario::where('fecha', $fecha->format('Y-m-d'))->first();
        
        if (!$indicadores) {
            // Calcular indicadores si no existen
            $indicadores = $this->calculadora->calcularIndicadoresDia($fecha);
        }
        
        // Obtener hallazgos del día
        $hallazgos = RegistroHallazgo::with(['tipoHallazgo', 'puestoTrabajo', 'operario', 'producto'])
            ->whereDate('created_at', $fecha)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $data = [
            'fecha' => $fecha->format('d/m/Y'),
            'indicadores' => $indicadores,
            'hallazgos' => $hallazgos,
            'indicadoresPorPuesto' => json_decode($indicadores->indicadores_puesto ?? '[]', true)
        ];
        
        $pdf = Pdf::loadView('reportes.diario', $data);
        return $pdf->download('reporte-diario-' . $fecha->format('Y-m-d') . '.pdf');
    }
    
    /**
     * Generar reporte mensual en PDF
     */
    public function generarReporteMensualPDF($mes, $anio)
    {
        $indicadoresMes = $this->calculadora->calcularIndicadoresMes($mes, $anio);
        
        $fechaInicio = Carbon::create($anio, $mes, 1)->startOfMonth();
        $fechaFin = Carbon::create($anio, $mes, 1)->endOfMonth();
        
        // Indicadores por día
        $indicadoresDiarios = IndicadorDiario::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->orderBy('fecha', 'asc')
            ->get();
        
        $data = [
            'mes' => $fechaInicio->format('F'),
            'anio' => $anio,
            'indicadoresMes' => $indicadoresMes,
            'indicadoresDiarios' => $indicadoresDiarios
        ];
        
        $pdf = Pdf::loadView('reportes.mensual', $data);
        return $pdf->download('reporte-mensual-' . $mes . '-' . $anio . '.pdf');
    }
    
    /**
     * Exportar hallazgos a Excel
     */
    public function exportarHallazgosExcel($fechaInicio, $fechaFin)
    {
        return Excel::download(
            new \App\Exports\HallazgosExport($fechaInicio, $fechaFin),
            'hallazgos-' . $fechaInicio . '-' . $fechaFin . '.xlsx'
        );
    }
    
    /**
     * Generar reporte por operario
     */
    public function generarReporteOperarioPDF($operarioId, $fechaInicio, $fechaFin)
    {
        $hallazgos = RegistroHallazgo::with(['tipoHallazgo', 'puestoTrabajo', 'producto'])
            ->where('operario_id', $operarioId)
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->orderBy('created_at', 'desc')
            ->get();
        
        $operario = $hallazgos->first()->operario ?? null;
        
        $totalHallazgos = $hallazgos->count();
        $criticos = $hallazgos->filter(function($h) {
            return $h->tipoHallazgo && $h->tipoHallazgo->es_critico;
        })->count();
        
        $data = [
            'operario' => $operario,
            'fechaInicio' => Carbon::parse($fechaInicio)->format('d/m/Y'),
            'fechaFin' => Carbon::parse($fechaFin)->format('d/m/Y'),
            'hallazgos' => $hallazgos,
            'totalHallazgos' => $totalHallazgos,
            'criticos' => $criticos,
            'leves' => $totalHallazgos - $criticos
        ];
        
        $pdf = Pdf::loadView('reportes.operario', $data);
        return $pdf->download('reporte-operario-' . $operarioId . '.pdf');
    }
    
    /**
     * Generar reporte por puesto de trabajo
     */
    public function generarReportePuestoPDF($puestoId, $fechaInicio, $fechaFin)
    {
        $hallazgos = RegistroHallazgo::with(['tipoHallazgo', 'operario', 'producto'])
            ->where('puesto_trabajo_id', $puestoId)
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->orderBy('created_at', 'desc')
            ->get();
        
        $puesto = $hallazgos->first()->puestoTrabajo ?? null;
        
        $totalHallazgos = $hallazgos->count();
        $criticos = $hallazgos->filter(function($h) {
            return $h->tipoHallazgo && $h->tipoHallazgo->es_critico;
        })->count();
        
        // Agrupar por operario
        $hallazgosPorOperario = $hallazgos->groupBy('operario_id')->map(function($items) {
            return [
                'operario' => $items->first()->operario,
                'total' => $items->count(),
                'criticos' => $items->filter(function($h) {
                    return $h->tipoHallazgo && $h->tipoHallazgo->es_critico;
                })->count()
            ];
        });
        
        $data = [
            'puesto' => $puesto,
            'fechaInicio' => Carbon::parse($fechaInicio)->format('d/m/Y'),
            'fechaFin' => Carbon::parse($fechaFin)->format('d/m/Y'),
            'hallazgos' => $hallazgos,
            'totalHallazgos' => $totalHallazgos,
            'criticos' => $criticos,
            'leves' => $totalHallazgos - $criticos,
            'hallazgosPorOperario' => $hallazgosPorOperario
        ];
        
        $pdf = Pdf::loadView('reportes.puesto', $data);
        return $pdf->download('reporte-puesto-' . $puestoId . '.pdf');
    }
    
    /**
     * Exportar indicadores diarios a Excel
     */
    public function exportarIndicadoresExcel($fechaInicio, $fechaFin)
    {
        return Excel::download(
            new \App\Exports\IndicadoresExport($fechaInicio, $fechaFin),
            'indicadores-' . $fechaInicio . '-' . $fechaFin . '.xlsx'
        );
    }
}