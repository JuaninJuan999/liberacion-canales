<?php

use App\Models\MenuModulo;
use Illuminate\Database\Migrations\Migration;

/**
 * Ajuste de etiquetas del menú: orden ya definido; solo texto legible (no todo en mayúsculas).
 */
return new class extends Migration
{
    /** @var array<string, string> vista => nombre */
    protected array $nombresLegibles = [
        'dashboard.mensual' => 'Dashboard Mensual',
        'dashboard' => 'Dashboard Diario',
        'indicadores.detalle-dia' => 'Indicadores por día',
        'hallazgos.registrar' => 'Registros de hallazgos',
        'tolerancia-cero.registrar' => 'Registros de hallazgos TC',
        'animales.index' => 'Animales procesados',
        'hallazgos.historial' => 'Historial de registros',
        'tolerancia-cero.historial' => 'Historial de registros TC',
        'operarios.index' => 'Catálogo de operarios',
        'operarios-dia.index' => 'Gestión de operarios',
        'puestos_trabajo.index' => 'Puestos de trabajo',
        'titulacion-acido-lactico' => 'Titulación de ácido láctico',
        'consumo-acido-lactico' => 'Consumo de ácido láctico',
        'usuarios.index' => 'Gestión de usuarios',
        'tiempo-usabilidad' => 'Tiempo de usabilidad',
    ];

    /** Mayúsculas (estado anterior a esta corrección). */
    protected array $nombresMayusculas = [
        'dashboard.mensual' => 'DASHBOARD MENSUAL',
        'dashboard' => 'DASHBOARD DIARIO',
        'indicadores.detalle-dia' => 'INDICADOR DIARIO',
        'hallazgos.registrar' => 'REGISTROS DE HALLAZGOS',
        'tolerancia-cero.registrar' => 'REGISTROS DE HALLAZGOS TC',
        'animales.index' => 'ANIMALES PROCESADOS',
        'hallazgos.historial' => 'HISTORIAL DE REGISTROS',
        'tolerancia-cero.historial' => 'HISTORIAL DE REGISTROS TC',
        'operarios.index' => 'CATALOGO DE OPERARIOS',
        'operarios-dia.index' => 'GESTION DE OPERARIOS',
        'puestos_trabajo.index' => 'PUESTOS DE TRABAJO',
        'titulacion-acido-lactico' => 'TITULACION DE ACIDO LACTICO',
        'consumo-acido-lactico' => 'CONSUMO DE ACIDO LACTICO',
        'usuarios.index' => 'GESTION DE USUARIOS',
        'tiempo-usabilidad' => 'TIEMPO DE USABILIDAD',
    ];

    public function up(): void
    {
        foreach ($this->nombresLegibles as $vista => $nombre) {
            MenuModulo::query()->where('vista', $vista)->update(['nombre' => $nombre]);
        }
    }

    public function down(): void
    {
        foreach ($this->nombresMayusculas as $vista => $nombre) {
            MenuModulo::query()->where('vista', $vista)->update(['nombre' => $nombre]);
        }
    }
};
