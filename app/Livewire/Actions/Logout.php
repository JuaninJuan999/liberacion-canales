<?php

namespace App\Livewire\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\SesionUsuario;

class Logout
{
    /**
     * Log the current user out of the application.
     */
    public function __invoke(): void
    {
        // Cerrar sesión de tracking
        $sesionId = Session::get('sesion_usuario_id');
        if ($sesionId) {
            $sesion = SesionUsuario::find($sesionId);
            if ($sesion && !$sesion->logout_at) {
                $sesion->update([
                    'logout_at' => now(),
                    'ultima_actividad' => now(),
                    'duracion_minutos' => round($sesion->login_at->diffInSeconds(now()) / 60, 2),
                ]);
            }
        }

        Auth::guard('web')->logout();

        Session::invalidate();
        Session::regenerateToken();
    }
}
