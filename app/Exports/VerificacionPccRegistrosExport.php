<?php

namespace App\Exports;

use App\Models\VerificacionPccRegistro;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VerificacionPccRegistrosExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        protected ?string $fechaYmd = null
    ) {}

    public function collection(): Collection
    {
        $q = VerificacionPccRegistro::query()
            ->with('usuario')
            ->orderByDesc('created_at');

        if ($this->fechaYmd !== null && $this->fechaYmd !== '') {
            $q->whereDate('created_at', $this->fechaYmd);
        }

        return $q->get();
    }

    public function headings(): array
    {
        return [
            'Fecha y hora',
            'ID producto',
            'Propietario (snapshot)',
            'Media canal 1',
            'Media canal 2',
            'Responsable puesto',
            'Observación',
            'Acción correctiva',
            'Usuario registro',
            'ID ins. externo',
        ];
    }

    /**
     * @param  VerificacionPccRegistro  $row
     */
    public function map($row): array
    {
        $propietario = trim((string) (data_get($row->snapshot_externo, 'nombre_empresa') ?? ''));

        return [
            $row->created_at?->format('d/m/Y H:i') ?? '',
            $row->codigoProductoCompleto(),
            $propietario !== '' ? $propietario : '—',
            $row->cumple_media_canal_1 ? 'Cumple' : 'No cumple',
            $row->cumple_media_canal_2 ? 'Cumple' : 'No cumple',
            $row->responsablePuestoResuelto(),
            $row->observacion ?? '',
            $row->accion_correctiva ?? '',
            $row->usuario?->name ?? '—',
            $row->external_ins_id !== null ? (string) $row->external_ins_id : '—',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        if ($this->fechaYmd !== null && $this->fechaYmd !== '') {
            return 'PCC '.$this->fechaYmd;
        }

        return 'Verificación PCC';
    }
}
