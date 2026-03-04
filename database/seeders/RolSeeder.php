<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    /**
     * Roles según especificación: Admin, Calidad, Operaciones, Gerencia.
     */
    public function run(): void
    {
        $roles = [
            ['nombre' => 'ADMINISTRADOR'],
            ['nombre' => 'CALIDAD'],
            ['nombre' => 'OPERACIONES'],
            ['nombre' => 'GERENCIA'],
        ];

        foreach ($roles as $rol) {
            Rol::firstOrCreate(['nombre' => $rol['nombre']]);
        }
    }
}
