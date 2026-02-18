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
            ['nombre' => 'Primera par', 'descripcion' => 'Primera línea par'],
            ['nombre' => 'Primera impar', 'descripcion' => 'Primera línea impar'],
            ['nombre' => 'Segunda par', 'descripcion' => 'Segunda línea par'],
            ['nombre' => 'Segunda impar', 'descripcion' => 'Segunda línea impar'],
            ['nombre' => 'Zapata Izquierda', 'descripcion' => 'Zapata lado izquierdo'],
            ['nombre' => 'Zapata Derecha', 'descripcion' => 'Zapata lado derecho'],
            ['nombre' => 'Cadera 1', 'descripcion' => 'Cadera puesto 1'],
            ['nombre' => 'Cadera 2', 'descripcion' => 'Cadera puesto 2'],
            ['nombre' => 'Limpieza superior', 'descripcion' => 'Limpieza parte superior'],
        ];

        foreach ($puestos as $puesto) {
            PuestoTrabajo::create($puesto);
        }
    }
}
