<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $mapeo = [
            'CLIPADO DE ESOFAGO'   => 'Clipado de Esófago',
            'EVISERADO DE BLANCAS' => 'Eviscerado de Blancas',
            'CORTE DE ESTERNON'    => 'Corte Esternón',
            'CORTE DE PATAS'       => 'Desuello de Pierna',
            'MANIPULACION'         => 'Desuello de Pierna',
            'CHOQUE DE CANAL'      => 'Desuello de Pierna',
            'DESPEJE DE RECTO'     => 'Despeje de Recto',
            'RAYADO DE PECHO'      => 'Corte de Manos',
            'DESUELLO DE MANOS'    => 'Desuello de Manos',
            'DESOLLADORA'          => 'Desolladora',
            'TRANSFERENCIA'        => 'Transferencia',
        ];

        // Mapeo directo por ID para los que tienen acentos y no coinciden con UPPER
        $mapeoPorId = [
            3  => 12, // CLIPADO DE ESOFAGO → Clipado de Esófago
            5  => 13, // CORTE DE ESTERNON → Corte Esternón
        ];

        foreach ($mapeo as $ubicacionNombre => $puestoNombre) {
            $puesto = DB::table('puestos_trabajo')
                ->whereRaw('UPPER(nombre) = ?', [strtoupper($puestoNombre)])
                ->first();

            if ($puesto) {
                DB::table('ubicaciones')
                    ->whereRaw('UPPER(nombre) = ?', [strtoupper($ubicacionNombre)])
                    ->update(['puesto_trabajo_id' => $puesto->id]);
            }
        }

        // Actualizar los que no coincidieron por acentos
        foreach ($mapeoPorId as $ubicacionId => $puestoId) {
            DB::table('ubicaciones')
                ->where('id', $ubicacionId)
                ->whereNull('puesto_trabajo_id')
                ->update(['puesto_trabajo_id' => $puestoId]);
        }
    }

    public function down(): void
    {
        DB::table('ubicaciones')
            ->whereIn('nombre', [
                'CLIPADO DE ESOFAGO', 'EVISERADO DE BLANCAS', 'CORTE DE ESTERNON',
                'CORTE DE PATAS', 'MANIPULACION', 'CHOQUE DE CANAL',
                'DESPEJE DE RECTO', 'RAYADO DE PECHO', 'DESUELLO DE MANOS',
                'DESOLLADORA', 'TRANSFERENCIA',
            ])
            ->update(['puesto_trabajo_id' => null]);
    }
};
