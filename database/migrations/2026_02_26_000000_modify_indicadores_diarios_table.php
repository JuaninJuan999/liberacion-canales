<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('indicadores_diarios', function (Blueprint $table) {
            // 1. Añadir la nueva columna JSON
            $table->json('desglose_hallazgos')->nullable()->after('participacion_total');

            // 2. Eliminar las columnas harcodeadas
            $table->dropColumn([
                'cobertura_grasa',
                'hematomas',
                'cortes_piernas',
                'sobrebarriga_rota',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('indicadores_diarios', function (Blueprint $table) {
            // Revertir los cambios
            $table->dropColumn('desglose_hallazgos');
            $table->integer('cobertura_grasa')->default(0);
            $table->integer('hematomas')->default(0);
            $table->integer('cortes_piernas')->default(0);
            $table->integer('sobrebarriga_rota')->default(0);
        });
    }
};
