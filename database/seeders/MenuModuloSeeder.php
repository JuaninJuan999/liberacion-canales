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
                'roles' => ['CALIDAD', 'ADMINISTRADOR'],
            ],
            [
                'nombre' => 'Historial de Registros',
                'vista' => 'hallazgos.historial',
                'icono' => 'document-text',
                'orden' => 2,
                'roles' => ['OPERACIONES', 'CALIDAD', 'ADMINISTRADOR', 'GERENCIA'],
            ],
            [
                'nombre' => 'Indicadores por Día',
                'vista' => 'indicadores.detalle-dia',
                'icono' => 'calendar',
                'orden' => 3,
                'roles' => ['OPERACIONES', 'CALIDAD', 'ADMINISTRADOR', 'GERENCIA'],
            ],
            [
                'nombre' => 'Dashboard Diario',
                'vista' => 'dashboard',
                'icono' => 'chart-pie',
                'orden' => 4,
                'roles' => ['OPERACIONES', 'CALIDAD', 'ADMINISTRADOR', 'GERENCIA'],
            ],
            [
                'nombre' => 'Dashboard Mensual',
                'vista' => 'dashboard.mensual',
                'icono' => 'presentation-chart-line',
                'orden' => 5,
                'roles' => ['OPERACIONES', 'CALIDAD', 'ADMINISTRADOR', 'GERENCIA'],
            ],
            [
                'nombre' => 'Gestión de Operarios',
                'vista' => 'operarios-dia.index',
                'icono' => 'users',
                'orden' => 6,
                'roles' => ['OPERACIONES', 'ADMINISTRADOR'],
            ],
            [
                'nombre' => 'Puestos de Trabajo',
                'vista' => 'puestos_trabajo.index',
                'icono' => 'briefcase',
                'orden' => 7,
                'roles' => ['ADMINISTRADOR'],
            ],
            [
                'nombre' => 'Gestión de Usuarios',
                'vista' => 'usuarios.index',
                'icono' => 'user-plus',
                'orden' => 8,
                'roles' => ['ADMINISTRADOR'],
            ],
            [
                'nombre' => 'Hallazgos Tolerancia Cero',
                'vista' => 'tolerancia-cero.registrar',
                'icono' => 'exclamation-triangle',
                'orden' => 9,
                'roles' => ['ADMINISTRADOR', 'OPERACIONES'],
            ],
            [
                'nombre' => 'Historial Registros TC',
                'vista' => 'tolerancia-cero.historial',
                'icono' => 'clock',
                'orden' => 10,
                'roles' => ['ADMINISTRADOR', 'OPERACIONES', 'CALIDAD', 'GERENCIA'],
            ],
            [
                'nombre' => 'Animales Procesados',
                'vista' => 'animales.index',
                'icono' => 'document-stack',
                'orden' => 11,
                'roles' => ['OPERACIONES', 'CALIDAD', 'ADMINISTRADOR', 'GERENCIA'],
            ],
            [
                'nombre' => 'Catálogo de Operarios',
                'vista' => 'operarios.index',
                'icono' => 'users-circle',
                'orden' => 12,
                'roles' => ['OPERACIONES', 'ADMINISTRADOR'],
            ],
            [
                'nombre' => 'Tiempo de Usabilidad',
                'vista' => 'tiempo-usabilidad',
                'icono' => 'stopwatch',
                'orden' => 13,
                'roles' => ['ADMINISTRADOR'],
            ],
            [
                'nombre' => 'Titulación de Ácido Láctico',
                'vista' => 'titulacion-acido-lactico',
                'icono' => 'beaker',
                'orden' => 14,
                'roles' => ['OPERACIONES', 'CALIDAD', 'ADMINISTRADOR', 'GERENCIA'],
            ],
            [
                'nombre' => 'Consumo de Ácido Láctico',
                'vista' => 'consumo-acido-lactico',
                'icono' => 'chart-bar',
                'orden' => 15,
                'roles' => ['OPERACIONES', 'CALIDAD', 'ADMINISTRADOR', 'GERENCIA'],
            ],
        ];

        foreach ($modulos as $modulo) {
            MenuModulo::updateOrCreate(
                ['nombre' => $modulo['nombre']],
                $modulo
            );
        }
    }
}
