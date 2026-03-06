<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->timestamps();
        });

        // Insertar roles por defecto
        DB::table('roles')->insert([
            ['nombre' => 'ADMINISTRADOR', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CALIDAD', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'OPERACIONES', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'GERENCIA', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
