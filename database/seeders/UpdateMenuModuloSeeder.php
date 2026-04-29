<?php

namespace Database\Seeders;

use App\Models\MenuModulo;
use Illuminate\Database\Seeder;

class UpdateMenuModuloSeeder extends Seeder
{
    public function run(): void
    {
        $modulo = MenuModulo::where('vista', 'usuarios.index')->first();

        if ($modulo) {
            $modulo->vista = 'usuarios.gestion';
            $modulo->save();
            echo "\n✅ Módulo usuarios (sidebar) actualizado a ruta 'usuarios.gestion'\n\n";
        } else {
            echo "\n⚠️ Módulo con vista 'usuarios.index' no encontrado\n\n";
        }
    }
}
