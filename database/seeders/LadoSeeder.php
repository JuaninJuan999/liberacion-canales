<?php

namespace Database\Seeders;

use App\Models\Lado;
use Illuminate\Database\Seeder;

class LadoSeeder extends Seeder
{
    /**
     * Lados según especificación: Par, Impar.
     */
    public function run(): void
    {
        $lados = [
            ['nombre' => 'Par'],
            ['nombre' => 'Impar'],
        ];

        foreach ($lados as $l) {
            Lado::firstOrCreate(['nombre' => $l['nombre']]);
        }
    }
}
