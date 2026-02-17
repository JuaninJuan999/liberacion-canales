<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_app', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('logo')->nullable();
            $table->text('mensaje_bienvenida')->nullable();
            $table->timestamps();
        });

        // Insertar configuración por defecto
        DB::table('configuracion_app')->insert([
            'nombre' => 'Liberación de Canales',
            'mensaje_bienvenida' => 'Sistema de control de calidad para el proceso de beneficio de reses',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_app');
    }
};
