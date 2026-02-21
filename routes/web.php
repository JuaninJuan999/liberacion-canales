<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HallazgoController;
use App\Http\Controllers\OperarioController;
use App\Http\Controllers\AnimalesController;
use App\Http\Controllers\IndicadorController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\PuestoTrabajoController; // Importar el nuevo controlador
use App\Livewire\RegistroHallazgo;
use App\Livewire\HistorialRegistros;
use App\Livewire\DashboardDia;
use App\Livewire\DashboardMes;
use App\Livewire\IndicadoresDia;
use App\Livewire\GestionOperariosDia;
use App\Livewire\AsignacionOperarios;

// Rutas públicas (sin autenticación)
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Rutas autenticadas (requieren login)
Route::middleware(['auth'])->group(function () {
    
    // Perfil de Usuario
    Route::get('/profile', [UsuarioController::class, 'profile'])->name('profile');
    Route::get('/profile/edit', [UsuarioController::class, 'editProfile'])->name('profile.edit');
    Route::put('/profile', [UsuarioController::class, 'updateProfile'])->name('profile.update');
    
    // Dashboard principal
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    
    // Dashboard mensual
    Route::get('/dashboard/mensual', [DashboardController::class, 'mensual'])->name('dashboard.mensual');
    
    // Gestión de Operarios
    Route::get('/operarios/dia', GestionOperariosDia::class)->name('operarios-dia.index');
    Route::get('/operarios/gestion-dia', GestionOperariosDia::class)->name('operarios.gestion-dia');
    Route::get('/operarios/asignacion', AsignacionOperarios::class)->name('operarios.asignacion');
    Route::resource('operarios', OperarioController::class);
    Route::patch('/operarios/{operario}/toggle-estado', [OperarioController::class, 'toggleEstado'])->name('operarios.toggle-estado');

    
    // Registro y Historial de Hallazgos
    Route::get('/hallazgos/registrar', RegistroHallazgo::class)->name('hallazgos.registrar');
    Route::get('/hallazgos/historial', HistorialRegistros::class)->name('hallazgos.historial');
    Route::resource('hallazgos', HallazgoController::class);
    
    // Animales Procesados
    Route::resource('animales', AnimalesController::class);
    
    // Dashboards e Indicadores
    Route::get('/indicadores/dia', DashboardDia::class)->name('indicadores.dia');
    Route::get('/indicadores/mes', DashboardMes::class)->name('indicadores.mes');
    Route::get('/indicadores/detalle-dia', IndicadoresDia::class)->name('indicadores.detalle-dia');
    Route::get('/indicadores', [IndicadorController::class, 'indicadoresDia'])->name('indicadores.index');
    Route::get('/indicadores/{fecha}', [IndicadorController::class, 'indicadoresDia'])->name('indicadores.dia.fecha');
    
    // Gestión de Usuarios y Catálogos (Solo Admin)
    Route::middleware('admin')->group(function () {
        Route::resource('usuarios', UsuarioController::class);
        // Nueva ruta para Puestos de Trabajo
        Route::resource('puestos_trabajo', PuestoTrabajoController::class)->except(['show']);
    });
    
    // Reportes y Exportaciones
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::get('/reportes/diario/{fecha}', [ReporteController::class, 'diario'])->name('reportes.diario');
    Route::get('/reportes/mensual/{mes}/{anio}', [ReporteController::class, 'mensualPdf'])->name('reportes.mensual');
    Route::get('/reportes/mensual/{mes}/{anio}/excel', [ReporteController::class, 'mensualExcel'])->name('reportes.mensual.excel');

});

// Rutas de API (para AJAX y componentes)
Route::prefix('api')->middleware(['auth'])->group(function () {
    Route::get('/indicadores/graficos', [IndicadorController::class, 'datosGraficos'])->name('api.indicadores.graficos');
    Route::post('/indicadores/recalcular', [IndicadorController::class, 'recalcular'])->name('api.indicadores.recalcular');
    Route::get('/usuarios/activos', [UsuarioController::class, 'activos'])->name('api.usuarios.activos');
    Route::post('/usuarios/{usuario}/toggle-activo', [UsuarioController::class, 'toggleActivo'])->name('api.usuarios.toggle');
});

// Exportaciones de reportes
Route::middleware(['auth'])->group(function () {
    Route::get('/exportar/hallazgos', [ReporteController::class, 'exportarHallazgos'])->name('exportar.hallazgos');
    Route::get('/exportar/indicadores', [IndicadorController::class, 'exportarExcel'])->name('exportar.indicadores');
});

require __DIR__.'/auth.php';