<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware('guest')->group(function () {
    Volt::route('register', 'pages.auth.register')
        ->name('register');

    Volt::route('login', 'pages.auth.login')
        ->name('login');

    Volt::route('forgot-password', 'pages.auth.forgot-password')
        ->name('password.request');

    Volt::route('reset-password/{token}', 'pages.auth.reset-password')
        ->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Volt::route('verify-email', 'pages.auth.verify-email')
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Volt::route('confirm-password', 'pages.auth.confirm-password')
        ->name('password.confirm');
    
    // Ruta de logout
    Route::post('logout', function () {
        $sesionId = request()->session()->get('sesion_usuario_id');
        if ($sesionId) {
            $sesion = \App\Models\SesionUsuario::find($sesionId);
            if ($sesion && !$sesion->logout_at) {
                $sesion->update([
                    'logout_at' => now(),
                    'ultima_actividad' => now(),
                    'duracion_minutos' => (int) round($sesion->login_at->diffInSeconds(now()) / 60),
                ]);
            }
        }

        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/');
    })->name('logout');
});