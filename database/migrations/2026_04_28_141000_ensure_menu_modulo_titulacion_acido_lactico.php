<?php

use App\Models\MenuModulo;
use Illuminate\Database\Migrations\Migration;

/**
 * Asegura fila en menu_modulos para entornos ya sembrados antes de incluir este módulo.
 */
return new class extends Migration
{
    public function up(): void
    {
        MenuModulo::query()->updateOrCreate(
            ['vista' => 'titulacion-acido-lactico'],
            [
                'nombre' => 'Titulación de Ácido Láctico',
                'icono' => 'beaker',
                'orden' => 14,
                'roles' => ['OPERACIONES', 'CALIDAD', 'ADMINISTRADOR', 'GERENCIA'],
            ]
        );
    }

    public function down(): void
    {
        MenuModulo::query()->where('vista', 'titulacion-acido-lactico')->delete();
    }
};
