<?php

namespace App\Console\Commands;

use App\Models\SesionUsuario;
use Illuminate\Console\Command;

class CerrarSesionesInactivasCommand extends Command
{
    protected $signature = 'usabilidad:cerrar-sesiones-inactivas';

    protected $description = 'Finaliza sesiones de usabilidad sin actividad reciente';

    public function handle(): int
    {
        $cerradas = SesionUsuario::cerrarSesionesInactivas();

        if ($cerradas > 0) {
            $this->info("Sesiones cerradas por inactividad: {$cerradas}");
        }

        return self::SUCCESS;
    }
}
