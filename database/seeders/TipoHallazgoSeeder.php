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
            ['nombre' => 'CORTE EN PIERNAS'],
            ['nombre' => 'SOBREBARRIGA ROTA'],
        ];

        foreach ($hallazgos as $hallazgo) {
            TipoHallazgo::create($hallazgo);
        }
    }
}
