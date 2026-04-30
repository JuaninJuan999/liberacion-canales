<?php

namespace Database\Seeders;

use App\Models\PuestoTrabajo;
use Illuminate\Database\Seeder;

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
            // Tolerancia cero / enlace con ubicaciones (ver RelacionarUbicacionesPuestosSeeder y migration link_ubicaciones)
            ['nombre' => 'Clipado de Esófago', 'descripcion' => 'Puesto tolerancia cero / ubicaciones', 'orden' => 10],
            ['nombre' => 'Eviscerado de Blancas', 'descripcion' => 'Puesto tolerancia cero / ubicaciones', 'orden' => 11],
            ['nombre' => 'Corte Esternón', 'descripcion' => 'Puesto tolerancia cero / ubicaciones', 'orden' => 12],
            ['nombre' => 'Desuello de Pierna', 'descripcion' => 'Puesto tolerancia cero / ubicaciones', 'orden' => 13],
            ['nombre' => 'Despeje de Recto', 'descripcion' => 'Puesto tolerancia cero / ubicaciones', 'orden' => 14],
            ['nombre' => 'Corte de Manos', 'descripcion' => 'Puesto tolerancia cero / ubicaciones', 'orden' => 15],
            ['nombre' => 'Desuello de Manos', 'descripcion' => 'Puesto tolerancia cero / ubicaciones', 'orden' => 16],
            ['nombre' => 'Desolladora', 'descripcion' => 'Puesto tolerancia cero / ubicaciones', 'orden' => 17],
            ['nombre' => 'Transferencia', 'descripcion' => 'Puesto tolerancia cero / ubicaciones', 'orden' => 18],
        ];

        foreach ($puestos as $puesto) {
            PuestoTrabajo::updateOrCreate(
                ['nombre' => $puesto['nombre']],
                [
                    'descripcion' => $puesto['descripcion'],
                    'orden' => $puesto['orden'],
                ]
            );
        }
    }
}
