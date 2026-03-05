<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hallazgos_tolerancia_cero', function (Blueprint $table) {
            $table->foreignId('ubicacion_id')->nullable()->constrained('ubicaciones')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('hallazgos_tolerancia_cero', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['ubicacion_id']);
            $table->dropColumn('ubicacion_id');
        });
    }
};
