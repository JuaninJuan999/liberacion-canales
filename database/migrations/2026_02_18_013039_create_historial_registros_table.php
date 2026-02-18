<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_registros', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('codigo')->nullable();
            $table->text('hallazgos')->nullable();
            $table->foreignId('producto_id')->nullable()->constrained('productos')->nullOnDelete();
            $table->integer('cant')->nullable();
            $table->text('observacion')->nullable();
            $table->text('observacion1')->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('f_operacion')->nullable();
            $table->string('evidencia')->nullable();
            $table->foreignId('operario_id')->nullable()->constrained('operarios')->nullOnDelete();
            $table->timestamps();
            
            $table->index(['fecha', 'usuario_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_registros');
    }
};
