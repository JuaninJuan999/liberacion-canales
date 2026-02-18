<?php

namespace App\Http\Controllers;

use App\Models\IndicadorDiario;
use App\Models\RegistroHallazgo;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\IndicadoresMensualesExport;

class ReporteController extends Controller
{
    /**
     * Reporte mensual en PDF
     */
    public function mensualPdf(Request $request)
    {
        $mes = $request->get('mes', now()->month);
        $anio = $request->get('anio', now()->year);
        
        $indicadores = IndicadorDiario::where('mes', $mes)
            ->where('año', $anio)
            ->orderBy('fecha_operacion')
            ->get();
        
        $totales = [
            'animales' => $indicadores->sum('animales_procesados'),
            'hallazgos' => $indicadores->sum('total_hallazgos'),
            'hematomas' => $indicadores->sum('hematomas'),
            'cobertura' => $indicadores->sum('cobertura_grasa'),
            'dias_operados' => $indicadores->count(),
        ];
        
        $nombreMes = \Carbon\Carbon::create()->month($mes)->locale('es')->isoFormat('MMMM');
        
        $pdf = Pdf::loadView('reportes.mensual-pdf', compact(
            'indicadores',
            'totales',
            'mes',
            'anio',
            'nombreMes'
        ));
        
        $pdf->setPaper('letter', 'landscape');
        
        return $pdf->download("reporte-mensual-{$nombreMes}-{$anio}.pdf");
    }
    
    /**
     * Reporte mensual en Excel
     */
    public function mensualExcel(Request $request)
    {
        $mes = $request->get('mes', now()->month);
        $anio = $request->get('anio', now()->year);
        
        $nombreMes = \Carbon\Carbon::create()->month($mes)->locale('es')->isoFormat('MMMM');
        
        return Excel::download(
            new IndicadoresMensualesExport($mes, $anio),
            "reporte-mensual-{$nombreMes}-{$anio}.xlsx"
        );
    }
    
    /**
     * Reporte de hallazgos del día en PDF
     */
    public function hallazgosDiaPdf(Request $request)
    {
        $fecha = $request->get('fecha', now()->toDateString());
        
        $hallazgos = RegistroHallazgo::where('fecha_operacion', $fecha)
            ->with(['tipoHallazgo', 'producto', 'ubicacion', 'lado', 'operario', 'usuario'])
            ->orderBy('created_at')
            ->get();
        
        $indicador = IndicadorDiario::where('fecha_operacion', $fecha)->first();
        
        $pdf = Pdf::loadView('reportes.hallazgos-dia-pdf', compact(
            'hallazgos',
            'indicador',
            'fecha'
        ));
        
        $pdf->setPaper('letter');
        
        return $pdf->download("hallazgos-" . str_replace('-', '', $fecha) . ".pdf");
    }
}
