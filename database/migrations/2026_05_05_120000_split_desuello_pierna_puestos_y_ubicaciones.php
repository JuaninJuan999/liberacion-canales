<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Separa el puesto "Desuello de Pierna" en línea par / impar para asignación de operarios TC.
     * Las ubicaciones CORTE DE PATAS / MANIPULACION / CHOQUE DE CANAL dejan de tener un único FK
     * porque el puesto se resuelve con par_impar del registro (véase HallazgoToleranciaZero::puestoTrabajoIdParaOperario).
     */
    public function up(): void
    {
        $viejo = DB::table('puestos_trabajo')->where('nombre', 'Desuello de Pierna')->first();

        if ($viejo !== null) {
            DB::table('puestos_trabajo')->where('orden', '>', 13)->increment('orden');
            DB::table('puestos_trabajo')->where('id', $viejo->id)->update([
                'nombre' => 'Desuello de Pierna 1',
                'descripcion' => 'Puesto tolerancia cero — línea par',
                'updated_at' => now(),
            ]);
        }

        if (! DB::table('puestos_trabajo')->where('nombre', 'Desuello de Pierna 2')->exists()) {
            DB::table('puestos_trabajo')->insert([
                'nombre' => 'Desuello de Pierna 2',
                'descripcion' => 'Puesto tolerancia cero — línea impar',
                'orden' => 14,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('ubicaciones')
            ->whereIn('nombre', ['CORTE DE PATAS', 'MANIPULACION', 'CHOQUE DE CANAL'])
            ->update(['puesto_trabajo_id' => null, 'updated_at' => now()]);
    }

    public function down(): void
    {
        $idPrimero = DB::table('puestos_trabajo')->where('nombre', 'Desuello de Pierna 1')->value('id');
        DB::table('puestos_trabajo')->where('nombre', 'Desuello de Pierna 2')->delete();

        if ($idPrimero) {
            DB::table('puestos_trabajo')->where('id', $idPrimero)->update([
                'nombre' => 'Desuello de Pierna',
                'descripcion' => 'Puesto tolerancia cero / ubicaciones',
                'updated_at' => now(),
            ]);

            foreach (['CORTE DE PATAS', 'MANIPULACION', 'CHOQUE DE CANAL'] as $nombre) {
                DB::table('ubicaciones')
                    ->where('nombre', $nombre)
                    ->update(['puesto_trabajo_id' => $idPrimero, 'updated_at' => now()]);
            }
        }

        DB::table('puestos_trabajo')->where('orden', '>', 14)->decrement('orden');
    }
};
