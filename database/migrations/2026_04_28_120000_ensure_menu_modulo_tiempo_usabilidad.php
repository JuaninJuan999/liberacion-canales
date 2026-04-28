<?php

use App\Models\MenuModulo;
use Illuminate\Database\Migrations\Migration;

/**
 * Entornos que crearon menu_modulos antes de incluir Tiempo de Usabilidad no tienen la fila:
 * autorizarVistaMenu('tiempo-usabilidad') devolvía 403 y el módulo no aparecía en Gestión de roles.
 */
return new class extends Migration
{
    public function up(): void
    {
        MenuModulo::query()->updateOrCreate(
            ['vista' => 'tiempo-usabilidad'],
            [
                'nombre' => 'Tiempo de Usabilidad',
                'icono' => 'stopwatch',
                'orden' => 13,
                'roles' => ['ADMINISTRADOR'],
            ]
        );
    }

    public function down(): void
    {
        MenuModulo::query()->where('vista', 'tiempo-usabilidad')->delete();
    }
};
