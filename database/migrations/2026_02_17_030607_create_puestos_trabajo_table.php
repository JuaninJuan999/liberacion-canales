<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('puestos_trabajo', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->timestamps();
        });

        // Insertar puestos por defecto
        DB::table('puestos_trabajo')->insert([
            ['nombre' => 'Primera Par', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Primera Impar', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Segunda Par', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Segunda Impar', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('puestos_trabajo');
    }
};
