<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consumo_acido_lactico_registros', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('hora', 8);
            $table->decimal('litros_preparados', 12, 3);
            $table->decimal('cantidad_acido_lactico_ml', 12, 3);
            $table->text('observacion')->nullable();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->timestamps();

            $table->index(['fecha', 'hora']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consumo_acido_lactico_registros');
    }
};
