<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operarios_por_dia', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_operacion');
            $table->foreignId('puesto_trabajo_id')->constrained('puestos_trabajo')->cascadeOnDelete();
            $table->foreignId('operario_id')->constrained('operarios')->cascadeOnDelete();
            $table->timestamps();

            // Índice para búsquedas por fecha
            $table->index('fecha_operacion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operarios_por_dia');
    }
};
