<?php

namespace App\Http\Controllers;

use App\Models\IndicadorDiario;
use App\Models\RegistroHallazgo;
use App\Models\AnimalProcesado;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Dashboard principal con indicadores del día
     */
    public function index(Request $request)
    {
        $fecha = $request->get('fecha', now()->toDateString());
        
        // Obtener indicador del día
        $indicador = IndicadorDiario::where('fecha_operacion', $fecha)->first();
        
        // Datos base
        $animales = AnimalProcesado::where('fecha_operacion', $fecha)->first();
        $animalesProcesados = $animales ? $animales->cantidad_animales : 0;
        
        // Hallazgos del día
        $hallazgosDia = RegistroHallazgo::where('fecha_operacion', $fecha)
            ->with(['tipoHallazgo', 'producto', 'ubicacion'])
            ->latest('created_at')
            ->get();
        
        // Top 5 tipos de hallazgos
        $topHallazgos = RegistroHallazgo::where('fecha_operacion', $fecha)
            ->select('tipo_hallazgo_id', \DB::raw('count(*) as total'))
            ->groupBy('tipo_hallazgo_id')
            ->orderByDesc('total')
            ->limit(5)
            ->with('tipoHallazgo')
            ->get();
        
        // Indicadores del mes
        $indicadoresMes = IndicadorDiario::whereMonth('fecha_operacion', Carbon::parse($fecha)->month)
            ->whereYear('fecha_operacion', Carbon::parse($fecha)->year)
            ->orderBy('fecha_operacion', 'desc')
            ->get();
        
        // Promedios del mes
        $promediosMes = [
            'participacion' => $indicadoresMes->avg('participacion_total'),
            'hematomas' => $indicadoresMes->avg('hematomas'),
            'cobertura' => $indicadoresMes->avg('cobertura_grasa'),
        ];
        
        return view('dashboard.index', compact(
            'fecha',
            'indicador',
            'animalesProcesados',
            'hallazgosDia',
            'topHallazgos',
            'indicadoresMes',
            'promediosMes'
        ));
    }
    
    /**
     * Dashboard mensual
     */
    public function mensual(Request $request)
    {
        $mes = $request->get('mes', now()->month);
        $anio = $request->get('anio', now()->year);
        
        $indicadores = IndicadorDiario::where('mes', $mes)
            ->where('anio', $anio)
            ->orderBy('fecha_operacion')
            ->get();
        
        // Totales del mes
        $totales = [
            'animales' => $indicadores->sum('animales_procesados'),
            'hallazgos' => $indicadores->sum('total_hallazgos'),
            'hematomas' => $indicadores->sum('hematomas'),
            'cobertura' => $indicadores->sum('cobertura_grasa'),
            'dias_operados' => $indicadores->count(),
        ];
        
        return view('dashboard.mensual', compact(
            'mes',
            'anio',
            'indicadores',
            'totales'
        ));
    }
}
