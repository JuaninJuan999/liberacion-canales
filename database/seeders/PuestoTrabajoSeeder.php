<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PuestoTrabajo;

class PuestoTrabajoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $puestos = [
            ['nombre' => 'Primera par', 'descripcion' => 'Primera línea par', 'orden' => 1],
            ['nombre' => 'Primera impar', 'descripcion' => 'Primera línea impar', 'orden' => 2],
            ['nombre' => 'Segunda par', 'descripcion' => 'Segunda línea par', 'orden' => 3],
            ['nombre' => 'Segunda impar', 'descripcion' => 'Segunda línea impar', 'orden' => 4],
            ['nombre' => 'Zapata Izquierda', 'descripcion' => 'Zapata lado izquierdo', 'orden' => 5],
            ['nombre' => 'Zapata Derecha', 'descripcion' => 'Zapata lado derecho', 'orden' => 6],
            ['nombre' => 'Cadera 1', 'descripcion' => 'Cadera puesto 1', 'orden' => 7],
            ['nombre' => 'Cadera 2', 'descripcion' => 'Cadera puesto 2', 'orden' => 8],
            ['nombre' => 'Limpieza superior', 'descripcion' => 'Limpieza parte superior', 'orden' => 9],
        ];

        foreach ($puestos as $puesto) {
            PuestoTrabajo::updateOrCreate(
                ['nombre' => $puesto['nombre']],
                [
                    'descripcion' => $puesto['descripcion'],
                    'orden' => $puesto['orden']
                ]
            );
        }
    }
}
