<?php

namespace App\Support;

use App\Models\IndicadorDiario;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Tabla ACUMULADO LIBERACION DE CANALES (estilo Excel institucional).
 *
 * Por mes: (suma hallazgos del tipo / suma medias canales) × 100.
 * Promedio por ítem: media aritmética de los % mensuales de esa fila.
 * Total Hallazgos (mes): suma de los 4 ratios sobre medias canales, redondeada una sola vez
 * (igual que la tarjeta «Acumulado del mes» del dashboard mensual).
 * Promedio del año (total): SUMA de los promedios de las 4 filas (=SI(SUMA(P6:P9)=0;"",SUMA(P6:P9))).
 */
final class AcumuladoAnualLiberacion
{
    /** @var list<string> */
    private const MESES_ETIQUETA = [
        'EN', 'FEB', 'MZO', 'ABRIL', 'MAY', 'JUN', 'JUL', 'AGTO', 'SEP', 'OCT', 'NOV', 'DIC',
    ];

    public static function mesLimite(int $anio): int
    {
        $hoy = Carbon::today();

        if ($anio > $hoy->year) {
            return 0;
        }

        if ($anio === $hoy->year) {
            return $hoy->month;
        }

        return 12;
    }

    /**
     * @return array{
     *     anio: int,
     *     titulo: string,
     *     mes_limite: int,
     *     columnas_meses: list<array{num: int, label: string}>,
     *     filas: list<array{
     *         key: string,
     *         label: string,
     *         valores: list<float>,
     *         promedio: float|null,
     *         is_total?: bool
     *     }>,
     *     promedio_anual_total: float|null,
     *     chart: array{labels: list<string>, datasets: list<array<string, mixed>>}
     * }
     */
    public static function build(int $anio): array
    {
        $mesLimite = self::mesLimite($anio);
        $columnasMeses = [];

        $cob = [];
        $sob = [];
        $cor = [];
        $hem = [];
        $totalesMes = [];

        for ($m = 1; $m <= $mesLimite; $m++) {
            $columnasMeses[] = [
                'num' => $m,
                'label' => self::MESES_ETIQUETA[$m - 1],
            ];

            $inds = self::indicadoresMesDeduplicados($m, $anio);

            if ($inds->isEmpty()) {
                $cob[] = 0.0;
                $sob[] = 0.0;
                $cor[] = 0.0;
                $hem[] = 0.0;
                $totalesMes[] = 0.0;

                continue;
            }

            $sumMedias = (int) $inds->sum('medias_canales_total');
            if ($sumMedias <= 0) {
                $sumMedias = (int) max(0, $inds->sum('animales_procesados')) * 2;
            }
            $sumMedias = max(1, $sumMedias);

            $ratioCob = (int) $inds->sum('cobertura_grasa') / $sumMedias;
            $ratioSob = (int) $inds->sum('sobrebarriga_rota') / $sumMedias;
            $ratioCor = (int) $inds->sum('cortes_piernas') / $sumMedias;
            $ratioHem = (int) $inds->sum('hematomas') / $sumMedias;

            $pc = PorcentajeVista::mediaCanalPuntos2($ratioCob);
            $ps = PorcentajeVista::mediaCanalPuntos2($ratioSob);
            $pr = PorcentajeVista::mediaCanalPuntos2($ratioCor);
            $ph = PorcentajeVista::mediaCanalPuntos2($ratioHem);

            $cob[] = $pc;
            $sob[] = $ps;
            $cor[] = $pr;
            $hem[] = $ph;
            $totalesMes[] = PorcentajeVista::mediaCanalPuntos2($ratioCob + $ratioSob + $ratioCor + $ratioHem);
        }

        $promCob = self::promedioFila($cob);
        $promSob = self::promedioFila($sob);
        $promCor = self::promedioFila($cor);
        $promHem = self::promedioFila($hem);

        $sumPromediosCategorias = ($promCob ?? 0) + ($promSob ?? 0) + ($promCor ?? 0) + ($promHem ?? 0);
        $promedioAnualTotal = $sumPromediosCategorias > 0
            ? round($sumPromediosCategorias, 2)
            : null;

        $filas = [
            [
                'key' => 'cobertura_grasa',
                'label' => 'Cobertura grasa',
                'valores' => $cob,
                'promedio' => $promCob,
            ],
            [
                'key' => 'sobrebarriga_rota',
                'label' => 'Sobre barrigas rotas',
                'valores' => $sob,
                'promedio' => $promSob,
            ],
            [
                'key' => 'cortes_piernas',
                'label' => 'Cortes en piernas',
                'valores' => $cor,
                'promedio' => $promCor,
            ],
            [
                'key' => 'hematomas',
                'label' => 'Hematomas',
                'valores' => $hem,
                'promedio' => $promHem,
            ],
            [
                'key' => 'total_hallazgos',
                'label' => 'Total Hallazgos',
                'valores' => $totalesMes,
                'promedio' => $promedioAnualTotal,
                'is_total' => true,
            ],
        ];

        $labelsChart = array_column($columnasMeses, 'label');

        return [
            'anio' => $anio,
            'titulo' => 'ACUMULADO LIBERACION DE CANALES '.$anio,
            'mes_limite' => $mesLimite,
            'columnas_meses' => $columnasMeses,
            'filas' => $filas,
            'promedio_anual_total' => $promedioAnualTotal,
            'chart' => [
                'labels' => $labelsChart,
                'datasets' => [
                    [
                        'label' => 'Cobertura grasa',
                        'data' => $cob,
                        'borderColor' => '#EF4444',
                        'backgroundColor' => 'rgba(239, 68, 68, 0.15)',
                    ],
                    [
                        'label' => 'Sobre barrigas rotas',
                        'data' => $sob,
                        'borderColor' => '#22C55E',
                        'backgroundColor' => 'rgba(34, 197, 94, 0.15)',
                    ],
                    [
                        'label' => 'Cortes en piernas',
                        'data' => $cor,
                        'borderColor' => '#3B82F6',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.15)',
                    ],
                    [
                        'label' => 'Hematomas',
                        'data' => $hem,
                        'borderColor' => '#EC4899',
                        'backgroundColor' => 'rgba(236, 72, 153, 0.15)',
                    ],
                    [
                        'label' => 'Total Hallazgos',
                        'data' => $totalesMes,
                        'borderColor' => '#111827',
                        'backgroundColor' => 'rgba(17, 24, 39, 0.08)',
                        'borderWidth' => 3,
                    ],
                ],
            ],
        ];
    }

    public static function formatoPorcentaje(?float $valor): string
    {
        if ($valor === null) {
            return '';
        }

        return number_format($valor, 2, ',', '.').'%';
    }

    /**
     * Un registro por día (misma regla que el dashboard mensual).
     *
     * @return Collection<int, IndicadorDiario>
     */
    private static function indicadoresMesDeduplicados(int $mes, int $anio): Collection
    {
        $mesStr = str_pad((string) $mes, 2, '0', STR_PAD_LEFT);

        return IndicadorDiario::where('mes', $mesStr)
            ->where('año', $anio)
            ->orderBy('fecha_operacion')
            ->get()
            ->groupBy(fn ($d) => Carbon::parse($d->fecha_operacion)->format('Y-m-d'))
            ->map(fn ($rows) => $rows->sortBy(fn ($x) => $x->id)->last())
            ->values();
    }

    /**
     * @param  list<float>  $valores
     */
    private static function promedioFila(array $valores): ?float
    {
        if ($valores === []) {
            return null;
        }

        return round(array_sum($valores) / count($valores), 2);
    }
}
