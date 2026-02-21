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
                'vista' => 'hallazgos.registro',
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
                'nombre' => 'Ingreso Cantidad Animales',
                'vista' => 'animales.registro',
                'icono' => 'chart-bar',
                'orden' => 3,
                'roles' => ['Calidad', 'Admin'],
            ],
            [
                'nombre' => 'Indicadores por Día',
                'vista' => 'indicadores.diario',
                'icono' => 'calendar',
                'orden' => 4,
                'roles' => ['Operaciones', 'Calidad', 'Admin', 'Gerencia'],
            ],
            [
                'nombre' => 'Dashboard Diario',
                'vista' => 'dashboard.dia',
                'icono' => 'chart-pie',
                'orden' => 5,
                'roles' => ['Operaciones', 'Calidad', 'Admin', 'Gerencia'],
            ],
            [
                'nombre' => 'Dashboard Mensual',
                'vista' => 'dashboard.mes',
                'icono' => 'presentation-chart-line',
                'orden' => 6,
                'roles' => ['Operaciones', 'Calidad', 'Admin', 'Gerencia'],
            ],
            [
                'nombre' => 'Gestión de Operarios',
                'vista' => 'operarios.gestion',
                'icono' => 'users',
                'orden' => 7,
                'roles' => ['Operaciones', 'Admin'],
            ],
            // Módulo añadido
            [
                'nombre' => 'Puestos de Trabajo',
                'vista' => 'puestos_trabajo.index',
                'icono' => 'briefcase',
                'orden' => 7.5, 
                'roles' => ['Admin'],
            ],
            [
                'nombre' => 'Gestión de Usuarios',
                'vista' => 'usuarios.gestion',
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
