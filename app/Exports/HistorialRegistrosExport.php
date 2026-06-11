<?php

namespace App\Exports;

use App\Models\RegistroHallazgo;
use App\Support\HistorialRegistrosConsulta;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HistorialRegistrosExport implements FromArray, WithCustomStartCell, WithEvents, WithStyles, WithTitle
{
    protected const LOGO_LAST_COL = 'B';

    protected const TITULO_COL = 'C';

    protected const FIRST_DATA_COL = 'A';

    protected const EVIDENCIA_COL = 'K';

    protected const OPERARIO_COL = 'J';

    protected const OBS_COL = 'L';

    protected const LAST_COL = 'L';

    protected const FILA_ENCABEZADO_TABLA = 5;

    protected const LOGO_ALTO_PX = 30;

    protected const IMAGEN_PX = 36;

    protected const ROW_HEIGHT_CON_IMAGEN = 44;

    /** @var Collection<int, RegistroHallazgo> */
    protected Collection $registros;

    protected string $periodoLabel;

    protected string $generadoLabel;

    /**
     * @param  array<string, mixed>  $filtros
     */
    public function __construct(
        protected array $filtros = []
    ) {
        $this->registros = HistorialRegistrosConsulta::queryConFiltros($this->filtros)
            ->orderByDesc('registros_hallazgos.created_at')
            ->get();

        $inicio = Carbon::parse($this->filtros['fecha_inicio'] ?? now())->format('d/m/Y');
        $fin = Carbon::parse($this->filtros['fecha_fin'] ?? now())->format('d/m/Y');
        $this->periodoLabel = "Período operativo: {$inicio} — {$fin}";
        $this->generadoLabel = 'Generado: '.now()->format('d/m/Y H:i');
    }

    public function startCell(): string
    {
        return self::FIRST_DATA_COL.self::FILA_ENCABEZADO_TABLA;
    }

    public function array(): array
    {
        $rows = [$this->headings()];

        foreach ($this->registros as $row) {
            $rows[] = [
                $row->created_at?->format('d/m/Y H:i') ?? '',
                $row->fecha_operacion?->format('d/m/Y') ?? '',
                $row->codigo ?? '',
                $row->producto->nombre ?? 'N/A',
                $row->tipoHallazgo->nombre ?? 'N/A',
                ($row->tipoHallazgo->es_critico ?? false) ? 'Sí' : 'No',
                HistorialRegistrosConsulta::ubicacionHallazgo($row),
                HistorialRegistrosConsulta::detallePierna($row),
                $row->usuario->name ?? 'N/A',
                HistorialRegistrosConsulta::operarioResponsable($row),
                HistorialRegistrosConsulta::rutaAbsolutaEvidencia($row->evidencia_path) ? '' : 'N/A',
                trim((string) ($row->observacion ?? '')),
            ];
        }

        return $rows;
    }

    protected function filaInicioDatos(): int
    {
        return self::FILA_ENCABEZADO_TABLA + 1;
    }

    public function headings(): array
    {
        return [
            'Fecha de registro',
            'Fecha operación',
            'Código',
            'Producto',
            'Tipo hallazgo',
            'Crítico',
            'Ubicación hallazgo',
            'Detalle (pierna)',
            'Usuario',
            'Operario',
            'Evidencia',
            'Observación',
        ];
    }

    /**
     * @return array<string, float>
     */
    protected function anchosColumnas(): array
    {
        return [
            'A' => 17,
            'B' => 13,
            'C' => 11,
            'D' => 20,
            'E' => 18,
            'F' => 8,
            'G' => 20,
            'H' => 12,
            'I' => 16,
            'J' => 26,
            'K' => 11,
            'L' => 30,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $ws = $event->sheet->getDelegate();

                foreach ($this->anchosColumnas() as $col => $width) {
                    $ws->getColumnDimension($col)->setWidth($width);
                }

                $ws->mergeCells('A1:'.self::LOGO_LAST_COL.'4');
                $ws->mergeCells(self::TITULO_COL.'1:'.self::LAST_COL.'3');
                $ws->mergeCells(self::TITULO_COL.'4:'.self::LAST_COL.'4');

                $ws->setCellValue(self::TITULO_COL.'1', "Liberación de canales\n".mb_strtoupper('Historial de registros de hallazgos'));
                $ws->getStyle(self::TITULO_COL.'1')->getAlignment()
                    ->setWrapText(true)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $ws->getStyle(self::TITULO_COL.'1')->getFont()->setBold(true)->setSize(12);
                $ws->getRowDimension(1)->setRowHeight(22);
                $ws->getRowDimension(2)->setRowHeight(22);
                $ws->getRowDimension(3)->setRowHeight(22);
                $ws->getRowDimension(4)->setRowHeight(18);

                $ws->setCellValue(self::TITULO_COL.'4', $this->periodoLabel.'    '.$this->generadoLabel);
                $ws->getStyle(self::TITULO_COL.'4')->getFont()->setSize(10);
                $ws->getStyle(self::TITULO_COL.'4')->getFont()->getColor()->setRGB('555555');

                $logo = public_path('logo.png');
                if (is_file($logo)) {
                    $drawing = new Drawing;
                    $drawing->setName('Logo');
                    $drawing->setPath($logo);
                    $drawing->setResizeProportional(true);
                    $drawing->setHeight(self::LOGO_ALTO_PX);
                    $drawing->setCoordinates('A1');
                    $drawing->setOffsetX(8);
                    $drawing->setOffsetY(10);
                    $drawing->setWorksheet($ws);
                }

                $headerRow = self::FILA_ENCABEZADO_TABLA;
                $lastRow = $ws->getHighestRow();
                $rangoTabla = self::FIRST_DATA_COL.$headerRow.':'.self::LAST_COL.$lastRow;

                $ws->getStyle($rangoTabla)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D1D5DB'],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);

                $ws->getStyle(self::FIRST_DATA_COL.$headerRow.':'.self::LAST_COL.$headerRow)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => 'B91C1C'],
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
                $ws->getRowDimension($headerRow)->setRowHeight(28);

                $ws->freezePane(self::FIRST_DATA_COL.$this->filaInicioDatos());

                if ($lastRow > $headerRow) {
                    $dataStart = $this->filaInicioDatos();

                    $ws->getStyle(self::OPERARIO_COL.$dataStart.':'.self::OPERARIO_COL.$lastRow)
                        ->getAlignment()
                        ->setWrapText(true);

                    $ws->getStyle(self::OBS_COL.$dataStart.':'.self::OBS_COL.$lastRow)
                        ->getAlignment()
                        ->setWrapText(true);

                    $ws->getStyle(self::EVIDENCIA_COL.$dataStart.':'.self::EVIDENCIA_COL.$lastRow)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                foreach ($this->registros as $index => $registro) {
                    $excelRow = $index + $this->filaInicioDatos();
                    $rutaImagen = HistorialRegistrosConsulta::rutaAbsolutaEvidencia($registro->evidencia_path);

                    if ($rutaImagen === null) {
                        continue;
                    }

                    $ws->getRowDimension($excelRow)->setRowHeight(self::ROW_HEIGHT_CON_IMAGEN);

                    $drawing = new Drawing;
                    $drawing->setName('Evidencia '.$registro->id);
                    $drawing->setDescription('Evidencia hallazgo '.$registro->codigo);
                    $drawing->setPath($rutaImagen);
                    $drawing->setResizeProportional(true);
                    $drawing->setHeight(self::IMAGEN_PX);
                    $drawing->setWidth(self::IMAGEN_PX);
                    $drawing->setCoordinates(self::EVIDENCIA_COL.$excelRow);
                    $drawing->setOffsetX(6);
                    $drawing->setOffsetY(4);
                    $drawing->setWorksheet($ws);
                }
            },
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [];
    }

    public function title(): string
    {
        $inicio = $this->filtros['fecha_inicio'] ?? '';
        $fin = $this->filtros['fecha_fin'] ?? '';

        if ($inicio !== '' && $fin !== '') {
            return 'Historial '.$inicio.'_'.$fin;
        }

        return 'Historial registros';
    }
}
