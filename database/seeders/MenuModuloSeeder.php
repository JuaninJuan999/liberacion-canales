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
                'nombre' => 'Registro de Hallazgos',
                'vista' => 'hallazgos.registrar',
                'icono' => 'clipboard-document-check',
                'orden' => 1,
                'roles' => ['Calidad', 'Admin'],
            ],
            [
                'nombre' => 'Historial de Registros',
                'vista' => 'hallazgos.historial',
                'icono' => 'document-text',
                'orden' => 2,
                'roles' => ['Operaciones', 'Calidad', 'Admin', 'Gerencia'],
            ],
            [
                'nombre' => 'Indicadores por Día',
                'vista' => 'indicadores.detalle-dia',
                'icono' => 'calendar',
                'orden' => 3,
                'roles' => ['Operaciones', 'Calidad', 'Admin', 'Gerencia'],
            ],
            [
                'nombre' => 'Dashboard Diario',
                'vista' => 'dashboard',
                'icono' => 'chart-pie',
                'orden' => 4,
                'roles' => ['Operaciones', 'Calidad', 'Admin', 'Gerencia'],
            ],
            [
                'nombre' => 'Dashboard Mensual',
                'vista' => 'dashboard.mensual',
                'icono' => 'presentation-chart-line',
                'orden' => 5,
                'roles' => ['Operaciones', 'Calidad', 'Admin', 'Gerencia'],
            ],
            [
                'nombre' => 'Gestión de Operarios',
                'vista' => 'operarios-dia.index',
                'icono' => 'users',
                'orden' => 6,
                'roles' => ['Operaciones', 'Admin'],
            ],
            [
                'nombre' => 'Puestos de Trabajo',
                'vista' => 'puestos_trabajo.index',
                'icono' => 'briefcase',
                'orden' => 7,
                'roles' => ['Admin'],
            ],
            [
                'nombre' => 'Gestión de Usuarios',
                'vista' => 'usuarios.index',
                'icono' => 'user-plus',
                'orden' => 8,
                'roles' => ['Admin'],
            ],
        ];

        foreach ($modulos as $modulo) {
            MenuModulo::firstOrCreate(['nombre' => $modulo['nombre']], $modulo);
        }
    }
}
