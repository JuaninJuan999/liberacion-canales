<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HallazgoController;
use App\Http\Controllers\OperarioController;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Rutas protegidas por login
Route::middleware(['auth'])->group(function () {
    // Hallazgos
    Route::get('/hallazgos', [HallazgoController::class, 'index'])->name('hallazgos.index');
    Route::post('/hallazgos', [HallazgoController::class, 'store'])->name('hallazgos.store');
    
    // Operarios
    Route::resource('operarios', OperarioController::class);
    Route::patch('/operarios/{operario}/toggle-estado', [OperarioController::class, 'toggleEstado'])
        ->name('operarios.toggle-estado');
});

require __DIR__ . '/auth.php';
