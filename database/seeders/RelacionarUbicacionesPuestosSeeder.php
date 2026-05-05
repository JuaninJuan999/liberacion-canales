<?php

namespace Database\Seeders;

use App\Models\Ubicacion;
use App\Models\PuestoTrabajo;
use Illuminate\Database\Seeder;

class RelacionarUbicacionesPuestosSeeder extends Seeder
{
    public function run(): void
    {
        $relaciones = [
            'CLIPADO DE ESOFAGO' => 'Clipado de Esófago',
            'EVISERADO DE BLANCAS' => 'Eviscerado de Blancas',
            'CORTE DE ESTERNON' => 'Corte Esternón',
            // CORTE DE PATAS, MANIPULACION, CHOQUE DE CANAL → sin FK fijo:
            // el registro TC usa Desuello de Pierna 1 o 2 según par/impar y producto/tipo.
            'DESPEJE DE RECTO' => 'Despeje de Recto',
            'RAYADO DE PECHO' => 'Corte de Manos',
            'DESUELLO DE MANOS' => 'Desuello de Manos',
            'DESOLLADORA' => 'Desolladora',
            'TRANSFERENCIA' => 'Transferencia',
        ];

        foreach ($relaciones as $nombreUbicacion => $nombrePuesto) {
            $ubicacion = Ubicacion::where('nombre', $nombreUbicacion)->first();
            $puesto = PuestoTrabajo::where('nombre', $nombrePuesto)->first();

            if ($ubicacion && $puesto) {
                $ubicacion->update(['puesto_trabajo_id' => $puesto->id]);
            }
        }

        Ubicacion::query()
            ->whereIn('nombre', ['CORTE DE PATAS', 'MANIPULACION', 'CHOQUE DE CANAL'])
            ->update(['puesto_trabajo_id' => null]);
    }
}
