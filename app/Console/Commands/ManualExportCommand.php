<?php

namespace App\Console\Commands;

use App\Http\Controllers\ManualUsuarioController;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;

class ManualExportCommand extends Command
{
    protected $signature = 'manual:export {--sin-pdf : No regenerar el PDF (solo mensaje)}';

    protected $description = 'Genera el PDF estático del manual de usuario en public/manual/';

    public function handle(): int
    {
        if ($this->option('sin-pdf')) {
            $this->info('Ejecute antes: npm run manual:capturas (con la app en marcha y .env.manual).');

            return self::SUCCESS;
        }

        $capturas = ManualUsuarioController::capturasBase64();
        $faltan = array_keys(array_filter($capturas, fn ($v) => $v === null));

        if ($faltan !== []) {
            $this->warn('Faltan capturas PNG para: '.implode(', ', $faltan));
            $this->warn('El PDF se generará con marcas "Sin captura". Use: npm run manual:capturas');
        }

        $generado = now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY, HH:mm');
        $pdf = Pdf::loadView('manual.usuario-pdf', compact('capturas', 'generado'))
            ->setPaper('a4', 'portrait');

        $destino = public_path('manual/Manual-Usuario-Liberacion-Canales.pdf');
        if (! is_dir(dirname($destino))) {
            mkdir(dirname($destino), 0755, true);
        }

        file_put_contents($destino, $pdf->output());

        $this->info('PDF guardado en: '.$destino);

        return self::SUCCESS;
    }
}
