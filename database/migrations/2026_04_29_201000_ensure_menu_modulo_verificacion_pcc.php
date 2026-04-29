<?php

use App\Models\MenuModulo;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $orden = (int) (MenuModulo::query()->max('orden') ?? 0) + 1;

        MenuModulo::query()->updateOrCreate(
            ['vista' => 'verificacion-pcc'],
            [
                'nombre' => 'VERIFICACION PCC',
                'icono' => 'clipboard-document-check',
                'orden' => $orden,
                'roles' => ['OPERACIONES', 'CALIDAD', 'ADMINISTRADOR', 'GERENCIA'],
            ]
        );
    }

    public function down(): void
    {
        MenuModulo::query()->where('vista', 'verificacion-pcc')->delete();
    }
};
