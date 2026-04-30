<?php

namespace App\Http\Controllers;

use App\Exports\TitulacionAcidoLacticoRegistrosExport;
use App\Models\MenuModulo;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TitulacionAcidoLacticoHistorialExcelController extends Controller
{
    public function __invoke(Request $request): BinaryFileResponse
    {
        if (! auth()->check()) {
            abort(401);
        }

        $rolUsuario = auth()->user()->rolNormalizado();
        $modulo = MenuModulo::where('vista', 'titulacion-acido-lactico')->first();
        if (! $modulo || ! $modulo->visibleParaRol($rolUsuario)) {
            abort(403);
        }

        $desde = null;
        $hasta = null;
        $actividad = null;

        if ($request->filled('desde') || $request->filled('hasta') || $request->filled('actividad')) {
            $validated = $request->validate([
                'desde' => ['nullable', 'date'],
                'hasta' => ['nullable', 'date'],
                'actividad' => ['nullable', 'string', 'max:32'],
            ]);

            $desde = $validated['desde'] ?? null;
            $hasta = $validated['hasta'] ?? null;
            $actividad = $validated['actividad'] ?? null;
        }

        $suffixParts = [];
        if ($desde) {
            $suffixParts[] = 'desde_'.str_replace('-', '', (string) $desde);
        }
        if ($hasta) {
            $suffixParts[] = 'hasta_'.str_replace('-', '', (string) $hasta);
        }
        if ($actividad) {
            $suffixParts[] = 'act_'.preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $actividad);
        }
        $suffix = $suffixParts !== [] ? implode('_', $suffixParts) : 'todos';

        $filename = 'titulacion-acido-lactico_historial_'.$suffix.'.xlsx';

        return Excel::download(new TitulacionAcidoLacticoRegistrosExport($desde, $hasta, $actividad), $filename);
    }
}

