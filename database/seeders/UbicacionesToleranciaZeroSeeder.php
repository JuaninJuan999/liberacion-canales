<?php

namespace Database\Seeders;

use App\Models\Ubicacion;
use Illuminate\Database\Seeder;

class UbicacionesToleranciaZeroSeeder extends Seeder
{
    public function run(): void
    {
        $ubicaciones = [
            // Cuarto anterior + Contenido ruminal
            'CLIPADO DE ESOFAGO',
            'EVISERADO DE BLANCAS',
            'CORTE DE ESTERNON',
            // Cuarto posterior + Materia fecal
            'CORTE DE PATAS',
            'MANIPULACION',
            'CHOQUE DE CANAL',
            'DESPEJE DE RECTO',
            // Cuarto anterior + Materia fecal
            'RAYADO DE PECHO',
            'DESUELLO DE MANOS',
            'DESOLLADORA',
        ];

        foreach ($ubicaciones as $nombre) {
            Ubicacion::firstOrCreate(['nombre' => $nombre]);
        }
    }
}
