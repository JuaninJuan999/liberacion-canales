<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('tipos_hallazgo')->insert([
            [
                'nombre' => 'MATERIA FECAL',
                'es_critico' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'CONTENIDO RUMINAL',
                'es_critico' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'LECHE VISIBLE',
                'es_critico' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('tipos_hallazgo')->whereIn('nombre', [
            'MATERIA FECAL',
            'CONTENIDO RUMINAL',
            'LECHE VISIBLE',
        ])->delete();
    }
};
