<?php

namespace App\Exports;

use App\Models\IndicadorDiario;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IndicadoresMensualesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $mes;
    protected $anio;
    
    public function __construct($mes, $anio)
    {
        $this->mes = $mes;
        $this->anio = $anio;
    }
    
    public function collection()
    {
        return IndicadorDiario::where('mes', $this->mes)
            ->where('año', $this->anio)
            ->orderBy('fecha_operacion')
            ->get();
    }
    
    public function headings(): array
    {
        return [
            'Fecha',
            'Animales Procesados',
            'Total Hallazgos',
            'Medias Canales',
            'Media Canal 1',
            'Media Canal 2',
            'Participación %',
            'Cobertura Grasa',
            'Hematomas',
            'Cortes Piernas',
            'Sobrebarriga Rota',
        ];
    }
    
    public function map($indicador): array
    {
        return [
            \Carbon\Carbon::parse($indicador->fecha_operacion)->format('d/m/Y'),
            $indicador->animales_procesados,
            $indicador->total_hallazgos,
            $indicador->medias_canales_total,
            $indicador->medias_canal_1,
            $indicador->medias_canal_2,
            number_format($indicador->participacion_total, 2),
            $indicador->cobertura_grasa,
            $indicador->hematomas,
            $indicador->cortes_piernas,
            $indicador->sobrebarriga_rota,
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
    
    public function title(): string
    {
        $nombreMes = \Carbon\Carbon::create()->month($this->mes)->locale('es')->isoFormat('MMMM');
        return ucfirst($nombreMes) . ' ' . $this->anio;
    }
}
