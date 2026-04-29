<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('titulacion_acido_lactico_registros', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('hora', 8)->comment('HH:MM o HH:MM:SS');
            $table->decimal('volumen_naoh_ml', 5, 2);
            $table->decimal('concentracion_sol_pct', 5, 2);
            $table->boolean('cumple')->default(true);
            $table->text('correccion')->nullable();
            $table->string('actividad', 32);
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('verificado_nombre', 255);
            $table->timestamps();

            $table->index(['fecha', 'hora']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titulacion_acido_lactico_registros');
    }
};
