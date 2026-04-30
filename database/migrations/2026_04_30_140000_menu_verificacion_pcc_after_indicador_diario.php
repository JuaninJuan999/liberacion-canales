<?php

use App\Models\MenuModulo;
use Illuminate\Database\Migrations\Migration;

/**
 * Verificación PCC justo debajo de Indicador diario en la barra lateral.
 */
return new class extends Migration
{
    /** @var array<string, int> vista => orden */
    protected array $ordenSidebar = [
        'dashboard.mensual' => 1,
        'dashboard' => 2,
        'indicadores.detalle-dia' => 3,
        'verificacion-pcc' => 4,
        'hallazgos.registrar' => 5,
        'tolerancia-cero.registrar' => 6,
        'animales.index' => 7,
        'hallazgos.historial' => 8,
        'tolerancia-cero.historial' => 9,
        'operarios.index' => 10,
        'operarios-dia.index' => 11,
        'puestos_trabajo.index' => 12,
        'titulacion-acido-lactico' => 13,
        'consumo-acido-lactico' => 14,
        'usuarios.index' => 15,
        'tiempo-usabilidad' => 16,
    ];

    /** Estado anterior (sin slot fijo para verificacion-pcc entre 3 y 4). */
    protected array $ordenAnterior = [
        'dashboard.mensual' => 1,
        'dashboard' => 2,
        'indicadores.detalle-dia' => 3,
        'hallazgos.registrar' => 4,
        'tolerancia-cero.registrar' => 5,
        'animales.index' => 6,
        'hallazgos.historial' => 7,
        'tolerancia-cero.historial' => 8,
        'operarios.index' => 9,
        'operarios-dia.index' => 10,
        'puestos_trabajo.index' => 11,
        'titulacion-acido-lactico' => 12,
        'consumo-acido-lactico' => 13,
        'usuarios.index' => 14,
        'tiempo-usabilidad' => 15,
    ];

    public function up(): void
    {
        MenuModulo::query()->updateOrCreate(
            ['vista' => 'verificacion-pcc'],
            [
                'nombre' => 'Verificación PCC',
                'icono' => 'shield-check',
                'orden' => 4,
                'roles' => ['OPERACIONES', 'CALIDAD', 'ADMINISTRADOR', 'GERENCIA'],
            ]
        );

        foreach ($this->ordenSidebar as $vista => $orden) {
            MenuModulo::query()->where('vista', $vista)->update(['orden' => $orden]);
        }
    }

    public function down(): void
    {
        foreach ($this->ordenAnterior as $vista => $orden) {
            MenuModulo::query()->where('vista', $vista)->update(['orden' => $orden]);
        }

        $max = (int) (MenuModulo::query()->where('vista', '!=', 'verificacion-pcc')->max('orden') ?? 0);
        MenuModulo::query()->where('vista', 'verificacion-pcc')->update(['orden' => $max + 1]);
    }
};
