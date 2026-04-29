<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('verificacion_pcc_registros', function (Blueprint $table) {
            $table->dropColumn([
                'media_canal_1',
                'media_canal_2',
                'cumple',
                'observacion',
                'accion_correctiva',
            ]);
        });

        Schema::table('verificacion_pcc_registros', function (Blueprint $table) {
            $table->boolean('cumple_media_canal_1')->default(false);
            $table->boolean('cumple_media_canal_2')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('verificacion_pcc_registros', function (Blueprint $table) {
            $table->dropColumn(['cumple_media_canal_1', 'cumple_media_canal_2']);
        });

        Schema::table('verificacion_pcc_registros', function (Blueprint $table) {
            $table->decimal('media_canal_1', 12, 3)->nullable()->after('snapshot_externo');
            $table->decimal('media_canal_2', 12, 3)->nullable()->after('media_canal_1');
            $table->boolean('cumple')->default(false)->after('media_canal_2');
            $table->text('observacion')->nullable()->after('cumple');
            $table->text('accion_correctiva')->nullable()->after('observacion');
        });
    }
};
