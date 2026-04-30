<?php

namespace App\Exports;

use App\Exports\Support\DashboardExcelGraphics;
use App\Models\TitulacionAcidoLacticoRegistro;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TitulacionAcidoLacticoRegistrosExport implements FromArray, ShouldAutoSize, WithEvents, WithStrictNullComparison, WithStyles, WithTitle
{
    public function __construct(
        protected ?string $desdeYmd = null,
        protected ?string $hastaYmd = null,
        protected ?string $actividad = null,
    ) {}

    public function array(): array
    {
        $q = TitulacionAcidoLacticoRegistro::query()
            ->with(['usuario', 'verificadoPor'])
            ->orderByDesc('fecha')
            ->orderByDesc('hora');

        if ($this->desdeYmd !== null && $this->desdeYmd !== '') {
            $q->whereDate('fecha', '>=', $this->desdeYmd);
        }

        if ($this->hastaYmd !== null && $this->hastaYmd !== '') {
            $q->whereDate('fecha', '<=', $this->hastaYmd);
        }

        if ($this->actividad !== null && $this->actividad !== '') {
            $q->where('actividad', $this->actividad);
        }

        $rows = array_merge(DashboardExcelGraphics::institutionalHeaderBlock(), [
            [
                'Fecha',
                'Hora',
                'Volumen NaOH (ml)',
                'Concentración solución (%)',
                'Cumple',
                'Corrección',
                'Actividad',
                'Responsable',
                'Verificado',
            ],
        ]);

        foreach ($q->get() as $row) {
            $rows[] = [
                $row->fecha?->format('d/m/Y') ?? '',
                is_string($row->hora) ? substr($row->hora, 0, 5) : '',
                $row->volumen_naoh_ml !== null ? (float) $row->volumen_naoh_ml : null,
                $row->concentracion_sol_pct !== null ? (float) $row->concentracion_sol_pct : null,
                $row->cumple ? 'Cumple' : 'No cumple',
                $row->correccion ?? '',
                (string) ($row->actividad ?? ''),
                $row->usuario?->name ?? '—',
                $row->verificadoPor?->name ?? $row->verificado_nombre ?? '—',
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $hdrRow = 1 + DashboardExcelGraphics::HEADER_ROWS;

        return [
            $hdrRow => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $ws = $event->sheet->getDelegate();

                // Encabezado institucional (sin texto fijo de "Dashboard mensual")
                $ws->getColumnDimension('A')->setWidth(18);
                $ws->getColumnDimension('B')->setWidth(2.5);
                $ws->getColumnDimension('C')->setAutoSize(true);

                $ws->mergeCells('C1:I3');
                $ws->setCellValue('C1', "Liberación de canales\n".mb_strtoupper($this->title()));
                $ws->getStyle('C1')->getAlignment()
                    ->setWrapText(true)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $ws->getStyle('C1')->getFont()->setBold(true)->setSize(12);
                $ws->getRowDimension(1)->setRowHeight(20);
                $ws->getRowDimension(2)->setRowHeight(20);
                $ws->getRowDimension(3)->setRowHeight(20);

                $logo = public_path('logo.png');
                if (is_file($logo)) {
                    $drawing = new Drawing;
                    $drawing->setName('Logo');
                    $drawing->setPath($logo);
                    $drawing->setHeight(34); // más pequeño
                    $drawing->setCoordinates('A1');
                    $drawing->setOffsetX(6);
                    $drawing->setOffsetY(6);
                    $drawing->setWorksheet($ws);
                }

                $headerRow = 1 + DashboardExcelGraphics::HEADER_ROWS;
                $lastRow = $ws->getHighestRow();
                $lastCol = $ws->getHighestColumn();

                // Estilo de encabezado de tabla
                $ws->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => '0F766E'], // teal-700
                    ],
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);
                $ws->getRowDimension($headerRow)->setRowHeight(20);

                // Bordes finos a toda la tabla (incluye header)
                if ($lastRow > $headerRow) {
                    $ws->getStyle("A{$headerRow}:{$lastCol}{$lastRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'D1D5DB'], // gray-300
                            ],
                        ],
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);
                }

                // Congelar panel debajo del encabezado y activar autofiltro
                $ws->freezePane('A'.($headerRow + 1));
                $ws->setAutoFilter("A{$headerRow}:{$lastCol}{$headerRow}");

                // Anchos puntuales para mejor lectura (ShouldAutoSize hace el resto)
                $ws->getColumnDimension('A')->setWidth(12);
                $ws->getColumnDimension('B')->setWidth(8);
                $ws->getColumnDimension('F')->setWidth(40);
                $ws->getColumnDimension('H')->setWidth(22);
                $ws->getColumnDimension('I')->setWidth(22);
            },
        ];
    }

    public function title(): string
    {
        $desde = $this->desdeYmd !== null && $this->desdeYmd !== '' ? $this->desdeYmd : null;
        $hasta = $this->hastaYmd !== null && $this->hastaYmd !== '' ? $this->hastaYmd : null;
        $actividad = $this->actividad !== null && $this->actividad !== '' ? $this->actividad : null;

        if ($desde && $hasta) {
            return 'Historial titulación — '.$desde.' a '.$hasta.($actividad ? ' ('.$actividad.')' : '');
        }

        if ($desde) {
            return 'Historial titulación — desde '.$desde.($actividad ? ' ('.$actividad.')' : '');
        }

        if ($hasta) {
            return 'Historial titulación — hasta '.$hasta.($actividad ? ' ('.$actividad.')' : '');
        }

        return 'Historial titulación — ácido láctico'.($actividad ? ' ('.$actividad.')' : '');
    }
}

