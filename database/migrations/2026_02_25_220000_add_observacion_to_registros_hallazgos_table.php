<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('registros_hallazgos', function (Blueprint $table) {
            $table->string('observacion')->nullable()->after('evidencia_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registros_hallazgos', function (Blueprint $table) {
            $table->dropColumn('observacion');
        });
    }
};
