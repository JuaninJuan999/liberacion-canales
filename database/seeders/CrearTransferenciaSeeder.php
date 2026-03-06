<?php

namespace Database\Seeders;

use App\Models\Ubicacion;
use App\Models\PuestoTrabajo;
use Illuminate\Database\Seeder;

class CrearTransferenciaSeeder extends Seeder
{
    public function run(): void
    {
        // Crear la ubicación TRANSFERENCIA
        $ubicacion = Ubicacion::firstOrCreate(
            ['nombre' => 'TRANSFERENCIA'],
            ['nombre' => 'TRANSFERENCIA']
        );

        // Asignar al puesto Transferencia
        $puestoTransferencia = PuestoTrabajo::where('nombre', 'Transferencia')->first();
        if ($puestoTransferencia) {
            $ubicacion->update(['puesto_trabajo_id' => $puestoTransferencia->id]);
        }
    }
}
