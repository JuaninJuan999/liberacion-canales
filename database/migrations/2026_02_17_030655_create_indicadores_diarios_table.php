<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indicadores_diarios', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_operacion')->unique();
            $table->integer('animales_procesados')->default(0);
            $table->integer('medias_canales_total')->default(0);
            $table->integer('medias_canal_1')->default(0);
            $table->integer('medias_canal_2')->default(0);
            $table->integer('total_hallazgos')->default(0);
            $table->integer('cobertura_grasa')->default(0);
            $table->integer('hematomas')->default(0);
            $table->integer('cortes_piernas')->default(0);
            $table->integer('sobrebarriga_rota')->default(0);
            $table->decimal('participacion_total', 5, 2)->default(0);
            $table->string('mes');
            $table->integer('año');
            $table->timestamps();

            // Índices para búsquedas
            $table->index('fecha_operacion');
            $table->index(['año', 'mes']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indicadores_diarios');
    }
};
