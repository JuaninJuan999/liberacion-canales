<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('indicadores_diarios', function (Blueprint $table) {
            // Agregar columnas si no existen
            if (!Schema::hasColumn('indicadores_diarios', 'cobertura_grasa')) {
                $table->integer('cobertura_grasa')->default(0)->after('total_hallazgos');
            }
            if (!Schema::hasColumn('indicadores_diarios', 'hematomas')) {
                $table->integer('hematomas')->default(0)->after('cobertura_grasa');
            }
            if (!Schema::hasColumn('indicadores_diarios', 'cortes_piernas')) {
                $table->integer('cortes_piernas')->default(0)->after('hematomas');
            }
            if (!Schema::hasColumn('indicadores_diarios', 'sobrebarriga_rota')) {
                $table->integer('sobrebarriga_rota')->default(0)->after('cortes_piernas');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('indicadores_diarios', function (Blueprint $table) {
            $table->dropColumn([
                'cobertura_grasa',
                'hematomas',
                'cortes_piernas',
                'sobrebarriga_rota'
            ]);
        });
    }
};
