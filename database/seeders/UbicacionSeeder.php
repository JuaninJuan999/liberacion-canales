<?php

namespace Database\Seeders;

use App\Models\Ubicacion;
use Illuminate\Database\Seeder;

class UbicacionSeeder extends Seeder
{
    /**
     * Ubicaciones según especificación: Cadera, Pierna.
     */
    public function run(): void
    {
        $ubicaciones = [
            ['nombre' => 'Cadera'],
            ['nombre' => 'Pierna'],
        ];

        foreach ($ubicaciones as $u) {
            Ubicacion::firstOrCreate(['nombre' => $u['nombre']]);
        }
    }
}
