<?php

use App\Models\MenuModulo;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        MenuModulo::query()->updateOrCreate(
            ['vista' => 'consumo-acido-lactico'],
            [
                'nombre' => 'Consumo de Ácido Láctico',
                'icono' => 'chart-bar',
                'orden' => 15,
                'roles' => ['OPERACIONES', 'CALIDAD', 'ADMINISTRADOR', 'GERENCIA'],
            ]
        );
    }

    public function down(): void
    {
        MenuModulo::query()->where('vista', 'consumo-acido-lactico')->delete();
    }
};
