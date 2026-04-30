<?php

namespace App\Http\Controllers;

use App\Exports\VerificacionPccRegistrosExport;
use App\Models\MenuModulo;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VerificacionPccHistorialExcelController extends Controller
{
    public function __invoke(Request $request): BinaryFileResponse
    {
        if (! auth()->check()) {
            abort(401);
        }

        $rolUsuario = auth()->user()->rolNormalizado();
        $modulo = MenuModulo::where('vista', 'verificacion-pcc')->first();
        if (! $modulo || ! $modulo->visibleParaRol($rolUsuario)) {
            abort(403);
        }

        $fecha = null;
        if ($request->filled('fecha')) {
            $fecha = $request->validate([
                'fecha' => ['required', 'date'],
            ])['fecha'];
        }

        $suffix = $fecha !== null ? str_replace('-', '', (string) $fecha) : 'todos';
        $filename = 'verificacion-pcc_historial_'.$suffix.'.xlsx';

        return Excel::download(new VerificacionPccRegistrosExport($fecha), $filename);
    }
}
