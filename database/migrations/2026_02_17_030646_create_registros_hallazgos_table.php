<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registros_hallazgos', function (Blueprint $table) {
            $table->id();
            $table->dateTime('fecha_registro');
            $table->date('fecha_operacion');
            $table->string('codigo');
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('tipo_hallazgo_id')->constrained('tipos_hallazgo')->cascadeOnDelete();
            $table->foreignId('ubicacion_id')->nullable()->constrained('ubicaciones')->nullOnDelete();
            $table->foreignId('lado_id')->nullable()->constrained('lados')->nullOnDelete();
            $table->integer('cantidad')->default(1);
            $table->string('evidencia_path')->nullable();
            $table->foreignId('operario_id')->nullable()->constrained('operarios')->nullOnDelete();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            // Índices para optimizar búsquedas
            $table->index('fecha_operacion');
            $table->index('fecha_registro');
            $table->index('codigo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registros_hallazgos');
    }
};
