<?php

namespace App\Http\Controllers;

use App\Models\IndicadorDiario;
use App\Services\CalculadoraIndicadores;
use App\Services\GeneradorReportes;
use Illuminate\Http\Request;
use Carbon\Carbon;

class IndicadorController extends Controller
{
    protected $calculadora;
    protected $generador;
    
    public function __construct(CalculadoraIndicadores $calculadora, GeneradorReportes $generador)
    {
        $this->calculadora = $calculadora;
        $this->generador = $generador;
    }
    
    /**
     * Mostrar indicadores del día
     */
    public function indicadoresDia(Request $request)
    {
        $fecha = $request->get('fecha', now()->format('Y-m-d'));
        
        // Buscar indicadores existentes
        $indicadores = IndicadorDiario::where('fecha', $fecha)->first();
        
        // Si no existen, calcularlos
        if (!$indicadores) {
            $indicadores = $this->calculadora->calcularIndicadoresDia($fecha);
        }
        
        // Obtener tendencia semanal
        $tendencia = $this->calculadora->obtenerTendenciaSemanal();
        
        // Indicadores por puesto
        $indicadoresPorPuesto = $indicadores 
            ? json_decode($indicadores->indicadores_puesto, true) 
            : [];
        
        return view('indicadores.dia', compact('indicadores', 'tendencia', 'indicadoresPorPuesto', 'fecha'));
    }
    
    /**
     * Mostrar indicadores del mes
     */
    public function indicadoresMes(Request $request)
    {
        $mes = $request->get('mes', now()->month);
        $anio = $request->get('anio', now()->year);
        
        $indicadoresMes = $this->calculadora->calcularIndicadoresMes($mes, $anio);
        
        // Obtener indicadores diarios del mes
        $fechaInicio = Carbon::create($anio, $mes, 1)->startOfMonth();
        $fechaFin = Carbon::create($anio, $mes, 1)->endOfMonth();
        
        $indicadoresDiarios = IndicadorDiario::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->orderBy('fecha', 'asc')
            ->get();
        
        return view('indicadores.mes', compact('indicadoresMes', 'indicadoresDiarios', 'mes', 'anio'));
    }
    
    /**
     * Recalcular indicadores de un día específico
     */
    public function recalcular(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date'
        ]);
        
        $fecha = $request->fecha;
        $indicadores = $this->calculadora->calcularIndicadoresDia($fecha);
        
        return response()->json([
            'success' => true,
            'message' => 'Indicadores recalculados correctamente',
            'indicadores' => $indicadores
        ]);
    }
    
    /**
     * Obtener indicadores por operario
     */
    public function indicadoresPorOperario(Request $request)
    {
        $fecha = $request->get('fecha', now()->format('Y-m-d'));
        $indicadores = $this->calculadora->calcularIndicadoresPorOperario($fecha);
        
        return response()->json($indicadores);
    }
    
    /**
     * Exportar indicadores a Excel
     */
    public function exportarExcel(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio'
        ]);
        
        return $this->generador->exportarIndicadoresExcel(
            $request->fecha_inicio,
            $request->fecha_fin
        );
    }
    
    /**
     * Obtener datos para gráficos
     */
    public function datosGraficos(Request $request)
    {
        $dias = $request->get('dias', 7);
        $fechaInicio = Carbon::now()->subDays($dias);
        
        $indicadores = IndicadorDiario::where('fecha', '>=', $fechaInicio)
            ->orderBy('fecha', 'asc')
            ->get();
        
        $labels = $indicadores->pluck('fecha')->map(function($fecha) {
            return Carbon::parse($fecha)->format('d/m');
        });
        
        $datasets = [
            [
                'label' => 'Porcentaje de Liberación',
                'data' => $indicadores->pluck('porcentaje_liberacion'),
                'borderColor' => 'rgb(34, 197, 94)',
                'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
            ],
            [
                'label' => 'Total Hallazgos',
                'data' => $indicadores->pluck('total_hallazgos'),
                'borderColor' => 'rgb(239, 68, 68)',
                'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
            ]
        ];
        
        return response()->json([
            'labels' => $labels,
            'datasets' => $datasets
        ]);
    }
    
    /**
     * Dashboard de indicadores
     */
    public function dashboard()
    {
        $hoy = now()->format('Y-m-d');
        $indicadoresHoy = IndicadorDiario::where('fecha', $hoy)->first();
        
        if (!$indicadoresHoy) {
            $indicadoresHoy = $this->calculadora->calcularIndicadoresDia($hoy);
        }
        
        $tendenciaSemanal = $this->calculadora->obtenerTendenciaSemanal();
        
        $indicadoresMes = $this->calculadora->calcularIndicadoresMes(
            now()->month,
            now()->year
        );
        
        return view('indicadores.dashboard', compact(
            'indicadoresHoy',
            'tendenciaSemanal',
            'indicadoresMes'
        ));
    }
}