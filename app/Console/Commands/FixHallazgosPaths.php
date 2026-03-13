<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RegistroHallazgo;

class FixHallazgosPaths extends Command
{
    protected $signature = 'fix:hallazgos-paths';
    protected $description = 'Actualizar rutas de evidencias de "evidencias/" a "hallazgos/"';

    public function handle()
    {
        $this->info('🔍 Buscando registros con rutas de evidencias antiguas...');
        
        // Obtener registros que tienen evidencias
        $registros = RegistroHallazgo::whereNotNull('evidencia_path')->get();
        
        if ($registros->isEmpty()) {
            $this->info('✅ No hay registros con evidencias para actualizar.');
            return 0;
        }

        $this->info("📊 Se encontraron {$registros->count()} registros con evidencias.\n");

        $actualizados = 0;
        $sinCambios = 0;

        foreach ($registros as $registro) {
            $rutaAnterior = $registro->evidencia_path;
            $rutaNueva = $rutaAnterior;

            // Si contiene la ruta antigua, reemplazarla
            if (str_contains($rutaNueva, 'evidencias/')) {
                $rutaNueva = str_replace('evidencias/', 'hallazgos/', $rutaNueva);
                $registro->update(['evidencia_path' => $rutaNueva]);
                $actualizados++;
                $this->line("✅ Actualizado ID {$registro->id}: {$rutaAnterior} → {$rutaNueva}");
            } elseif (!str_contains($rutaNueva, 'hallazgos/')) {
                // Si no tiene ningún prefijo, agregarle 'hallazgos/'
                $rutaNueva = 'hallazgos/' . $rutaNueva;
                $registro->update(['evidencia_path' => $rutaNueva]);
                $actualizados++;
                $this->line("✅ Actualizado ID {$registro->id}: {$rutaAnterior} → {$rutaNueva}");
            } else {
                $sinCambios++;
            }
        }

        $this->info("\n📈 Resumen:");
        $this->info("  ✅ Registros actualizados: {$actualizados}");
        $this->info("  ℹ️  Registros sin cambios: {$sinCambios}");
        $this->info("  📊 Total procesado: {$registros->count()}");

        return 0;
    }
}
