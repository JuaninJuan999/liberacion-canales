<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('productos')->insert([
            [
                'nombre' => 'CUARTO ANTERIOR',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'CUARTO POSTERIOR',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('productos')->whereIn('nombre', [
            'CUARTO ANTERIOR',
            'CUARTO POSTERIOR',
        ])->delete();
    }
};
