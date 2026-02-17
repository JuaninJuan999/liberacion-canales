<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_modulos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('vista');
            $table->string('icono')->nullable();
            $table->integer('orden')->default(0);
            $table->json('roles');
            $table->timestamps();

            $table->index('orden');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_modulos');
    }
};
