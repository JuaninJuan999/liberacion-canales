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
        $hallazgosChartData = $hallazgosRango->groupBy('tipoHallazgo.nombre')->map(fn($item) => $item->count());
        $productosChartData = $hallazgosRango->groupBy('producto.nombre')->map(fn($item) => $item->count());

        // Gráfica por Puesto: Operario + Tipo de hallazgo (nombre del operario relacionado al hallazgo con el tipo)
        $puestosChartData = $hallazgosRango->map(function ($hallazgo) {
            $operarioNombre = $hallazgo->operario ? $hallazgo->operario->nombre : 'Sin asignar';
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
            'hallazgosChartData' => $hallazgosChartData,
            'productosChartData' => $productosChartData,
            'puestosChartData' => $puestosChartData,
        ]);
    }
    
    public function mensual(Request $request)
    {
        $mes = (int) $request->get('mes', now()->month);
        $anio = (int) $request->get('anio', now()->year);
        $mesStr = str_pad((string) $mes, 2, '0', STR_PAD_LEFT);

        $indicadores = IndicadorDiario::where('mes', $mesStr)
            ->where('año', $anio)
            ->orderBy('fecha_operacion')
            ->get();
        
        $totales = [
            'animales' => $indicadores->sum('animales_procesados'),
            'hallazgos' => $indicadores->sum('total_hallazgos'),
            'dias_operados' => $indicadores->count(),
            'hematomas' => $indicadores->sum('hematomas'),
            'cobertura' => $indicadores->sum('cobertura_grasa'),
            'cortes_piernas' => $indicadores->sum('cortes_piernas'),
        ];

        // --- Datos para Gráficos ---
        $labels = $indicadores->map(fn($d) => Carbon::parse($d->fecha_operacion)->format('d/m'));
        $daysCount = $indicadores->count();

        // Metas hardcodeadas temporalmente
        $metas = [
            'meta_sobrebarriga_rotas' => 0.9,
            'meta_hematomas' => 0.5,
            'meta_corte_en_piernas' => 1.0,
            'meta_cobertura_grasa' => 1.5,
        ];

        $chartData = [
            'labels' => $labels,
            'datasets' => [
                'sobrebarriga' => [
                    ['label' => 'Sobrerbarriga R', 'data' => $indicadores->pluck('porcentaje_sobrebarriga_rotas'), 'borderColor' => '#EF4444', 'tension' => 0.1],
                    ['label' => 'META', 'data' => array_fill(0, $daysCount, $metas['meta_sobrebarriga_rotas']), 'borderColor' => '#F97316', 'borderDash' => [5, 5], 'pointRadius' => 0],
                ],
                'hematomas' => [
                    ['label' => 'Hematomas', 'data' => $indicadores->pluck('porcentaje_hematomas'), 'borderColor' => '#22C55E', 'tension' => 0.1],
                    ['label' => 'META', 'data' => array_fill(0, $daysCount, $metas['meta_hematomas']), 'borderColor' => '#F97316', 'borderDash' => [5, 5], 'pointRadius' => 0],
                ],
                'cortes_piernas' => [
                    ['label' => 'Cortes en Piernas', 'data' => $indicadores->pluck('porcentaje_corte_en_piernas'), 'borderColor' => '#EC4899', 'tension' => 0.1],
                    ['label' => 'META', 'data' => array_fill(0, $daysCount, $metas['meta_corte_en_piernas']), 'borderColor' => '#F97316', 'borderDash' => [5, 5], 'pointRadius' => 0],
                ],
                'cobertura_grasa' => [
                    ['label' => 'Cobertura Grasa', 'data' => $indicadores->pluck('porcentaje_cobertura_grasa'), 'borderColor' => '#3B82F6', 'tension' => 0.1],
                    ['label' => 'META', 'data' => array_fill(0, $daysCount, $metas['meta_cobertura_grasa']), 'borderColor' => '#F97316', 'borderDash' => [5, 5], 'pointRadius' => 0],
                ]
            ]
        ];
        
        return view('dashboard.mensual', compact('mes', 'anio', 'indicadores', 'totales', 'chartData'));
    }
}
