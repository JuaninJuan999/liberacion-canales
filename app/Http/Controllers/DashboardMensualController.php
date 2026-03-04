<?php

namespace App\Http\Controllers;

use App\Models\IndicadorDiario;
use App\Models\RegistroHallazgo;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardMensualController extends Controller
{
    public function __invoke(Request $request)
    {
        $mes = (int) $request->get('mes', now()->month);
        $anio = (int) $request->get('anio', now()->year);
        $mesStr = str_pad((string) $mes, 2, '0', STR_PAD_LEFT);

        $indicadores = IndicadorDiario::where('mes', $mesStr)
            ->where('año', $anio)
            ->orderBy('fecha_operacion')
            ->get();

        // --- Calcular porcentajes para los gráficos ---
        // El cálculo se hace sobre el total de MEDIAS CANALES (animales * 2)
        $indicadores->each(function ($indicador) {
            $mediasCanales = ($indicador->animales_procesados > 0 ? $indicador->animales_procesados : 1) * 2;
            $indicador->porcentaje_sobrebarriga_rotas = ($indicador->sobrebarriga_rota / $mediasCanales) * 100;
            $indicador->porcentaje_hematomas = ($indicador->hematomas / $mediasCanales) * 100;
            $indicador->porcentaje_corte_en_piernas = ($indicador->cortes_piernas / $mediasCanales) * 100;
            $indicador->porcentaje_cobertura_grasa = ($indicador->cobertura_grasa / $mediasCanales) * 100;
        });
        
        $totales = [
            'animales' => $indicadores->sum('animales_procesados'),
            'hallazgos' => $indicadores->sum('total_hallazgos'),
            'dias_operados' => $indicadores->count(),
            'hematomas' => $indicadores->sum('hematomas'),
            'cobertura' => $indicadores->sum('cobertura_grasa'),
            'cortes_piernas' => $indicadores->sum('cortes_piernas'),
            'sobrebarriga_rotas' => $indicadores->sum('sobrebarriga_rota'),
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
        
        // Datos de hallazgos nuevos
        $hallazgosNuevos = $this->contarHallazgosNuevos($indicadores);
        
        return view('dashboard.mensual', compact('mes', 'anio', 'indicadores', 'totales', 'chartData', 'hallazgosNuevos'));
    }

    private function contarHallazgosNuevos($indicadores)
    {
        if ($indicadores->isEmpty()) {
            return ['MATERIA FECAL' => 0, 'CONTENIDO RUMINAL' => 0, 'LECHE VISIBLE' => 0];
        }

        // Obtener fechas del rango de indicadores
        $fechaInicio = $indicadores->first()->fecha_operacion;
        $fechaFin = $indicadores->last()->fecha_operacion;

        // Obtener todos los hallazgos del mes
        $hallazgos = RegistroHallazgo::whereBetween('fecha_operacion', [$fechaInicio, $fechaFin])
            ->with('tipoHallazgo')
            ->get();

        $tiposNuevos = ['MATERIA FECAL', 'CONTENIDO RUMINAL', 'LECHE VISIBLE'];
        
        $resultado = [];
        foreach ($tiposNuevos as $tipo) {
            $resultado[$tipo] = $hallazgos
                ->filter(function ($h) use ($tipo) {
                    return stripos($h->tipoHallazgo->nombre ?? '', $tipo) !== false;
                })
                ->count();
        }
        
        return $resultado;
    }
}
