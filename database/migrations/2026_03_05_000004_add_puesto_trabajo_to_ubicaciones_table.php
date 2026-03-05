<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ubicaciones', function (Blueprint $table) {
            $table->foreignId('puesto_trabajo_id')->nullable()->constrained('puestos_trabajo')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ubicaciones', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['puesto_trabajo_id']);
            $table->dropColumn('puesto_trabajo_id');
        });
    }
};
