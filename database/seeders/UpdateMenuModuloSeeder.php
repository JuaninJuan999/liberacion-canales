<?php

namespace Database\Seeders;

use App\Models\MenuModulo;
use Illuminate\Database\Seeder;

class UpdateMenuModuloSeeder extends Seeder
{
    public function run(): void
    {
        // Actualizar el módulo de Gestión de Usuarios
        $modulo = MenuModulo::where('nombre', 'Gestión de Usuarios')
            ->orWhere('nombre', 'Gestion de Usuarios')
            ->first();

        if ($modulo) {
            $modulo->vista = 'usuarios.gestion';
            $modulo->save();
            echo "\n✅ Módulo 'Gestión de Usuarios' actualizado a ruta 'usuarios.gestion'\n\n";
        } else {
            echo "\n⚠️ Módulo 'Gestión de Usuarios' no encontrado\n\n";
        }
    }
}
