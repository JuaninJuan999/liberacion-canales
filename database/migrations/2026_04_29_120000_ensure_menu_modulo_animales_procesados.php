<?php

use App\Models\MenuModulo;
use Illuminate\Database\Migrations\Migration;

/**
 * Entornos antiguos pueden no tener esta fila en menu_modulos; sin ella no aparece en menú/bienvenida ni en Gestión de roles.
 */
return new class extends Migration
{
    public function up(): void
    {
        MenuModulo::query()->updateOrCreate(
            ['vista' => 'animales.index'],
            [
                'nombre' => 'Animales Procesados',
                'icono' => 'document-stack',
                'orden' => 11,
                'roles' => ['OPERACIONES', 'CALIDAD', 'ADMINISTRADOR', 'GERENCIA'],
            ]
        );
    }

    public function down(): void
    {
        MenuModulo::query()->where('vista', 'animales.index')->delete();
    }
};
