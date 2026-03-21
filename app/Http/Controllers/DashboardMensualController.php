<?php

namespace App\Http\Controllers;

use App\Models\IndicadorDiario;
use App\Models\HallazgoToleranciaZero;
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

        // --- Generar todos los días del mes ---
        $inicio = Carbon::create($anio, $mes, 1)->startOfMonth();
        $fin = Carbon::create($anio, $mes, 1)->endOfMonth();
        // Si el mes es el actual, solo mostramos hasta hoy
        if ($inicio->year === now()->year && $inicio->month === now()->month) {
            $fin = now()->startOfDay();
        }

        $indicadoresPorFecha = $indicadores->keyBy(fn($d) => Carbon::parse($d->fecha_operacion)->format('Y-m-d'));

        $labels = [];
        $sobrebarrigaData = [];
        $hematomasData = [];
        $cortesData = [];
        $coberturaData = [];

        $cursor = $inicio->copy();
        while ($cursor->lte($fin)) {
            $fechaKey = $cursor->format('Y-m-d');
            $labels[] = $cursor->format('d/m');
            $ind = $indicadoresPorFecha->get($fechaKey);
            $sobrebarrigaData[] = $ind ? round($ind->porcentaje_sobrebarriga_rotas, 2) : 0;
            $hematomasData[]    = $ind ? round($ind->porcentaje_hematomas, 2) : 0;
            $cortesData[]       = $ind ? round($ind->porcentaje_corte_en_piernas, 2) : 0;
            $coberturaData[]    = $ind ? round($ind->porcentaje_cobertura_grasa, 2) : 0;
            $cursor->addDay();
        }

        $daysCount = count($labels);

        // Metas
        $metas = [
            'meta_sobrebarriga_rotas' => 1.0,
            'meta_hematomas' => 0.5,
            'meta_corte_en_piernas' => 1.0,
            'meta_cobertura_grasa' => 1.5,
        ];

        $chartData = [
            'labels' => $labels,
            'datasets' => [
                'sobrebarriga' => [
                    ['label' => 'Sobrerbarriga R', 'data' => $sobrebarrigaData, 'borderColor' => '#EF4444', 'tension' => 0.1],
                    ['label' => 'META', 'data' => array_fill(0, $daysCount, $metas['meta_sobrebarriga_rotas']), 'borderColor' => '#F97316', 'borderDash' => [5, 5], 'pointRadius' => 0],
                ],
                'hematomas' => [
                    ['label' => 'Hematomas', 'data' => $hematomasData, 'borderColor' => '#22C55E', 'tension' => 0.1],
                    ['label' => 'META', 'data' => array_fill(0, $daysCount, $metas['meta_hematomas']), 'borderColor' => '#F97316', 'borderDash' => [5, 5], 'pointRadius' => 0],
                ],
                'cortes_piernas' => [
                    ['label' => 'Cortes en Piernas', 'data' => $cortesData, 'borderColor' => '#EC4899', 'tension' => 0.1],
                    ['label' => 'META', 'data' => array_fill(0, $daysCount, $metas['meta_corte_en_piernas']), 'borderColor' => '#F97316', 'borderDash' => [5, 5], 'pointRadius' => 0],
                ],
                'cobertura_grasa' => [
                    ['label' => 'Cobertura Grasa', 'data' => $coberturaData, 'borderColor' => '#3B82F6', 'tension' => 0.1],
                    ['label' => 'META', 'data' => array_fill(0, $daysCount, $metas['meta_cobertura_grasa']), 'borderColor' => '#F97316', 'borderDash' => [5, 5], 'pointRadius' => 0],
                ],
            ],
        ];

        // Datos de hallazgos TC por tipo
        $hallazgosNuevos = $this->contarHallazgosNuevos($labels, $inicio, $fin, $indicadoresPorFecha);
        $hallazgosNuevos['meta'] = 1.0;

        return view('dashboard.mensual', compact('mes', 'anio', 'indicadores', 'totales', 'chartData', 'hallazgosNuevos'));
    }

    private function contarHallazgosNuevos(array $labels, Carbon $inicio, Carbon $fin, $indicadoresPorFecha): array
    {
        $tiposNuevos = ['MATERIA FECAL', 'CONTENIDO RUMINAL', 'LECHE VISIBLE'];

        $resultado = [
            'fechas'            => $labels,
            'MATERIA FECAL'     => [],
            'CONTENIDO RUMINAL' => [],
            'LECHE VISIBLE'     => [],
        ];

        $cursor = $inicio->copy();
        while ($cursor->lte($fin)) {
            $fechaKey = $cursor->format('Y-m-d');
            $ind = $indicadoresPorFecha->get($fechaKey);
            $animalesProcesados = (int) ($ind->animales_procesados ?? 0);
            $divisor = $animalesProcesados * 4;

            foreach ($tiposNuevos as $tipo) {
                if ($ind && $divisor > 0) {
                    $cantidad = HallazgoToleranciaZero::porFechaConTurno($fechaKey)
                        ->whereHas('tipoHallazgo', function ($query) use ($tipo) {
                            $query->where('nombre', $tipo);
                        })
                        ->count();
                    $resultado[$tipo][] = round(($cantidad / $divisor) * 100, 2);
                } else {
                    $resultado[$tipo][] = 0;
                }
            }

            $cursor->addDay();
        }

        return $resultado;
    }
}
