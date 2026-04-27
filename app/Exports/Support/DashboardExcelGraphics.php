<?php

namespace App\Exports\Support;

use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class DashboardExcelGraphics
{
    public const HEADER_ROWS = 4;

    public static function mesTitulo(array $data): string
    {
        $m = (int) ($data['mes'] ?? 1);
        $a = (int) ($data['anio'] ?? (int) date('Y'));

        return \Carbon\Carbon::create($a, $m, 1)->locale('es')->isoFormat('MMMM YYYY');
    }

    /**
     * Filas que se anteponen a cada hoja (el logo y textos se terminan de aplicar en applyInstitutionalHeader).
     *
     * @return list<list<string|int|float|''>>
     */
    public static function institutionalHeaderBlock(): array
    {
        // Columna A: logo; B: separación; C–H: texto (se fusiona en applyInstitutionalHeader)
        return [
            ['', '', '', '', '', '', ''],
            ['', '', '', '', '', '', ''],
            ['', '', '', '', '', '', ''],
            ['', '', '', '', '', '', ''],
        ];
    }

    public static function escSheet(string $name): string
    {
        return "'".str_replace("'", "''", $name)."'";
    }

    public static function applyInstitutionalHeader(Worksheet $sheet, string $mesTitulo): void
    {
        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('B')->setWidth(2.5);
        $sheet->getColumnDimension('C')->setAutoSize(true);

        $sheet->mergeCells('C1:H3');
        $sheet->setCellValue('C1', "Liberación de canales\nDashboard mensual — ".mb_strtoupper($mesTitulo));
        $sheet->getStyle('C1')->getAlignment()
            ->setWrapText(true)
            ->setVertical('center')
            ->setHorizontal('left');
        $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(12);
        $sheet->getRowDimension(1)->setRowHeight(22);
        $sheet->getRowDimension(2)->setRowHeight(22);
        $sheet->getRowDimension(3)->setRowHeight(22);

        $logo = public_path('logo.png');
        if (is_file($logo)) {
            $drawing = new Drawing;
            $drawing->setName('Logo');
            $drawing->setPath($logo);
            $drawing->setHeight(48);
            $drawing->setCoordinates('A1');
            $drawing->setOffsetX(6);
            $drawing->setOffsetY(4);
            $drawing->setWorksheet($sheet);
        }
    }

    public static function addLineChartTwoSeries(
        Worksheet $sheet,
        string $chartName,
        string $chartTitle,
        int $dataStartRow,
        int $dataEndRow,
        string $catCol = 'A',
        string $val1Col = 'B',
        string $val2Col = 'C',
        string $topLeft = 'E4',
        string $bottomRight = 'T22',
    ): void {
        if ($dataEndRow < $dataStartRow) {
            return;
        }
        $sn = self::escSheet($sheet->getTitle());
        $n = $dataEndRow - $dataStartRow + 1;
        $hdr = $dataStartRow - 1;
        $refCat = "{$sn}!\${$catCol}\${$dataStartRow}:\${$catCol}\${$dataEndRow}";
        $refV1 = "{$sn}!\${$val1Col}\${$dataStartRow}:\${$val1Col}\${$dataEndRow}";
        $refV2 = "{$sn}!\${$val2Col}\${$dataStartRow}:\${$val2Col}\${$dataEndRow}";
        $refL1 = "{$sn}!\${$val1Col}\${$hdr}";
        $refL2 = "{$sn}!\${$val2Col}\${$hdr}";

        $labels = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, $refL1, null, 1),
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, $refL2, null, 1),
        ];
        $x = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, $refCat, null, $n),
        ];
        $vals = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, $refV1, null, $n),
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, $refV2, null, $n),
        ];
        $series = new DataSeries(
            DataSeries::TYPE_LINECHART,
            DataSeries::GROUPING_STANDARD,
            [0, 1],
            $labels,
            $x,
            $vals
        );
        $plot = new PlotArea(null, [$series]);
        $legend = new Legend(Legend::POSITION_BOTTOM, null, false);
        $title = new Title($chartTitle);
        $yLabel = new Title('%');
        $chart = new Chart($chartName, $title, $legend, $plot, true, DataSeries::EMPTY_AS_GAP, null, $yLabel);
        $chart->setTopLeftPosition($topLeft);
        $chart->setBottomRightPosition($bottomRight);
        $sheet->addChart($chart);
    }

    /**
     * @param  int[]  $valCols  Índices de columna 1-based (1=A) para series de valores; leyenda en fila $dataStartRow-1
     */
    public static function addLineChartMultiSeries(
        Worksheet $sheet,
        string $chartName,
        string $chartTitle,
        int $dataStartRow,
        int $dataEndRow,
        int $catCol = 1,
        array $valCols = [2, 3, 4, 5],
        string $topLeft = 'E4',
        string $bottomRight = 'T24',
    ): void {
        if ($dataEndRow < $dataStartRow || $valCols === []) {
            return;
        }
        $sn = self::escSheet($sheet->getTitle());
        $n = $dataEndRow - $dataStartRow + 1;
        $hdr = $dataStartRow - 1;
        $catLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($catCol);
        $refCat = "{$sn}!\${$catLetter}\${$dataStartRow}:\${$catLetter}\${$dataEndRow}";

        $labels = [];
        $vals = [];
        foreach ($valCols as $cIdx) {
            $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx);
            $refL = "{$sn}!\${$letter}\${$hdr}";
            $labels[] = new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, $refL, null, 1);
            $refV = "{$sn}!\${$letter}\${$dataStartRow}:\${$letter}\${$dataEndRow}";
            $vals[] = new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, $refV, null, $n);
        }
        $x = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, $refCat, null, $n)];
        $order = range(0, count($vals) - 1);
        $series = new DataSeries(
            DataSeries::TYPE_LINECHART,
            DataSeries::GROUPING_STANDARD,
            $order,
            $labels,
            $x,
            $vals
        );
        $plot = new PlotArea(null, [$series]);
        $legend = new Legend(Legend::POSITION_BOTTOM, null, false);
        $title = new Title($chartTitle);
        $yLabel = new Title('%');
        $chart = new Chart($chartName, $title, $legend, $plot, true, DataSeries::EMPTY_AS_GAP, null, $yLabel);
        $chart->setTopLeftPosition($topLeft);
        $chart->setBottomRightPosition($bottomRight);
        $sheet->addChart($chart);
    }

    /**
     * Gráfica de columnas agrupadas: categorías en columna, dos series (valores en dos columnas).
     */
    public static function addColumnChartTwoSeries(
        Worksheet $sheet,
        string $chartName,
        string $chartTitle,
        int $dataStartRow,
        int $dataEndRow,
        string $catCol = 'A',
        string $val1Col = 'B',
        string $val2Col = 'C',
        string $topLeft = 'E4',
        string $bottomRight = 'T22',
    ): void {
        if ($dataEndRow < $dataStartRow) {
            return;
        }
        $sn = self::escSheet($sheet->getTitle());
        $n = $dataEndRow - $dataStartRow + 1;
        $hdr = $dataStartRow - 1;
        $refCat = "{$sn}!\${$catCol}\${$dataStartRow}:\${$catCol}\${$dataEndRow}";
        $refV1 = "{$sn}!\${$val1Col}\${$dataStartRow}:\${$val1Col}\${$dataEndRow}";
        $refV2 = "{$sn}!\${$val2Col}\${$dataStartRow}:\${$val2Col}\${$dataEndRow}";
        $refL1 = "{$sn}!\${$val1Col}\${$hdr}";
        $refL2 = "{$sn}!\${$val2Col}\${$hdr}";

        $labels = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, $refL1, null, 1),
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, $refL2, null, 1),
        ];
        $x = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, $refCat, null, $n)];
        $vals = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, $refV1, null, $n),
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, $refV2, null, $n),
        ];
        $series = new DataSeries(
            DataSeries::TYPE_BARCHART,
            DataSeries::GROUPING_CLUSTERED,
            [0, 1],
            $labels,
            $x,
            $vals,
        );
        $series->setPlotDirection(DataSeries::DIRECTION_COL);
        $plot = new PlotArea(null, [$series]);
        $legend = new Legend(Legend::POSITION_BOTTOM, null, false);
        $title = new Title($chartTitle);
        $yLabel = new Title('%');
        $chart = new Chart($chartName, $title, $legend, $plot, true, DataSeries::EMPTY_AS_GAP, null, $yLabel);
        $chart->setTopLeftPosition($topLeft);
        $chart->setBottomRightPosition($bottomRight);
        $sheet->addChart($chart);
    }
}
