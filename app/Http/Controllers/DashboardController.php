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
        // Dashboard diario: por defecto fecha del día actual; días anteriores se consultan manualmente
        $hoy = Carbon::now()->toDateString();
        $fecha_inicio = $request->get('fecha_inicio', $hoy);
        $fecha_fin = $request->get('fecha_fin', $hoy);

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
        $hallazgosChartDataCanal1 = $hallazgosRango->filter(function ($hallazgo) {
            return $hallazgo->producto && str_contains($hallazgo->producto->nombre, 'Media Canal 1');
        })->groupBy('tipoHallazgo.nombre')->map(fn($item) => $item->count());

        $hallazgosChartDataCanal2 = $hallazgosRango->filter(function ($hallazgo) {
            return $hallazgo->producto && str_contains($hallazgo->producto->nombre, 'Media Canal 2');
        })->groupBy('tipoHallazgo.nombre')->map(fn($item) => $item->count());

        $productosChartData = $hallazgosRango->groupBy('producto.nombre')->map(fn($item) => $item->count());

        $puestosChartData = $hallazgosRango->map(function ($hallazgo) {
            $operarioNombre = $hallazgo->operario ? $hallazgo->operario->nombre_completo : 'Sin asignar';
            $tipoHallazgo = $hallazgo->tipoHallazgo->nombre ?? 'N/A';
            return $operarioNombre . ' · ' . $tipoHallazgo;
        })->countBy()->sortDesc();
        
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
            'hallazgosChartDataCanal1' => $hallazgosChartDataCanal1,
            'hallazgosChartDataCanal2' => $hallazgosChartDataCanal2,
            'productosChartData' => $productosChartData,
            'puestosChartData' => $puestosChartData,
        ]);
    }
}
