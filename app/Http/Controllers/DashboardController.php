<?php

namespace App\Http\Controllers;

use App\Models\IndicadorDiario;
use App\Models\RegistroHallazgo;
use App\Models\AnimalProcesado;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        return $this->index($request);
    }
    
    public function index(Request $request)
    {
        $fecha_inicio = $request->get('fecha_inicio', now()->toDateString());
        $fecha_fin = $request->get('fecha_fin', now()->toDateString());

        // Consultas
        $indicadoresRango = IndicadorDiario::whereBetween('fecha_operacion', [$fecha_inicio, $fecha_fin])->get();
        $hallazgosRango = RegistroHallazgo::whereBetween('fecha_operacion', [$fecha_inicio, $fecha_fin])
            ->with(['tipoHallazgo', 'producto', 'ubicacion', 'operario'])
            ->latest('created_at')
            ->get();
        
        // Sumarizados para las tarjetas
        $animalesProcesados = AnimalProcesado::whereBetween('fecha_operacion', [$fecha_inicio, $fecha_fin])->sum('cantidad_animales');
        $indicador = (object)[
            'total_hallazgos'      => $indicadoresRango->sum('total_hallazgos'),
            'participacion_total'  => $indicadoresRango->avg('participacion_total'),
            'medias_canales_total' => $indicadoresRango->sum('medias_canales_total'),
        ];
        
        $topHallazgos = $hallazgosRango->groupBy('tipoHallazgo.nombre')
            ->map(fn($item) => $item->count())
            ->sortDesc()
            ->take(5);

        // --- Datos para Gráficos ---
        $hallazgosChartData = $hallazgosRango->groupBy('tipoHallazgo.nombre')->map(fn($item) => $item->count());
        $productosChartData = $hallazgosRango->groupBy('producto.nombre')->map(fn($item) => $item->count());
        
        // Lógica de negocio para determinar el Puesto de Trabajo, restaurada y corregida.
        $puestosChartData = $hallazgosRango->map(function ($hallazgo) {
            $puestoTrabajoNombre = 'No Asignado'; // Valor por defecto

            $tipoHallazgo = $hallazgo->tipoHallazgo->nombre ?? '';
            $producto = $hallazgo->producto->nombre ?? '';
            $ubicacion = $hallazgo->ubicacion->nombre ?? '';
            $codigo = $hallazgo->codigo ?? '';
            
            $numero = is_numeric(substr($codigo, -1)) ? (int) substr($codigo, -1) : 0;
            $paridad = ($numero % 2 == 0) ? 'Par' : 'Impar';

            $esUbicacionCadera = str_contains(strtoupper($ubicacion), 'CADERA');
            $esUbicacionPierna = str_contains(strtoupper($ubicacion), 'PIERNA');

            $tipoHallazgoUpper = strtoupper($tipoHallazgo);

            if ($tipoHallazgoUpper === 'COBERTURA DE GRASA' || $tipoHallazgoUpper === 'CORTES EN LA PIERNA' || $tipoHallazgoUpper === 'SOBREBARRIGA ROTA') {
                if ($producto === 'Media Canal 1 Lengua') {
                    if ($esUbicacionCadera || ($esUbicacionPierna && $paridad === 'Par')) {
                        $puestoTrabajoNombre = 'CADERA 1';
                    }
                } elseif ($producto === 'Media Canal 2 Cola') {
                    if ($esUbicacionCadera || ($esUbicacionPierna && $paridad === 'Impar')) {
                        $puestoTrabajoNombre = 'CADERA 2';
                    }
                }
            } elseif ($tipoHallazgoUpper === 'HEMATOMAS') {
                $puestoTrabajoNombre = 'LIMPIEZA SUPERIOR';
            }
            
            $hallazgo->puesto_calculado = $puestoTrabajoNombre;
            return $hallazgo;
        })
        ->groupBy('puesto_calculado')
        ->map(fn($group) => $group->count());
        
        // Promedios del mes
        $indicadoresMes = IndicadorDiario::whereMonth('fecha_operacion', Carbon::parse($fecha_fin)->month)
            ->whereYear('fecha_operacion', Carbon::parse($fecha_fin)->year)
            ->get();
        
        $promediosMes = [
            'participacion' => $indicadoresMes->avg('participacion_total'),
        ];
        
        return view('dashboard.index', [
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'indicador' => $indicador,
            'animalesProcesados' => $animalesProcesados,
            'hallazgosDia' => $hallazgosRango,
            'topHallazgos' => $topHallazgos,
            'promediosMes' => $promediosMes,
            'hallazgosChartData' => $hallazgosChartData,
            'productosChartData' => $productosChartData,
            'puestosChartData' => $puestosChartData,
        ]);
    }
    
    public function mensual(Request $request)
    {
        $mes = $request->get('mes', now()->month);
        $anio = $request->get('anio', now()->year);
        
        $indicadores = IndicadorDiario::where('mes', $mes)
            ->where('año', $anio)
            ->orderBy('fecha_operacion')
            ->get();
        
        $totales = [
            'animales' => $indicadores->sum('animales_procesados'),
            'hallazgos' => $indicadores->sum('total_hallazgos'),
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
