<?php

namespace App\Exports;

use App\Exports\Support\DashboardExcelGraphics;
use App\Support\PorcentajeVista;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCharts;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DashboardGraficasMensualesExport implements WithCharts, WithMultipleSheets
{
    public const ALL_HOJAS = [
        'resumen',
        'sobrebarriga',
        'hematomas',
        'cortes_piernas',
        'cobertura_grasa',
        'hallazgos_tc',
        'seguimiento',
    ];

    /**
     * @param  array<string, mixed>  $data
     * @param  string[]  $hojas  Claves a incluir (ver ALL_HOJAS)
     */
    public function __construct(
        private array $data,
        private array $hojas,
    ) {}

    /**
     * Activar escritura de gráficos en el .xlsx (WriterFactory hace setIncludeCharts(true) si WithCharts).
     * Los gráficos reales se añaden con addChart() en AfterSheet de cada hoja.
     *
     * @return array<int, Chart>
     */
    public function charts(): array
    {
        return [];
    }

    public function sheets(): array
    {
        $sheets = [];
        $hojas = $this->hojas;
        if ($hojas === []) {
            $hojas = self::ALL_HOJAS;
        }

        foreach (self::ALL_HOJAS as $key) {
            if (! in_array($key, $hojas, true)) {
                continue;
            }
            $sheets[] = match ($key) {
                'resumen' => new DashboardGraficaSheetResumen($this->data),
                'sobrebarriga' => new DashboardGraficaSheetTendencia($this->data, 'sobrebarriga', 'Sobrebarriga rotas'),
                'hematomas' => new DashboardGraficaSheetTendencia($this->data, 'hematomas', 'Hematomas'),
                'cortes_piernas' => new DashboardGraficaSheetTendencia($this->data, 'cortes_piernas', 'Cortes en piernas'),
                'cobertura_grasa' => new DashboardGraficaSheetTendencia($this->data, 'cobertura_grasa', 'Cobertura grasa'),
                'hallazgos_tc' => new DashboardGraficaSheetHallazgosTc($this->data),
                'seguimiento' => new DashboardGraficaSheetSeguimiento($this->data),
            };
        }

        if ($sheets === []) {
            $sheets[] = new DashboardGraficaSheetResumen($this->data);
        }

        return $sheets;
    }
}

class DashboardGraficaSheetResumen implements FromArray, ShouldAutoSize, WithEvents, WithStrictNullComparison, WithStyles, WithTitle
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        private array $data
    ) {}

    public function array(): array
    {
        $t = $this->data['totales'] ?? [];
        $s = $this->data['seguimientoSemanal'] ?? [];
        $pPct = function (string $k): string {
            $pc = (array) (($this->data['seguimientoSemanal'] ?? [])['por_clave'] ?? []);

            return PorcentajeVista::mediaCanalFormato2((float) ($pc[$k]['pct_media'] ?? 0));
        };
        $ac = PorcentajeVista::mediaCanalFormato2((float) ($s['acumulado_pct_media'] ?? 0));

        $m = (int) ($this->data['mes'] ?? 1);
        $a = (int) ($this->data['anio'] ?? date('Y'));

        return array_merge(DashboardExcelGraphics::institutionalHeaderBlock(), [
            ['Resumen del mes (dashboard)'],
            ['Mes / año', sprintf('%02d / %d', $m, $a)],
            ['Días operados', $t['dias_operados'] ?? 0],
            ['Total animales', $t['animales'] ?? 0],
            ['Total medias canales', $t['medias_canales'] ?? 0],
            ['Total hallazgos', $t['hallazgos'] ?? 0],
            ['Sobrebarriga rotas (cant.)', $t['sobrebarriga_rotas'] ?? 0, 'Promedio', $pPct('sobrebarriga_rota')],
            ['Hematomas (cant.)', $t['hematomas'] ?? 0, 'Promedio', $pPct('hematomas')],
            ['Cobertura grasa (cant.)', $t['cobertura'] ?? 0, 'Promedio', $pPct('cobertura_grasa')],
            ['Cortes piernas (cant.)', $t['cortes_piernas'] ?? 0, 'Promedio', $pPct('cortes_piernas')],
            ['Acumulado del mes (suma % m.c.)', $ac],
        ]);
    }

    public function title(): string
    {
        return 'Resumen';
    }

    public function styles(Worksheet $sheet)
    {
        $dataRow = 1 + DashboardExcelGraphics::HEADER_ROWS;

        return [
            $dataRow => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $ws = $event->sheet->getDelegate();
                DashboardExcelGraphics::applyInstitutionalHeader($ws, DashboardExcelGraphics::mesTitulo($this->data));
            },
        ];
    }
}

class DashboardGraficaSheetTendencia implements FromArray, ShouldAutoSize, WithEvents, WithStrictNullComparison, WithStyles, WithTitle
{
    public int $excelDataStartRow = 0;

    public int $excelDataEndRow = 0;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        private array $data,
        private string $chartKey,
        private string $sheetTitle,
    ) {}

    public function array(): array
    {
        $chart = $this->data['chartData'] ?? [];
        $labels = (array) ($chart['labels'] ?? []);
        $ds = (array) (($chart['datasets'] ?? [])[$this->chartKey] ?? []);
        $serie = (array) ($ds[0]['data'] ?? []);
        $meta = (array) ($ds[1]['data'] ?? []);
        $serieName = (string) ($ds[0]['label'] ?? 'Indicador');
        $n = max(count($labels), count($serie), count($meta));
        $headerRow = 1 + DashboardExcelGraphics::HEADER_ROWS;
        $this->excelDataStartRow = $headerRow + 1;
        $this->excelDataEndRow = $headerRow + $n;

        $rows = array_merge(DashboardExcelGraphics::institutionalHeaderBlock(), [
            ['Día (mes)', $serieName, 'META (%)'],
        ]);
        for ($i = 0; $i < $n; $i++) {
            $rows[] = [
                $labels[$i] ?? '',
                $serie[$i] ?? 0,
                $meta[$i] ?? 0,
            ];
        }

        return $rows;
    }

    public function title(): string
    {
        $max = 31;
        $t = $this->sheetTitle;
        if (function_exists('mb_substr')) {
            return mb_substr($t, 0, $max);
        }

        return substr($t, 0, $max);
    }

    public function styles(Worksheet $sheet)
    {
        $hdr = 1 + DashboardExcelGraphics::HEADER_ROWS;

        return [
            $hdr => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $ws = $event->sheet->getDelegate();
                DashboardExcelGraphics::applyInstitutionalHeader($ws, DashboardExcelGraphics::mesTitulo($this->data));
                if ($this->excelDataEndRow >= $this->excelDataStartRow) {
                    DashboardExcelGraphics::addLineChartTwoSeries(
                        $ws,
                        'chart_'.$this->chartKey,
                        'Tendencia diaria (%)',
                        $this->excelDataStartRow,
                        $this->excelDataEndRow,
                        'A',
                        'B',
                        'C',
                        'E4',
                        'U26'
                    );
                }
            },
        ];
    }
}

class DashboardGraficaSheetHallazgosTc implements FromArray, ShouldAutoSize, WithEvents, WithStrictNullComparison, WithStyles, WithTitle
{
    public int $excelDataStartRow = 0;

    public int $excelDataEndRow = 0;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        private array $data
    ) {}

    public function array(): array
    {
        $hn = (array) ($this->data['hallazgosNuevos'] ?? []);
        $fechas = (array) ($hn['fechas'] ?? []);
        $m = (float) ($hn['meta'] ?? 0);
        $n = count($fechas);
        $headerRow = 1 + DashboardExcelGraphics::HEADER_ROWS;
        $this->excelDataStartRow = $headerRow + 1;
        $this->excelDataEndRow = $headerRow + $n;

        $rows = array_merge(DashboardExcelGraphics::institutionalHeaderBlock(), [
            [
                'Día (mes)',
                'Materia fecal (%)',
                'Contenido ruminal (%)',
                'Leche visible (%)',
                'META (%)',
            ],
        ]);
        for ($i = 0; $i < $n; $i++) {
            $rows[] = [
                $fechas[$i] ?? '',
                $hn['MATERIA FECAL'][$i] ?? 0,
                $hn['CONTENIDO RUMINAL'][$i] ?? 0,
                $hn['LECHE VISIBLE'][$i] ?? 0,
                $m,
            ];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Hallazgos TC';
    }

    public function styles(Worksheet $sheet)
    {
        $hdr = 1 + DashboardExcelGraphics::HEADER_ROWS;

        return [
            $hdr => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $ws = $event->sheet->getDelegate();
                DashboardExcelGraphics::applyInstitutionalHeader($ws, DashboardExcelGraphics::mesTitulo($this->data));
                if ($this->excelDataEndRow >= $this->excelDataStartRow) {
                    DashboardExcelGraphics::addLineChartMultiSeries(
                        $ws,
                        'chart_hallazgos_tc',
                        'Hallazgos TC por tipo (%)',
                        $this->excelDataStartRow,
                        $this->excelDataEndRow,
                        1,
                        [2, 3, 4, 5],
                        'E4',
                        'V28'
                    );
                }
            },
        ];
    }
}

class DashboardGraficaSheetSeguimiento implements FromArray, ShouldAutoSize, WithEvents, WithStrictNullComparison, WithStyles, WithTitle
{
    public int $excelComboDataStart = 0;

    public int $excelComboDataEnd = 0;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        private array $data
    ) {}

    public function array(): array
    {
        $s = (array) ($this->data['seguimientoSemanal'] ?? []);
        $filas = (array) ($s['filas'] ?? []);
        $combo = (array) ($s['chart_combo'] ?? []);

        $rows = array_merge(DashboardExcelGraphics::institutionalHeaderBlock(), [
            [($combo['titulo'] ?? 'Seguimiento')],
            ['Tipo de hallazgo', 'Cantidad (mes)', '% animal (ratio)', '% media canales (ratio)'],
        ]);
        foreach ($filas as $f) {
            $f = (array) $f;
            $rows[] = [
                $f['item'] ?? '',
                $f['cantidad'] ?? '',
                $f['pct_animal'] ?? '',
                $f['pct_media'] ?? '',
            ];
        }
        $rows[] = [
            'Acumulado (suma ratios % m.c., sin redondeo intermedio)',
            '', '', (float) ($s['acumulado_pct_media'] ?? 0),
        ];
        $rows[] = [];
        $rows[] = [
            'Gráfica combo (categoría)',
            'Resultado (%)',
            'META (%)',
        ];
        $this->excelComboDataStart = count($rows) + 1;
        $lab = (array) ($combo['labels'] ?? []);
        $ac = (float) ($combo['acumulado_bar'] ?? 0);
        $res = (array) ($combo['resultado_bars'] ?? []);
        $ml = (array) ($combo['meta_line'] ?? []);
        for ($i = 0; $i < 5; $i++) {
            $l = (string) ($lab[$i] ?? '');
            if ($i === 0) {
                $rows[] = [$l, $ac, (float) ($ml[0] ?? 0)];
            } else {
                $j = $i - 1;
                $rows[] = [$l, (float) ($res[$j] ?? 0), (float) ($ml[$i] ?? 0)];
            }
        }
        $this->excelComboDataEnd = $this->excelComboDataStart + 4;

        return $rows;
    }

    public function title(): string
    {
        return 'Seguimiento';
    }

    public function styles(Worksheet $sheet)
    {
        $h = 1 + DashboardExcelGraphics::HEADER_ROWS;

        return [
            $h => ['font' => ['bold' => true, 'size' => 11]],
            $h + 1 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $ws = $event->sheet->getDelegate();
                DashboardExcelGraphics::applyInstitutionalHeader($ws, DashboardExcelGraphics::mesTitulo($this->data));
                if ($this->excelComboDataEnd >= $this->excelComboDataStart) {
                    DashboardExcelGraphics::addColumnChartTwoSeries(
                        $ws,
                        'chart_seguimiento_combo',
                        ($this->data['seguimientoSemanal']['chart_combo']['titulo'] ?? 'Acumulado').' — %',
                        $this->excelComboDataStart,
                        $this->excelComboDataEnd,
                        'A',
                        'B',
                        'C',
                        'E4',
                        'T24'
                    );
                }
            },
        ];
    }
}
