<?php

use App\Models\MenuModulo;
use Illuminate\Database\Migrations\Migration;

/**
 * Bases antiguas pueden no tener estas filas; sin ellas no aparecen en menú/bienvenida ni en Gestión de roles.
 */
return new class extends Migration
{
    public function up(): void
    {
        MenuModulo::query()->updateOrCreate(
            ['vista' => 'operarios-dia.index'],
            [
                'nombre' => 'Gestión de Operarios',
                'icono' => 'users',
                'orden' => 6,
                'roles' => ['OPERACIONES', 'ADMINISTRADOR'],
            ]
        );

        MenuModulo::query()->updateOrCreate(
            ['vista' => 'operarios.index'],
            [
                'nombre' => 'Catálogo de Operarios',
                'icono' => 'users-circle',
                'orden' => 12,
                'roles' => ['OPERACIONES', 'ADMINISTRADOR'],
            ]
        );
    }

    public function down(): void
    {
        MenuModulo::query()->whereIn('vista', ['operarios-dia.index', 'operarios.index'])->delete();
    }
};
