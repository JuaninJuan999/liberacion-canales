<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement(
                'ALTER TABLE verificacion_pcc_registros ALTER COLUMN id_producto TYPE VARCHAR(96) USING TRIM(id_producto::text)'
            );
        } elseif ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement(
                'ALTER TABLE verificacion_pcc_registros MODIFY id_producto VARCHAR(96) NOT NULL'
            );
        }
    }

    public function down(): void
    {
        // No revertir a BIGINT: se perderían códigos con guión u otros caracteres.
    }
};
