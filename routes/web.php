<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HallazgoController;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Rutas protegidas por login
Route::middleware(['auth'])->group(function () {
    Route::get('/hallazgos', [HallazgoController::class, 'index'])->name('hallazgos.index');
    Route::post('/hallazgos', [HallazgoController::class, 'store'])->name('hallazgos.store'); // ← Esta línea
});

require __DIR__ . '/auth.php';
