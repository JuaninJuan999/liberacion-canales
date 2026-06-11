<?php

namespace App\Http\Controllers;

use App\Exports\HistorialRegistrosExport;
use App\Models\MenuModulo;
use App\Support\HistorialRegistrosConsulta;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class HistorialRegistrosExportController extends Controller
{
    public function excel(Request $request): BinaryFileResponse
    {
        $this->autorizar();

        $filtros = $this->filtrosDesdeRequest($request);

        return Excel::download(
            new HistorialRegistrosExport($filtros),
            $this->nombreArchivo('xlsx', $filtros)
        );
    }

    public function pdf(Request $request): Response
    {
        $this->autorizar();

        $filtros = $this->filtrosDesdeRequest($request);

        $registros = HistorialRegistrosConsulta::queryConFiltros($filtros)
            ->orderByDesc('registros_hallazgos.created_at')
            ->get();

        $pdf = Pdf::loadView('reportes.historial-registros-pdf', [
            'registros' => $registros,
            'filtros' => $filtros,
            'generado' => now(),
            'logoDataUri' => HistorialRegistrosConsulta::logoInstitucionalDataUri(),
        ]);

        $pdf->setPaper('letter', 'landscape');

        return $pdf->download($this->nombreArchivo('pdf', $filtros));
    }

    protected function autorizar(): void
    {
        if (! auth()->check()) {
            abort(401);
        }

        $rolUsuario = auth()->user()->rolNormalizado();
        $modulo = MenuModulo::where('vista', 'hallazgos.historial')->first();

        if (! $modulo || ! $modulo->visibleParaRol($rolUsuario)) {
            abort(403);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function filtrosDesdeRequest(Request $request): array
    {
        $validated = $request->validate([
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date'],
            'producto_id' => ['nullable', 'integer', 'exists:productos,id'],
            'tipo_hallazgo_id' => ['nullable', 'integer', 'exists:tipos_hallazgo,id'],
            'numero_canal' => ['nullable', 'string', 'max:100'],
            'solo_criticos' => ['nullable'],
        ]);

        return [
            'fecha_inicio' => $validated['fecha_inicio'] ?? Carbon::now()->format('Y-m-d'),
            'fecha_fin' => $validated['fecha_fin'] ?? Carbon::now()->format('Y-m-d'),
            'producto_id' => $validated['producto_id'] ?? '',
            'tipo_hallazgo_id' => $validated['tipo_hallazgo_id'] ?? '',
            'numero_canal' => $validated['numero_canal'] ?? '',
            'solo_criticos' => filter_var($validated['solo_criticos'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    protected function nombreArchivo(string $extension, array $filtros): string
    {
        $inicio = str_replace('-', '', (string) ($filtros['fecha_inicio'] ?? ''));
        $fin = str_replace('-', '', (string) ($filtros['fecha_fin'] ?? ''));

        return "historial-registros_{$inicio}-{$fin}.{$extension}";
    }
}
