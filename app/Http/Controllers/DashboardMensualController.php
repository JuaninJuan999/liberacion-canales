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
        $semanaIso = $request->get('semana_iso') ? (string) $request->get('semana_iso') : null;
        $incluirDomingoSemanal = $request->boolean('incluir_domingo');

        return view('dashboard.mensual', $this->loadMensualData($mes, $anio, $semanaIso, $incluirDomingoSemanal));
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

        $data = $this->loadMensualData($mes, $anio, null, false);
        $filename = 'dashboard-mensual-graficas-'.Str::slug(
            Carbon::create($anio, $mes, 1)->locale('es')->isoFormat('MMMM').'-'.$anio
        ).'.xlsx';

        return Excel::download(new DashboardGraficasMensualesExport($data, $hojas), $filename);
    }

    /**
     * @return array{mes: int, anio: int, indicadores: \Illuminate\Support\Collection, totales: array, chartData: array, hallazgosNuevos: array, seguimientoSemanal: array, seguimientoSemanalLinea: array, seguimientoAnual: array}
     */
    private function loadMensualData(int $mes, int $anio, ?string $semanaIso = null, bool $incluirDomingoSemanal = false): array
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
        $seguimientoSemanalLinea = $this->buildSeguimientoSemanalLinea($inicio, $fin, $semanaIso, $incluirDomingoSemanal);
        $seguimientoAnual = $this->buildSeguimientoAnual($anio);

        return [
            'mes' => $mes,
            'anio' => $anio,
            'indicadores' => $indicadores,
            'totales' => $totales,
            'chartData' => $chartData,
            'hallazgosNuevos' => $hallazgosNuevos,
            'seguimientoSemanal' => $seguimientoSemanal,
            'seguimientoSemanalLinea' => $seguimientoSemanalLinea,
            'seguimientoAnual' => $seguimientoAnual,
        ];
    }

    /**
     * Por cada mes del año: (suma hallazgos del tipo / suma medias canales del mes) × 100.
     * Solo incluye meses hasta el actual si el año es el de hoy; años pasados = 12 meses.
     *
     * @return array{anio: int, titulo_grafico: string, labels: list<string>, y_max: float, datasets: list{array<string, mixed>}}
     */
    private function buildSeguimientoAnual(int $anio): array
    {
        $mesCortos = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $hoy = Carbon::today();

        if ($anio > $hoy->year) {
            $mesLimite = 0;
        } elseif ($anio === $hoy->year) {
            $mesLimite = $hoy->month;
        } else {
            $mesLimite = 12;
        }

        $cob = [];
        $sob = [];
        $cor = [];
        $hem = [];

        $baseLine = [
            'borderWidth' => 2.5,
            'pointRadius' => 5,
            'pointHoverRadius' => 6,
            'pointBackgroundColor' => '#ffffff',
            'pointBorderWidth' => 2,
            'tension' => 0.15,
            'spanGaps' => false,
            'fill' => false,
        ];

        if ($mesLimite === 0) {
            return [
                'anio' => $anio,
                'titulo_grafico' => 'CONSOLIDADO LIBERACION CANALES POR VARIABLE',
                'labels' => [],
                'y_max' => 1.5,
                'datasets' => [
                    array_merge($baseLine, [
                        'label' => 'Cobertura grasa - 1,5%',
                        'data' => [],
                        'borderColor' => '#EF4444',
                        'pointBorderColor' => '#EF4444',
                    ]),
                    array_merge($baseLine, [
                        'label' => 'Sobre barrigas rotas - 1%',
                        'data' => [],
                        'borderColor' => '#22C55E',
                        'pointBorderColor' => '#22C55E',
                    ]),
                    array_merge($baseLine, [
                        'label' => 'Cortes en piernas - 1%',
                        'data' => [],
                        'borderColor' => '#3B82F6',
                        'pointBorderColor' => '#3B82F6',
                    ]),
                    array_merge($baseLine, [
                        'label' => 'Hematomas - 0,5%',
                        'data' => [],
                        'borderColor' => '#EC4899',
                        'pointBorderColor' => '#EC4899',
                    ]),
                ],
            ];
        }

        $labels = array_slice($mesCortos, 0, $mesLimite);

        for ($m = 1; $m <= $mesLimite; $m++) {
            $mesStr = str_pad((string) $m, 2, '0', STR_PAD_LEFT);
            $inds = IndicadorDiario::where('mes', $mesStr)->where('año', $anio)->get();

            if ($inds->isEmpty()) {
                $cob[] = 0.0;
                $sob[] = 0.0;
                $cor[] = 0.0;
                $hem[] = 0.0;

                continue;
            }

            $sumMedias = (int) $inds->sum('medias_canales_total');
            if ($sumMedias <= 0) {
                $sumMedias = (int) max(0, $inds->sum('animales_procesados')) * 2;
            }
            $sumMedias = max(1, $sumMedias);

            $sumCob = (int) $inds->sum('cobertura_grasa');
            $sumSob = (int) $inds->sum('sobrebarriga_rota');
            $sumCor = (int) $inds->sum('cortes_piernas');
            $sumHem = (int) $inds->sum('hematomas');

            $pc = round(($sumCob / $sumMedias) * 100, 2);
            $ps = round(($sumSob / $sumMedias) * 100, 2);
            $pr = round(($sumCor / $sumMedias) * 100, 2);
            $ph = round(($sumHem / $sumMedias) * 100, 2);

            $cob[] = $pc;
            $sob[] = $ps;
            $cor[] = $pr;
            $hem[] = $ph;
        }

        return [
            'anio' => $anio,
            'titulo_grafico' => 'CONSOLIDADO LIBERACION CANALES POR VARIABLE',
            'labels' => $labels,
            /** Escala fija alrededor de la meta de referencia (p. ej. cobertura grasa 1,5%). */
            'y_max' => 1.5,
            'datasets' => [
                array_merge($baseLine, [
                    'label' => 'Cobertura grasa - 1,5%',
                    'data' => $cob,
                    'borderColor' => '#EF4444',
                    'pointBorderColor' => '#EF4444',
                ]),
                array_merge($baseLine, [
                    'label' => 'Sobre barrigas rotas - 1%',
                    'data' => $sob,
                    'borderColor' => '#22C55E',
                    'pointBorderColor' => '#22C55E',
                ]),
                array_merge($baseLine, [
                    'label' => 'Cortes en piernas - 1%',
                    'data' => $cor,
                    'borderColor' => '#3B82F6',
                    'pointBorderColor' => '#3B82F6',
                ]),
                array_merge($baseLine, [
                    'label' => 'Hematomas - 0,5%',
                    'data' => $hem,
                    'borderColor' => '#EC4899',
                    'pointBorderColor' => '#EC4899',
                ]),
            ],
        ];
    }

    /**
     * Gráfica de líneas por semana ISO: % hallazgo / medias canales por día.
     * Columna PROMEDIO = igual que Excel PROMEDIO: media aritmética de los % diarios visibles en la línea (días futuros omitidos).
     * Por defecto el domingo no se muestra ni entra en el promedio; con $incluirDomingoSemanal = true sí.
     */
    private function buildSeguimientoSemanalLinea(Carbon $vistaInicio, Carbon $vistaFin, ?string $semanaIsoSolicitada, bool $incluirDomingoSemanal = false): array
    {
        $opciones = $this->listarSemanasIsoEnRango($vistaInicio, $vistaFin);
        if ($opciones === []) {
            $ref = $vistaInicio->copy();
            $opciones[] = $this->opcionSemanaDesdeCualquierDia($ref);
        }

        $claves = array_column($opciones, 'key');
        $semanaElegida = $semanaIsoSolicitada;
        if (! $semanaElegida || ! in_array($semanaElegida, $claves, true)) {
            $hoy = Carbon::today();
            if ($hoy->between($vistaInicio, $vistaFin)) {
                $semanaElegida = $this->semanaIsoKey($hoy);
            } else {
                $pivote = $vistaFin->copy();
                $semanaElegida = $this->semanaIsoKey($pivote);
            }
            if (! in_array($semanaElegida, $claves, true)) {
                $semanaElegida = $opciones[0]['key'];
            }
        }

        $weekStart = $this->parseSemanaIsoKey($semanaElegida);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();
        $numSemana = (int) $weekStart->isoWeek;

        $indicadoresSemana = IndicadorDiario::whereBetween('fecha_operacion', [
            $weekStart->toDateString(),
            $weekEnd->toDateString(),
        ])->orderBy('fecha_operacion')->get()
            ->keyBy(fn (IndicadorDiario $d) => $d->fecha_operacion->format('Y-m-d'));

        $hoyDia = Carbon::today();

        $labels = [];
        $cobD = [];
        $sobD = [];
        $corD = [];
        $hemD = [];

        for ($i = 0; $i < 7; $i++) {
            $d = $weekStart->copy()->addDays($i);
            if ($d->isSunday() && ! $incluirDomingoSemanal) {
                continue;
            }

            $labels[] = (string) $d->day;
            $k = $d->format('Y-m-d');
            if ($d->gt($hoyDia)) {
                $cobD[] = null;
                $sobD[] = null;
                $corD[] = null;
                $hemD[] = null;

                continue;
            }
            $ind = $indicadoresSemana->get($k);
            if (! $ind) {
                $cobD[] = 0.0;
                $sobD[] = 0.0;
                $corD[] = 0.0;
                $hemD[] = 0.0;

                continue;
            }
            $mediasCanales = ($ind->animales_procesados > 0 ? (int) $ind->animales_procesados : 1) * 2;
            $c = (int) $ind->cobertura_grasa;
            $s = (int) $ind->sobrebarriga_rota;
            $p = (int) $ind->cortes_piernas;
            $h = (int) $ind->hematomas;
            $cobD[] = round(($c / $mediasCanales) * 100, 2);
            $sobD[] = round(($s / $mediasCanales) * 100, 2);
            $corD[] = round(($p / $mediasCanales) * 100, 2);
            $hemD[] = round(($h / $mediasCanales) * 100, 2);
        }

        $cobD[] = $this->promedioTipoExcelPorcentajesDiarios($cobD);
        $sobD[] = $this->promedioTipoExcelPorcentajesDiarios($sobD);
        $corD[] = $this->promedioTipoExcelPorcentajesDiarios($corD);
        $hemD[] = $this->promedioTipoExcelPorcentajesDiarios($hemD);
        $labels[] = 'PROMEDIO';

        $last = count($cobD) - 1;
        $totalAcumuladoPromediosSemana = round(
            (float) ($cobD[$last] ?? 0)
            + (float) ($sobD[$last] ?? 0)
            + (float) ($corD[$last] ?? 0)
            + (float) ($hemD[$last] ?? 0),
            2
        );

        $titulo = 'ACUMULADO SEMANA '.$numSemana;

        return [
            'semana_iso' => $semanaElegida,
            'semanas_opciones' => $opciones,
            'titulo' => strtoupper($titulo),
            'total_acumulado_promedios' => $totalAcumuladoPromediosSemana,
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Cobertura grasa (1,5%)',
                    'data' => $cobD,
                    'borderColor' => '#EF4444',
                    'meta_pct' => 1.5,
                ],
                [
                    'label' => 'Sobrebarrigas rotas (1%)',
                    'data' => $sobD,
                    'borderColor' => '#22C55E',
                    'meta_pct' => 1.0,
                ],
                [
                    'label' => 'Cortes en piernas (1%)',
                    'data' => $corD,
                    'borderColor' => '#3B82F6',
                    'meta_pct' => 1.0,
                ],
                [
                    'label' => 'Hematomas (Significativos) (0,5%)',
                    'data' => $hemD,
                    'borderColor' => '#A855F7',
                    'meta_pct' => 0.5,
                ],
            ],
            'incluir_domingo' => $incluirDomingoSemanal,
        ];
    }

    private function listarSemanasIsoEnRango(Carbon $vistaInicio, Carbon $vistaFin): array
    {
        $cursor = $vistaInicio->copy();
        $vistos = [];
        $opciones = [];
        while ($cursor->lte($vistaFin)) {
            $key = $this->semanaIsoKey($cursor);
            if (! isset($vistos[$key])) {
                $vistos[$key] = true;
                $opciones[] = $this->opcionSemanaDesdeCualquierDia($cursor);
            }
            $cursor->addDay();
        }

        return $opciones;
    }

    private function semanaIsoKey(Carbon $d): string
    {
        return sprintf('%d-W%02d', (int) $d->isoWeekYear, (int) $d->isoWeek);
    }

    private function opcionSemanaDesdeCualquierDia(Carbon $cualquierDia): array
    {
        $ini = $cualquierDia->copy()->startOfWeek(Carbon::MONDAY);
        $fin = $cualquierDia->copy()->endOfWeek(Carbon::SUNDAY);
        $key = $this->semanaIsoKey($cualquierDia);

        return [
            'key' => $key,
            'label' => 'Sem. '.$cualquierDia->isoWeek.' · '.$ini->format('d/m').' — '.$fin->format('d/m'),
        ];
    }

    private function parseSemanaIsoKey(string $key): Carbon
    {
        if (preg_match('/^(\d{4})-W(\d{1,2})$/', $key, $m)) {
            return (new Carbon)
                ->setISODate((int) $m[1], (int) $m[2])
                ->startOfWeek(Carbon::MONDAY)
                ->startOfDay();
        }

        return Carbon::today()->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
    }

    /**
     * Igual que Excel PROMEDIO sobre la fila de % diarios: solo entra cada día con número (los null = día futuro se omiten; el 0 sí cuenta).
     */
    private function promedioTipoExcelPorcentajesDiarios(array $serieSinPromedio): float
    {
        $nums = [];
        foreach ($serieSinPromedio as $v) {
            if ($v === null) {
                continue;
            }
            $nums[] = (float) $v;
        }
        if ($nums === []) {
            return 0.0;
        }

        return round(array_sum($nums) / count($nums), 2);
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
