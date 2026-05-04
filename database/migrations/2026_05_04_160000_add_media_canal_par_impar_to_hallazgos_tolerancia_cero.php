<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hallazgos_tolerancia_cero', function (Blueprint $table) {
            $table->string('media_canal', 1)->nullable()->after('ubicacion_id');
            $table->string('par_impar', 10)->nullable()->after('media_canal');
        });
    }

    public function down(): void
    {
        Schema::table('hallazgos_tolerancia_cero', function (Blueprint $table) {
            $table->dropColumn(['media_canal', 'par_impar']);
        });
    }
};
