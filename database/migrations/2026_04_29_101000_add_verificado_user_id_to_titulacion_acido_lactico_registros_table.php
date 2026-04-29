<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('titulacion_acido_lactico_registros', function (Blueprint $table) {
            $table->foreignId('verificado_user_id')->nullable()->after('user_id')->constrained('users')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('titulacion_acido_lactico_registros', function (Blueprint $table) {
            $table->dropForeign(['verificado_user_id']);
        });
    }
};
