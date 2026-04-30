<?php

namespace Database\Seeders;

use App\Models\MenuModulo;
use Illuminate\Database\Seeder;

class MenuModuloSeeder extends Seeder
{
    public function run(): void
    {
        $modulos = [
            [
                'nombre' => 'Dashboard Mensual',
                'vista' => 'dashboard.mensual',
                'icono' => 'presentation-chart-line',
                'orden' => 1,
                'roles' => ['OPERACIONES', 'CALIDAD', 'ADMINISTRADOR', 'GERENCIA'],
            ],
            [
                'nombre' => 'Dashboard Diario',
                'vista' => 'dashboard',
                'icono' => 'chart-pie',
                'orden' => 2,
                'roles' => ['OPERACIONES', 'CALIDAD', 'ADMINISTRADOR', 'GERENCIA'],
            ],
            [
                'nombre' => 'Indicadores por día',
                'vista' => 'indicadores.detalle-dia',
                'icono' => 'calendar',
                'orden' => 3,
                'roles' => ['OPERACIONES', 'CALIDAD', 'ADMINISTRADOR', 'GERENCIA'],
            ],
            [
                'nombre' => 'Verificación PCC',
                'vista' => 'verificacion-pcc',
                'icono' => 'shield-check',
                'orden' => 4,
                'roles' => ['OPERACIONES', 'CALIDAD', 'ADMINISTRADOR', 'GERENCIA'],
            ],
            [
                'nombre' => 'Registros de hallazgos',
                'vista' => 'hallazgos.registrar',
                'icono' => 'clipboard-document-check',
                'orden' => 5,
                'roles' => ['CALIDAD', 'ADMINISTRADOR'],
            ],
            [
                'nombre' => 'Registros de hallazgos TC',
                'vista' => 'tolerancia-cero.registrar',
                'icono' => 'exclamation-triangle',
                'orden' => 6,
                'roles' => ['ADMINISTRADOR', 'OPERACIONES'],
            ],
            [
                'nombre' => 'Animales procesados',
                'vista' => 'animales.index',
                'icono' => 'document-stack',
                'orden' => 7,
                'roles' => ['OPERACIONES', 'CALIDAD', 'ADMINISTRADOR', 'GERENCIA'],
            ],
            [
                'nombre' => 'Historial de registros',
                'vista' => 'hallazgos.historial',
                'icono' => 'document-text',
                'orden' => 8,
                'roles' => ['OPERACIONES', 'CALIDAD', 'ADMINISTRADOR', 'GERENCIA'],
            ],
            [
                'nombre' => 'Historial de registros TC',
                'vista' => 'tolerancia-cero.historial',
                'icono' => 'clock',
                'orden' => 9,
                'roles' => ['ADMINISTRADOR', 'OPERACIONES', 'CALIDAD', 'GERENCIA'],
            ],
            [
                'nombre' => 'Catálogo de operarios',
                'vista' => 'operarios.index',
                'icono' => 'users-circle',
                'orden' => 10,
                'roles' => ['OPERACIONES', 'ADMINISTRADOR'],
            ],
            [
                'nombre' => 'Gestión de operarios',
                'vista' => 'operarios-dia.index',
                'icono' => 'users',
                'orden' => 11,
                'roles' => ['OPERACIONES', 'ADMINISTRADOR'],
            ],
            [
                'nombre' => 'Puestos de trabajo',
                'vista' => 'puestos_trabajo.index',
                'icono' => 'briefcase',
                'orden' => 12,
                'roles' => ['ADMINISTRADOR'],
            ],
            [
                'nombre' => 'Titulación de ácido láctico',
                'vista' => 'titulacion-acido-lactico',
                'icono' => 'beaker',
                'orden' => 13,
                'roles' => ['OPERACIONES', 'CALIDAD', 'ADMINISTRADOR', 'GERENCIA'],
            ],
            [
                'nombre' => 'Consumo de ácido láctico',
                'vista' => 'consumo-acido-lactico',
                'icono' => 'chart-bar',
                'orden' => 14,
                'roles' => ['OPERACIONES', 'CALIDAD', 'ADMINISTRADOR', 'GERENCIA'],
            ],
            [
                'nombre' => 'Gestión de usuarios',
                'vista' => 'usuarios.index',
                'icono' => 'user-plus',
                'orden' => 15,
                'roles' => ['ADMINISTRADOR'],
            ],
            [
                'nombre' => 'Tiempo de usabilidad',
                'vista' => 'tiempo-usabilidad',
                'icono' => 'stopwatch',
                'orden' => 16,
                'roles' => ['ADMINISTRADOR'],
            ],
        ];

        foreach ($modulos as $modulo) {
            MenuModulo::updateOrCreate(
                ['vista' => $modulo['vista']],
                $modulo
            );
        }
    }
}
