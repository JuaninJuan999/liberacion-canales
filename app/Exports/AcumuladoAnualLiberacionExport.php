<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AcumuladoAnualLiberacionExport implements FromArray, WithEvents, WithTitle
{
    private const COLOR_VERDE = 'FF7CE8AD';

    private const COLOR_ROSA = 'FFF9DFF8';

    /**
     * @param  array<string, mixed>  $acumulado
     */
    public function __construct(
        protected array $acumulado
    ) {}

    public function title(): string
    {
        return 'Acumulado '.($this->acumulado['anio'] ?? '');
    }

    public function array(): array
    {
        $mesLabels = array_column($this->acumulado['columnas_meses'] ?? [], 'label');
        $header = array_merge(['Ítem'], $mesLabels, ['Promedio']);
        $rows = [$header];

        foreach ($this->acumulado['filas'] ?? [] as $fila) {
            $valores = array_map(
                fn ($v) => is_numeric($v) ? round((float) $v, 2) / 100 : null,
                $fila['valores'] ?? []
            );
            $prom = $fila['promedio'] ?? null;
            $rows[] = array_merge(
                [$fila['label'] ?? ''],
                $valores,
                [is_numeric($prom) ? round((float) $prom, 2) / 100 : null]
            );
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $anio = (int) ($this->acumulado['anio'] ?? now()->year);
                $titulo = 'ACUMULADO LIBERACION DE CANALES '.$anio;
                $mesCount = count($this->acumulado['columnas_meses'] ?? []);
                $lastColIndex = $mesCount + 2;
                $lastColLetter = $this->colLetter($lastColIndex);

                $sheet->insertNewRowBefore(1, 1);
                $sheet->mergeCells("A1:{$lastColLetter}1");
                $sheet->setCellValue('A1', $titulo);

                $lastDataRow = count($this->acumulado['filas'] ?? []) + 2;

                $sheet->getStyle("A1:{$lastColLetter}1")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '111827']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_VERDE]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                $sheet->getStyle("A2:{$lastColLetter}2")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '111827']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_VERDE]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getStyle("A2:A{$lastDataRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF7CE8AD33']],
                ]);

                if ($mesCount > 0) {
                    $firstMesCol = $this->colLetter(2);
                    $lastMesCol = $this->colLetter($mesCount + 1);
                    $sheet->getStyle("{$firstMesCol}2:{$lastMesCol}{$lastDataRow}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF9DFF833']],
                    ]);
                    $sheet->getStyle("{$firstMesCol}2:{$lastMesCol}2")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_ROSA]],
                    ]);
                }

                $sheet->getStyle("{$lastColLetter}2:{$lastColLetter}{$lastDataRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_ROSA]],
                ]);

                $sheet->getStyle("A1:{$lastColLetter}{$lastDataRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '1F2937'],
                        ],
                    ],
                ]);

                if ($lastDataRow > 2) {
                    $sheet->getStyle("A{$lastDataRow}:{$lastColLetter}{$lastDataRow}")->getFont()->setBold(true);
                }

                for ($row = 3; $row <= $lastDataRow; $row++) {
                    for ($col = 2; $col <= $lastColIndex; $col++) {
                        $cell = $this->colLetter($col).$row;
                        $value = $sheet->getCell($cell)->getValue();
                        if (is_numeric($value)) {
                            $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('0.00%');
                        }
                    }
                }

                $sheet->getColumnDimension('A')->setWidth(28);
                for ($c = 2; $c <= $lastColIndex; $c++) {
                    $sheet->getColumnDimension($this->colLetter($c))->setWidth(12);
                }
            },
        ];
    }

    private function colLetter(int $index): string
    {
        $letter = '';
        while ($index > 0) {
            $index--;
            $letter = chr(65 + ($index % 26)).$letter;
            $index = intdiv($index, 26);
        }

        return $letter;
    }
}
