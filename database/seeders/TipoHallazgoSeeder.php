<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TipoHallazgo;

class TipoHallazgoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hallazgos = [
            ['nombre' => 'COBERTURA DE GRASA'],
            ['nombre' => 'HEMATOMAS'],
            ['nombre' => 'CORTES EN LA PIERNA'],
            ['nombre' => 'SOBREBARRIGA ROTA'],
        ];

        foreach ($hallazgos as $hallazgo) {
            TipoHallazgo::firstOrCreate(
                ['nombre' => $hallazgo['nombre']],
                ['nombre' => $hallazgo['nombre']]
            );
        }

        TipoHallazgo::where('nombre', 'CORTE EN PIERNAS')->update(['nombre' => 'CORTES EN LA PIERNA']);
    }
}
