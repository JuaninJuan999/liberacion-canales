<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hallazgos_tolerancia_cero', function (Blueprint $table) {
            $table->id();
            $table->dateTime('fecha_registro');
            $table->date('fecha_operacion');
            $table->string('codigo'); // Código del canal
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete(); // CUARTO ANTERIOR / CUARTO POSTERIOR
            $table->foreignId('tipo_hallazgo_id')->constrained('tipos_hallazgo')->cascadeOnDelete(); // MATERIA FECAL, CONTENIDO RUMINAL, LECHE VISIBLE
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->string('observacion')->nullable();
            $table->timestamps();

            // Índices para optimizar búsquedas
            $table->index('fecha_operacion');
            $table->index('fecha_registro');
            $table->index('codigo');
            $table->index(['fecha_operacion', 'tipo_hallazgo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hallazgos_tolerancia_cero');
    }
};
