<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('titulacion_acido_lactico_registros', function (Blueprint $table) {
            $table->decimal('litros_preparados', 10, 3)->default(0)->after('concentracion_sol_pct');
        });
    }

    public function down(): void
    {
        Schema::table('titulacion_acido_lactico_registros', function (Blueprint $table) {
            $table->dropColumn('litros_preparados');
        });
    }
};
