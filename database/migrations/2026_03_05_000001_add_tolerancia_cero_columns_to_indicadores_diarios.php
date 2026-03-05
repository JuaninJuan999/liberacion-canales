<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('indicadores_diarios', function (Blueprint $table) {
            // Nuevos campos para Hallazgos Tolerancia Cero
            $table->integer('total_hallazgos_tolerancia_cero')->default(0)->after('sobrebarriga_rota');
            $table->integer('materia_fecal_tc')->default(0)->after('total_hallazgos_tolerancia_cero');
            $table->integer('contenido_ruminal_tc')->default(0)->after('materia_fecal_tc');
            $table->integer('leche_visible_tc')->default(0)->after('contenido_ruminal_tc');
        });
    }

    public function down(): void
    {
        Schema::table('indicadores_diarios', function (Blueprint $table) {
            $table->dropColumn([
                'total_hallazgos_tolerancia_cero',
                'materia_fecal_tc',
                'contenido_ruminal_tc',
                'leche_visible_tc'
            ]);
        });
    }
};
