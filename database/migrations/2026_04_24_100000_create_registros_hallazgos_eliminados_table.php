<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registros_hallazgos_eliminados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('registro_hallazgo_id')->comment('ID del registro antes de eliminarlo');
            $table->json('payload');
            $table->foreignId('eliminado_por_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('eliminado_por_nombre');
            $table->timestamps();

            $table->index('created_at');
            $table->index('registro_hallazgo_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registros_hallazgos_eliminados');
    }
};
