<?php

use App\Models\MenuModulo;
use Illuminate\Database\Migrations\Migration;

/**
 * Orden lateral solicitado (tras SUIT PRINCIPAL fijo): dashboards → indicador → hallazgos TC/animal/historiales → catálogo/gestión → ácidos → usuarios → tiempo.
 */
return new class extends Migration
{
    /** @var array<string, array{nombre: string, orden: int}> */
    protected array $anteriorPorVista = [
        'hallazgos.registrar' => ['nombre' => 'Registro de Hallazgos', 'orden' => 1],
        'hallazgos.historial' => ['nombre' => 'Historial de Registros', 'orden' => 2],
        'indicadores.detalle-dia' => ['nombre' => 'Indicadores por Día', 'orden' => 3],
        'dashboard' => ['nombre' => 'Dashboard Diario', 'orden' => 4],
        'dashboard.mensual' => ['nombre' => 'Dashboard Mensual', 'orden' => 5],
        'operarios-dia.index' => ['nombre' => 'Gestión de Operarios', 'orden' => 6],
        'puestos_trabajo.index' => ['nombre' => 'Puestos de Trabajo', 'orden' => 7],
        'usuarios.index' => ['nombre' => 'Gestión de Usuarios', 'orden' => 8],
        'tolerancia-cero.registrar' => ['nombre' => 'Hallazgos Tolerancia Cero', 'orden' => 9],
        'tolerancia-cero.historial' => ['nombre' => 'Historial Registros TC', 'orden' => 10],
        'animales.index' => ['nombre' => 'Animales Procesados', 'orden' => 11],
        'operarios.index' => ['nombre' => 'Catálogo de Operarios', 'orden' => 12],
        'tiempo-usabilidad' => ['nombre' => 'Tiempo de Usabilidad', 'orden' => 13],
        'titulacion-acido-lactico' => ['nombre' => 'Titulación de Ácido Láctico', 'orden' => 14],
        'consumo-acido-lactico' => ['nombre' => 'Consumo de Ácido Láctico', 'orden' => 15],
    ];

    /** @var array<string, array{nombre: string, orden: int}> */
    protected array $nuevoPorVista = [
        'dashboard.mensual' => ['nombre' => 'DASHBOARD MENSUAL', 'orden' => 1],
        'dashboard' => ['nombre' => 'DASHBOARD DIARIO', 'orden' => 2],
        'indicadores.detalle-dia' => ['nombre' => 'INDICADOR DIARIO', 'orden' => 3],
        'hallazgos.registrar' => ['nombre' => 'REGISTROS DE HALLAZGOS', 'orden' => 4],
        'tolerancia-cero.registrar' => ['nombre' => 'REGISTROS DE HALLAZGOS TC', 'orden' => 5],
        'animales.index' => ['nombre' => 'ANIMALES PROCESADOS', 'orden' => 6],
        'hallazgos.historial' => ['nombre' => 'HISTORIAL DE REGISTROS', 'orden' => 7],
        'tolerancia-cero.historial' => ['nombre' => 'HISTORIAL DE REGISTROS TC', 'orden' => 8],
        'operarios.index' => ['nombre' => 'CATALOGO DE OPERARIOS', 'orden' => 9],
        'operarios-dia.index' => ['nombre' => 'GESTION DE OPERARIOS', 'orden' => 10],
        'puestos_trabajo.index' => ['nombre' => 'PUESTOS DE TRABAJO', 'orden' => 11],
        'titulacion-acido-lactico' => ['nombre' => 'TITULACION DE ACIDO LACTICO', 'orden' => 12],
        'consumo-acido-lactico' => ['nombre' => 'CONSUMO DE ACIDO LACTICO', 'orden' => 13],
        'usuarios.index' => ['nombre' => 'GESTION DE USUARIOS', 'orden' => 14],
        'tiempo-usabilidad' => ['nombre' => 'TIEMPO DE USABILIDAD', 'orden' => 15],
    ];

    public function up(): void
    {
        foreach ($this->nuevoPorVista as $vista => $attrs) {
            MenuModulo::query()->where('vista', $vista)->update($attrs);
        }
    }

    public function down(): void
    {
        foreach ($this->anteriorPorVista as $vista => $attrs) {
            MenuModulo::query()->where('vista', $vista)->update($attrs);
        }
    }
};
