<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_hallazgo', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->timestamps();
        });

        // Insertar tipos de hallazgo por defecto
        DB::table('tipos_hallazgo')->insert([
            ['nombre' => 'COBERTURA DE GRASA', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'HEMATOMAS', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CORTE EN PIERNAS', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'SOBREBARRIGA ROTA', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_hallazgo');
    }
};
