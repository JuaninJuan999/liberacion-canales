<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('animales_procesados', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_operacion');
            $table->integer('cantidad_animales')->default(0);
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            // Índice para búsquedas por fecha
            $table->index('fecha_operacion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('animales_procesados');
    }
};
