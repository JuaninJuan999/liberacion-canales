<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ManualUsuarioController extends Controller
{
    /**
     * @return array<string, string|null> data:image/png;base64,... o null si falta archivo
     */
    public static function capturasBase64(): array
    {
        $archivos = [
            'login' => '01-login.png',
            'bienvenida' => '02-bienvenida.png',
            'dashboard' => '03-dashboard.png',
            'registro_hallazgos' => '04-registro-hallazgos.png',
            'historial' => '05-historial.png',
            'operarios' => '06-operarios.png',
            'asignacion_dia' => '07-asignacion-dia.png',
            'tolerancia_cero' => '08-tolerancia-cero.png',
            'indicadores' => '09-indicadores-dia.png',
            'usuarios' => '10-gestion-usuarios.png',
        ];

        $out = [];
        foreach ($archivos as $clave => $archivo) {
            $ruta = public_path('manual/capturas/'.$archivo);
            if (! is_file($ruta)) {
                $out[$clave] = null;

                continue;
            }
            $out[$clave] = 'data:image/png;base64,'.base64_encode((string) file_get_contents($ruta));
        }

        return $out;
    }

    public function pdf(): Response
    {
        $capturas = self::capturasBase64();
        $generado = now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY, HH:mm');

        $pdf = Pdf::loadView('manual.usuario-pdf', compact('capturas', 'generado'))
            ->setPaper('a4', 'portrait');

        $nombre = 'Manual-Usuario-Liberacion-Canales.pdf';

        return $pdf->download($nombre);
    }

    public function preview(): \Illuminate\Contracts\View\View
    {
        $capturas = self::capturasBase64();
        $generado = now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY, HH:mm');

        return view('manual.usuario-pdf', compact('capturas', 'generado'));
    }
}
