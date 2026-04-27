<?php

namespace App\Http\Controllers;

use App\Exports\DashboardGraficasMensualesExport;
use App\Models\HallazgoToleranciaZero;
use App\Models\IndicadorDiario;
use App\Support\PorcentajeVista;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class DashboardMensualController extends Controller
{
    public function __invoke(Request $request)
    {
        $mes = (int) $request->get('mes', now()->month);
        $anio = (int) $request->get('anio', now()->year);

        return view('dashboard.mensual', $this->loadMensualData($mes, $anio));
    }

    public function exportGraficasExcel(Request $request)
    {
        $mes = (int) $request->get('mes', now()->month);
        $anio = (int) $request->get('anio', now()->year);
        $hojas = $request->input('hojas', []);
        if (! is_array($hojas)) {
            $hojas = [];
        }
        $hojas = array_filter(array_map('strval', $hojas), fn (string $h) => in_array($h, DashboardGraficasMensualesExport::ALL_HOJAS, true));
        if ($hojas === []) {
            $hojas = DashboardGraficasMensualesExport::ALL_HOJAS;
        } else {
            $hojas = array_values(array_unique($hojas));
        }

        $data = $this->loadMensualData($mes, $anio);
        $filename = 'dashboard-mensual-graficas-'.Str::slug(
            Carbon::create($anio, $mes, 1)->locale('es')->isoFormat('MMMM').'-'.$anio
        ).'.xlsx';

        return Excel::download(new DashboardGraficasMensualesExport($data, $hojas), $filename);
    }

    /**
     * @return array{mes: int, anio: int, indicadores: \Illuminate\Support\Collection, totales: array, chartData: array, hallazgosNuevos: array, seguimientoSemanal: array}
     */
    private function loadMensualData(int $mes, int $anio): array
    {
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

        $sumMediasTotales = (int) $indicadores->sum('medias_canales_total');
        if ($sumMediasTotales <= 0 && $indicadores->isNotEmpty()) {
            $sumMediasTotales = (int) max(0, $indicadores->sum('animales_procesados')) * 2;
        }

        $totales = [
            'animales' => $indicadores->sum('animales_procesados'),
            'medias_canales' => $sumMediasTotales,
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

        $indicadoresPorFecha = $indicadores->keyBy(fn ($d) => Carbon::parse($d->fecha_operacion)->format('Y-m-d'));

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
            $hematomasData[] = $ind ? round($ind->porcentaje_hematomas, 2) : 0;
            $cortesData[] = $ind ? round($ind->porcentaje_corte_en_piernas, 2) : 0;
            $coberturaData[] = $ind ? round($ind->porcentaje_cobertura_grasa, 2) : 0;
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

        $seguimientoSemanal = $this->buildSeguimientoSemanalMensual($indicadores, $totales, $mes, $anio);

        return [
            'mes' => $mes,
            'anio' => $anio,
            'indicadores' => $indicadores,
            'totales' => $totales,
            'chartData' => $chartData,
            'hallazgosNuevos' => $hallazgosNuevos,
            'seguimientoSemanal' => $seguimientoSemanal,
        ];
    }

    /**
     * Resumen estilo Excel "Liberación de canales / seguimiento": sumas del mes por tipo de hallazgo.
     * (El nombre "semanal" se usa en plantilla; los totales son del mes seleccionado.)
     */
    private function buildSeguimientoSemanalMensual($indicadores, array $totales, int $mes, int $anio): array
    {
        $sumMedias = (int) ($totales['medias_canales'] ?? 0);
        if ($sumMedias <= 0) {
            $sumMedias = (int) max(0, $totales['animales']) * 2;
        }
        $totalAnim = (int) max(1, $totales['animales']);
        $sumMediasSafe = max(1, $sumMedias);

        $definicion = [
            ['key' => 'cobertura_grasa', 'label' => 'Cobertura grasa', 'meta_media' => 0.015],
            ['key' => 'sobrebarriga_rota', 'label' => 'Sobrebarrigas rotas', 'meta_media' => 0.01],
            ['key' => 'cortes_piernas', 'label' => 'Cortes en piernas', 'meta_media' => 0.01],
            ['key' => 'hematomas', 'label' => 'Hematomas (Significativos)', 'meta_media' => 0.005],
        ];

        $filas = [];
        $chartLabels = [];
        $chartCantidades = [];
        $chartColors = ['#3B82F6', '#EF4444', '#EC4899', '#22C55E'];

        $porClave = [];

        foreach ($definicion as $t) {
            $sum = (int) $indicadores->sum($t['key']);
            $pa = $sum / $totalAnim;
            $pm = $sum / $sumMediasSafe;
            $fila = [
                'key' => $t['key'],
                'item' => $t['label'],
                'cantidad' => $sum,
                'pct_animal' => $pa,
                'pct_media' => $pm,
                'meta_media' => $t['meta_media'],
            ];
            $filas[] = $fila;
            $porClave[$t['key']] = $fila;
            $chartLabels[] = $t['label'];
            $chartCantidades[] = $sum;
        }

        $acumuladoPctMedia = array_sum(array_column($filas, 'pct_media'));

        $resultadoParaGraf = [];
        foreach ($definicion as $t) {
            $resultadoParaGraf[] = PorcentajeVista::mediaCanalPuntos2(
                (float) ($porClave[$t['key']]['pct_media'] ?? 0)
            );
        }

        $mesLeyenda = strtoupper(
            Carbon::create($anio, $mes, 1)->locale('es')->isoFormat('MMMM YYYY')
        );

        $chartCombo = [
            'labels' => [
                'Σ acumulado',
                'Cobertura grasa',
                'Sobrebarrigas rotas',
                'Cortes en piernas',
                'Hematomas (Significativos)',
            ],
            'acumulado_bar' => PorcentajeVista::mediaCanalPuntos2($acumuladoPctMedia),
            'resultado_bars' => $resultadoParaGraf,
            'meta_line' => [4.0, 1.5, 1.0, 1.0, 0.5],
            'titulo' => 'ACUMULADO '.$mesLeyenda,
        ];

        return [
            'mes_titulo' => Carbon::create($anio, $mes, 1)->locale('es')->isoFormat('MMMM YYYY'),
            'total_animales' => (int) $totales['animales'],
            'total_medias' => (int) $sumMedias,
            'total_hallazgos' => (int) $totales['hallazgos'],
            'filas' => $filas,
            'por_clave' => $porClave,
            'acumulado_pct_media' => $acumuladoPctMedia,
            'chart_combo' => $chartCombo,
            'chart' => [
                'labels' => $chartLabels,
                'cantidades' => $chartCantidades,
                'colors' => $chartColors,
            ],
        ];
    }

    private function contarHallazgosNuevos(array $labels, Carbon $inicio, Carbon $fin, $indicadoresPorFecha): array
    {
        $tiposNuevos = ['MATERIA FECAL', 'CONTENIDO RUMINAL', 'LECHE VISIBLE'];

        $resultado = [
            'fechas' => $labels,
            'MATERIA FECAL' => [],
            'CONTENIDO RUMINAL' => [],
            'LECHE VISIBLE' => [],
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
