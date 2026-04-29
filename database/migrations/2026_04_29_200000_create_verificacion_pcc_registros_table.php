<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verificacion_pcc_registros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('external_ins_id')->nullable()->index();
            $table->unsignedBigInteger('id_producto')->index();
            $table->json('snapshot_externo')->nullable();
            $table->decimal('media_canal_1', 12, 3)->nullable();
            $table->decimal('media_canal_2', 12, 3)->nullable();
            $table->boolean('cumple')->default(false);
            $table->text('observacion')->nullable();
            $table->text('accion_correctiva')->nullable();
            $table->string('responsable_puesto_trabajo', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verificacion_pcc_registros');
    }
};
